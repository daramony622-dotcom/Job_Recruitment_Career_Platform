<script setup>
import { onMounted, ref } from 'vue'
import {
  LayoutDashboard,
  Briefcase,
  Users,
  Building2,
  FileText,
  TrendingUp,
  ArrowRight,
  RefreshCw,
} from 'lucide-vue-next'
import { adminApi } from '../api'
import StatusBadge from '../components/StatusBadge.vue'

const loading = ref(false)
const error = ref('')
const offline = ref(false)

const summary = ref({
  total_jobs: 0,
  published_jobs: 0,
  total_candidates: 0,
  total_companies: 0,
  total_applications: 0,
})

const jobSummary = ref([])
const recentJobs = ref([])
const recentApplications = ref([])

const mockData = {
  summary: {
    total_jobs: 128,
    published_jobs: 92,
    total_candidates: 1540,
    total_companies: 47,
    total_applications: 890,
  },
  jobSummary: [
    { status: 'published', count: 92 },
    { status: 'draft', count: 18 },
    { status: 'closed', count: 12 },
    { status: 'expired', count: 6 },
  ],
  recentJobs: [
    { id: 1, title: 'Senior Frontend Developer', status: 'published', company: { name: 'TechNova' }, city: 'Ho Chi Minh City' },
    { id: 2, title: 'Backend Engineer (PHP/Laravel)', status: 'published', company: { name: 'CloudWorks' }, city: 'Ha Noi' },
    { id: 3, title: 'Full Stack Developer', status: 'draft', company: { name: 'FinEdge' }, city: 'Da Nang' },
    { id: 4, title: 'DevOps Engineer', status: 'closed', company: { name: 'DataCore' }, city: 'Ho Chi Minh City' },
    { id: 5, title: 'UI/UX Designer', status: 'published', company: { name: 'PixelStudio' }, city: 'Ha Noi' },
  ],
  recentApplications: [
    { id: 1, status: 'in_review', jobSeeker: { name: 'Nguyen Van A' }, jobPost: { title: 'Senior Frontend Developer', company: { name: 'TechNova' } } },
    { id: 2, status: 'shortlisted', jobSeeker: { name: 'Tran Thi B' }, jobPost: { title: 'Backend Engineer (PHP/Laravel)', company: { name: 'CloudWorks' } } },
    { id: 3, status: 'pending', jobSeeker: { name: 'Le Van C' }, jobPost: { title: 'Full Stack Developer', company: { name: 'FinEdge' } } },
    { id: 4, status: 'hired', jobSeeker: { name: 'Pham Thi D' }, jobPost: { title: 'DevOps Engineer', company: { name: 'DataCore' } } },
    { id: 5, status: 'rejected', jobSeeker: { name: 'Hoang Van E' }, jobPost: { title: 'UI/UX Designer', company: { name: 'PixelStudio' } } },
  ],
}

function isOfflineError(e) {
  return e.code === 'ECONNABORTED' || !e.response
}

function applyMockData() {
  summary.value = { ...mockData.summary }
  jobSummary.value = mockData.jobSummary
  recentJobs.value = mockData.recentJobs
  recentApplications.value = mockData.recentApplications
}

function countBy(items, key) {
  const map = {}
  for (const item of items) {
    const val = item?.[key] || 'unknown'
    map[val] = (map[val] || 0) + 1
  }
  return map
}

function extractPager(res) {
  const body = res?.data
  if (Array.isArray(body)) {
    return { data: body, total: body.length }
  }
  const pager = body?.data && typeof body.data === 'object' && !Array.isArray(body.data)
    ? body.data
    : body
  if (pager && typeof pager.total !== 'number') {
    pager.total = Array.isArray(pager.data) ? pager.data.length : 0
  }
  return pager || { data: [], total: 0 }
}

