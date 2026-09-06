/**
 * Dark / Light Theme Composable
 * Supports single click toggle button with localStorage persistence
 */
import { ref, computed } from 'vue'

const THEME_KEY = 'theme'
const isDark = ref(false)

function calculateInitialTheme() {
  if (typeof window === 'undefined') return false

  const storedTheme = window.localStorage.getItem(THEME_KEY)
  if (storedTheme === 'dark') return true
  if (storedTheme === 'light') return false

  return window.matchMedia?.('(prefers-color-scheme: dark)').matches ?? false
}

function updateDOM(dark) {
  const root = document.documentElement
  if (dark) {
    root.classList.add('dark')
  } else {
    root.classList.remove('dark')
  }
}

export function initTheme() {
  const dark = calculateInitialTheme()
  isDark.value = dark
  updateDOM(dark)
  if (typeof window !== 'undefined') {
    window.localStorage.setItem(THEME_KEY, dark ? 'dark' : 'light')
  }
  return dark
}

export function toggleTheme() {
  isDark.value = !isDark.value
  const newTheme = isDark.value ? 'dark' : 'light'
  localStorage.setItem(THEME_KEY, newTheme)
  updateDOM(isDark.value)
  return newTheme
}

export function useTheme() {
  return {
    isDark: computed(() => isDark.value),
    toggleTheme,
    initTheme
  }
}
