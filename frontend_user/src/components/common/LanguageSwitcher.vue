<template>
  <div class="language-switcher relative inline-block">
    <button
      type="button"
      @click="menuOpen = !menuOpen"
      class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/80 dark:bg-slate-800/80 border border-slate-200/90 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-500/60 hover:bg-blue-50/60 dark:hover:bg-blue-950/50 transition-all duration-200 cursor-pointer text-slate-700 dark:text-slate-200 font-semibold text-xs shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
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
        class="absolute right-0 mt-2 w-44 rounded-2xl border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-900 p-1.5 shadow-xl shadow-slate-900/10 dark:shadow-black/30 z-50"
        role="listbox"
        aria-label="Language options"
      >
        <button
          v-for="language in languages"
          :key="language.code"
          type="button"
          role="option"
          :aria-selected="currentLang === language.code"
          @click="selectLanguage(language.code)"
          class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-left text-xs font-semibold transition-colors duration-150"
          :class="currentLang === language.code
            ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300'
            : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800'"
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
import FlagIcon from './FlagIcon.vue'

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
  ensureGoogleTranslate()
  document.addEventListener('click', closeMenu)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', closeMenu)
})
</script>
