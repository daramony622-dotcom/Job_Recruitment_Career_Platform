<script setup>
import { ref, computed } from 'vue'
import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
import {
  Bell, BellOff, Check, CheckCheck, Trash2, Filter,
  Mail, MessageSquare, Briefcase, Building2, Star,
  ChevronDown, RefreshCcw, Inbox, Clock, Circle
} from 'lucide-vue-next'

// ─── Mock Notification Data (matches schema) ──────────────────────────────────
const notifications = ref([
  {
    id: 'a1b2c3d4-0001-0001-0001-000000000001',
    type: 'App\\Notifications\\JobApplicationReceived',
    notifiable_id: 1,
    notifiable_type: 'App\\Models\\User',
    data: {
      title: 'New Application Received',
      body: 'TechMatrix Global has reviewed your application for Senior Full Stack Developer.',
      action_url: '/jobs/1',
      action_label: 'View Application',
      icon_type: 'job',
      company: 'TechMatrix Global',
      avatar: 'https://images.unsplash.com/photo-1549923746-c502d488b3ea?w=60&h=60&fit=crop'
    },
    channel: 'database',
    read_at: null,
    created_at: '2026-09-02T08:15:00Z'
  },
  {
    id: 'a1b2c3d4-0001-0001-0001-000000000002',
    type: 'App\\Notifications\\InterviewScheduled',
    notifiable_id: 1,
    notifiable_type: 'App\\Models\\User',
    data: {
      title: 'Interview Scheduled 🎉',
      body: 'ABA Digital Tech has scheduled an interview for the UI/UX Designer role on Sep 5, 2026 at 10:00 AM.',
      action_url: '/jobs/2',
      action_label: 'View Details',
      icon_type: 'interview',
      company: 'ABA Digital Tech',
      avatar: 'https://images.unsplash.com/photo-1560179707-f14e90ef3623?w=60&h=60&fit=crop'
    },
    channel: 'email',
    read_at: null,
    created_at: '2026-09-02T07:42:00Z'
  },
  {
    id: 'a1b2c3d4-0001-0001-0001-000000000003',
    type: 'App\\Notifications\\JobRecommendation',
    notifiable_id: 1,
    notifiable_type: 'App\\Models\\User',
    data: {
      title: 'New Job Matching Your Profile',
      body: 'We found 3 new jobs matching your skills in Vue.js, Laravel, and Tailwind CSS.',
      action_url: '/jobs',
      action_label: 'Browse Matches',
      icon_type: 'recommendation',
      company: null,
      avatar: null
    },
    channel: 'database',
    read_at: '2026-09-01T14:20:00Z',
    created_at: '2026-09-01T12:00:00Z'
  },
  {
    id: 'a1b2c3d4-0001-0001-0001-000000000004',
    type: 'App\\Notifications\\ProfileViewAlert',
    notifiable_id: 1,
    notifiable_type: 'App\\Models\\User',
    data: {
      title: 'Your Profile Was Viewed',
      body: 'A recruiter from CambodiaTech Solutions viewed your profile and saved it to their shortlist.',
      action_url: '/profile',
      action_label: 'View Profile',
      icon_type: 'profile',
      company: 'CambodiaTech Solutions',
      avatar: null
    },
    channel: 'database',
    read_at: '2026-09-01T10:00:00Z',
    created_at: '2026-09-01T09:30:00Z'
  },
  {
    id: 'a1b2c3d4-0001-0001-0001-000000000005',
    type: 'App\\Notifications\\ApplicationStatusUpdate',
    notifiable_id: 1,
    notifiable_type: 'App\\Models\\User',
    data: {
      title: 'Application Status Updated',
      body: 'Your application for Data Analyst at Wing Bank has been moved to the final round.',
      action_url: '/jobs/5',
      action_label: 'Check Status',
      icon_type: 'status',
      company: 'Wing Bank',
      avatar: 'https://images.unsplash.com/photo-1521791136064-7986c2920216?w=60&h=60&fit=crop'
    },
    channel: 'sms',
    read_at: null,
    created_at: '2026-08-31T16:45:00Z'
  },
  {
    id: 'a1b2c3d4-0001-0001-0001-000000000006',
    type: 'App\\Notifications\\SystemAnnouncement',
    notifiable_id: 1,
    notifiable_type: 'App\\Models\\User',
    data: {
      title: 'Platform Maintenance Notice',
      body: 'Job Search will undergo scheduled maintenance on Sep 3, 2026 from 2:00–4:00 AM (ICT). Some features may be unavailable.',
      action_url: null,
      action_label: null,
      icon_type: 'system',
      company: null,
      avatar: null
    },
    channel: 'email',
    read_at: '2026-08-31T08:00:00Z',
    created_at: '2026-08-30T18:00:00Z'
  }
])

// ─── Filter State ─────────────────────────────────────────────────────────────
const activeFilter = ref('all')
const activeChannel = ref('all')
const isRefreshing = ref(false)

