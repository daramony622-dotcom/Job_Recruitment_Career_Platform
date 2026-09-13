<script setup>
import { ShieldCheck, Briefcase } from 'lucide-vue-next'

const props = defineProps({
  status: {
    type: String,
    default: 'All systems operational',
  },
  statusTone: {
    type: String,
    default: 'emerald',
    validator: (v) => ['emerald', 'amber', 'rose'].includes(v),
  },
})

const links = [
  { label: 'Privacy', to: '#' },
  { label: 'Terms', to: '#' },
  { label: 'Support', to: '#' },
]

const toneClasses = {
  emerald: 'bg-emerald-400 text-emerald-950',
  amber: 'bg-amber-400 text-amber-950',
  rose: 'bg-rose-400 text-rose-950',
}
</script>

<template>
  <footer class="admin-footer mt-auto border-t border-slate-200 bg-white px-4 py-4 sm:px-6 dark:border-slate-800 dark:bg-[#101a29]">
    <div class="mx-auto flex max-w-[1600px] flex-col items-center justify-between gap-4 text-center lg:flex-row lg:text-left">
      <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-950 text-white shadow-lg shadow-slate-900/10 ring-1 ring-slate-900/10 dark:bg-blue-500 dark:shadow-blue-500/20">
          <Briefcase class="w-4 h-4" />
        </div>
        <div>
          <div class="text-sm font-extrabold tracking-[0.08em] text-slate-900 dark:text-white">JOB SEARCH</div>
          <p class="text-[11px] text-slate-400">© 2026 All rights reserved.</p>
        </div>
      </div>

      <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-[11px] font-semibold text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300">
        <ShieldCheck class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-300" />
        <span class="relative flex items-center gap-1.5">
          <span class="relative flex h-1.5 w-1.5">
            <span
              class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-60"
              :class="toneClasses[statusTone]"
            ></span>
            <span
              class="relative inline-flex h-1.5 w-1.5 rounded-full"
              :class="toneClasses[statusTone]"
            ></span>
          </span>
          {{ status }}
        </span>
      </span>

      <nav class="flex items-center gap-4">
        <a
          v-for="link in links"
          :key="link.label"
          :href="link.to"
          class="text-[11px] font-medium text-slate-500 transition-colors hover:text-blue-600 dark:text-slate-400 dark:hover:text-white"
        >
          {{ link.label }}
        </a>
      </nav>
    </div>
  </footer>
</template>