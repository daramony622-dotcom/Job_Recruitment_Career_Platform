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
  <footer class="border-t border-slate-800 bg-slate-900 px-4 sm:px-6 py-4 mt-auto">
    <div class="mx-auto max-w-7xl flex flex-col lg:flex-row items-center justify-between gap-4 text-center lg:text-left">
      
      <!-- Logo & Copyright -->
      <div class="flex items-center gap-3">
        <div class="w-7 h-7 rounded-lg bg-blue-600 flex items-center justify-center text-white shrink-0">
          <Briefcase class="w-4 h-4" />
        </div>
        <div>
          <span class="text-sm font-bold text-white tracking-wide">Job Search</span>
          <p class="text-xs text-slate-500">© 2026 All rights reserved.</p>
        </div>
      </div>

      <!-- Status Badge -->
      <span class="inline-flex items-center gap-2 rounded-full border border-slate-800 bg-slate-950 px-3 py-1 text-xs text-slate-400">
        <ShieldCheck class="w-3.5 h-3.5 text-slate-500" />
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

      <!-- Links -->
      <nav class="flex items-center gap-4">
        <a
          v-for="link in links"
          :key="link.label"
          :href="link.to"
          class="text-xs text-slate-400 hover:text-slate-200 transition-colors"
        >
          {{ link.label }}
        </a>
      </nav>
    </div>
  </footer>
</template>