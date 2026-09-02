<script setup>
import { onMounted } from 'vue'
import { Sun, Moon } from 'lucide-vue-next'
import { useTheme } from '../../composables/useTheme'

const { isDark, toggleTheme, initTheme } = useTheme()

onMounted(() => {
  initTheme()
})
</script>

<template>
  <button
    @click="toggleTheme"
    type="button"
    role="switch"
    :aria-checked="isDark"
    aria-label="Toggle Dark Mode"
    class="relative inline-flex h-8 w-14 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-slate-200 dark:bg-slate-700/80 transition-colors duration-300 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900 shadow-inner"
    :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
  >
    <span class="sr-only">Toggle dark mode</span>
    <Sun class="absolute left-1.5 top-1.5 h-4 w-4 text-amber-500 transition-opacity duration-200" :class="isDark ? 'opacity-30' : 'opacity-100'" />
    <Moon class="absolute right-1.5 top-1.5 h-4 w-4 text-indigo-400 dark:text-sky-300 transition-opacity duration-200" :class="isDark ? 'opacity-100' : 'opacity-30'" />
    <span
      class="pointer-events-none inline-block h-7 w-7 transform rounded-full bg-white dark:bg-slate-900 shadow-md ring-0 transition-transform duration-300 ease-out flex items-center justify-center border border-slate-100 dark:border-slate-800"
      :class="isDark ? 'translate-x-6' : 'translate-x-0'"
    >
      <Sun v-if="!isDark" class="h-4 w-4 text-amber-500 stroke-[2.5]" />
      <Moon v-else class="h-4 w-4 text-sky-400 stroke-[2.5]" />
    </span>
  </button>
</template>
