<script setup>
<<<<<<< HEAD
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  LayoutDashboard,
  Building2,
  Briefcase,
  Users,
  FileText,
  LogOut,
  Bell,
  ShieldCheck,
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()

const sidebarOpen = ref(false)
const user = ref(JSON.parse(localStorage.getItem('admin_user') || 'null'))

const navItems = [
  { name: 'Dashboard', path: '/dashboard', icon: LayoutDashboard },
  { name: 'Job Posts', path: '/job-posts', icon: Briefcase },
  { name: 'Candidates', path: '/candidates', icon: Users },
  { name: 'Companies', path: '/companies', icon: Building2 },
  { name: 'Reports', path: '/reports', icon: FileText },
]

function logout() {
  localStorage.removeItem('admin_token')
  localStorage.removeItem('admin_user')
  router.push('/login')
}
</script>

<template>
  <!-- Standalone pages (login) -->
  <router-view v-if="route.meta.public" />

  <!-- Admin layout -->
  <div v-else class="min-h-screen bg-slate-950 text-slate-100">
    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 border-r border-slate-800 transition-transform lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="flex items-center gap-3 px-6 h-16 border-b border-slate-800 shrink-0">
        <div class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center font-black text-white">A</div>
        <div>
          <p class="font-bold text-slate-100 leading-tight">Recruit Admin</p>
          <p class="text-xs text-slate-400">Management Console</p>
        </div>
      </div>

      <nav class="p-3 space-y-1">
        <router-link
          v-for="item in navItems"
          :key="item.path"
          :to="item.path"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
          :class="route.path.startsWith(item.path)
            ? 'bg-blue-600 text-white'
            : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100'"
        >
          <component :is="item.icon" class="w-5 h-5" />
          <span>{{ item.name }}</span>
        </router-link>
      </nav>

      <div class="absolute bottom-0 left-0 right-0 p-3 border-t border-slate-800 space-y-1">
        <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg">
          <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-200 shrink-0 uppercase">
            {{ (user?.name || 'A').charAt(0) }}
          </div>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-100 truncate">{{ user?.name || 'Admin' }}</p>
            <p class="text-xs text-slate-500 truncate flex items-center gap-1">
              <ShieldCheck class="w-3 h-3" /> {{ user?.role || 'admin' }}
            </p>
          </div>
        </div>
        <button
          @click="logout"
          class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-rose-400 transition-colors"
        >
          <LogOut class="w-5 h-5" />
          <span>Sign out</span>
        </button>
      </div>
    </aside>

    <!-- Sidebar overlay for mobile -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-30 bg-black/50 lg:hidden"
      @click="sidebarOpen = false"
    ></div>

    <!-- Main column (reserves sidebar width on lg+) -->
    <div class="lg:pl-64 flex flex-col min-h-screen">
      <!-- Topbar -->
      <header class="sticky top-0 z-20 h-16 bg-slate-900/95 backdrop-blur border-b border-slate-800 flex items-center justify-between px-4 sm:px-6 gap-4">
        <div class="flex items-center gap-3 min-w-0">
          <button
            class="lg:hidden p-2 text-slate-400 hover:text-slate-100"
            @click="sidebarOpen = true"
            aria-label="Open menu"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>

        <div class="flex items-center gap-3 ml-auto">
          <button class="relative p-2 text-slate-400 hover:text-slate-100" aria-label="Notifications">
            <Bell class="w-5 h-5" />
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-blue-500 rounded-full"></span>
          </button>
          <div class="hidden sm:flex items-center gap-2 pl-3 border-l border-slate-800">
            <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-200 uppercase">
              {{ (user?.name || 'A').charAt(0) }}
            </div>
            <span class="text-sm font-medium text-slate-300 hidden md:block">{{ user?.name }}</span>
          </div>
        </div>
      </header>

      <!-- Page content -->
      <main class="flex-1 overflow-x-hidden">
        <router-view />
      </main>
    </div>
=======
import { onMounted, ref } from 'vue'
import { initTheme, toggleTheme, useTheme } from './composables/useTheme'

const { isDark } = useTheme()

const sidebarOpen = ref(false)
const activeSection = ref('Overview')

