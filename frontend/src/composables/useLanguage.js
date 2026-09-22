/**
 * Language Selector Composable (EN / KH)
 *
 * Thin wrapper over useGoogleTranslate so there is ONE source of truth
 * (the googtrans cookie). The old localStorage "language" key is no longer
 * needed and can be removed.
 */
import { computed } from 'vue'
import { useGoogleTranslate, setGoogleLanguage } from './useGoogleTranslate'

const languages = [
  { code: 'EN', label: 'English', flag: 'gb' },
  { code: 'KH', label: 'Khmer (ភាសាខ្មែរ)', flag: 'kh' }
]

const TO_GOOGLE = { EN: 'en', KH: 'km' }
const FROM_GOOGLE = { en: 'EN', km: 'KH' }

export function setLanguage(code) {
  return setGoogleLanguage(TO_GOOGLE[code] || 'en')
}

export function useLanguage() {
  const { currentLang: googleLang, isTranslating } = useGoogleTranslate()

  const currentLang = computed(() => FROM_GOOGLE[googleLang.value] || 'EN')

  const activeLang = computed(
    () => languages.find((l) => l.code === currentLang.value) || languages[0]
  )

  return {
    currentLang,
    activeLang,
    languages,
    isTranslating,
    setLanguage
  }
}