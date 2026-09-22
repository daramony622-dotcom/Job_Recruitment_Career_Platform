<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import {
  Code2, Calculator, TrendingUp, Palette,
  HeartPulse, GraduationCap, Wrench, BarChart3,
  ArrowRight, ShieldCheck, Network, Briefcase,
  Users, Smartphone, Server, Cloud, Sparkles, FolderTree,
  RotateCcw
} from 'lucide-vue-next'
import { useAuth } from '../../composables/useAuth'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  navigateOnSelect: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'select'])
const router = useRouter()
const { request } = useAuth()

const categories = ref([])
const isLoading = ref(true)

const stylePresets = [
  { icon: Code2, bg: 'bg-blue-50 dark:bg-blue-950/40', icon_cls: 'text-blue-600 dark:text-blue-400' },
  { icon: Palette, bg: 'bg-purple-50 dark:bg-purple-950/40', icon_cls: 'text-purple-600 dark:text-purple-400' },
  { icon: Server, bg: 'bg-indigo-50 dark:bg-indigo-950/40', icon_cls: 'text-indigo-600 dark:text-indigo-400' },
  { icon: Smartphone, bg: 'bg-emerald-50 dark:bg-emerald-950/40', icon_cls: 'text-emerald-600 dark:text-emerald-400' },
  { icon: Cloud, bg: 'bg-cyan-50 dark:bg-cyan-950/40', icon_cls: 'text-cyan-600 dark:text-cyan-400' },
  { icon: BarChart3, bg: 'bg-violet-50 dark:bg-violet-950/40', icon_cls: 'text-violet-600 dark:text-violet-400' },
  { icon: ShieldCheck, bg: 'bg-rose-50 dark:bg-rose-950/40', icon_cls: 'text-rose-600 dark:text-rose-400' },
  { icon: Network, bg: 'bg-sky-50 dark:bg-sky-950/40', icon_cls: 'text-sky-600 dark:text-sky-400' },
  { icon: Briefcase, bg: 'bg-teal-50 dark:bg-teal-950/40', icon_cls: 'text-teal-600 dark:text-teal-400' },
  { icon: TrendingUp, bg: 'bg-orange-50 dark:bg-orange-950/40', icon_cls: 'text-orange-600 dark:text-orange-400' },
  { icon: Users, bg: 'bg-pink-50 dark:bg-pink-950/40', icon_cls: 'text-pink-600 dark:text-pink-400' },
  { icon: Calculator, bg: 'bg-emerald-50 dark:bg-emerald-950/40', icon_cls: 'text-emerald-600 dark:text-emerald-400' },
]

function getCategoryStyle(cat, index) {
  const name = (cat.name || cat.title || '').toLowerCase()
  const slug = (cat.slug || '').toLowerCase()

  if (name.includes('front') || name.includes('web') || slug.includes('frontend')) {
    return { icon: Code2, bg: 'bg-blue-50 dark:bg-blue-950/40', icon_cls: 'text-blue-600 dark:text-blue-400' }
  }
  if (name.includes('back') || name.includes('server') || slug.includes('backend')) {
    return { icon: Server, bg: 'bg-indigo-50 dark:bg-indigo-950/40', icon_cls: 'text-indigo-600 dark:text-indigo-400' }
  }
  if (name.includes('mobile') || name.includes('app') || slug.includes('mobile')) {
    return { icon: Smartphone, bg: 'bg-emerald-50 dark:bg-emerald-950/40', icon_cls: 'text-emerald-600 dark:text-emerald-400' }
  }
  if (name.includes('design') || name.includes('ui') || name.includes('ux') || slug.includes('ui-ux')) {
    return { icon: Palette, bg: 'bg-purple-50 dark:bg-purple-950/40', icon_cls: 'text-purple-600 dark:text-purple-400' }
  }
  if (name.includes('cloud') || name.includes('devops') || slug.includes('devops')) {
    return { icon: Cloud, bg: 'bg-cyan-50 dark:bg-cyan-950/40', icon_cls: 'text-cyan-600 dark:text-cyan-400' }
  }
  if (name.includes('data') || name.includes('ai') || name.includes('machine') || slug.includes('data-science')) {
    return { icon: BarChart3, bg: 'bg-indigo-50 dark:bg-indigo-950/40', icon_cls: 'text-indigo-600 dark:text-indigo-400' }
  }
  if (name.includes('security') || name.includes('cyber') || slug.includes('cybersecurity')) {
    return { icon: ShieldCheck, bg: 'bg-rose-50 dark:bg-rose-950/40', icon_cls: 'text-rose-600 dark:text-rose-400' }
  }
  if (name.includes('network') || name.includes('it') || slug.includes('networking')) {
    return { icon: Network, bg: 'bg-sky-50 dark:bg-sky-950/40', icon_cls: 'text-sky-600 dark:text-sky-400' }
  }
  if (name.includes('market') || name.includes('sales') || slug.includes('marketing')) {
    return { icon: TrendingUp, bg: 'bg-orange-50 dark:bg-orange-950/40', icon_cls: 'text-orange-600 dark:text-orange-400' }
  }
  if (name.includes('project') || name.includes('scrum') || name.includes('product') || slug.includes('project')) {
    return { icon: Briefcase, bg: 'bg-teal-50 dark:bg-teal-950/40', icon_cls: 'text-teal-600 dark:text-teal-400' }
  }
  if (name.includes('human') || name.includes('hr') || name.includes('talent') || slug.includes('human')) {
    return { icon: Users, bg: 'bg-pink-50 dark:bg-pink-950/40', icon_cls: 'text-pink-600 dark:text-pink-400' }
  }
  if (name.includes('account') || name.includes('finance') || slug.includes('finance')) {
    return { icon: Calculator, bg: 'bg-emerald-50 dark:bg-emerald-950/40', icon_cls: 'text-emerald-600 dark:text-emerald-400' }
  }
  if (name.includes('engineer') || name.includes('tech') || slug.includes('software')) {
    return { icon: Wrench, bg: 'bg-blue-50 dark:bg-blue-950/40', icon_cls: 'text-blue-600 dark:text-blue-400' }
  }
  if (name.includes('health') || name.includes('nurse') || name.includes('doctor')) {
    return { icon: HeartPulse, bg: 'bg-rose-50 dark:bg-rose-950/40', icon_cls: 'text-rose-600 dark:text-rose-400' }
  }
  if (name.includes('educat') || name.includes('train') || name.includes('teacher')) {
    return { icon: GraduationCap, bg: 'bg-amber-50 dark:bg-amber-950/40', icon_cls: 'text-amber-600 dark:text-amber-400' }
  }

  return stylePresets[index % stylePresets.length]
}

