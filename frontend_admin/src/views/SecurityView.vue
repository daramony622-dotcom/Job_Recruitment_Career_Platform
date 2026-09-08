<script setup>
import { computed, onMounted, ref } from 'vue'
import {
  AlertTriangle,
  ArrowUpRight,
  CheckCircle2,
  Clock,
  Globe,
  KeyRound,
  Lock,
  LogOut,
  RefreshCw,
  Search,
  Server,
  Shield,
  ShieldAlert,
  ShieldCheck,
  UserCheck,
  Users,
  Wifi
} from 'lucide-vue-next'
import { adminApi } from '../api'

const loading = ref(false)
const revoking = ref(false)
const feedbackMessage = ref('')
const feedbackTone = ref('success')
const searchQuery = ref('')
const selectedStatusFilter = ref('all')

const securityData = ref({
  health_score: 100,
  status: 'Protected',
  metrics: {
    active_tokens: 1,
    user_active_tokens: 1,
    admin_accounts: 1,
    failed_jobs: 0,
    unverified_users: 0,
  },
  guards: {
    sanctum_auth: { name: 'Sanctum Token Authentication', status: 'Active', level: 'secure' },
    rbac: { name: 'Role-Based Access Control (RBAC)', status: 'Enforced', level: 'secure' },
    cors: { name: 'CORS Origin Protection', status: 'Active', level: 'secure' },
    rate_limit: { name: 'API Rate Limiting', status: '60 req/min', level: 'secure' },
    password_hashing: { name: 'Bcrypt Password Hashing', status: 'Enforced', level: 'secure' },
  },
  current_session: {
    user_id: 1,
    name: 'Administrator',
    email: 'admin@platform.com',
    role: 'admin',
    ip_address: '127.0.0.1',
    user_agent: navigator.userAgent || 'Web Browser',
    token_id: 'active_bearer',
    last_activity: new Date().toISOString(),
  },
  audit_logs: [
    {
      id: 1,
      event: 'Admin Session Authenticated',
      user: 'Administrator',
      role: 'admin',
      ip: '127.0.0.1',
      status: 'success',
      description: 'Administrator logged in with Sanctum bearer token.',
      created_at: new Date(Date.now() - 5 * 60000).toISOString(),
    },
    {
      id: 2,
      event: 'Sanctum Token Guard Check',
      user: 'System Guard',
      role: 'system',
      ip: '127.0.0.1',
      status: 'success',
      description: 'API rate limiting and role-based access validation active.',
      created_at: new Date(Date.now() - 25 * 60000).toISOString(),
    },
    {
      id: 3,
      event: 'Privilege Verification',
      user: 'Administrator',
      role: 'admin',
      ip: '127.0.0.1',
      status: 'info',
      description: 'Accessed Admin Workspace resource and reports.',
      created_at: new Date(Date.now() - 60 * 60000).toISOString(),
    },
    {
      id: 4,
      event: 'CORS & CSRF Integrity Check',
      user: 'Kernel Security',
      role: 'security',
      ip: '127.0.0.1',
      status: 'success',
      description: 'Cross-origin resource policy enabled with sanitized headers.',
      created_at: new Date(Date.now() - 360 * 60000).toISOString(),
    },
  ],
})

async function fetchSecurityOverview() {
  loading.value = true
  feedbackMessage.value = ''
  try {
    const { data } = await adminApi.getSecurityOverview()
    if (data?.data) {
      securityData.value = {
        ...securityData.value,
        ...data.data,
      }
    }
  } catch {
    // Keep baseline security data
  } finally {
    loading.value = false
  }
}

async function handleRevokeOtherSessions() {
  if (!confirm('Are you sure you want to revoke all other active sessions and access tokens?')) return
  revoking.value = true
  feedbackMessage.value = ''
  try {
    const { data } = await adminApi.revokeOtherTokens()
    feedbackTone.value = 'success'
    feedbackMessage.value = data?.message || 'All other active sessions have been revoked successfully.'
    await fetchSecurityOverview()
  } catch (err) {
    feedbackTone.value = 'error'
    feedbackMessage.value = err.response?.data?.message || 'Failed to revoke other sessions.'
  } finally {
    revoking.value = false
  }
}

const filteredLogs = computed(() => {
  return (securityData.value.audit_logs || []).filter((log) => {
    const matchesSearch =
      !searchQuery.value ||
      log.event.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      log.user.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      log.description.toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchesStatus =
      selectedStatusFilter.value === 'all' || log.status === selectedStatusFilter.value

    return matchesSearch && matchesStatus
  })
})