function getErrorMessage(e) {
  if (e.code === 'ECONNABORTED') {
    return 'The request timed out. The backend server may be slow or unreachable.'
  }
  if (!e.response) {
    return 'Unable to reach the backend server. Please check your connection and ensure the API (http://localhost:8000/api) is running.'
  }
  if (e.response.status >= 500) {
    return `Server error (${e.response.status}). The backend encountered an issue. Please try again later.`
  }
  return e.response.data?.message || `Request failed with status ${e.response.status}.`
}

async function fetchDashboard() {
  loading.value = true
  error.value = ''
  try {
    const [jobs, users, companies, applications] = await Promise.all([
      adminApi.getJobPosts({ per_page: 100 }),
      adminApi.getCandidates({ role: 'user', per_page: 100 }),
      adminApi.getCompanies({ per_page: 100 }),
      adminApi.getApplications({ per_page: 100 }),
    ])

    const jobPager = extractPager(jobs)
    const userPager = extractPager(users)
    const companyPager = extractPager(companies)
    const appPager = extractPager(applications)

    const jobsData = Array.isArray(jobPager.data) ? jobPager.data : []
    const candidatesData = Array.isArray(userPager.data) ? userPager.data : []
    const companiesData = Array.isArray(companyPager.data) ? companyPager.data : []
    const applicationsData = Array.isArray(appPager.data) ? appPager.data : []

    const statusCounts = countBy(jobsData, 'status')

    summary.value = {
      total_jobs: jobPager.total || jobsData.length,
      published_jobs: statusCounts.published || 0,
      total_candidates: userPager.total || candidatesData.length,
      total_companies: companyPager.total || companiesData.length,
      total_applications: appPager.total || applicationsData.length,
    }

    jobSummary.value = Object.entries(statusCounts).map(([status, count]) => ({ status, count }))
    recentJobs.value = jobsData.slice(0, 5)
    recentApplications.value = applicationsData.slice(0, 5)
  } catch (e) {
    error.value = getErrorMessage(e)
    if (isOfflineError(e)) {
      offline.value = true
      applyMockData()
    }
  } finally {
    loading.value = false
  }
}

const statCards = [
  { key: 'total_jobs', label: 'Job Posts', to: '/job-posts', icon: Briefcase, color: 'text-blue-400 bg-blue-500/10' },
  { key: 'total_candidates', label: 'Candidates', to: '/candidates', icon: Users, color: 'text-emerald-400 bg-emerald-500/10' },
  { key: 'total_companies', label: 'Companies', to: '/companies', icon: Building2, color: 'text-indigo-400 bg-indigo-500/10' },
  { key: 'total_applications', label: 'Applications', to: '/job-posts', icon: FileText, color: 'text-amber-400 bg-amber-500/10' },
]

