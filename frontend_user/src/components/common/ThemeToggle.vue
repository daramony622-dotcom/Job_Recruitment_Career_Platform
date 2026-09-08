<script setup>
import { Sun, Moon } from 'lucide-vue-next'
import { useTheme } from '../../composables/useTheme'

const { isDark, toggleTheme } = useTheme()
</script>

<template>
  <button
    @click="toggleTheme"
    type="button"
    role="switch"
    :aria-checked="isDark"
    :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
    aria-label="Toggle theme"
    class="relative inline-flex h-10 w-10 shrink-0 cursor-pointer items-center justify-center rounded-xl border border-slate-200/90 bg-white/80 text-slate-600 shadow-sm backdrop-blur-sm transition-all duration-300 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-300 dark:hover:border-blue-500/60 dark:hover:bg-blue-950/50 dark:hover:text-blue-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
  >
    <Sun v-if="!isDark" class="h-4.5 w-4.5 text-amber-500 transition-transform duration-300" />
    <Moon v-else class="h-4.5 w-4.5 text-sky-400 transition-transform duration-300" />
  </button>
</template>