const navigation = [
  { label: 'Overview', icon: 'grid' },
  { label: 'Job listings', icon: 'briefcase' },
  { label: 'Companies', icon: 'building' },
  { label: 'Candidates', icon: 'users' },
  { label: 'Reports', icon: 'chart' }
]

const stats = [
  { label: 'Active job listings', value: '1,284', change: '+12.8%', note: 'vs last month', tone: 'blue' },
  { label: 'Registered candidates', value: '18,492', change: '+8.4%', note: 'vs last month', tone: 'green' },
  { label: 'Pending reviews', value: '46', change: 'Needs attention', note: 'across all queues', tone: 'amber' },
  { label: 'Successful placements', value: '726', change: '+18.2%', note: 'this quarter', tone: 'violet' }
]

const applications = [
  { company: 'Northstar Labs', role: 'Senior Product Designer', candidate: 'Amelia Okafor', status: 'Review', time: '12 min ago', initials: 'NO', color: 'navy' },
  { company: 'Mosaic Finance', role: 'Backend Engineer', candidate: 'Daniel Park', status: 'Interview', time: '38 min ago', initials: 'MF', color: 'coral' },
  { company: 'Greenline Health', role: 'Marketing Manager', candidate: 'Sofia Martins', status: 'Review', time: '1 hr ago', initials: 'GH', color: 'mint' },
  { company: 'Arc & Co.', role: 'Operations Lead', candidate: 'Liam Chen', status: 'Shortlisted', time: '2 hrs ago', initials: 'AC', color: 'violet' }
]

const setSection = (label) => {
  activeSection.value = label
  sidebarOpen.value = false
}

onMounted(() => {
  initTheme()
})
</script>

