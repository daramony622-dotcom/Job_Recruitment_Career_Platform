<script setup>
import { computed } from 'vue'

const props = defineProps({
  value: { type: String, default: '' },
  type: { type: String, default: 'status' },
})

const defaultMap = {
  active: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
  published: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
  verified: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
  draft: 'bg-slate-500/15 text-slate-400 border-slate-500/30',
  pending: 'bg-amber-500/15 text-amber-400 border-amber-500/30',
  closed: 'bg-rose-500/15 text-rose-400 border-rose-500/30',
  expired: 'bg-rose-500/15 text-rose-400 border-rose-500/30',
  suspended: 'bg-rose-500/15 text-rose-400 border-rose-500/30',
  rejected: 'bg-rose-500/15 text-rose-400 border-rose-500/30',
  in_review: 'bg-blue-500/15 text-blue-400 border-blue-500/30',
  hired: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
  shortlisted: 'bg-indigo-500/15 text-indigo-400 border-indigo-500/30',
  admin: 'bg-rose-500/15 text-rose-400 border-rose-500/30',
  hr: 'bg-blue-500/15 text-blue-400 border-blue-500/30',
  company: 'bg-indigo-500/15 text-indigo-400 border-indigo-500/30',
  user: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
}

const toneMap = {
  status: defaultMap,
}

const classes = computed(() => {
  const map = toneMap[props.type] || defaultMap
  return map[String(props.value).toLowerCase()] || 'bg-slate-500/15 text-slate-300 border-slate-600/40'
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
