<script setup>
import { computed, onMounted, ref } from 'vue'
import { ArrowDownRight, ArrowUpRight, Briefcase, Building2, CalendarDays, FileText, Inbox, RefreshCw, Users } from 'lucide-vue-next'
import { adminApi } from '../api'
import StatusBadge from './StatusBadge.vue'

const loading = ref(false)
const error = ref('')
const lastUpdated = ref(null)
const interviews = ref([])
const range = ref('6M')
const hoverIndex = ref(null)
const visible = ref({ applications: true, jobs: true })
const ranges = [{ key: '3M', n: 3 }, { key: '6M', n: 6 }, { key: '12M', n: 12 }]
const report = ref({
  summary: { total_jobs: 0, published_jobs: 0, draft_jobs: 0, closed_jobs: 0, total_candidates: 0, active_candidates: 0, total_companies: 0, total_applications: 0 },
  monthly_trends: [], job_posts_by_status: {}, recent_jobs: [], recent_applications: [], category_progress: [],
})

const currentUser = computed(() => {
  try { return JSON.parse(localStorage.getItem('admin_user') || localStorage.getItem('user') || 'null') } catch { return null }
})
const greetingName = computed(() => currentUser.value?.name?.trim()?.split(/\s+/)[0] || 'there')
const greeting = computed(() => { const hour = new Date().getHours(); return hour < 12 ? 'Good morning' : hour < 18 ? 'Good afternoon' : 'Good evening' })
const isCompanyScopedUser = computed(() => ['hr', 'company'].includes(currentUser.value?.role))
const stats = computed(() => report.value.summary)
const asNumber = (value) => Number.isFinite(Number(value)) ? Number(value) : 0
const asList = (value) => Array.isArray(value) ? value : []
const formatNumber = (value) => asNumber(value).toLocaleString()
const showValue = (value) => loading.value && !lastUpdated.value ? '—' : formatNumber(value)
const percentage = (value, total) => total ? Math.min(100, Math.round((asNumber(value) / asNumber(total)) * 100)) : 0
const formatDate = (value) => value && !Number.isNaN(new Date(value).getTime()) ? new Date(value).toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) : '—'
const formatDateTime = (value) => value && !Number.isNaN(new Date(value).getTime()) ? new Date(value).toLocaleString(undefined, { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' }) : '—'
const humanize = (value) => String(value || '—').replace(/[_-]+/g, ' ').replace(/^./, (char) => char.toUpperCase())
const initials = (name) => String(name || '?').trim().split(/\s+/).slice(0, 2).map((part) => part[0]?.toUpperCase()).join('') || '?'
const appName = (item) => item?.candidate?.name || item?.candidate_name || item?.job_seeker?.name || item?.jobSeeker?.name || item?.user?.name || item?.name || 'Candidate'
const appRole = (item) => item?.job_post?.title || item?.jobPost?.title || item?.job?.title || item?.job_title || 'Role not set'
const jobTitle = (item) => item?.title || item?.name || 'Untitled role'
const jobCompany = (item) => item?.company?.name || item?.company_name || 'No company'
const interviewWhen = (item) => item?.scheduled_at || item?.interview_at || item?.starts_at || item?.date || item?.created_at
const interviewName = (item) => item?.candidate?.name || item?.application?.candidate?.name || item?.candidate_name || 'Candidate'
const interviewRole = (item) => item?.job_post?.title || item?.application?.job_post?.title || item?.job_title || 'Interview'

const trends = computed(() => asList(report.value.monthly_trends).map((item) => ({ month: item.month ?? item.label ?? '', applications: asNumber(item.applications), jobs: asNumber(item.jobs) })).slice(-(ranges.find((item) => item.key === range.value)?.n || 6)))
const hasTrends = computed(() => trends.value.length > 0)
const CHART = { w: 640, h: 260, l: 42, r: 16, t: 16, b: 34 }
const innerW = CHART.w - CHART.l - CHART.r
const innerH = CHART.h - CHART.t - CHART.b
const niceStep = (value) => { if (value <= 0) return 1; const power = 10 ** Math.floor(Math.log10(value)); const scaled = value / power; return ([1, 2, 2.5, 5, 10].find((item) => item >= scaled) || 10) * power }
const yMax = computed(() => niceStep(Math.max(...trends.value.flatMap((item) => [item.applications, item.jobs]), 0) / 4) * 4)
const xAt = (index, count) => count <= 1 ? CHART.l + innerW / 2 : CHART.l + (index * innerW) / (count - 1)
const yAt = (value) => CHART.t + innerH - (value / yMax.value) * innerH
const pointsFor = (key) => trends.value.map((item, index) => ({ x: xAt(index, trends.value.length), y: yAt(item[key]) }))
const appsPts = computed(() => pointsFor('applications'))
const jobsPts = computed(() => pointsFor('jobs'))
function smoothPath(points) { if (!points.length) return ''; return points.reduce((path, point, index) => `${path}${index ? ` L ${point.x},${point.y}` : `M ${point.x},${point.y}`}`, '') }
const appsLine = computed(() => smoothPath(appsPts.value))
const jobsLine = computed(() => smoothPath(jobsPts.value))
const yTicks = computed(() => [0, 1, 2, 3, 4].map((index) => ({ value: (yMax.value / 4) * index, y: yAt((yMax.value / 4) * index) })))
const hoverPoint = computed(() => hoverIndex.value == null ? null : { ...trends.value[hoverIndex.value], x: appsPts.value[hoverIndex.value]?.x })
const tooltipLeft = computed(() => hoverPoint.value ? `${Math.min(86, Math.max(14, (hoverPoint.value.x / CHART.w) * 100))}%` : '0%')
const deltaOf = (key) => { if (trends.value.length < 2 || !trends.value.at(-2)[key]) return null; return Math.round(((trends.value.at(-1)[key] - trends.value.at(-2)[key]) / trends.value.at(-2)[key]) * 1000) / 10 }
const sparkPath = (values) => smoothPath(values.map((value, index) => ({ x: values.length <= 1 ? 44 : (index * 88) / (values.length - 1), y: 26 - ((value - Math.min(...values)) / (Math.max(...values) - Math.min(...values) || 1)) * 22 })))
const jobStatuses = computed(() => Object.entries(report.value.job_posts_by_status || {}).map(([key, value]) => ({ key, label: humanize(key), value: asNumber(value) })).filter((item) => item.value > 0).sort((a, b) => b.value - a.value))
const categoryBars = computed(() => asList(report.value.category_progress).slice(0, 5).map((item) => ({ name: item.name || item.category, value: Math.min(100, asNumber(item.progress) || percentage(item.jobs_count, stats.value.total_jobs || 1)) })))
const recentApplications = computed(() => asList(report.value.recent_applications).slice(0, 5))
const recentJobs = computed(() => asList(report.value.recent_jobs).slice(0, 5))
const upcomingInterviews = computed(() => [...interviews.value].sort((a, b) => new Date(interviewWhen(a)) - new Date(interviewWhen(b))).slice(0, 4))

function toggleSeries(key) { const other = key === 'applications' ? 'jobs' : 'applications'; if (visible.value[key] && !visible.value[other]) return; visible.value = { ...visible.value, [key]: !visible.value[key] } }
async function fetchDashboardData() {
  loading.value = true; error.value = ''
  try {
    const [reportResponse, interviewsResponse] = await Promise.all([adminApi.getReports(), adminApi.getInterviews({ per_page: 20 })])
    const data = reportResponse.data?.data || {}
    const interviewData = interviewsResponse.data?.data || interviewsResponse.data || []
    interviews.value = Array.isArray(interviewData) ? interviewData : interviewData.data || []
    report.value = { ...report.value, ...data, summary: { ...report.value.summary, ...(data.summary || {}) } }
    lastUpdated.value = new Date()
  } catch (requestError) { error.value = requestError.response?.data?.message || 'Unable to load dashboard data.' } finally { loading.value = false }
}
onMounted(fetchDashboardData)
</script>

<template>
  <div class="min-h-full space-y-7 bg-slate-50/70 p-4 text-slate-800 sm:p-6 lg:p-8 dark:bg-slate-950/20 dark:text-slate-100">
    <div class="mx-auto max-w-7xl space-y-7">
      <header class="flex flex-col gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end sm:justify-between dark:border-slate-800">
        <div><p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-300">Recruitment overview</p><h1 class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white">{{ greeting }}, {{ greetingName }}</h1><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Live activity across your recruitment workspace.</p></div>
        <div class="flex items-center gap-2"><div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5 shadow-sm dark:border-slate-700 dark:bg-slate-900"><button v-for="item in ranges" :key="item.key" type="button" class="rounded-md px-3 py-1.5 text-xs font-semibold" :class="range === item.key ? 'bg-blue-700 text-white' : 'text-slate-500 hover:text-blue-700'" @click="range = item.key">{{ item.key }}</button></div><button type="button" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:border-blue-300 hover:text-blue-700 disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" :disabled="loading" @click="fetchDashboardData"><RefreshCw class="h-3.5 w-3.5" :class="loading ? 'animate-spin' : ''" /> Refresh</button></div>
      </header>
      <p v-if="error" role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ error }}</p>

      <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article v-for="card in [{ label: 'Total applications', value: stats.total_applications, icon: FileText, series: 'applications', color: '#2563eb' }, { label: 'Published jobs', value: stats.published_jobs, meta: `of ${formatNumber(stats.total_jobs)} posts`, icon: Briefcase, series: 'jobs', color: '#0891b2' }, { label: 'Active candidates', value: stats.active_candidates, meta: `of ${formatNumber(stats.total_candidates)} total`, icon: Users, color: '#1d4ed8' }, { label: isCompanyScopedUser ? 'Workspace status' : 'Companies', value: isCompanyScopedUser ? 'Active' : stats.total_companies, meta: isCompanyScopedUser ? 'Enterprise channel' : 'Registered employers', icon: Building2, color: '#102a63' }]" :key="card.label" class="min-h-39 rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
          <div class="flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ card.label }}</span><span class="rounded-xl bg-blue-50 p-2 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300"><component :is="card.icon" class="h-4 w-4" /></span></div><div class="mt-4 flex items-baseline gap-2"><span class="text-2xl font-bold text-slate-950 dark:text-white">{{ showValue(card.value) }}</span><span v-if="card.meta" class="text-xs text-slate-400">{{ card.meta }}</span><span v-if="card.series && deltaOf(card.series) !== null" class="inline-flex items-center text-xs font-semibold text-blue-700 dark:text-blue-300"><ArrowUpRight v-if="deltaOf(card.series) >= 0" class="h-3 w-3" /><ArrowDownRight v-else class="h-3 w-3" />{{ Math.abs(deltaOf(card.series)) }}%</span></div><svg v-if="card.series && hasTrends" viewBox="0 0 88 28" class="mt-4 h-7 w-22"><path :d="sparkPath(trends.map((item) => item[card.series]))" fill="none" :stroke="card.color" stroke-width="2" stroke-linecap="round" /></svg><div v-else class="mt-5 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"><div class="h-full rounded-full bg-blue-700" :style="{ width: `${percentage(stats.active_candidates, stats.total_candidates)}%` }"></div></div>
        </article>
      </section>

      <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <article class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm sm:p-6 xl:col-span-2 dark:border-slate-800 dark:bg-slate-900"><div class="flex items-start justify-between gap-4"><div><h2 class="text-base font-bold text-slate-950 dark:text-white">Activity trend</h2><p class="mt-1 text-xs text-slate-500">Applications and published jobs over time</p></div><div class="flex gap-3 text-xs"><button type="button" class="text-blue-700" @click="toggleSeries('applications')">● Applications</button><button type="button" class="text-cyan-700" @click="toggleSeries('jobs')">● Job posts</button></div></div><div class="mt-5 overflow-x-auto"><div class="min-w-160"><svg v-if="hasTrends" :viewBox="`0 0 ${CHART.w} ${CHART.h}`" class="h-auto w-full" @mouseleave="hoverIndex = null"><line v-for="tick in yTicks" :key="tick.value" :x1="CHART.l" :x2="CHART.w - CHART.r" :y1="tick.y" :y2="tick.y" stroke="#e2e8f0" /><text v-for="tick in yTicks" :key="`label-${tick.value}`" :x="CHART.l - 8" :y="tick.y + 4" text-anchor="end" fill="#94a3b8" font-size="11">{{ formatNumber(tick.value) }}</text><text v-for="(item, index) in trends" :key="item.month + index" :x="xAt(index, trends.length)" :y="CHART.h - 8" text-anchor="middle" fill="#94a3b8" font-size="11">{{ item.month }}</text><path v-if="visible.applications" :d="appsLine" fill="none" stroke="#2563eb" stroke-width="3" stroke-linecap="round" /><path v-if="visible.jobs" :d="jobsLine" fill="none" stroke="#0891b2" stroke-width="3" stroke-linecap="round" /><rect v-for="(item, index) in trends" :key="`hit-${index}`" :x="xAt(index, trends.length) - innerW / trends.length / 2" :y="CHART.t" :width="innerW / trends.length" :height="innerH" fill="transparent" @mouseenter="hoverIndex = index" /></svg><div v-else class="flex h-56 items-center justify-center text-sm text-slate-400"><Inbox class="mr-2 h-5 w-5" /> No activity to chart yet</div></div></div><div v-if="hoverPoint" class="mt-2 text-xs font-semibold text-slate-600">{{ hoverPoint.month }} · {{ formatNumber(hoverPoint.applications) }} applications · {{ formatNumber(hoverPoint.jobs) }} jobs</div></article>
        <article class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"><h2 class="text-base font-bold text-slate-950 dark:text-white">Job posts by status</h2><p class="mt-1 text-xs text-slate-500">Current hiring pipeline distribution</p><div v-if="jobStatuses.length" class="mt-6 space-y-4"><div v-for="item in jobStatuses" :key="item.key"><div class="mb-1 flex justify-between text-xs"><span class="font-medium text-slate-600 dark:text-slate-300">{{ item.label }}</span><span class="font-bold text-slate-900 dark:text-white">{{ formatNumber(item.value) }}</span></div><div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"><div class="h-full rounded-full bg-blue-700" :style="{ width: `${percentage(item.value, stats.total_jobs)}%` }"></div></div></div></div><div v-else class="flex h-48 items-center justify-center text-sm text-slate-400">No job status data yet</div></article>
      </section>

      <section class="grid grid-cols-1 gap-6 lg:grid-cols-3"><article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><h2 class="text-base font-bold">Top categories</h2><div v-if="categoryBars.length" class="mt-5 space-y-4"><div v-for="item in categoryBars" :key="item.name"><div class="flex justify-between text-xs"><span class="truncate text-slate-600">{{ item.name }}</span><b>{{ item.value }}%</b></div><div class="mt-1.5 h-2 rounded-full bg-slate-100"><div class="h-full rounded-full bg-cyan-600" :style="{ width: `${item.value}%` }"></div></div></div></div><p v-else class="mt-8 text-center text-sm text-slate-400">No category data yet</p></article><article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-2"><h2 class="text-base font-bold">Recent applications</h2><div v-if="recentApplications.length" class="mt-4 divide-y divide-slate-100"><div v-for="(item, index) in recentApplications" :key="item.id || index" class="flex items-center gap-3 py-3"><span class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-700">{{ initials(appName(item)) }}</span><div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold">{{ appName(item) }}</p><p class="truncate text-xs text-slate-500">Applied for {{ appRole(item) }}</p></div><StatusBadge :value="item.status || 'pending'" /><span class="hidden text-xs text-slate-400 sm:block">{{ formatDate(item.created_at) }}</span></div></div><p v-else class="mt-8 text-center text-sm text-slate-400">No applications received yet</p></article></section>
      <section class="grid grid-cols-1 gap-6 lg:grid-cols-2"><article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><h2 class="text-base font-bold">Recent job posts</h2><div v-if="recentJobs.length" class="mt-4 divide-y divide-slate-100"><div v-for="(item, index) in recentJobs" :key="item.id || index" class="flex items-center gap-3 py-3"><Briefcase class="h-4 w-4 text-blue-600" /><div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold">{{ jobTitle(item) }}</p><p class="truncate text-xs text-slate-500">{{ jobCompany(item) }} · {{ formatDate(item.created_at) }}</p></div><StatusBadge :value="item.status || 'draft'" /></div></div><p v-else class="mt-8 text-center text-sm text-slate-400">No job posts yet</p></article><article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><h2 class="text-base font-bold">Upcoming interviews</h2><div v-if="upcomingInterviews.length" class="mt-4 divide-y divide-slate-100"><div v-for="(item, index) in upcomingInterviews" :key="item.id || index" class="flex items-center gap-3 py-3"><CalendarDays class="h-4 w-4 text-blue-600" /><div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold">{{ interviewName(item) }}</p><p class="truncate text-xs text-slate-500">{{ interviewRole(item) }}</p></div><span class="text-xs text-slate-400">{{ formatDateTime(interviewWhen(item)) }}</span></div></div><p v-else class="mt-8 text-center text-sm text-slate-400">No interviews scheduled</p></article></section>
    </div>
  </div>
</template>
