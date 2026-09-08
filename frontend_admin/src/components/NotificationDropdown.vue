<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  Bell,
  Check,
  CheckCheck,
  ExternalLink,
  RefreshCw,
  ShieldAlert,
  ShieldCheck,
  AlertTriangle,
  Briefcase,
  Building2,
  Users,
  Clock,
  Trash2,
  X
} from 'lucide-vue-next'
import { adminApi } from '../api'

const router = useRouter()
const isOpen = ref(false)
const loading = ref(false)
const activeTab = ref('all') // 'all' | 'unread' | 'security' | 'recruitment'

const notifications = ref([
  {
    id: 'sec_admin_access',
    title: 'Admin Privileged Access',
    message: 'Active administrator session initialized with Sanctum bearer token.',
    category: 'security',
    level: 'info',
    link: '/security',
    read: false,
    created_at: new Date(Date.now() - 5 * 60000).toISOString(),
  },
  {
    id: 'sec_pending_companies',
    title: 'Pending Company Verification',
    message: 'New company registration requires document and credential verification.',
    category: 'security',
    level: 'warning',
    link: '/companies',
    read: false,
    created_at: new Date(Date.now() - 25 * 60000).toISOString(),
  },
  {
    id: 'rec_draft_jobs',
    title: 'Draft Job Posts',
    message: 'Recent job drafts pending review and publication.',
    category: 'recruitment',
    level: 'info',
    link: '/job-posts',
    read: true,
    created_at: new Date(Date.now() - 120 * 60000).toISOString(),
  }
])

const unreadCount = computed(() => {
  return notifications.value.filter((n) => !n.read).length
})

const filteredNotifications = computed(() => {
  if (activeTab.value === 'unread') {
    return notifications.value.filter((n) => !n.read)
  }
  if (activeTab.value === 'security') {
    return notifications.value.filter((n) => n.category === 'security' || n.category === 'system')
  }
  if (activeTab.value === 'recruitment') {
    return notifications.value.filter((n) => n.category === 'recruitment')
  }
  return notifications.value
})

async function fetchNotifications() {
  loading.value = true
  try {
    const { data } = await adminApi.getNotifications()
    if (data?.data && Array.isArray(data.data) && data.data.length > 0) {
      notifications.value = data.data
    }
  } catch {
    // Keep cached / default fallback notifications on error
  } finally {
    loading.value = false
  }
}

async function markAsRead(notification) {
  notification.read = true
  try {
    await adminApi.markNotificationAsRead(notification.id)
  } catch {
    // Graceful fallback
  }
}

async function markAllAsRead() {
  notifications.value.forEach((n) => (n.read = true))
  try {
    await adminApi.markAllNotificationsAsRead()
  } catch {
    // Graceful fallback
  }
}

async function deleteNotification(id, event) {
  event?.stopPropagation()
  notifications.value = notifications.value.filter((n) => n.id !== id)
  try {
    await adminApi.deleteNotification(id)
  } catch {
    // Graceful fallback
  }
}

function handleNotificationClick(notification) {
  markAsRead(notification)
  isOpen.value = false
  if (notification.link) {
    router.push(notification.link)
  }
}

function formatTimeAgo(isoString) {
  if (!isoString) return ''
  const diffMs = Date.now() - new Date(isoString).getTime()
  const diffMins = Math.floor(diffMs / 60000)
  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  const diffHours = Math.floor(diffMins / 60)
  if (diffHours < 24) return `${diffHours}h ago`
  return `${Math.floor(diffHours / 24)}d ago`
}

function getIcon(notification) {
  if (notification.category === 'security') return ShieldAlert
  if (notification.level === 'danger' || notification.level === 'warning') return AlertTriangle
  if (notification.category === 'recruitment') return Briefcase
  return ShieldCheck
}

function getIconColor(notification) {
  if (notification.level === 'danger') return 'text-rose-600 bg-rose-50 dark:bg-rose-950/50 dark:text-rose-400'
  if (notification.level === 'warning') return 'text-amber-600 bg-amber-50 dark:bg-amber-950/50 dark:text-amber-400'
  if (notification.category === 'security') return 'text-purple-600 bg-purple-50 dark:bg-purple-950/50 dark:text-purple-400'
  return 'text-blue-600 bg-blue-50 dark:bg-blue-950/50 dark:text-blue-400'
}

function handleOutsideClick(event) {
  if (!event.target.closest('.notification-dropdown-container')) {
    isOpen.value = false
  }
}

onMounted(() => {
  fetchNotifications()
  document.addEventListener('click', handleOutsideClick)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleOutsideClick)
})
</script>

