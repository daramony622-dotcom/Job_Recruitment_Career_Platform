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
    aria-label="Toggle Theme"
    class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-slate-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer flex items-center justify-center"
    :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
  >
    <Sun v-if="isDark" class="w-5 h-5 text-amber-400 stroke-[2.2]" />
    <Moon v-else class="w-5 h-5 text-slate-600 stroke-[2.2]" />
  </button>
</template>
