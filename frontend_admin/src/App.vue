<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  LayoutDashboard, Building2, Briefcase, Users, FileText, CalendarClock,
  Tags, Settings, Wrench, Archive, AlertTriangle, LogOut, Bell, ShieldAlert,
  Menu, X, ArrowLeft, FolderTree, Mail, ExternalLink, Search, ChevronDown
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
const user = ref(JSON.parse(localStorage.getItem('admin_user') || localStorage.getItem('user') || 'null'))
const clientUrl = import.meta.env.VITE_CLIENT_URL || 'http://localhost:5173/'
const isCompanyScopedUser = computed(() => ['hr', 'company'].includes(user.value?.role))

watch(() => route.path, () => { sidebarOpen.value = false })

async function refreshAdminProfile() {
  try {
    const { data } = await adminApi.getMe()
    const profile = data.data || data
    user.value = profile
    localStorage.setItem('admin_user', JSON.stringify(profile))
    localStorage.setItem('user', JSON.stringify(profile))
  } catch { /* Keep cached session */ }
}

const adminAvatar = computed(() => profileImage(user.value))

onMounted(refreshAdminProfile)

const navGroups = computed(() => {
  const workspaceItems = [
    { name: 'Dashboard', path: '/dashboard', icon: LayoutDashboard },
    { name: 'Job Posts', path: '/job-posts', icon: Briefcase },
  ]

  if (isCompanyScopedUser.value) {
    return [
      { label: 'MAIN', items: workspaceItems },
      {
        label: 'RECRUITMENT',
        items: [
          { name: 'Applications', path: '/admin-resources/applications', icon: FileText },
          { name: 'Interviews', path: '/admin-resources/interviews', icon: CalendarClock },
          { name: 'Job Categories', path: '/admin-resources/categories', icon: Tags },
          { name: 'Skills Directory', path: '/admin-resources/skills', icon: Wrench },
          { name: 'Reports', path: '/reports', icon: FileText },
          { name: 'Settings', path: '/settings', icon: Settings },
        ],
      },
    ]
  }

  return [
    {
      label: 'MAIN',
      items: [
        ...workspaceItems,
        { name: 'Users & HR', path: '/candidates', icon: Users },
        { name: 'Companies', path: '/companies', icon: Building2 },
      ],
    },
    {
      label: 'SHORTCUTS',
      items: [
        { name: 'Applications', path: '/admin-resources/applications', icon: FileText },
        { name: 'Interviews', path: '/admin-resources/interviews', icon: CalendarClock },
        { name: 'Reports', path: '/reports', icon: FileText },
        { name: 'Job Categories', path: '/admin-resources/categories', icon: Tags },
        { name: 'Skills', path: '/admin-resources/skills', icon: Wrench },
        { name: 'Messages', path: '/admin-resources/messages', icon: Mail },
        { name: 'Security', path: '/security', icon: ShieldAlert },
        { name: 'Settings', path: '/settings', icon: Settings },
      ],
    },
  ]
})

const pageTitle = computed(() => {
  for (const group of navGroups.value) {
    const item = group.items.find((entry) => isActive(entry))
    if (item) return item.name
  }
  return 'Dashboard'
})

function isActive(item) {
  return route.path === item.path || route.path.startsWith(`${item.path}/`)
}

function logout() {
  localStorage.clear()
  window.location.href = `${clientUrl.replace(/\/$/, '')}/login`
}
</script>

