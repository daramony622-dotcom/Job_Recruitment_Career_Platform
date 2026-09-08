<script setup>
import { computed, onMounted, ref } from 'vue'
import {
  ArrowRight,
  ArrowUpRight,
  BarChart3,
  Briefcase,
  Building2,
  CheckCircle2,
  Clock3,
  FileText,
  RefreshCw,
  ShieldCheck,
  Users,
} from 'lucide-vue-next'
import { adminApi } from '../api'
import StatusBadge from '../components/StatusBadge.vue'

const loading = ref(false)
const error = ref('')
const lastUpdated = ref(null)
const report = ref({
  summary: {
    total_jobs: 0,
    published_jobs: 0,
    draft_jobs: 0,
    closed_jobs: 0,
    total_candidates: 0,
    active_candidates: 0,
    total_companies: 0,
    total_applications: 0,
  },
  job_posts_by_status: {},
  applications_by_status: {},
  recent_jobs: [],
  recent_applications: [],
})

const stats = computed(() => report.value.summary)

const jobStatuses = computed(() => Object.entries(report.value.job_posts_by_status || {}).map(([status, count]) => ({
  status,
  count: Number(count),
  percentage: percentage(count, stats.value.total_jobs),
})))

const applicationStatuses = computed(() => Object.entries(report.value.applications_by_status || {}).map(([status, count]) => ({
  status,
  count: Number(count),
  percentage: percentage(count, stats.value.total_applications),
})))

const publishedRate = computed(() => percentage(stats.value.published_jobs, stats.value.total_jobs))
const candidateRate = computed(() => percentage(stats.value.active_candidates, stats.value.total_candidates))
const companyRate = computed(() => percentage(stats.value.total_companies, Math.max(stats.value.total_companies, 1)))

const cards = computed(() => [
  { label: 'Total job posts', value: stats.value.total_jobs, meta: `${publishedRate.value}% published`, icon: Briefcase, tone: 'blue', to: '/job-posts' },
  { label: 'Candidates', value: stats.value.total_candidates, meta: `${candidateRate.value}% verified`, icon: Users, tone: 'teal', to: '/candidates' },
  { label: 'Companies', value: stats.value.total_companies, meta: 'Platform employers', icon: Building2, tone: 'amber', to: '/companies' },
  { label: 'Applications', value: stats.value.total_applications, meta: 'All submissions', icon: FileText, tone: 'violet', to: '/admin-resources/applications' },
])

function percentage(value, total) {
  return total ? Math.min(100, Math.round((Number(value) / Number(total)) * 100)) : 0
}

function formatDate(value) {
  if (!value) return '—'
  return new Date(value).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
}