onMounted(fetchDashboard)
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-100 flex items-center gap-2">
          <LayoutDashboard class="w-6 h-6 text-blue-500" />
          Dashboard
        </h2>
        <p class="text-sm text-slate-400 mt-1">Overview of the recruitment platform</p>
      </div>
      <button
        @click="fetchDashboard"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-700 text-slate-300 text-sm font-semibold hover:border-blue-500 hover:text-blue-400 transition-colors"
      >
        <RefreshCw class="w-4 h-4" />
        Refresh
      </button>
    </div>

    <div
      v-if="offline"
      class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3"
    >
      <div>
        <p class="text-sm font-semibold text-amber-300">Backend is offline — showing sample data</p>
        <p class="text-xs text-amber-200/70 mt-0.5">Could not reach {{ 'http://localhost:8000/api' }}. The figures below are placeholder values.</p>
      </div>
      <button
        @click="fetchDashboard"
        class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-300 hover:text-amber-100 transition-colors shrink-0"
      >
        <RefreshCw class="w-3.5 h-3.5" />
        Retry
      </button>
    </div>

    <div
      v-else-if="error"
      class="mb-4 text-sm text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3"
    >
      {{ error }}
    </div>

    <p v-if="loading" class="text-sm text-slate-400 mb-4">Loading dashboard...</p>

    <!-- Stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
      <router-link
        v-for="card in statCards"
        :key="card.key"
        :to="card.to"
        class="group bg-slate-900 border border-slate-800 rounded-2xl p-5 hover:border-blue-600/60 hover:shadow-lg hover:shadow-blue-900/20 transition-all"
      >
        <div class="flex items-center justify-between">
          <div :class="`w-10 h-10 rounded-xl ${card.color} flex items-center justify-center`">
            <component :is="card.icon" class="w-5 h-5" />
          </div>
          <span class="text-2xl font-bold text-slate-100">{{ summary[card.key] }}</span>
        </div>
        <div class="mt-3 flex items-center justify-between">
          <p class="text-sm text-slate-400">{{ card.label }}</p>
          <ArrowRight class="w-4 h-4 text-slate-600 group-hover:text-blue-400 transition-colors" />
        </div>
      </router-link>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      <!-- Published vs total -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 lg:col-span-1">
        <div class="flex items-center gap-2 mb-4">
          <TrendingUp class="w-5 h-5 text-blue-500" />
          <h3 class="font-semibold text-slate-100">Publishing overview</h3>
        </div>
        <div class="space-y-3">
          <div class="flex items-center justify-between text-sm">
            <span class="text-slate-400">Published jobs</span>
            <span class="font-semibold text-slate-100">{{ summary.published_jobs }} / {{ summary.total_jobs }}</span>
          </div>
          <div class="h-2 rounded-full bg-slate-800 overflow-hidden">
            <div
              class="h-full bg-blue-500 rounded-full transition-all"
              :style="{ width: summary.total_jobs ? `${Math.round((summary.published_jobs / summary.total_jobs) * 100)}%` : '0%' }"
            ></div>
          </div>
          <p class="text-xs text-slate-500 mt-1">Percentage of job posts currently published.</p>
        </div>
      </div>

      <!-- Job posts by status -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 lg:col-span-1">
        <h3 class="font-semibold text-slate-100 mb-4">Job Posts by Status</h3>
        <div class="space-y-3">
          <div
            v-for="row in jobSummary"
            :key="row.status"
            class="flex items-center justify-between text-sm"
          >
            <StatusBadge :value="row.status" />
            <span class="text-slate-300 font-semibold">{{ row.count }}</span>
          </div>
          <p v-if="jobSummary.length === 0" class="text-sm text-slate-500">No data</p>
        </div>
      </div>

      <!-- Recent job posts -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden lg:col-span-1">
        <h3 class="font-semibold text-slate-100 px-5 py-4 border-b border-slate-800">Recent Job Posts</h3>
        <div class="divide-y divide-slate-800">
          <div v-for="job in recentJobs" :key="job.id" class="px-5 py-3.5 flex items-center justify-between gap-3">
            <div class="min-w-0">
              <p class="font-semibold text-slate-100 truncate">{{ job.title }}</p>
              <p class="text-xs text-slate-500 truncate">{{ job.company?.name || '—' }} · {{ job.city || job.location || '—' }}</p>
            </div>
            <StatusBadge :value="job.status" />
          </div>
          <p v-if="recentJobs.length === 0" class="px-5 py-6 text-sm text-slate-500">No recent job posts</p>
        </div>
      </div>
    </div>

    <!-- Recent applications -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
      <h3 class="font-semibold text-slate-100 px-5 py-4 border-b border-slate-800">Recent Applications</h3>
      <div class="divide-y divide-slate-800">
        <div v-for="app in recentApplications" :key="app.id" class="px-5 py-3.5 flex items-center justify-between gap-3">
          <div class="min-w-0">
            <p class="font-semibold text-slate-100 truncate">{{ app.jobSeeker?.name || 'Candidate' }}</p>
            <p class="text-xs text-slate-500 truncate">{{ app.jobPost?.title || '—' }} · {{ app.jobPost?.company?.name || '' }}</p>
          </div>
          <StatusBadge :value="app.status" />
        </div>
        <p v-if="recentApplications.length === 0" class="px-5 py-6 text-sm text-slate-500">No recent applications</p>
      </div>
    </div>
  </div>
</template>