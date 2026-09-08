<script setup>
import { ref } from 'vue'
import {
  Code2, Calculator, TrendingUp, Palette,
  HeartPulse, GraduationCap, Wrench, BarChart3,
  ArrowRight
} from 'lucide-vue-next'

const categories = ref([
  { title: 'Web Developer',        roles: '342 open roles', icon: Code2,          bg: 'bg-blue-50 dark:bg-blue-950/40',    icon_cls: 'text-blue-600 dark:text-blue-400',    border: 'border-blue-100 dark:border-blue-900/50' },
  { title: 'Accounting & Finance', roles: '218 open roles', icon: Calculator,     bg: 'bg-emerald-50 dark:bg-emerald-950/40', icon_cls: 'text-emerald-600 dark:text-emerald-400', border: 'border-emerald-100 dark:border-emerald-900/50' },
  { title: 'Marketing & Sales',    roles: '195 open roles', icon: TrendingUp,     bg: 'bg-orange-50 dark:bg-orange-950/40',  icon_cls: 'text-orange-600 dark:text-orange-400',  border: 'border-orange-100 dark:border-orange-900/50' },
  { title: 'UI / UX Design',       roles: '164 open roles', icon: Palette,        bg: 'bg-purple-50 dark:bg-purple-950/40',  icon_cls: 'text-purple-600 dark:text-purple-400',  border: 'border-purple-100 dark:border-purple-900/50' },
  { title: 'Healthcare & Nursing', roles: '289 open roles', icon: HeartPulse,     bg: 'bg-rose-50 dark:bg-rose-950/40',     icon_cls: 'text-rose-600 dark:text-rose-400',     border: 'border-rose-100 dark:border-rose-900/50' },
  { title: 'Education & Training', roles: '132 open roles', icon: GraduationCap, bg: 'bg-amber-50 dark:bg-amber-950/40',    icon_cls: 'text-amber-600 dark:text-amber-400',    border: 'border-amber-100 dark:border-amber-900/50' },
  { title: 'Engineering & Tech',   roles: '276 open roles', icon: Wrench,         bg: 'bg-cyan-50 dark:bg-cyan-950/40',     icon_cls: 'text-cyan-600 dark:text-cyan-400',     border: 'border-cyan-100 dark:border-cyan-900/50' },
  { title: 'Data Science & AI',    roles: '201 open roles', icon: BarChart3,      bg: 'bg-indigo-50 dark:bg-indigo-950/40',  icon_cls: 'text-indigo-600 dark:text-indigo-400',  border: 'border-indigo-100 dark:border-indigo-900/50' }
])
</script>

<template>
  <section class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Browse by Category</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Explore career opportunities in your field of expertise</p>
      </div>
      <router-link to="/jobs" class="text-blue-600 dark:text-blue-400 text-sm font-bold hover:underline inline-flex items-center gap-1 shrink-0">
        <span class="hidden sm:inline">View all</span>
        <ArrowRight class="w-4 h-4" />
      </router-link>
    </div>

    <!-- Category Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
      <router-link
        v-for="cat in categories"
        :key="cat.title"
        to="/jobs"
        class="group bg-white dark:bg-[#0d1526] p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800/60 shadow-2xs transition-all duration-200 cursor-pointer hover:shadow-md hover:-translate-y-0.5 hover:border-blue-300 dark:hover:border-blue-700/60 flex items-center gap-4"

      >
        <div
          class="p-3 rounded-xl w-12 h-12 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110"
          :class="[cat.bg, cat.icon_cls]"
        >
          <component :is="cat.icon" class="w-5 h-5" />
        </div>
        <div class="min-w-0 flex-1">
          <h3 class="font-bold text-slate-900 dark:text-white text-sm truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-150">{{ cat.title }}</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ cat.roles }}</p>
        </div>
      </router-link>
    </div>
  </section>
</template>
