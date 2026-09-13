<script setup>
import { computed, onMounted, ref } from 'vue'
import {
  ArrowUpRight, Briefcase, Building2, CalendarDays, CheckCircle2, Clock3,
  FileText, RefreshCw, ShieldCheck, Sparkles, Users, MoreHorizontal
} from 'lucide-vue-next'
import { adminApi } from '../api'

const loading = ref(false)
const error = ref('')
const lastUpdated = ref(null)
const interviews = ref([])

const currentUser = computed(() => {
  try { return JSON.parse(localStorage.getItem('admin_user') || localStorage.getItem('user') || 'null') } catch { return null }
})
const greetingName = computed(() => currentUser.value?.name?.trim()?.split(/\s+/)[0] || 'there')
const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 12) return 'Good morning'
  if (hour < 18) return 'Good afternoon'
  return 'Good evening'
})
const isCompanyScopedUser = computed(() => ['hr', 'company'].includes(currentUser.value?.role))

const report = ref({
  summary: { total_jobs: 0, published_jobs: 0, draft_jobs: 0, closed_jobs: 0, total_candidates: 0, active_candidates: 0, total_companies: 0, total_applications: 0 },
  job_posts_by_status: {},
  applications_by_status: {},
  recent_jobs: [],
  recent_applications: [],
})

const stats = computed(() => report.value.summary)

function asList(value) { return Array.isArray(value) ? value : [] }
function asNumber(value) { const parsed = Number(value); return Number.isFinite(parsed) ? parsed : 0 }
function formatNumber(value) { return asNumber(value).toLocaleString() }
function percentage(value, total) { return total ? Math.min(100, Math.round((Number(value) / Number(total)) * 100)) : 0 }

const publishedRate = computed(() => percentage(stats.value.published_jobs, stats.value.total_jobs))
const candidateRate = computed(() => percentage(stats.value.active_candidates, stats.value.total_candidates))
const companyRate = computed(() => percentage(stats.value.total_companies, Math.max(stats.value.total_companies, 1)))

const activitySeries = computed(() => {
  const today = new Date()
  return Array.from({ length: 7 }, (_, index) => {
    const date = new Date(today)
    date.setDate(today.getDate() - (6 - index))
    const key = date.toISOString().slice(0, 10)
    const jobs = asList(report.value.recent_jobs).filter((item) => String(item.created_at || item.published_at || '').slice(0, 10) === key).length
    const applications = asList(report.value.recent_applications).filter((item) => String(item.created_at || '').slice(0, 10) === key).length
    return { label: date.toLocaleDateString(undefined, { weekday: 'short' }), jobs, applications }
  })
})
const activityMax = computed(() => Math.max(...activitySeries.value.flatMap((item) => [item.jobs, item.applications]), 1))

function chartPoints(key) {
  const width = 640
  const height = 210
  const step = width / (activitySeries.value.length - 1)
  return activitySeries.value.map((item, index) => `${index * step},${height - (item[key] / activityMax.value) * height}`).join(' ')
}

const cards = computed(() => {
  if (isCompanyScopedUser.value) {
    return [
      { label: 'Total Jobs', value: stats.value.total_jobs, meta: `${publishedRate.value}% published`, icon: Briefcase, tone: 'blue', to: '/job-posts' },
      { label: 'Applications', value: stats.value.total_applications, meta: 'All submissions', icon: FileText, tone: 'violet', to: '/admin-resources/applications' },
      { label: 'Interviews', value: interviews.value.length, meta: 'Scheduled meetings', icon: Clock3, tone: 'amber', to: '/admin-resources/interviews' },
      { label: 'Candidates', value: stats.value.total_candidates || 0, meta: 'Registered seekers', icon: Users, tone: 'teal', to: '/candidates' },
    ]
  }
  return [
    { label: 'Total Jobs', value: stats.value.total_jobs, meta: `${publishedRate.value}% published`, icon: Briefcase, tone: 'blue', to: '/job-posts' },
    { label: 'Candidates', value: stats.value.total_candidates, meta: `${candidateRate.value}% active`, icon: Users, tone: 'teal', to: '/candidates' },
    { label: 'Companies', value: stats.value.total_companies, meta: 'Platform employers', icon: Building2, tone: 'amber', to: '/companies' },
    { label: 'Applications', value: stats.value.total_applications, meta: 'All submissions', icon: FileText, tone: 'violet', to: '/admin-resources/applications' },
  ]
})

const healthItems = computed(() => [
  { label: 'Published jobs', value: publishedRate.value, icon: Briefcase, tone: 'blue', detail: `${stats.value.published_jobs} live roles` },
  { label: 'Candidate activity', value: candidateRate.value, icon: Users, tone: 'teal', detail: `${stats.value.active_candidates} active` },
  { label: 'Employer coverage', value: companyRate.value, icon: Building2, tone: 'amber', detail: `${stats.value.total_companies} employers` },
])

