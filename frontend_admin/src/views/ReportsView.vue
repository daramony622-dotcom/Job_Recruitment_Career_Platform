<script setup>
import { onMounted, ref } from 'vue'
import { BarChart3, Briefcase, Users, FileText, RefreshCw, TrendingUp } from 'lucide-vue-next'
import { adminApi } from '../api'
import StatusBadge from '../components/StatusBadge.vue'

const loading = ref(false)
const error = ref('')

const summary = ref({
  total_jobs: 0,
  published_jobs: 0,
  draft_jobs: 0,
  closed_jobs: 0,
  total_candidates: 0,
  active_candidates: 0,
  total_applications: 0,
})

const recentJobs = ref([])
const recentApplications = ref([])

const jobSummary = ref([])
const applicationByStatus = ref([])

function countBy(items, key) {
  const map = {}
  for (const item of items) {
    const val = item[key] || 'unknown'
    map[val] = (map[val] || 0) + 1
  }
  return map
}

async function fetchReports() {
  loading.value = true
  error.value = ''
  try {
    const [jobsRes, usersRes, applicationsRes] = await Promise.all([
      adminApi.getJobPosts({ per_page: 100 }),
      adminApi.getCandidates({ role: 'user', per_page: 100 }),
      adminApi.getApplications({ per_page: 100 }),
    ])

    const jobPager = jobsRes.data
    const jobsData = jobPager.data || []

    const userPager = usersRes.data?.data || usersRes.data
    const candidatesData = userPager.data || []

    const appPager = applicationsRes.data?.data || applicationsRes.data
    const applicationsData = appPager.data || []

    const statusCounts = countBy(jobsData, 'status')
    const appStatusCounts = countBy(applicationsData, 'status')

    summary.value = {
      total_jobs: jobPager.total || jobsData.length,
      published_jobs: statusCounts.published || 0,
      draft_jobs: statusCounts.draft || 0,
      closed_jobs: (statusCounts.closed || 0) + (statusCounts.suspended || 0),
      total_candidates: userPager.total || candidatesData.length,
      active_candidates: candidatesData.filter((c) => c.is_active).length,
      total_applications: appPager.total || applicationsData.length,
    }

    jobSummary.value = Object.entries(statusCounts).map(([status, count]) => ({ status, count }))
    applicationByStatus.value = Object.entries(appStatusCounts).map(([status, count]) => ({ status, count }))

    recentJobs.value = jobsData.slice(0, 5)
    recentApplications.value = applicationsData.slice(0, 5)
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to load reports. Confirm you are logged in as admin.'
  } finally {
    loading.value = false
  }
}

const statCards = [
  { key: 'total_jobs', label: 'Total Job Posts', icon: Briefcase, color: 'text-blue-400 bg-blue-500/10' },
  { key: 'total_candidates', label: 'Total Candidates', icon: Users, color: 'text-emerald-400 bg-emerald-500/10' },
  { key: 'total_applications', label: 'Applications', icon: FileText, color: 'text-amber-400 bg-amber-500/10' },
  { key: 'published_jobs', label: 'Published Jobs', icon: TrendingUp, color: 'text-indigo-400 bg-indigo-500/10' },
]

onMounted(fetchReports)
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-100 flex items-center gap-2">
          <BarChart3 class="w-6 h-6 text-blue-500" />
          Reports
        </h2>
        <p class="text-sm text-slate-400 mt-1">Real-time platform overview</p>
      </div>
      <button
        @click="fetchReports"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-700 text-slate-300 text-sm font-semibold hover:border-blue-500 hover:text-blue-400 transition-colors"
      >
        <RefreshCw class="w-4 h-4" />
        Refresh
      </button>
    </div>

    <p v-if="error" class="mb-4 text-sm text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">
      {{ error }}
    </p>

    <p v-if="loading" class="text-sm text-slate-400 mb-4">Loading reports...</p>

    <!-- Summary cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
      <div
        v-for="card in statCards"
        :key="card.key"
        class="bg-slate-900 border border-slate-800 rounded-2xl p-5"
      >
        <div class="flex items-center justify-between">
          <div :class="`w-10 h-10 rounded-xl ${card.color} flex items-center justify-center`">
            <component :is="card.icon" class="w-5 h-5" />
          </div>
          <span class="text-2xl font-bold text-slate-100">{{ summary[card.key] }}</span>
        </div>
        <p class="mt-3 text-sm text-slate-400">{{ card.label }}</p>
      </div>
    </div>

    <!-- Breakdowns -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
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

      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
        <h3 class="font-semibold text-slate-100 mb-4">Applications by Status</h3>
        <div class="space-y-3">
          <div
            v-for="row in applicationByStatus"
            :key="row.status"
            class="flex items-center justify-between text-sm"
          >
            <StatusBadge :value="row.status" />
            <span class="text-slate-300 font-semibold">{{ row.count }}</span>
          </div>
          <p v-if="applicationByStatus.length === 0" class="text-sm text-slate-500">No data</p>
        </div>
      </div>
    </div>

    <!-- Recent lists -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
        <h3 class="font-semibold text-slate-100 px-5 py-4 border-b border-slate-800">Recent Job Posts</h3>
        <div class="divide-y divide-slate-800">
          <div v-for="job in recentJobs" :key="job.id" class="px-5 py-3.5 flex items-center justify-between gap-3">
            <div class="min-w-0">
              <p class="font-semibold text-slate-100 truncate">{{ job.title }}</p>
              <p class="text-xs text-slate-500">{{ job.company?.name || '—' }} · {{ job.city || job.location || '—' }}</p>
            </div>
            <StatusBadge :value="job.status" />
          </div>
          <p v-if="recentJobs.length === 0" class="px-5 py-6 text-sm text-slate-500">No recent job posts</p>
        </div>
      </div>

      <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
        <h3 class="font-semibold text-slate-100 px-5 py-4 border-b border-slate-800">Recent Applications</h3>
        <div class="divide-y divide-slate-800">
          <div
            v-for="app in recentApplications"
            :key="app.id"
            class="px-5 py-3.5 flex items-center justify-between gap-3"
          >
            <div class="min-w-0">
              <p class="font-semibold text-slate-100 truncate">{{ app.jobSeeker?.name || 'Candidate' }}</p>
              <p class="text-xs text-slate-500 truncate">{{ app.jobPost?.title || '—' }}</p>
            </div>
            <StatusBadge :value="app.status" />
          </div>
          <p v-if="recentApplications.length === 0" class="px-5 py-6 text-sm text-slate-500">No recent applications</p>
        </div>
      </div>
    </div>
  </div>
</template>