<template>
  <div class="admin-shell">
    <button v-if="sidebarOpen" class="sidebar-backdrop" aria-label="Close navigation" @click="sidebarOpen = false"></button>

    <aside class="sidebar" :class="{ 'sidebar--open': sidebarOpen }">
      <div class="brand">
        <span class="brand-mark">J</span>
        <span>Jobly<span class="brand-dot">.</span></span>
      </div>

      <div class="workspace-label">Workspace</div>
      <nav class="main-nav" aria-label="Main navigation">
        <button
          v-for="item in navigation"
          :key="item.label"
          class="nav-item"
          :class="{ 'nav-item--active': activeSection === item.label }"
          type="button"
          @click="setSection(item.label)"
        >
          <span class="nav-icon" :class="`nav-icon--${item.icon}`" aria-hidden="true"></span>
          <span>{{ item.label }}</span>
          <span v-if="item.label === 'Job listings'" class="nav-count">12</span>
        </button>
      </nav>

      <div class="sidebar-bottom">
        <div class="support-card">
          <span class="support-icon">?</span>
          <div>
            <strong>Need a hand?</strong>
            <span>Visit the help center</span>
          </div>
        </div>
        <button class="nav-item nav-item--muted" type="button" @click="setSection('Settings')">
          <span class="nav-icon nav-icon--settings" aria-hidden="true"></span>
          <span>Settings</span>
        </button>
        <div class="admin-profile">
          <span class="avatar avatar--profile">AS</span>
          <span class="profile-copy"><strong>Alex Smith</strong><small>Administrator</small></span>
          <button class="more-button" type="button" aria-label="More account options">•••</button>
        </div>
      </div>
    </aside>

    <main class="main-content">
      <header class="topbar">
        <button class="menu-button" type="button" aria-label="Open navigation" @click="sidebarOpen = true">☰</button>
        <div class="breadcrumb"><span>Dashboard</span><span>/</span><strong>{{ activeSection }}</strong></div>
        <div class="topbar-actions">
          <button class="icon-button" type="button" aria-label="Search">⌕</button>
          <button class="icon-button notification-button" type="button" aria-label="Notifications">♧<i></i></button>
          <button
            class="theme-button"
            type="button"
            :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
            :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
            @click="toggleTheme"
          >{{ isDark ? '☀' : '◐' }}</button>
          <span class="topbar-divider"></span>
          <span class="topbar-date">Thursday, 24 April 2025</span>
        </div>
      </header>

      <div class="page-content">
        <section class="page-heading">
          <div>
            <p class="eyebrow">Good morning, Alex</p>
            <h1>{{ activeSection }}</h1>
            <p class="page-subtitle">Here’s what’s happening across your recruitment platform today.</p>
          </div>
          <button class="primary-button" type="button"><span>＋</span> Add new listing</button>
        </section>

        <section class="stats-grid" aria-label="Platform overview">
          <article v-for="stat in stats" :key="stat.label" class="stat-card">
            <div class="stat-card-top"><span>{{ stat.label }}</span><span class="stat-menu">•••</span></div>
            <strong>{{ stat.value }}</strong>
            <div class="stat-footer"><span class="stat-change" :class="`stat-change--${stat.tone}`">{{ stat.change }}</span><span>{{ stat.note }}</span></div>
          </article>
        </section>

        <section class="dashboard-grid">
          <article class="panel performance-panel">
            <div class="panel-heading"><div><h2>Hiring activity</h2><p>Applications received over the last 30 days</p></div><button class="select-button" type="button">Last 30 days <span>⌄</span></button></div>
            <div class="chart-wrap">
              <div class="chart-y-axis"><span>800</span><span>600</span><span>400</span><span>200</span><span>0</span></div>
              <div class="chart-area">
                <div class="chart-gridlines"><i></i><i></i><i></i><i></i><i></i></div>
                <svg class="line-chart" viewBox="0 0 620 190" preserveAspectRatio="none" aria-label="Hiring activity chart" role="img"><defs><linearGradient id="chart-fill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#3478f6" stop-opacity=".22"/><stop offset="100%" stop-color="#3478f6" stop-opacity="0"/></linearGradient></defs><path class="chart-fill" d="M0,150 C38,142 54,120 88,128 S140,100 174,112 S220,72 260,91 S306,68 350,86 S390,42 430,63 S476,36 510,49 S560,22 620,31 L620,190 L0,190 Z"/><path class="chart-line" d="M0,150 C38,142 54,120 88,128 S140,100 174,112 S220,72 260,91 S306,68 350,86 S390,42 430,63 S476,36 510,49 S560,22 620,31"/></svg>
                <div class="chart-x-axis"><span>01 Apr</span><span>07 Apr</span><span>14 Apr</span><span>21 Apr</span><span>24 Apr</span></div>
              </div>
            </div>
          </article>

          <article class="panel queue-panel">
            <div class="panel-heading"><div><h2>Review queue</h2><p>Items waiting for your attention</p></div><button class="text-button" type="button">View all <span>→</span></button></div>
            <div class="queue-list"><div class="queue-row"><span class="queue-icon queue-icon--amber">!</span><span><strong>Job listings</strong><small>28 need moderation</small></span><b>28</b></div><div class="queue-row"><span class="queue-icon queue-icon--blue">↗</span><span><strong>Company profiles</strong><small>12 awaiting approval</small></span><b>12</b></div><div class="queue-row"><span class="queue-icon queue-icon--coral">◆</span><span><strong>Reported content</strong><small>6 reports to resolve</small></span><b>06</b></div></div>
            <button class="queue-action" type="button">Open review center <span>→</span></button>
          </article>
        </section>

        <section class="panel applications-panel">
          <div class="panel-heading"><div><h2>Recent applications</h2><p>Latest candidate activity from across the platform</p></div><button class="text-button" type="button">View all applications <span>→</span></button></div>
          <div class="table-scroll"><table><thead><tr><th>Company</th><th>Position</th><th>Candidate</th><th>Status</th><th>Received</th><th></th></tr></thead><tbody><tr v-for="application in applications" :key="application.company"><td><span class="company-cell"><span class="company-logo" :class="`company-logo--${application.color}`">{{ application.initials }}</span><strong>{{ application.company }}</strong></span></td><td>{{ application.role }}</td><td>{{ application.candidate }}</td><td><span class="status" :class="`status--${application.status.toLowerCase()}`">{{ application.status }}</span></td><td class="muted-cell">{{ application.time }}</td><td><button class="row-menu" type="button" aria-label="Application options">•••</button></td></tr></tbody></table></div>
        </section>
      </div>
    </main>
>>>>>>> e82792f (Update_api)
  </div>
</template>