const toneClasses = {
  blue:   { icon: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',   bar: 'bg-blue-500' },
  teal:   { icon: 'bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400',   bar: 'bg-teal-500' },
  amber:  { icon: 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400', bar: 'bg-amber-500' },
  violet: { icon: 'bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400', bar: 'bg-violet-500' },
}

function formatDate(value) {
  if (!value) return '—'
  return new Date(value).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
}

async function fetchReport() {
  loading.value = true
  error.value = ''
  try {
    const [reportResponse, interviewsResponse] = await Promise.all([
      adminApi.getReports(),
      adminApi.getInterviews({ per_page: 20 })
    ])
    const data = reportResponse.data?.data || {}
    const interviewData = interviewsResponse.data?.data || interviewsResponse.data || []
    interviews.value = Array.isArray(interviewData) ? interviewData : interviewData.data || []
    report.value = {
      ...report.value,
      ...data,
      summary: { ...report.value.summary, ...(data.summary || {}) },
      recent_jobs: asList(data.recent_jobs),
      recent_applications: asList(data.recent_applications),
    }
    lastUpdated.value = new Date()
  } catch (requestError) {
    error.value = requestError.response?.data?.message || 'Unable to load dashboard data.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchReport)
</script>

<template>
  <div class="space-y-6">

    <!-- ===== Page Header ===== -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
          {{ greeting }}, {{ greetingName }}
          <Sparkles class="w-6 h-6 text-amber-400" />
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Here's what's happening across your platform today.</p>
        <!-- Tab Bar -->
        <div class="flex items-center gap-6 mt-3 border-b border-slate-200 dark:border-slate-800">
          <button class="pb-2 text-sm font-semibold text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400 -mb-px">Summary</button>
          <button class="pb-2 text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">Overview</button>
          <button class="pb-2 text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">Comparison</button>
        </div>
      </div>
      <div class="flex items-center gap-3 pb-2">
        <span v-if="lastUpdated" class="text-xs text-slate-400">
          Synced {{ lastUpdated.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
        </span>
        <button
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:border-blue-400 hover:text-blue-600 transition-colors disabled:opacity-50 cursor-pointer"
          :disabled="loading"
          @click="fetchReport"
        >
          <RefreshCw class="w-4 h-4" :class="loading ? 'animate-spin' : ''" />
          Refresh
        </button>
      </div>
    </div>

    <!-- Error Banner -->
    <p v-if="error" class="text-sm text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">
      {{ error }}
    </p>

    <!-- ===== Metric Cards ===== -->
    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
      <router-link
        v-for="card in cards"
        :key="card.label"
        :to="card.to"
        class="flex flex-col justify-between gap-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-900/10 hover:border-blue-500/40 transition-all duration-200 no-underline group"
      >
        <!-- Top row: icon + arrow -->
        <div class="flex items-start justify-between">
          <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" :class="toneClasses[card.tone]?.icon">
            <component :is="card.icon" class="w-5 h-5" />
          </span>
          <ArrowUpRight class="w-4 h-4 text-slate-300 dark:text-slate-600 group-hover:text-blue-500 transition-colors" />
        </div>
        <!-- Label -->
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ card.label }}</p>
        <!-- Value -->
        <p v-if="loading" class="h-9 w-20 rounded-lg bg-slate-200 dark:bg-slate-700 animate-pulse"></p>
        <p v-else class="text-3xl font-black text-slate-900 dark:text-slate-100 leading-none">{{ formatNumber(card.value) }}</p>
        <!-- Meta -->
        <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">{{ card.meta }}</p>
      </router-link>
    </section>

    <!-- ===== Chart + Health Panel ===== -->
    <section class="grid grid-cols-1 lg:grid-cols-[1.6fr_1fr] gap-5">

      <!-- Participation Chart -->
      <article class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Participation</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Jobs & applications over last 7 days</p>
          </div>
          <select class="bg-slate-100 dark:bg-slate-800 border-none rounded-lg px-2 py-1 text-xs font-semibold text-slate-700 dark:text-slate-300 outline-none cursor-pointer">
            <option>Weekly</option>
            <option>Monthly</option>
          </select>
        </div>

        <div class="relative flex gap-2">
          <!-- Y axis labels -->
          <div class="flex flex-col justify-between text-[10px] text-slate-400 py-1 shrink-0 w-5 text-right">
            <span>{{ activityMax }}</span>
            <span>{{ Math.ceil(activityMax / 2) }}</span>
            <span>0</span>
          </div>
          <!-- SVG chart -->
          <div class="flex-1 min-w-0">
            <svg viewBox="0 0 640 210" class="w-full h-44" role="img" aria-label="Participation chart">
              <defs>
                <linearGradient id="jobs-area" x1="0" x2="0" y1="0" y2="1">
                  <stop offset="0%" stop-color="#3b82f6" stop-opacity=".25" />
                  <stop offset="100%" stop-color="#3b82f6" stop-opacity="0" />
                </linearGradient>
              </defs>
              <!-- grid lines -->
              <line v-for="n in [0, 1, 2]" :key="n" x1="0" :y1="n * 105" x2="640" :y2="n * 105"
                class="stroke-slate-100 dark:stroke-slate-800 stroke-1" />
              <!-- area fill -->
              <polygon :points="`0,210 ${chartPoints('jobs')} 640,210`" fill="url(#jobs-area)" />
              <!-- job line -->
              <polyline :points="chartPoints('jobs')" fill="none" class="stroke-blue-500 stroke-2" />
              <!-- application line -->
              <polyline :points="chartPoints('applications')" fill="none" class="stroke-amber-500 stroke-2 [stroke-dasharray:5_3]" />
              <!-- dots -->
              <circle v-for="(pt, i) in activitySeries" :key="`j${i}`"
                :cx="i * (640 / 6)" :cy="210 - (pt.jobs / activityMax) * 210"
                r="4" class="fill-blue-500" />
              <circle v-for="(pt, i) in activitySeries" :key="`a${i}`"
                :cx="i * (640 / 6)" :cy="210 - (pt.applications / activityMax) * 210"
                r="4" class="fill-amber-500" />
            </svg>
            <!-- X axis labels -->
            <div class="flex justify-between text-[10px] text-slate-400 mt-1 px-1">
              <span v-for="pt in activitySeries" :key="pt.label">{{ pt.label }}</span>
            </div>
          </div>
        </div>

        <!-- Legend -->
        <div class="flex gap-5 mt-4 text-xs text-slate-500 dark:text-slate-400">
          <span class="flex items-center gap-1.5"><i class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500 not-italic"></i>Jobs published</span>
          <span class="flex items-center gap-1.5"><i class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500 not-italic"></i>Applications</span>
        </div>
      </article>

      <!-- Workspace Health -->
      <article class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-sm">
        <div class="mb-5">
          <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Workspace health</h2>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Platform activity indicators</p>
        </div>
        <div class="space-y-5">
          <div v-for="tile in healthItems" :key="tile.label" class="space-y-2">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" :class="toneClasses[tile.tone]?.icon">
                  <component :is="tile.icon" class="w-4 h-4" />
                </span>
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ tile.label }}</span>
              </div>
              <span class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ tile.value }}%</span>
            </div>
            <div class="w-full h-1.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
              <div class="h-full rounded-full transition-all duration-700" :class="toneClasses[tile.tone]?.bar" :style="{ width: `${tile.value}%` }"></div>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ tile.detail }}</p>
          </div>
        </div>
      </article>
    </section>

    <!-- ===== Recent Applications Table ===== -->
    <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
      <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
        <div>
          <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Recent Applications</h2>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Latest candidate submissions</p>
        </div>
        <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
          <MoreHorizontal class="w-5 h-5" />
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-800/50">
              <th class="text-left py-2.5 px-5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Application</th>
              <th class="text-left py-2.5 px-5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</th>
              <th class="text-left py-2.5 px-5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Candidate</th>
              <th class="text-left py-2.5 px-5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Date</th>
              <th class="text-left py-2.5 px-5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Progress</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr
              v-for="app in asList(report.recent_applications).slice(0, 6)"
              :key="app.id"
              class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="py-3 px-5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                    <FileText class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                  </div>
                  <div>
                    <p class="font-bold text-slate-800 dark:text-slate-100 text-sm leading-tight">{{ app.job_post?.title || 'Application' }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ app.job_post?.company?.name || 'Platform' }}</p>
                  </div>
                </div>
              </td>
              <td class="py-3 px-5">
                <span
                  class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider"
                  :class="{
                    'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400': app.status === 'pending',
                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400': app.status === 'hired',
                    'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400': app.status === 'rejected',
                    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': !['pending','hired','rejected'].includes(app.status),
                  }"
                >{{ app.status || 'pending' }}</span>
              </td>
              <td class="py-3 px-5 text-sm text-slate-700 dark:text-slate-300 font-medium">
                {{ app.job_seeker?.name || 'Candidate' }}
              </td>
              <td class="py-3 px-5 text-sm text-slate-500 dark:text-slate-400">
                {{ formatDate(app.created_at) }}
              </td>
              <td class="py-3 px-5">
                <div class="flex items-center gap-2">
                  <div class="flex-1 bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 max-w-[80px]">
                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: 65%"></div>
                  </div>
                  <span class="text-xs font-bold text-slate-500 dark:text-slate-400">65%</span>
                </div>
              </td>
            </tr>
            <tr v-if="asList(report.recent_applications).length === 0">
              <td colspan="5" class="text-center py-12 text-slate-400 dark:text-slate-500">
                <FileText class="w-8 h-8 mx-auto mb-2 opacity-40" />
                No recent applications found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

  </div>
</template>