const filters = [
  { key: 'all', label: 'All' },
  { key: 'unread', label: 'Unread' },
  { key: 'read', label: 'Read' }
]

const channels = [
  { key: 'all', label: 'All Channels' },
  { key: 'database', label: 'In-App' },
  { key: 'email', label: 'Email' },
  { key: 'sms', label: 'SMS' }
]

// ─── Derived Lists ────────────────────────────────────────────────────────────
const filteredNotifications = computed(() => {
  return notifications.value.filter(n => {
    const matchesRead =
      activeFilter.value === 'all' ||
      (activeFilter.value === 'unread' && !n.read_at) ||
      (activeFilter.value === 'read' && !!n.read_at)

    const matchesChannel =
      activeChannel.value === 'all' || n.channel === activeChannel.value

    return matchesRead && matchesChannel
  })
})

const unreadCount = computed(() => notifications.value.filter(n => !n.read_at).length)

// ─── Actions ──────────────────────────────────────────────────────────────────
const markAsRead = (id) => {
  const n = notifications.value.find(n => n.id === id)
  if (n && !n.read_at) n.read_at = new Date().toISOString()
}

const markAllRead = () => {
  notifications.value.forEach(n => {
    if (!n.read_at) n.read_at = new Date().toISOString()
  })
}

const deleteNotification = (id) => {
  notifications.value = notifications.value.filter(n => n.id !== id)
}

