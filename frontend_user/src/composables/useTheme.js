/**
 * Dark / Light Theme Composable
 * Supports single click toggle button with localStorage persistence
 */
import { ref, computed } from 'vue'

const THEME_KEY = 'theme'
const isDark = ref(false)

function calculateInitialTheme() {
  const saved = localStorage.getItem(THEME_KEY)
  if (saved === 'dark' || saved === 'light') {
    return saved === 'dark'
  }
  return window.matchMedia('(prefers-color-scheme: dark)').matches
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
