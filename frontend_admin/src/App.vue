<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  LayoutDashboard,
  Building2,
  Briefcase,
  Users,
  FileText,
  CalendarClock,
  Tags,
  Settings,
  Wrench,
  Archive,
  AlertTriangle,
  LogOut,
  Bell,
  ShieldCheck,
  ShieldAlert,
  Menu,
  X,
  ArrowLeft,
} from 'lucide-vue-next'
import LanguageSwitcher from './components/LanguageSwitcher.vue'
import ThemeToggle from './components/ThemeToggle.vue'
import NotificationDropdown from './components/NotificationDropdown.vue'
import AdminFooter from './components/AdminFooter.vue'
import { adminApi } from './api'
import { profileImage } from './utils/media'

const route = useRoute()
const router = useRouter()

const sidebarOpen = ref(false)
const user = ref(JSON.parse(localStorage.getItem('admin_user') || 'null'))
const clientUrl = import.meta.env.VITE_CLIENT_URL || 'http://localhost:5173/'


async function refreshAdminProfile() {
  if (!user.value?.id) return

  try {
    const { data } = await adminApi.showCandidate(user.value.id)
    const profile = data.data || data
    user.value = profile
    localStorage.setItem('admin_user', JSON.stringify(profile))
  } catch {
    // Keep the cached login identity available if the profile request fails.
  }
}

function openAdminProfile() {
  if (user.value?.id) router.push(`/candidates/${user.value.id}`)
}

const adminAvatar = computed(() => profileImage(user.value))

onMounted(refreshAdminProfile)

const navGroups = [
  {
    label: 'Workspace',
    items: [
      { name: 'Dashboard', path: '/dashboard', icon: LayoutDashboard },
      { name: 'Job Posts', path: '/job-posts', icon: Briefcase },
      { name: 'Users & HR', path: '/candidates', icon: Users },
      { name: 'Companies', path: '/companies', icon: Building2 },
    ],
  },
  {
    label: 'Recruitment',
    items: [
      { name: 'Applications', path: '/admin-resources/applications', icon: FileText },
      { name: 'Interviews', path: '/admin-resources/interviews', icon: CalendarClock },
      { name: 'Reports', path: '/reports', icon: FileText },
    ],
  },
  {
    label: 'Platform',
    items: [
      { name: 'Categories', path: '/admin-resources/categories', icon: Tags },
      { name: 'Skills', path: '/admin-resources/skills', icon: Wrench },
      { name: 'Queue Jobs', path: '/admin-resources/jobs', icon: Briefcase },
      { name: 'Job Batches', path: '/admin-resources/batches', icon: Archive },
      { name: 'Failed Jobs', path: '/admin-resources/failed-jobs', icon: AlertTriangle },
      { name: 'Security & Logs', path: '/security', icon: ShieldAlert },
      { name: 'Settings', path: '/settings', icon: Settings },
    ],
  },
]

const pageTitle = computed(() => {
  for (const group of navGroups) {
    const item = group.items.find((entry) => isActive(entry))
    if (item) return item.name
  }
  return 'Admin Console'
})

function isActive(item) {
  return route.path === item.path || route.path.startsWith(`${item.path}/`)
}

function logout() {
  localStorage.removeItem('admin_token')
  localStorage.removeItem('admin_user')
  router.push('/login')
}
</script>

