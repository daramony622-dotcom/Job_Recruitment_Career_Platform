import { ref } from 'vue'

const SUPPORTED = ['en', 'km']
const SCRIPT_ID = 'google-translate-script'
const HOST_ID = 'google_translate_element'
const BUSY_CLASS = 'gt-busy'
const TIMEOUT_MS = 3000

let initPromise = null
let widgetReady = false
let observer = null
let pendingTarget = null
let busyTimer = null

/* ------------------------------------------------------------------ *
 * Vue + Google Translate crash fix
 * Google wraps text nodes in <font> tags. When Vue later tries to
 * remove/replace those nodes it throws "removeChild ... not a child
 * of this node" and the UI breaks. These guards make it safe.
 * ------------------------------------------------------------------ */

function patchDomForTranslate() {
  if (typeof Node !== 'function' || !Node.prototype || Node.prototype.__gtPatched) return

  const originalRemoveChild = Node.prototype.removeChild
  Node.prototype.removeChild = function (child) {
    if (child.parentNode !== this) return child
    return originalRemoveChild.apply(this, arguments)
  }

  const originalInsertBefore = Node.prototype.insertBefore
  Node.prototype.insertBefore = function (newNode, referenceNode) {
    if (referenceNode && referenceNode.parentNode !== this) return newNode
    return originalInsertBefore.apply(this, arguments)
  }

  Node.prototype.__gtPatched = true
}
patchDomForTranslate()

/* ------------------------------------------------------------------ *
 * Cookie helpers (googtrans is the single source of truth)
 * ------------------------------------------------------------------ */
function readGoogleLanguage() {
  if (typeof document === 'undefined') return 'en'
  const match = document.cookie.match(/(?:^|;\s*)googtrans=([^;]+)/)
  if (!match) return 'en'
  const lang = decodeURIComponent(match[1]).split('/').pop()
  return SUPPORTED.includes(lang) ? lang : 'en'
}

function cookieDomain() {
  const host = window.location.hostname
  const isIp = /^\d+\.\d+\.\d+\.\d+$/.test(host)
  return host && host !== 'localhost' && !isIp ? host : null
}

function writeCookie(lang) {
  const base = `googtrans=/en/${lang}; path=/; max-age=31536000; SameSite=Lax`
  document.cookie = base
  const domain = cookieDomain()
  if (domain) document.cookie = `${base}; domain=${domain}`
}

function clearCookie() {
  const expired = 'googtrans=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT'
  document.cookie = expired
  const domain = cookieDomain()
  if (domain) {
    document.cookie = `${expired}; domain=${domain}`
    document.cookie = `${expired}; domain=.${domain}`
  }
}

/* ------------------------------------------------------------------ *
 * Reactive state
 * ------------------------------------------------------------------ */
const currentLang = ref(readGoogleLanguage())
const isTranslating = ref(false)

function applyDocumentState(lang) {
  const root = document.documentElement
  root.lang = lang
  root.dataset.lang = lang // used by the CSS for Khmer line-height
}

/* ------------------------------------------------------------------ *
 * Transition state (lets the CSS fade the page while translating)
 * ------------------------------------------------------------------ */
function beginTransition(target) {
  pendingTarget = target
  isTranslating.value = true
  document.documentElement.classList.add(BUSY_CLASS)
  clearTimeout(busyTimer)
  busyTimer = setTimeout(endTransition, TIMEOUT_MS) // safety net
}

function endTransition() {
  clearTimeout(busyTimer)
  pendingTarget = null
  isTranslating.value = false
  document.documentElement.classList.remove(BUSY_CLASS)
}

// Google adds "translated-ltr" to <html> once the page has been translated.
function observeTranslation() {
  if (observer || typeof MutationObserver === 'undefined') return

  observer = new MutationObserver(() => {
    if (!pendingTarget) return
    const cl = document.documentElement.classList
    const translated = cl.contains('translated-ltr') || cl.contains('translated-rtl')
    if ((pendingTarget === 'km') === translated) {
      window.setTimeout(endTransition, 60) // let text nodes finish swapping
    }
  })

  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
  })
}

/* ------------------------------------------------------------------ *
 * Widget setup
 * ------------------------------------------------------------------ */