async function fetchReport() {
  loading.value = true
  error.value = ''
  try {
    const response = await adminApi.getReports()
    const data = response.data?.data || {}
    report.value = {
      ...report.value,
      ...data,
      summary: { ...report.value.summary, ...(data.summary || {}) },
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
  <div class="dashboard-page">
    <div class="dashboard-container">
      <header class="dashboard-header">
        <div>
          <div class="dashboard-eyebrow"><span class="live-dot"></span> Platform overview</div>
          <h1>Dashboard</h1>
          <p>Monitor recruitment activity, account health, and the latest platform movement.</p>
        </div>
        <button class="dashboard-refresh" :disabled="loading" @click="fetchReport">
          <RefreshCw class="w-4 h-4" :class="loading ? 'animate-spin' : ''" />
          {{ loading ? 'Refreshing' : 'Refresh data' }}
        </button>
      </header>

      <p v-if="error" class="dashboard-alert">{{ error }}</p>

      <section class="metric-grid" aria-label="Platform metrics">
        <router-link v-for="card in cards" :key="card.label" :to="card.to" class="metric-card" :class="`metric-${card.tone}`">
          <div class="metric-topline">
            <span class="metric-icon"><component :is="card.icon" class="w-5 h-5" /></span>
            <ArrowUpRight class="w-4 h-4 metric-arrow" />
          </div>
          <p class="metric-label">{{ card.label }}</p>
          <p v-if="loading" class="metric-value metric-skeleton"></p>
          <p v-else class="metric-value">{{ card.value.toLocaleString() }}</p>
          <p class="metric-meta"><span class="metric-positive">{{ card.meta }}</span></p>
          <div class="metric-sparkline"><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
        </router-link>
      </section>

      <section class="dashboard-grid dashboard-grid-primary">
        <article class="dashboard-panel progress-panel">
          <div class="panel-heading">
            <div><p class="panel-kicker">Operations health</p><h2>Recruitment activity</h2></div>
            <BarChart3 class="w-5 h-5 panel-heading-icon" />
          </div>
          <div class="progress-list">
            <div class="progress-row"><div class="progress-copy"><span>Published job posts</span><strong>{{ publishedRate }}%</strong></div><div class="progress-track"><span class="progress-fill fill-blue" :style="{ width: `${publishedRate}%` }"></span></div><small>{{ stats.published_jobs }} of {{ stats.total_jobs }} total posts are live</small></div>
            <div class="progress-row"><div class="progress-copy"><span>Verified candidates</span><strong class="text-teal">{{ candidateRate }}%</strong></div><div class="progress-track"><span class="progress-fill fill-teal" :style="{ width: `${candidateRate}%` }"></span></div><small>{{ stats.active_candidates }} active and verified accounts</small></div>
            <div class="progress-row"><div class="progress-copy"><span>Company coverage</span><strong class="text-amber">{{ companyRate }}%</strong></div><div class="progress-track"><span class="progress-fill fill-amber" :style="{ width: `${companyRate}%` }"></span></div><small>{{ stats.total_companies }} employers registered on the platform</small></div>
          </div>
        </article>

        <article class="dashboard-panel status-panel">
          <div class="panel-heading"><div><p class="panel-kicker">Live distribution</p><h2>Job post status</h2></div><router-link to="/job-posts" class="panel-link">View all <ArrowRight class="w-3.5 h-3.5" /></router-link></div>
          <div v-if="loading" class="panel-empty">Loading status data...</div>
          <div v-else-if="jobStatuses.length === 0" class="panel-empty">No job status data yet.</div>
          <div v-else class="status-list"><div v-for="row in jobStatuses" :key="row.status" class="status-row"><div class="status-name"><span class="status-dot" :class="`status-dot-${row.status}`"></span><StatusBadge :value="row.status" /></div><div class="status-count"><strong>{{ row.count }}</strong><span>{{ row.percentage }}%</span></div></div></div>
          <div class="panel-footer"><span>Total tracked posts</span><strong>{{ stats.total_jobs.toLocaleString() }}</strong></div>
        </article>
      </section>

      <section class="dashboard-grid dashboard-grid-secondary">
        <article class="dashboard-panel activity-panel">
          <div class="panel-heading"><div><p class="panel-kicker">Latest movement</p><h2>Recent job posts</h2></div><router-link to="/job-posts" class="panel-link">Open list <ArrowRight class="w-3.5 h-3.5" /></router-link></div>
          <div v-if="loading" class="panel-empty">Loading recent jobs...</div>
          <div v-else-if="report.recent_jobs.length === 0" class="panel-empty">No recent job posts.</div>
          <div v-else class="activity-list"><div v-for="job in report.recent_jobs" :key="job.id" class="activity-row"><div class="activity-avatar avatar-blue"><Briefcase class="w-4 h-4" /></div><div class="activity-content"><strong>{{ job.title }}</strong><span>{{ job.company?.name || 'Unassigned company' }} · {{ formatDate(job.created_at) }}</span></div><StatusBadge :value="job.status" /></div></div>
        </article>

        <article class="dashboard-panel activity-panel">
          <div class="panel-heading"><div><p class="panel-kicker">Candidate pipeline</p><h2>Recent applications</h2></div><router-link to="/admin-resources/applications" class="panel-link">Open list <ArrowRight class="w-3.5 h-3.5" /></router-link></div>
          <div v-if="loading" class="panel-empty">Loading recent applications...</div>
          <div v-else-if="report.recent_applications.length === 0" class="panel-empty">No recent applications.</div>
          <div v-else class="activity-list"><div v-for="application in report.recent_applications" :key="application.id" class="activity-row"><div class="activity-avatar avatar-teal"><Users class="w-4 h-4" /></div><div class="activity-content"><strong>{{ application.job_seeker?.name || application.jobSeeker?.name || 'Candidate' }}</strong><span>{{ application.job_post?.title || application.jobPost?.title || 'Application' }} · {{ formatDate(application.created_at) }}</span></div><StatusBadge :value="application.status" /></div></div>
        </article>
      </section>

      <section class="dashboard-grid dashboard-grid-bottom">
        <article class="dashboard-panel compact-panel">
          <div class="panel-heading"><div><p class="panel-kicker">Application pipeline</p><h2>Applications by status</h2></div><FileText class="w-5 h-5 panel-heading-icon" /></div>
          <div v-if="applicationStatuses.length" class="mini-status-list"><div v-for="row in applicationStatuses" :key="row.status" class="mini-status-row"><StatusBadge :value="row.status" /><div class="mini-bar"><span :style="{ width: `${row.percentage}%` }"></span></div><strong>{{ row.count }}</strong></div></div>
          <p v-else class="panel-empty">No application data yet.</p>
        </article>

        <article class="dashboard-panel compact-panel system-panel">
          <div class="panel-heading">
            <div>
              <p class="panel-kicker">Security & Platform Guard</p>
              <h2>All systems protected</h2>
            </div>
            <router-link to="/security" class="panel-link">
              Security Center <ArrowRight class="w-3.5 h-3.5" />
            </router-link>
          </div>
          <div class="system-status">
            <CheckCircle2 class="w-5 h-5 text-teal" />
            <div>
              <strong>Sanctum Bearer Guard & RBAC</strong>
              <span>All administrative tokens and endpoints secured</span>
            </div>
            <span class="system-live">Guarded</span>
          </div>
          <div class="system-status">
            <Clock3 class="w-5 h-5 text-slate-400" />
            <div>
              <strong>Last telemetry sync</strong>
              <span>{{ lastUpdated ? lastUpdated.toLocaleTimeString() : 'Waiting for sync' }}</span>
            </div>
          </div>
        </article>
      </section>
    </div>
  </div>
</template>
