<script setup>
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
  </div>
</template>