async function fetchCategories() {
  isLoading.value = true
  try {
    const res = await request('/categories')
    const payload = res?.data ?? res
    const list = payload?.all || payload?.categories || payload?.skill_categories || []
    if (Array.isArray(list) && list.length > 0) {
      categories.value = list
    }
  } catch {
    // Keep empty or fallbacks
  } finally {
    isLoading.value = false
  }
}

function handleCategoryClick(cat) {
  const identifier = cat.slug || cat.name
  emit('update:modelValue', identifier)
  emit('select', cat)

  if (props.navigateOnSelect) {
    router.push({
      path: '/jobs',
      query: { category: identifier }
    })
  }
}

function isSelected(cat) {
  if (!props.modelValue) return false
  return props.modelValue.toLowerCase() === (cat.slug || '').toLowerCase() ||
         props.modelValue.toLowerCase() === (cat.name || '').toLowerCase()
}

onMounted(fetchCategories)
</script>

<template>
  <section class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Browse by Category</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Explore career opportunities in your field of expertise</p>
      </div>
      <div class="flex items-center gap-3">
        <button
          v-if="modelValue"
          @click="$emit('update:modelValue', '')"
          class="text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline inline-flex items-center gap-1 cursor-pointer"
        >
          <RotateCcw class="w-3.5 h-3.5" />
          <span>Reset filter</span>
        </button>
        <router-link to="/jobs" class="text-blue-600 dark:text-blue-400 text-sm font-bold hover:underline inline-flex items-center gap-1 shrink-0">
          <span class="hidden sm:inline">View all</span>
          <ArrowRight class="w-4 h-4" />
        </router-link>
      </div>
    </div>

    <!-- Loading Skeleton Grid -->
    <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
      <div
        v-for="i in 8"
        :key="i"
        class="bg-white dark:bg-[#0d1526] p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800/60 flex items-center gap-4 animate-pulse"
      >
        <div class="w-12 h-12 rounded-xl bg-slate-200 dark:bg-slate-800 shrink-0"></div>
        <div class="space-y-2 flex-1 min-w-0">
          <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded-md w-3/4"></div>
          <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded-md w-1/2"></div>
        </div>
      </div>
    </div>

    <!-- Category Grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
      <button
        v-for="(cat, index) in categories"
        :key="cat.id || cat.slug || cat.name"
        type="button"
        @click="handleCategoryClick(cat)"
        class="group text-left bg-white dark:bg-[#0d1526] p-4 rounded-2xl border transition-all duration-200 cursor-pointer shadow-2xs hover:shadow-md hover:-translate-y-0.5 flex items-center gap-4"
        :class="isSelected(cat)
          ? 'border-blue-500 dark:border-blue-500 bg-blue-50/50 dark:bg-blue-950/30 ring-2 ring-blue-500/20'
          : 'border-slate-200/80 dark:border-slate-800/60 hover:border-blue-300 dark:hover:border-blue-700/60'"
      >
        <div
          class="p-3 rounded-xl w-12 h-12 shrink-0 flex items-center justify-center transition-transform duration-200 group-hover:scale-110"
          :class="[getCategoryStyle(cat, index).bg, getCategoryStyle(cat, index).icon_cls]"
        >
          <component :is="getCategoryStyle(cat, index).icon" class="w-5 h-5" />
        </div>
        <div class="min-w-0 flex-1">
          <h3
            translate="no"
            class="notranslate font-bold text-sm truncate transition-colors duration-150"
            :class="isSelected(cat) ? 'text-blue-600 dark:text-blue-400' : 'text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400'"
          >
            {{ cat.name || cat.title }}
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            {{ cat.roles || (cat.jobs_count ? `${cat.jobs_count} open roles` : (cat.skills_count ? `${cat.skills_count} skills` : 'Explore roles')) }}
          </p>
        </div>
      </button>
    </div>
  </section>
</template>

