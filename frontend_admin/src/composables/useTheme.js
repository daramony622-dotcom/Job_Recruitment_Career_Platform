import { computed, ref } from 'vue'

const THEME_KEY = 'theme'
const isDark = ref(false)

function getInitialTheme() {
  if (typeof window === 'undefined') return false

  const storedTheme = window.localStorage.getItem(THEME_KEY)
  if (storedTheme === 'dark') return true
  if (storedTheme === 'light') return false

  return window.matchMedia?.('(prefers-color-scheme: dark)').matches ?? false
}

function applyTheme(dark) {
  document.documentElement.classList.toggle('dark', dark)
}

export function initTheme() {
  const dark = getInitialTheme()
  isDark.value = dark
  applyTheme(dark)
  window.localStorage.setItem(THEME_KEY, dark ? 'dark' : 'light')
  return dark
}

export function toggleTheme() {
  isDark.value = !isDark.value
  applyTheme(isDark.value)
  window.localStorage.setItem(THEME_KEY, isDark.value ? 'dark' : 'light')
  return isDark.value
}

export function useTheme() {
  return {
    isDark: computed(() => isDark.value),
    initTheme,
    toggleTheme
  }
}
