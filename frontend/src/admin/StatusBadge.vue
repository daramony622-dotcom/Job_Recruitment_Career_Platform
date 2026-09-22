<script setup>
import { computed } from 'vue'

const props = defineProps({
  value: { type: String, default: '' },
  type: { type: String, default: 'status' },
})

const defaultMap = {
  active: 'bg-blue-900/10 text-blue-800 dark:bg-blue-400/15 dark:text-blue-200 border-blue-800/25',
  published: 'bg-blue-900/10 text-blue-800 dark:bg-blue-400/15 dark:text-blue-200 border-blue-800/25',
  verified: 'bg-blue-900/10 text-blue-800 dark:bg-blue-400/15 dark:text-blue-200 border-blue-800/25',
  draft: 'bg-slate-500/15 text-slate-600 dark:text-slate-400 border-slate-500/30',
  pending: 'bg-blue-500/10 text-blue-700 dark:text-blue-300 border-blue-500/30',
  closed: 'bg-slate-500/15 text-slate-600 dark:text-slate-400 border-slate-500/30',
  expired: 'bg-slate-500/15 text-slate-600 dark:text-slate-400 border-slate-500/30',
  suspended: 'bg-slate-500/15 text-slate-600 dark:text-slate-400 border-slate-500/30',
  rejected: 'bg-slate-500/15 text-slate-600 dark:text-slate-400 border-slate-500/30',
  in_review: 'bg-blue-500/15 text-blue-600 dark:text-blue-400 border-blue-500/30',
  hired: 'bg-blue-900/10 text-blue-800 dark:bg-blue-400/15 dark:text-blue-200 border-blue-800/25',
  shortlisted: 'bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border-indigo-500/30',
  admin: 'bg-blue-900/10 text-blue-800 dark:bg-blue-400/15 dark:text-blue-200 border-blue-800/25',
  hr: 'bg-blue-500/15 text-blue-600 dark:text-blue-400 border-blue-500/30',
  company: 'bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border-indigo-500/30',
  user: 'bg-blue-500/10 text-blue-700 dark:text-blue-300 border-blue-500/30',
}

const toneMap = {
  status: defaultMap,
}

const classes = computed(() => {
  const map = toneMap[props.type] || defaultMap
  return map[String(props.value).toLowerCase()] || 'bg-slate-500/15 text-slate-700 dark:text-slate-300 border-slate-400/40 dark:border-slate-600/40'
})
</script>

<template>
  <span
    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border capitalize"
    :class="classes"
  >
    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
    {{ value }}
  </span>
</template>
