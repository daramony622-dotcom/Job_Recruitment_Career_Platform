import { ref } from 'vue'

const currentLang = ref('en')

let widgetInitialized = false
let initializationStarted = false
let initializationPromise = null

function readGoogleLanguage() {
  const match = document.cookie.match(/(?:^|;\s*)googtrans=([^;]+)/)

  if (!match) {
    return 'en'
  }

  const value = decodeURIComponent(match[1])
  const language = value.split('/').pop()
  return language && language !== 'en' ? language : 'en'
}

function syncCurrentLanguage() {
  currentLang.value = readGoogleLanguage()
}

function initializeWidget() {
  if (widgetInitialized || !window.google?.translate?.TranslateElement) {
    return
  }

  const host = document.getElementById('google_translate_element')
  if (!host) {
    return
  }

  new window.google.translate.TranslateElement(
    {
      pageLanguage: 'en',
      includedLanguages: 'en,km',
      autoDisplay: false,
      multilanguagePage: true
    },
    'google_translate_element'
  )

  widgetInitialized = true
  window.setTimeout(syncCurrentLanguage, 300)
}

function loadGoogleTranslateScript() {
  if (initializationPromise) {
    return initializationPromise
  }

  initializationStarted = true
  initializationPromise = new Promise((resolve) => {
    window.googleTranslateElementInit = () => {
      initializeWidget()
      resolve()
    }

    const existingScript = document.getElementById('google-translate-script')
    if (existingScript) {
      if (window.google?.translate?.TranslateElement) {
        initializeWidget()
        resolve()
      }
      return
    }

    const script = document.createElement('script')
    script.id = 'google-translate-script'
    script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit'
    script.async = true
    script.onerror = () => resolve()
    document.head.appendChild(script)
  })

  return initializationPromise
}

export function ensureGoogleTranslate() {
  syncCurrentLanguage()

  if (!initializationStarted) {
    loadGoogleTranslateScript()
  } else {
    initializeWidget()
  }

  return initializationPromise
}

export function setGoogleLanguage(language) {
  const targetLanguage = language === 'km' ? 'km' : 'en'
  currentLang.value = targetLanguage

  const selectLanguage = (attempt = 0) => {
    const select = document.querySelector('.goog-te-combo')

    if (select) {
      select.value = targetLanguage
      select.dispatchEvent(new Event('change'))
      window.setTimeout(syncCurrentLanguage, 500)
      return
    }

    if (attempt < 20) {
      window.setTimeout(() => selectLanguage(attempt + 1), 100)
    }
  }

  ensureGoogleTranslate()
  selectLanguage()
}

export function useGoogleTranslate() {
  return {
    currentLang,
    ensureGoogleTranslate,
    setGoogleLanguage
  }
}
