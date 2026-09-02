/**
 * Language Selector Composable (EN / KH)
 */
import { ref, computed } from 'vue'

const LANG_KEY = 'language'
const currentLang = ref(localStorage.getItem(LANG_KEY) || 'EN')

const languages = [
  { code: 'EN', label: 'English', flag: 'gb' },
  { code: 'KH', label: 'Khmer (ភាសាខ្មែរ)', flag: 'kh' }
]

export function setLanguage(code) {
  currentLang.value = code
  localStorage.setItem(LANG_KEY, code)
}

export function useLanguage() {
  const activeLang = computed(() => {
    return languages.find(l => l.code === currentLang.value) || languages[0]
  })

  return {
    currentLang: computed(() => currentLang.value),
    activeLang,
    languages,
    setLanguage
  }
}
