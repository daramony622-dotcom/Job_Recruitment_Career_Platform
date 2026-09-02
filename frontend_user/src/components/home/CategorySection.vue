<script setup>
import { ref } from 'vue'
import { 
  Code2, Calculator, TrendingUp, Palette, 
  HeartPulse, GraduationCap, Wrench, BarChart3,
  ArrowRight
} from 'lucide-vue-next'

const categories = ref([
  { title: 'Web Developer', roles: '342 open roles', icon: Code2, bg: 'bg-blue-50 dark:bg-blue-950/60', text: 'text-blue-600 dark:text-blue-400' },
  { title: 'Accounting & Finance', roles: '218 open roles', icon: Calculator, bg: 'bg-emerald-50 dark:bg-emerald-950/60', text: 'text-emerald-600 dark:text-emerald-400' },
  { title: 'Marketing & Sales', roles: '195 open roles', icon: TrendingUp, bg: 'bg-orange-50 dark:bg-orange-950/60', text: 'text-orange-600 dark:text-orange-400' },
  { title: 'UI / UX Design', roles: '164 open roles', icon: Palette, bg: 'bg-purple-50 dark:bg-purple-950/60', text: 'text-purple-600 dark:text-purple-400' },
  { title: 'Healthcare & Nursing', roles: '289 open roles', icon: HeartPulse, bg: 'bg-rose-50 dark:bg-rose-950/60', text: 'text-rose-600 dark:text-rose-400' },
  { title: 'Education & Training', roles: '132 open roles', icon: GraduationCap, bg: 'bg-amber-50 dark:bg-amber-950/60', text: 'text-amber-600 dark:text-amber-400' },
  { title: 'Engineering & Tech', roles: '276 open roles', icon: Wrench, bg: 'bg-cyan-50 dark:bg-cyan-950/60', text: 'text-cyan-600 dark:text-cyan-400' },
  { title: 'Data Science & AI', roles: '201 open roles', icon: BarChart3, bg: 'bg-indigo-50 dark:bg-indigo-950/60', text: 'text-indigo-600 dark:text-indigo-400' }
])
</script>

<template>
  <section class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Browse by Category</h2>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Explore career opportunities in your field of expertise</p>
      </div>
      <router-link to="/jobs" class="text-blue-600 dark:text-blue-400 text-xs sm:text-sm font-bold hover:underline inline-flex items-center gap-1">
        <span>View all categories</span>
        <ArrowRight class="w-4 h-4" />
      </router-link>
    </div>

    <!-- Category Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <router-link 
        v-for="cat in categories" 
        :key="cat.title"
        to="/jobs"
        class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-4 hover:shadow-md hover:-translate-y-0.5 transition duration-200 cursor-pointer group"
      >
        <div :class="[cat.bg, cat.text]" class="p-3 rounded-xl font-bold text-lg flex items-center justify-center w-12 h-12 shrink-0 group-hover:scale-105 transition transform">
          <component :is="cat.icon" class="w-6 h-6" />
        </div>
        <div class="min-w-0 flex-1">
          <h3 class="font-bold text-slate-900 dark:text-white text-sm truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">{{ cat.title }}</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ cat.roles }}</p>
        </div>
      </router-link>
    </div>
  </section>
</template>