<template>
  <div class="admin-shell min-h-screen bg-slate-50 text-slate-800 dark:bg-[#0c1420] dark:text-slate-100 flex flex-col font-sans">
    <!-- Top Header -->
    <header class="admin-header sticky top-0 z-30 h-16 border-b border-slate-200/80 bg-white/90 px-4 sm:px-6 backdrop-blur-xl dark:border-slate-800 dark:bg-[#101a29]/90">
      <div class="mx-auto flex h-full max-w-[1600px] items-center justify-between gap-3">
        <!-- Left: Mobile Menu & Search -->
        <div class="flex items-center gap-3 min-w-0 flex-1">
          <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:border-blue-200 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
            <Menu v-if="!sidebarOpen" class="w-5 h-5" />
            <X v-else class="w-5 h-5" />
          </button>
          
          <div class="relative hidden md:block w-full max-w-md">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input type="text" placeholder="Search..." class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl py-2 pl-9 pr-4 text-sm focus:ring-2 focus:ring-blue-500/20 outline-none" />
          </div>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-2 sm:gap-3">
          <a :href="clientUrl" class="hidden sm:inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-semibold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
            <ExternalLink class="w-3.5 h-3.5" /> Client Site
          </a>
          <NotificationDropdown />
          <LanguageSwitcher />
          <ThemeToggle />
          <div class="h-7 w-px bg-slate-200 dark:bg-slate-700"></div>
          <button @click="router.push(`/candidates/${user?.id}`)" class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-2 py-1.5 transition hover:border-blue-200 dark:border-slate-700 dark:bg-slate-800">
            <img :src="adminAvatar" class="h-8 w-8 rounded-lg object-cover bg-slate-200" />
            <div class="hidden sm:block text-left pr-1">
              <p class="text-[11px] font-bold leading-tight text-slate-800 dark:text-white">{{ user?.name || 'Admin' }}</p>
              <p class="text-[9px] uppercase font-semibold tracking-[0.18em] text-blue-600 dark:text-sky-300">{{ user?.role || 'Admin' }}</p>
            </div>
          </button>
        </div>
      </div>
    </header>

    <div class="flex flex-1 relative">
      <!-- Mobile Overlay -->
      <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-slate-950/45 backdrop-blur-[2px] lg:hidden"></div>

      <!-- Sidebar -->
      <aside :class="[
        'admin-sidebar fixed lg:sticky top-16 z-40 h-[calc(100vh-4rem)] w-72 border-r border-slate-200/80 bg-white shadow-[10px_0_30px_rgba(15,23,42,0.04)] transition-transform duration-200 ease-in-out dark:border-slate-800 dark:bg-[#101a29]',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]">
        <div class="flex h-full flex-col">
          <!-- Logo -->
          <div class="flex items-center gap-3 px-6 h-16 border-b border-slate-100 dark:border-slate-800">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-600/20">
              <Briefcase class="h-5 w-5" />
            </div>
            <span class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">BILLON</span>
          </div>

          <!-- User Profile Snippet (Image style) -->
          <div class="px-4 py-4">
            <div class="flex items-center gap-3 rounded-2xl bg-slate-50 dark:bg-slate-800/50 p-3 border border-slate-100 dark:border-slate-700/50">
              <img :src="adminAvatar" class="h-10 w-10 rounded-xl object-cover bg-slate-200" />
              <div class="min-w-0 flex-1">
                <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">Management</p>
                <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate">general team</p>
              </div>
              <ChevronDown class="w-4 h-4 text-slate-400" />
            </div>
          </div>

          <!-- Navigation -->
          <div class="flex-1 overflow-y-auto px-3 py-2 space-y-6">
            <div v-for="group in navGroups" :key="group.label">
              <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">{{ group.label }}</p>
              <router-link
                v-for="item in group.items"
                :key="item.path"
                :to="item.path"
                :class="[
                  'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-all duration-200 mb-1',
                  isActive(item)
                    ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300'
                    : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/80 dark:hover:text-slate-100'
                ]"
              >
                <component :is="item.icon" class="w-4 h-4 shrink-0" />
                <span>{{ item.name }}</span>
              </router-link>
            </div>
          </div>

          <!-- Bottom Actions -->
          <div class="p-4 border-t border-slate-100 dark:border-slate-800 space-y-2">
            <a :href="clientUrl" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800/80 transition-colors">
              <ArrowLeft class="w-4 h-4" /> Back to Client Site
            </a>
            <button @click="logout" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/30 transition-colors">
              <LogOut class="w-4 h-4" /> Sign Out
            </button>
          </div>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="admin-main flex-1 min-w-0 p-4 sm:p-6 lg:p-8 bg-slate-50/50 dark:bg-[#0c1420]">
        <div class="mx-auto max-w-screen-2xl">
          <router-view />
        </div>
      </main>
    </div>
    <AdminFooter />
  </div>
</template>