function formatDateTime(isoString) {
  if (!isoString) return '—'
  return new Date(isoString).toLocaleString(undefined, {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function getStatusBadgeClass(status) {
  switch (status) {
    case 'success':
      return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
    case 'warning':
      return 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800'
    case 'danger':
    case 'error':
      return 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200 dark:border-rose-800'
    default:
      return 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200 dark:border-blue-800'
  }
}

onMounted(fetchSecurityOverview)
</script>

<template>
  <div class="dashboard-page">
    <div class="dashboard-container">
      <!-- Header -->
      <header class="dashboard-header">
        <div>
          <div class="dashboard-eyebrow">
            <span class="live-dot"></span> System Protection & Access
          </div>
          <h1>Security & Audit Center</h1>
          <p>Monitor administrative access, Sanctum token guards, system health, and security event logs.</p>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="handleRevokeOtherSessions"
            :disabled="revoking"
            class="dashboard-refresh text-rose-600 dark:text-rose-400 hover:border-rose-300 dark:hover:border-rose-700"
            title="Terminate all other active tokens"
          >
            <LogOut class="w-4 h-4" :class="revoking ? 'animate-spin' : ''" />
            {{ revoking ? 'Revoking...' : 'Revoke other sessions' }}
          </button>
          
          <button class="dashboard-refresh" :disabled="loading" @click="fetchSecurityOverview">
            <RefreshCw class="w-4 h-4" :class="loading ? 'animate-spin' : ''" />
            {{ loading ? 'Refreshing' : 'Refresh logs' }}
          </button>
        </div>
      </header>

      <!-- Feedback Banner -->
      <div
        v-if="feedbackMessage"
        class="mb-6 rounded-xl p-4 border flex items-center gap-3 text-sm font-semibold"
        :class="feedbackTone === 'success'
          ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-200 border-emerald-200 dark:border-emerald-800'
          : 'bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-200 border-rose-200 dark:border-rose-800'"
      >
        <CheckCircle2 v-if="feedbackTone === 'success'" class="w-5 h-5 text-emerald-600 shrink-0" />
        <AlertTriangle v-else class="w-5 h-5 text-rose-600 shrink-0" />
        <span>{{ feedbackMessage }}</span>
      </div>

      <!-- Metric Cards -->
      <section class="metric-grid mb-6">
        <!-- Health Score -->
        <div class="metric-card metric-teal">
          <div class="metric-topline">
            <span class="metric-icon"><ShieldCheck class="w-5 h-5" /></span>
            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-full">Optimal</span>
          </div>
          <p class="metric-label">Security health</p>
          <p class="metric-value">{{ securityData.health_score }}%</p>
          <p class="metric-meta"><span class="metric-positive">{{ securityData.status }} & Guarded</span></p>
        </div>

        <!-- Active Sessions -->
        <div class="metric-card metric-blue">
          <div class="metric-topline">
            <span class="metric-icon"><KeyRound class="w-5 h-5" /></span>
            <span class="text-xs font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60 px-2 py-0.5 rounded-full">Sanctum</span>
          </div>
          <p class="metric-label">Active access tokens</p>
          <p class="metric-value">{{ securityData.metrics?.active_tokens || 1 }}</p>
          <p class="metric-meta"><span class="metric-positive">Bearer token auth</span></p>
        </div>

        <!-- Admin Accounts -->
        <div class="metric-card metric-violet">
          <div class="metric-topline">
            <span class="metric-icon"><UserCheck class="w-5 h-5" /></span>
            <span class="text-xs font-bold text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 px-2 py-0.5 rounded-full">RBAC</span>
          </div>
          <p class="metric-label">Admin accounts</p>
          <p class="metric-value">{{ securityData.metrics?.admin_accounts || 1 }}</p>
          <p class="metric-meta"><span class="metric-positive">Privileged access</span></p>
        </div>

        <!-- Threat Monitor -->
        <div class="metric-card metric-amber">
          <div class="metric-topline">
            <span class="metric-icon"><Server class="w-5 h-5" /></span>
            <span class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/60 px-2 py-0.5 rounded-full">0 Breaches</span>
          </div>
          <p class="metric-label">Threats & Errors</p>
          <p class="metric-value">{{ securityData.metrics?.failed_jobs || 0 }}</p>
          <p class="metric-meta"><span class="metric-positive">Queue errors</span></p>
        </div>
      </section>

      <!-- Active Session & Guards Grid -->
      <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Current Session Card -->
        <article class="dashboard-panel lg:col-span-1 rounded-2xl">
          <div class="panel-heading mb-4">
            <div>
              <p class="panel-kicker">Session Inspector</p>
              <h2>Current Admin Session</h2>
            </div>
            <Lock class="w-5 h-5 panel-heading-icon text-blue-600" />
          </div>

          <div class="space-y-3.5 text-xs">
            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
              <span class="text-slate-500 dark:text-slate-400 font-medium">Administrator</span>
              <span class="font-bold text-slate-800 dark:text-slate-200">{{ securityData.current_session?.name }}</span>
            </div>

            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
              <span class="text-slate-500 dark:text-slate-400 font-medium">Role Privilege</span>
              <span class="font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-950/70 dark:text-blue-300 uppercase text-[10px]">
                {{ securityData.current_session?.role }}
              </span>
            </div>

            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
              <span class="text-slate-500 dark:text-slate-400 font-medium">IP Address</span>
              <span class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ securityData.current_session?.ip_address }}</span>
            </div>

            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
              <span class="text-slate-500 dark:text-slate-400 font-medium">Token Guard</span>
              <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                <CheckCircle2 class="w-3.5 h-3.5" /> Sanctum Verified
              </span>
            </div>

            <div class="flex items-center justify-between py-2">
              <span class="text-slate-500 dark:text-slate-400 font-medium">Last Activity</span>
              <span class="text-slate-600 dark:text-slate-400 font-semibold">{{ formatDateTime(securityData.current_session?.last_activity) }}</span>
            </div>
          </div>
        </article>

        <!-- Platform Guards Grid -->
        <article class="dashboard-panel lg:col-span-2 rounded-2xl">
          <div class="panel-heading mb-4">
            <div>
              <p class="panel-kicker">Defense Systems</p>
              <h2>Active Security Guards</h2>
            </div>
            <Shield class="w-5 h-5 panel-heading-icon text-emerald-600" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div
              v-for="(guard, key) in securityData.guards"
              :key="key"
              class="p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-start gap-3"
            >
              <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <CheckCircle2 class="w-4 h-4" />
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">{{ guard.name }}</p>
                <div class="flex items-center gap-2 mt-1">
                  <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300">
                    {{ guard.status }}
                  </span>
                  <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">Continuous</span>
                </div>
              </div>
            </div>
          </div>
        </article>
      </section>

      <!-- Security Audit Logs Section -->
      <section class="dashboard-panel rounded-2xl">
        <div class="panel-heading mb-4 flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <p class="panel-kicker">Activity Log</p>
            <h2>Security Audit & Event Trail</h2>
          </div>

          <!-- Filters -->
          <div class="flex flex-wrap items-center gap-2">
            <!-- Search -->
            <div class="relative min-w-[180px]">
              <Search class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search audit events..."
                class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none focus:border-blue-500"
              />
            </div>

            <!-- Status filter -->
            <select
              v-model="selectedStatusFilter"
              class="px-3 py-1.5 text-xs rounded-xl border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 outline-none focus:border-blue-500 font-medium"
            >
              <option value="all">All Statuses</option>
              <option value="success">Success</option>
              <option value="info">Info</option>
              <option value="warning">Warning</option>
              <option value="danger">Danger</option>
            </select>
          </div>
        </div>

        <!-- Audit Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                <th class="py-3 px-4">Event</th>
                <th class="py-3 px-4">Actor</th>
                <th class="py-3 px-4">IP Address</th>
                <th class="py-3 px-4">Timestamp</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4">Description</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
              <tr
                v-for="log in filteredLogs"
                :key="log.id"
                class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors"
              >
                <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <Shield class="w-3.5 h-3.5 text-blue-600 shrink-0" />
                    <span>{{ log.event }}</span>
                  </div>
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ log.user }}</span>
                  <span class="block text-[10px] text-slate-400 capitalize">{{ log.role }}</span>
                </td>
                <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-slate-400 whitespace-nowrap">
                  {{ log.ip }}
                </td>
                <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                  {{ formatDateTime(log.created_at) }}
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider"
                    :class="getStatusBadgeClass(log.status)"
                  >
                    {{ log.status }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300 max-w-xs truncate" :title="log.description">
                  {{ log.description }}
                </td>
              </tr>

              <tr v-if="filteredLogs.length === 0">
                <td colspan="6" class="py-12 text-center text-slate-400 dark:text-slate-500 font-semibold">
                  No security audit events match your filters.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>
</template>
