<script setup>
import { ref } from 'vue'
import { ChevronDown } from 'lucide-vue-next'
import { useLanguage } from '../../composables/useLanguage'

const { activeLang, languages, setLanguage } = useLanguage()
const isOpen = ref(false)

const selectLang = (code) => {
  setLanguage(code)
  isOpen.value = false
}
</script>

<template>
  <div class="relative">
    <button
      @click="isOpen = !isOpen"
      type="button"
      class="flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition duration-200 text-xs font-bold focus:outline-none cursor-pointer border border-transparent hover:border-slate-200 dark:hover:border-slate-700"
    >
      <!-- UK Flag -->
      <svg v-if="activeLang.flag === 'gb'" class="w-4 h-3 rounded-xs shadow-2xs" viewBox="0 0 640 480">
        <path fill="#012169" d="M0 0h640v480H0z" />
        <path fill="#FFF" d="m75 0 245 184L565 0h75v55L435 240l205 154v56h-75L320 296 75 480H0v-56l205-154L0 55V0h75z" />
        <path fill="#C8102E" d="m424 286 216 162v32h-43L371 316l53-30zm-208 28L0 476v-32l216-162-16 32zM640 0v11L444 158l16-32L612 0h28zM0 0v11l196 147-16-32L28 0H0z" />
        <path fill="#FFF" d="M240 0h160v480H240zM0 160h640v160H0z" />
        <path fill="#C8102E" d="M267 0h106v480H267zM0 187h640v106H0z" />
      </svg>
      <!-- Cambodia Flag -->
      <svg v-else class="w-4 h-3 rounded-xs shadow-2xs" viewBox="0 0 640 480">
        <path fill="#032ea1" d="M0 0h640v480H0z" />
        <path fill="#e00025" d="M0 120h640v240H0z" />
        <path fill="#fff" d="M320 180c-5.5 0-10 4.5-10 10v10h-30v-10c0-5.5-4.5-10-10-10s-10 4.5-10 10v10h-20v20h160v-20h-20v-10c0-5.5-4.5-10-10-10zm-70 50h140v50H250zm-10 60h160v10H240z" />
      </svg>

      <span>{{ activeLang.code }}</span>
      <ChevronDown class="w-3.5 h-3.5 text-slate-400 transition-transform" :class="{ 'rotate-180': isOpen }" />
    </button>

    <!-- Dropdown Menu -->
    <div 
      v-if="isOpen" 
      class="absolute right-0 mt-2 w-44 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl py-1.5 z-50 overflow-hidden space-y-0.5"
    >
      <button
        v-for="lang in languages"
        :key="lang.code"
        @click="selectLang(lang.code)"
        class="w-full flex items-center gap-2.5 px-3.5 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800 transition text-xs font-semibold text-slate-700 dark:text-slate-200 cursor-pointer"
        :class="{ 'text-blue-600 dark:text-blue-400 bg-blue-50/60 dark:bg-blue-950/40': activeLang.code === lang.code }"
      >
        <svg v-if="lang.flag === 'gb'" class="w-4 h-3 rounded-xs shadow-2xs" viewBox="0 0 640 480">
          <path fill="#012169" d="M0 0h640v480H0z" />
          <path fill="#FFF" d="m75 0 245 184L565 0h75v55L435 240l205 154v56h-75L320 296 75 480H0v-56l205-154L0 55V0h75z" />
          <path fill="#C8102E" d="m424 286 216 162v32h-43L371 316l53-30zm-208 28L0 476v-32l216-162-16 32zM640 0v11L444 158l16-32L612 0h28zM0 0v11l196 147-16-32L28 0H0z" />
          <path fill="#FFF" d="M240 0h160v480H240zM0 160h640v160H0z" />
          <path fill="#C8102E" d="M267 0h106v480H267zM0 187h640v106H0z" />
        </svg>
        <svg v-else class="w-4 h-3 rounded-xs shadow-2xs" viewBox="0 0 640 480">
          <path fill="#032ea1" d="M0 0h640v480H0z" />
          <path fill="#e00025" d="M0 120h640v240H0z" />
          <path fill="#fff" d="M320 180c-5.5 0-10 4.5-10 10v10h-30v-10c0-5.5-4.5-10-10-10s-10 4.5-10 10v10h-20v20h160v-20h-20v-10c0-5.5-4.5-10-10-10zm-70 50h140v50H250zm-10 60h160v10H240z" />
        </svg>
        <span>{{ lang.label }}</span>
      </button>
    </div>
  </div>
</template>
