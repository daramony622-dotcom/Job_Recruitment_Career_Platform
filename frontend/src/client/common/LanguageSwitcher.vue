<template>
  <div class="language-switcher relative inline-block">
    <button
      type="button"
      @click="menuOpen = !menuOpen"
      class="language-control cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
      :aria-expanded="menuOpen"
      aria-haspopup="listbox"
      aria-label="Choose language"
    >
      <FlagIcon :country="activeLanguage.country" />
      <span>{{ activeLanguage.label }}</span>
      <ChevronDown class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="menuOpen ? 'rotate-180' : ''" />
    </button>

    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0 -translate-y-1 scale-95"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="opacity-100 translate-y-0 scale-100"
      leave-to-class="opacity-0 -translate-y-1 scale-95"
    >
      <div
        v-if="menuOpen"
        class="language-menu absolute right-0 z-50"
        role="menu"
        aria-label="Language options"
      >
        <button
          v-for="language in languages"
          :key="language.code"
          type="button"
          role="menuitem"
          @click="selectLanguage(language.code)"
          class="language-option"
          :class="{ 'is-active': currentLang === language.code }"
        >
          <FlagIcon :country="language.country" />
          <span class="flex-1">{{ language.label }}</span>
          <Check v-if="currentLang === language.code" class="w-3.5 h-3.5" />
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { Check, ChevronDown } from 'lucide-vue-next'
import { useGoogleTranslate } from '../../composables/useGoogleTranslate'
import FlagIcon from '../FlagIcon.vue'

const { currentLang, ensureGoogleTranslate, setGoogleLanguage } = useGoogleTranslate()
const menuOpen = ref(false)
const languages = [
  { code: 'en', label: 'English', country: 'usa' },
  { code: 'km', label: 'ខ្មែរ', country: 'cambodia' }
]

const activeLanguage = computed(() => {
  return languages.find((language) => language.code === currentLang.value) || languages[0]
})

const selectLanguage = (language) => {
  setGoogleLanguage(language)
  menuOpen.value = false
}

const closeMenu = (event) => {
  if (!event.target.closest('.language-switcher')) {
    menuOpen.value = false
  }
}

onMounted(() => {
  if (currentLang.value === 'km') {
    ensureGoogleTranslate()
  }
  document.addEventListener('click', closeMenu)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', closeMenu)
})
</script>