const refresh = () => {
  isRefreshing.value = true
  setTimeout(() => { isRefreshing.value = false }, 1200)
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
const formatTime = (iso) => {
  const date = new Date(iso)
  const now = new Date()
  const diff = Math.floor((now - date) / 1000)
  if (diff < 60) return 'Just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  return `${Math.floor(diff / 86400)}d ago`
}

const iconConfig = {
  job: { bg: 'bg-blue-50 dark:bg-blue-950/60', text: 'text-blue-600 dark:text-blue-400', icon: Briefcase },
  interview: { bg: 'bg-emerald-50 dark:bg-emerald-950/60', text: 'text-emerald-600 dark:text-emerald-400', icon: Star },
  recommendation: { bg: 'bg-indigo-50 dark:bg-indigo-950/60', text: 'text-indigo-600 dark:text-indigo-400', icon: Bell },
  profile: { bg: 'bg-purple-50 dark:bg-purple-950/60', text: 'text-purple-600 dark:text-purple-400', icon: Building2 },
  status: { bg: 'bg-amber-50 dark:bg-amber-950/60', text: 'text-amber-600 dark:text-amber-400', icon: CheckCheck },
  system: { bg: 'bg-slate-100 dark:bg-slate-800', text: 'text-slate-600 dark:text-slate-400', icon: MessageSquare }
}

const channelBadge = {
  database: { label: 'In-App', cls: 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400' },
  email: { label: 'Email', cls: 'bg-violet-50 dark:bg-violet-950/60 text-violet-600 dark:text-violet-400' },
  sms: { label: 'SMS', cls: 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' }
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors duration-200">
    <Navbar />

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">

      <!-- ─── Page Header ─────────────────────────────────────────────────── -->
      <div class="flex items-start justify-between gap-4 flex-wrap">
        <div class="space-y-1">
          <div class="flex items-center gap-2.5">
            <div class="p-2.5 bg-blue-600 text-white rounded-2xl shadow-md shadow-blue-500/20">
              <Bell class="w-5 h-5" />
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
              Notifications
            </h1>
            <span v-if="unreadCount > 0" class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full bg-blue-600 text-white text-xs font-bold min-w-[24px]">
              {{ unreadCount }}
            </span>
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Stay up to date with your job applications, interviews, and platform updates.
          </p>
        </div>

        <!-- Header Actions -->
        <div class="flex items-center gap-2 flex-wrap">
          <button
            @click="refresh"
            :disabled="isRefreshing"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold transition cursor-pointer shadow-xs"
          >
            <RefreshCcw class="w-4 h-4" :class="{ 'animate-spin': isRefreshing }" />
            <span>Refresh</span>
          </button>

          <button
            v-if="unreadCount > 0"
            @click="markAllRead"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition cursor-pointer shadow-md shadow-blue-500/20 active:scale-95"
          >
            <CheckCheck class="w-4 h-4" />
            <span>Mark All Read</span>
          </button>
        </div>
      </div>

      <!-- ─── Filter Tabs & Channel Selector ─────────────────────────────── -->
      <div class="flex items-center justify-between gap-4 flex-wrap">
        <!-- Read/Unread Tabs -->
        <div class="flex items-center gap-1 p-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs">
          <button
            v-for="f in filters"
            :key="f.key"
            @click="activeFilter = f.key"
            class="px-4 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer"
            :class="activeFilter === f.key
              ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
              : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'"
          >
            {{ f.label }}
          </button>
        </div>

        <!-- Channel Filter -->
        <div class="flex items-center gap-2">
          <Filter class="w-4 h-4 text-slate-400 shrink-0" />
          <div class="flex items-center gap-1 flex-wrap">
            <button
              v-for="ch in channels"
              :key="ch.key"
              @click="activeChannel = ch.key"
              class="px-3 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer"
              :class="activeChannel === ch.key
                ? 'bg-slate-800 dark:bg-white text-white dark:text-slate-900'
                : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'"
            >
              {{ ch.label }}
            </button>
          </div>
        </div>
      </div>

      <!-- ─── Notification List ───────────────────────────────────────────── -->
      <div class="space-y-3">

        <!-- Empty State -->
        <div
          v-if="filteredNotifications.length === 0"
          class="flex flex-col items-center justify-center py-20 text-center space-y-4"
        >
          <div class="p-5 bg-slate-100 dark:bg-slate-800 rounded-3xl">
            <BellOff class="w-10 h-10 text-slate-400 dark:text-slate-500" />
          </div>
          <h3 class="text-base font-bold text-slate-700 dark:text-slate-300">No notifications</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs">
            {{ activeFilter === 'unread' ? "You're all caught up! No unread notifications." : "No notifications match the current filter." }}
          </p>
        </div>

        <!-- Notification Items -->
        <transition-group name="list" tag="div" class="space-y-3">
          <div
            v-for="n in filteredNotifications"
            :key="n.id"
            @click="markAsRead(n.id)"
            class="group relative bg-white dark:bg-slate-900 border rounded-3xl p-5 shadow-xs hover:shadow-md transition duration-200 cursor-pointer"
            :class="!n.read_at
              ? 'border-blue-200 dark:border-blue-900/60 ring-1 ring-blue-100 dark:ring-blue-900/40'
              : 'border-slate-200/80 dark:border-slate-800'"
          >
            <!-- Unread Dot -->
            <span
              v-if="!n.read_at"
              class="absolute top-4 right-4 w-2.5 h-2.5 bg-blue-600 rounded-full ring-2 ring-white dark:ring-slate-900"
            ></span>

            <div class="flex items-start gap-4">

              <!-- Icon or Avatar -->
              <div class="shrink-0">
                <img
                  v-if="n.data.avatar"
                  :src="n.data.avatar"
                  :alt="n.data.company"
                  class="w-12 h-12 rounded-2xl object-cover border border-slate-100 dark:border-slate-800"
                />
                <div
                  v-else
                  class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
                  :class="iconConfig[n.data.icon_type]?.bg || 'bg-slate-100 dark:bg-slate-800'"
                >
                  <component
                    :is="iconConfig[n.data.icon_type]?.icon || Bell"
                    class="w-5 h-5"
                    :class="iconConfig[n.data.icon_type]?.text || 'text-slate-500'"
                  />
                </div>
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0 space-y-1.5">
                <div class="flex items-center gap-2 flex-wrap">
                  <h3
                    class="text-sm font-bold"
                    :class="!n.read_at ? 'text-slate-900 dark:text-white' : 'text-slate-700 dark:text-slate-300'"
                  >
                    {{ n.data.title }}
                  </h3>

                  <!-- Channel Badge -->
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide"
                    :class="channelBadge[n.channel]?.cls"
                  >
                    {{ channelBadge[n.channel]?.label }}
                  </span>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  {{ n.data.body }}
                </p>

                <!-- Footer: Timestamp + Action -->
                <div class="flex items-center justify-between gap-3 pt-1 flex-wrap">
                  <span class="flex items-center gap-1.5 text-[11px] text-slate-400 font-medium">
                    <Clock class="w-3.5 h-3.5" />
                    {{ formatTime(n.created_at) }}
                  </span>

                  <div class="flex items-center gap-2">
                    <a
                      v-if="n.data.action_url"
                      :href="n.data.action_url"
                      @click.stop
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 dark:hover:bg-blue-950/80 text-blue-600 dark:text-blue-400 rounded-xl text-[11px] font-bold transition"
                    >
                      {{ n.data.action_label }}
                    </a>
                  </div>
                </div>
              </div>

              <!-- Delete Button (hover) -->
              <button
                @click.stop="deleteNotification(n.id)"
                class="shrink-0 p-2 text-slate-300 dark:text-slate-700 hover:text-rose-500 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition opacity-0 group-hover:opacity-100 cursor-pointer"
                title="Delete notification"
              >
                <Trash2 class="w-4 h-4" />
              </button>

            </div>
          </div>
        </transition-group>

      </div>

      <!-- ─── Load More Footer ───────────────────────────────────────────── -->
      <div v-if="filteredNotifications.length > 0" class="flex justify-center pt-2">
        <button class="inline-flex items-center gap-2 px-6 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-2xl text-xs font-semibold shadow-xs transition cursor-pointer">
          <ChevronDown class="w-4 h-4" />
          <span>Load More Notifications</span>
        </button>
      </div>

    </main>

    <Footer />
  </div>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.25s ease;
}
.list-enter-from {
  opacity: 0;
  transform: translateY(-8px);
}
.list-leave-to {
  opacity: 0;
  transform: translateX(20px);
}
</style>