// Injected from JS so Google's banner and dropdown stay hidden even if the
// main stylesheet is missing, late, or broken.
const CRITICAL_CSS =
  '#google_translate_element,.goog-te-gadget,body>.skiptranslate,' +
  '.skiptranslate iframe,.goog-te-banner-frame,.goog-te-menu-frame,' +
  '.goog-te-balloon-frame,.goog-te-spinner-pos,.goog-tooltip,#goog-gt-tt,' +
  '.VIpgJd-ZVi9od-ORHb-OEVmcd,.VIpgJd-ZVi9od-aZ2wEe-wOHMyf' +
  '{display:none!important;visibility:hidden!important}' +
  'body{top:0!important}'

function injectCriticalStyle() {
  if (document.getElementById('gt-critical-style')) return
  const style = document.createElement('style')
  style.id = 'gt-critical-style'
  style.textContent = CRITICAL_CSS
  document.head.appendChild(style)
}

function ensureHost() {
  injectCriticalStyle()

  let host = document.getElementById(HOST_ID)
  if (!host) {
    host = document.createElement('div')
    host.id = HOST_ID
    document.body.appendChild(host)
  }
  // Also hide a host that already existed in index.html or a component
  host.style.display = 'none'
  host.setAttribute('translate', 'no')
  host.setAttribute('aria-hidden', 'true')
  return host
}

function initWidget() {
  if (widgetReady || !window.google?.translate?.TranslateElement) return

  ensureHost()
  new window.google.translate.TranslateElement(
    {
      pageLanguage: 'en',
      includedLanguages: 'en,km', // keep "en" so users can switch back
      autoDisplay: false,
      multilanguagePage: true
    },
    HOST_ID
  )
  widgetReady = true
}

function waitForCombo(tries = 130) {
  return new Promise((resolve) => {
    const check = (left) => {
      const select = document.querySelector('.goog-te-combo')
      if (select) return resolve(select)
      if (left <= 0) return resolve(null)
      window.setTimeout(() => check(left - 1), 30)
    }
    check(tries)
  })
}

export function ensureGoogleTranslate() {
  if (typeof document === 'undefined') return Promise.resolve()

  ensureHost()
  applyDocumentState(readGoogleLanguage())
  observeTranslation()

  if (!initPromise) {
    // Page loaded with Khmer saved: hide the English flash until translated
    if (readGoogleLanguage() === 'km') beginTransition('km')

    initPromise = new Promise((resolve) => {
      window.googleTranslateElementInit = () => {
        initWidget()
        resolve()
      }

      if (window.google?.translate?.TranslateElement) {
        initWidget()
        resolve()
        return
      }

      if (document.getElementById(SCRIPT_ID)) return

      const script = document.createElement('script')
      script.id = SCRIPT_ID
      script.src =
        'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit'
      script.async = true
      script.onerror = () => {
        endTransition()
        resolve()
      }
      document.head.appendChild(script)
    })
  }

  return initPromise
}

/* ------------------------------------------------------------------ *
 * Public API
 * ------------------------------------------------------------------ */
export async function setGoogleLanguage(language) {
  const target = SUPPORTED.includes(language) ? language : 'en'
  if (target === currentLang.value) return

  currentLang.value = target
  applyDocumentState(target)
  writeCookie(target)
  beginTransition(target)

  await ensureGoogleTranslate()
  const select = await waitForCombo()

  // Widget blocked or offline: the cookie is saved, so it still applies on next load
  if (!select) {
    endTransition()
    return
  }

  select.value = target

  if (select.value !== target) {
    // No matching option: restore the original page by clearing the cookie and reloading
    if (target === 'en') {
      clearCookie()
      window.location.reload()
    } else {
      endTransition()
    }
    return
  }

  select.dispatchEvent(new Event('change', { bubbles: true }))
}

// Call once after app.mount(): loads the Google widget while the browser is idle,
// so the first language switch doesn't have to download it.
export function preloadGoogleTranslate() {
  if (typeof window === 'undefined') return
  const run = () => ensureGoogleTranslate()
  if ('requestIdleCallback' in window) {
    window.requestIdleCallback(run, { timeout: 2000 })
  } else {
    window.setTimeout(run, 300)
  }
}

export function useGoogleTranslate() {
  return {
    currentLang,
    isTranslating,
    ensureGoogleTranslate,
    setGoogleLanguage
  }
}