<template>
  <div class="notification-dropdown-container relative inline-block">
    <!-- Trigger Button -->
    <button
      type="button"
      @click="isOpen = !isOpen"
      class="relative p-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
      :aria-expanded="isOpen"
      aria-haspopup="true"
      aria-label="Security and system notifications"
      :title="`${unreadCount} unread notification(s)`"
    >
      <Bell class="w-5 h-5" />
      
      <!-- Unread Indicator -->
      <span
        v-if="unreadCount > 0"
        class="absolute top-1.5 right-1.5 flex h-2.5 w-2.5 items-center justify-center"
      >
        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-400 opacity-75"></span>
        <span class="relative inline-flex h-2 w-2 rounded-full bg-rose-500"></span>
      </span>
    </button>

    <!-- Dropdown Panel -->
    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0 -translate-y-1 scale-95"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="opacity-100 translate-y-0 scale-100"
      leave-to-class="opacity-0 -translate-y-1 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-2xl shadow-slate-900/15 dark:shadow-black/40 z-50 overflow-hidden"
      >
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
          <div class="flex items-center gap-2">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Notifications & Security</h3>
            <span
              v-if="unreadCount > 0"
              class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300"
            >
              {{ unreadCount }} new
            </span>
          </div>

          <div class="flex items-center gap-1">
            <button
              v-if="unreadCount > 0"
              @click="markAllAsRead"
              type="button"
              class="p-1.5 text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/50 rounded-lg transition-colors flex items-center gap-1 font-semibold cursor-pointer"
              title="Mark all as read"
            >
              <CheckCheck class="w-3.5 h-3.5" />
              <span class="hidden sm:inline text-[11px]">Read all</span>
            </button>
            <button
              @click="fetchNotifications"
              type="button"
              class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer"
              title="Refresh"
            >
              <RefreshCw class="w-3.5 h-3.5" :class="loading ? 'animate-spin' : ''" />
            </button>
          </div>
        </div>

        <!-- Filter Tabs -->
        <div class="flex items-center gap-1 px-3 py-2 border-b border-slate-100 dark:border-slate-800/80 bg-white dark:bg-slate-900 text-xs font-semibold overflow-x-auto">
          <button
            v-for="tab in [
              { id: 'all', label: 'All' },
              { id: 'unread', label: 'Unread' },
              { id: 'security', label: 'Security & System' },
              { id: 'recruitment', label: 'Recruitment' },
            ]"
            :key="tab.id"
            @click="activeTab = tab.id"
            class="px-2.5 py-1 rounded-lg transition-colors whitespace-nowrap cursor-pointer"
            :class="activeTab === tab.id
              ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/70 dark:text-blue-300 font-bold'
              : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800'"
          >
            {{ tab.label }}
          </button>
        </div>

        <!-- Notification List -->
        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/60">
          <div
            v-if="filteredNotifications.length === 0"
            class="py-10 text-center px-4"
          >
            <ShieldCheck class="w-8 h-8 text-emerald-500 mx-auto mb-2 opacity-80" />
            <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">All caught up!</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">No notifications in this view.</p>
          </div>

          <div
            v-for="item in filteredNotifications"
            :key="item.id"
            @click="handleNotificationClick(item)"
            class="group relative flex items-start gap-3 p-3.5 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors cursor-pointer"
            :class="!item.read ? 'bg-blue-50/30 dark:bg-blue-950/20' : ''"
          >
            <!-- Status indicator line for unread -->
            <span
              v-if="!item.read"
              class="absolute left-0 top-3 bottom-3 w-1 rounded-r-full bg-blue-600 dark:bg-blue-400"
            ></span>

            <!-- Category / Level Icon -->
            <div
              class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
              :class="getIconColor(item)"
            >
              <component :is="getIcon(item)" class="w-4 h-4" />
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between gap-1 mb-0.5">
                <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">
                  {{ item.title }}
                </p>
                <span class="text-[10px] text-slate-400 dark:text-slate-500 shrink-0 font-medium">
                  {{ formatTimeAgo(item.created_at) }}
                </span>
              </div>
              <p class="text-xs text-slate-600 dark:text-slate-300 leading-snug line-clamp-2">
                {{ item.message }}
              </p>
              
              <div class="flex items-center gap-2 mt-2">
                <span
                  class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded"
                  :class="item.category === 'security'
                    ? 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300'
                    : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'"
                >
                  {{ item.category }}
                </span>
                <span v-if="item.link" class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-0.5">
                  View details <ExternalLink class="w-2.5 h-2.5" />
                </span>
              </div>
            </div>

            <!-- Action button -->
            <div class="shrink-0 flex items-center self-center opacity-0 group-hover:opacity-100 transition-opacity">
              <button
                type="button"
                @click="deleteNotification(item.id, )"
                class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg"
                title="Remove"
              >
                <X class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="p-2.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 text-center">
          <router-link
            to="/security"
            @click="isOpen = false"
            class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors inline-flex items-center gap-1.5"
          >
            <ShieldCheck class="w-3.5 h-3.5" /> Open Security & Audit Center
          </router-link>
        </div>
      </div>
    </Transition>
  </div>
</template>