<template>
  <div>
    <div id="google_translate_element" class="google-translate-host" aria-hidden="true"></div>

    <!-- Standalone pages (login) -->
    <router-view v-if="route.meta.public" />

    <!-- Admin layout -->
    <div v-else class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
    <aside
      class="fixed inset-y-0 left-0 z-40 flex w-72 flex-col bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 shadow-[8px_0_28px_rgba(15,23,42,0.03)] transition-transform lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="flex items-center justify-between gap-3 px-5 h-19 border-b border-slate-200 dark:border-slate-800 shrink-0">
        <div class="flex items-center gap-3 min-w-0">
          <div class="relative w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center font-black text-white shadow-lg shadow-blue-600/25 shrink-0">
            <span class="text-lg">A</span>
            <span class="absolute -right-1 -bottom-1 w-3 h-3 rounded-full bg-emerald-400 border-2 border-white dark:border-slate-900"></span>
          </div>
          <div class="min-w-0">
            <p class="font-bold text-slate-900 dark:text-slate-100 leading-tight truncate">Recruit Admin</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate">Operations workspace</p>
          </div>
        </div>
        <button class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Close menu" @click="sidebarOpen = false"><X class="w-5 h-5" /></button>
      </div>

      <nav class="admin-sidebar-scroll flex-1 overflow-y-auto px-3 py-5" aria-label="Main navigation">
        <div v-for="group in navGroups" :key="group.label" class="mb-6 last:mb-0">
          <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">{{ group.label }}</p>
          <div class="space-y-1">
            <router-link
              v-for="item in group.items"
              :key="item.path"
              :to="item.path"
              class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200"
              :class="isActive(item)
                ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20'
                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-slate-100'"
              @click="sidebarOpen = false"
            >
              <span v-if="isActive(item)" class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-white/90"></span>
              <component :is="item.icon" class="w-4.5 h-4.5 shrink-0" :class="isActive(item) ? 'text-white' : 'text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400'" />
              <span class="truncate">{{ item.name }}</span>
              <span v-if="isActive(item)" class="ml-auto w-1.5 h-1.5 rounded-full bg-white/90"></span>
            </router-link>
          </div>
        </div>
      </nav>

      <div class="p-3 border-t border-slate-200 dark:border-slate-800 shrink-0">
        <button class="w-full flex items-center gap-3 px-3 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 mb-2 text-left hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-colors" @click="openAdminProfile">
          <div class="w-9 h-9 rounded-full bg-blue-100 dark:bg-blue-950/70 flex items-center justify-center text-xs font-bold text-blue-700 dark:text-blue-300 uppercase shrink-0 overflow-hidden">
            <img v-if="adminAvatar" :src="adminAvatar" :alt="user?.name || 'Admin'" class="w-full h-full object-cover" />
            <template v-else>
            {{ (user?.name || 'A').charAt(0) }}
            </template>
          </div>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">{{ user?.name || 'Admin' }}</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate flex items-center gap-1 mt-0.5">
              <ShieldCheck class="w-3 h-3 text-emerald-500" /> {{ user?.role || 'admin' }} account
            </p>
          </div>
        </button>
        <!-- Back to Client Site -->
        <a
          :href="clientUrl"
          class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-blue-50 dark:hover:bg-blue-950/30 hover:text-blue-600 dark:hover:text-blue-400 transition-colors mb-1"
          title="Back to Job Search Client"
        >
          <ArrowLeft class="w-5 h-5 shrink-0" />
          <span>Back to Client</span>
        </a>
        <button
          @click="logout"
          class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-rose-600 dark:hover:text-rose-400 transition-colors"
        >
          <LogOut class="w-5 h-5" />
          <span>Sign out</span>
        </button>
      </div>
    </aside>

    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-30 bg-black/50 lg:hidden"
      @click="sidebarOpen = false"
    ></div>

    <div class="lg:pl-72 flex flex-col min-h-screen">
      <header class="sticky top-0 z-20 h-16 bg-white/95 dark:bg-slate-900/95 backdrop-blur border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 sm:px-6 gap-4">
        <div class="flex items-center gap-3 min-w-0">
          <button
            class="lg:hidden p-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"
            @click="sidebarOpen = true"
            aria-label="Open menu"
          >
            <Menu class="w-5 h-5" />
          </button>
          <div class="hidden sm:block min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">Admin workspace</p>
            <h1 class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate">{{ pageTitle }}</h1>
          </div>
        </div>

        <div class="flex items-center gap-3 ml-auto">
          <LanguageSwitcher />
          <ThemeToggle />
          <NotificationDropdown />
          <button class="hidden sm:flex items-center gap-2 pl-3 border-l border-slate-200 dark:border-slate-800 hover:opacity-80" title="Open admin profile" @click="openAdminProfile">
            <div class="w-8 h-8 rounded-full bg-slate-300 dark:bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-800 dark:text-slate-200 uppercase overflow-hidden">
              <img v-if="adminAvatar" :src="adminAvatar" :alt="user?.name || 'Admin'" class="w-full h-full object-cover" />
              <template v-else>
              {{ (user?.name || 'A').charAt(0) }}
              </template>
            </div>
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300 hidden md:block">{{ user?.name }}</span>
          </button>
        </div>
      </header>

      <!-- Page content -->
      <main class="flex-1 overflow-x-hidden">
        <router-view />
      </main>

      <!-- Admin Footer Component -->
      <AdminFooter status="All systems operational" statusTone="emerald" />
    </div>
    </div>
  </div>
</template>