<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  LayoutDashboard, Building2, Briefcase, Users, FileText, CalendarClock,
  Tags, Settings, Wrench, Archive, AlertTriangle, LogOut, Bell, ShieldAlert,
  Menu, X, ArrowLeft, FolderTree, Mail, ExternalLink, Globe
} from 'lucide-vue-next'
import LanguageSwitcher from './LanguageSwitcher.vue'
import ThemeToggle from './ThemeToggle.vue'
import NotificationDropdown from './NotificationDropdown.vue'
import AdminFooter from './AdminFooter.vue'
import { adminApi } from '../api'

import { profileImage } from '../utils/media'

const route = useRoute()
const router = useRouter()

const sidebarOpen = ref(false)
const user = ref(JSON.parse(localStorage.getItem('admin_user') || localStorage.getItem('user') || 'null'))
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

onMounted(refreshAdminProfile)

const navGroups = computed(() => {
  const companyOnly = isCompanyScopedUser.value
  return [
    {
      label: 'MAIN',
      items: [
        { name: 'Dashboard', path: '/dashboard', icon: LayoutDashboard },
        { name: 'Job Posts', path: '/job-posts', icon: Briefcase },
        { name: 'Applications', path: '/admin-resources/applications', icon: FileText },
        { name: 'Interviews', path: '/admin-resources/interviews', icon: CalendarClock },
        ...(user.value?.role !== 'hr' ? [{ name: 'Companies', path: '/admin-companies', icon: Building2 }] : []),
        ...(!companyOnly || user.value?.role === 'hr' ? [{ name: 'Users & Candidates', path: '/candidates', icon: Users }] : []),
      ],
    },
    {
      label: 'MANAGEMENT & TAXONOMY',
      items: [
        { name: 'Job Categories', path: '/admin-resources/categories', icon: Tags },
        { name: 'Skill Categories', path: '/admin-resources/skill-categories', icon: FolderTree },
        { name: 'Skills Directory', path: '/admin-resources/skills', icon: Wrench },
        ...(!companyOnly ? [{ name: 'Inquiries & Messages', path: '/admin-resources/messages', icon: Mail }] : []),
        { name: 'Reports', path: '/reports', icon: FileText },
        ...(!companyOnly ? [{ name: 'Security', path: '/security', icon: ShieldAlert }] : []),
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

async function logout() {
  try {
    await adminApi.logout()
  } catch {
    /* Ignore 401 or network errors on logout */
  } finally {
    localStorage.clear()
    sessionStorage.clear()
    window.location.href = '/login'
  }
}
</script>

<template>
  <div class="admin-shell h-screen w-screen overflow-hidden bg-slate-50 text-slate-800 dark:bg-[#070c18] dark:text-slate-100 flex flex-col font-sans">
    <!-- Top Header (Fixed h-16, Clean White Light Theme) -->
    <header class="admin-header h-16 shrink-0 z-30 border-b border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 shadow-xs sm:px-6">
      <div class="mx-auto flex h-full max-w-[1600px] items-center justify-between gap-3">
        <!-- Left: Mobile Menu & Logo -->
        <div class="flex items-center gap-3 min-w-0 flex-1">
          <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 transition hover:bg-slate-200">
            <Menu v-if="!sidebarOpen" class="w-5 h-5" />
            <X v-else class="w-5 h-5" />
          </button>

          <router-link to="/dashboard" class="flex items-center gap-2 shrink-0 group" aria-label="Job Search dashboard">
            <img
              src="/logo.png"
              alt="Job Search"
              class="h-10 sm:h-11 w-auto object-contain transition-transform duration-300 group-hover:scale-105"
            />
          </router-link>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-2 sm:gap-3">
          <router-link
            to="/"
            class="hidden sm:flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-950/60 dark:text-cyan-300 border border-blue-200 dark:border-blue-800 transition-all shadow-xs"
            title="Go to Client Website"
          >
            <Globe class="w-3.5 h-3.5 text-blue-600 dark:text-cyan-400" />
            <span>Client Site</span>
          </router-link>
          <NotificationDropdown />
          <LanguageSwitcher />
          <ThemeToggle />
          <div class="h-7 w-px bg-slate-200 dark:bg-slate-800"></div>
          <button @click="router.push(`/candidates/${user?.id}`)" class="flex items-center gap-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100/80 dark:bg-slate-800 px-2.5 py-1.5 transition hover:bg-slate-200 dark:hover:bg-slate-700 shadow-xs" title="Open profile">
            <img
              v-if="profileImage(user)"
              :src="profileImage(user)"
              :alt="user?.name || 'Admin'"
              class="w-8 h-8 rounded-full object-cover border border-blue-400/40 shrink-0"
            />
            <div
              v-else
              class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold shrink-0 shadow-xs"
            >
              {{ (user?.name || 'A').charAt(0).toUpperCase() }}
            </div>
            <div class="hidden sm:block text-left pr-1">
              <p class="text-[11px] font-bold leading-tight text-slate-900 dark:text-white">{{ user?.name || 'Admin' }}</p>
              <p class="text-[9px] uppercase font-bold tracking-[0.18em] text-blue-600 dark:text-cyan-400">{{ user?.role || 'Admin' }}</p>
            </div>
          </button>
        </div>
      </div>
    </header>

    <div id="google_translate_element" class="google-translate-host" aria-hidden="true"></div>

    <!-- Main Workspace Body (flex-1 min-h-0 flex overflow-hidden) -->
    <div class="flex-1 min-h-0 flex relative overflow-hidden">
      <!-- Mobile Overlay -->
      <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-slate-950/60 backdrop-blur-xs lg:hidden"></div>

      <!-- Sidebar (Rich Blue Theme with Glassmorphism Active/Hover, w-60) -->
      <aside :class="[
        'admin-sidebar lg:static fixed inset-y-0 left-0 z-40 w-60 shrink-0 h-full border-r border-blue-900/70 bg-gradient-to-b from-[#07142f] via-[#0a1d3d] to-[#050d22] text-blue-50 shadow-xl transition-transform duration-200 ease-in-out flex flex-col',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]">
        <div class="flex h-full flex-col">
          <!-- Navigation -->
          <div class="flex-1 overflow-y-auto px-2.5 py-3.5 space-y-4">
            <div v-for="group in navGroups" :key="group.label">
              <p class="px-3 mb-1.5 text-[8px] font-bold uppercase tracking-[0.18em] text-blue-300">{{ group.label }}</p>
              <router-link
                v-for="item in group.items"
                :key="item.path"
                :to="item.path"
                :class="[
                  'group flex items-center gap-2 rounded-lg px-3 py-1.5 text-[10px] font-semibold transition-all duration-200 mb-0.5 border',
                  isActive(item)
                    ? 'bg-blue-500/25 text-white font-bold border-cyan-400/50 shadow-md shadow-blue-950/50 ring-1 ring-cyan-300/25'
                    : 'border-transparent text-blue-200/80 hover:bg-blue-400/15 hover:text-blue-100 hover:border-blue-300/20'
                ]"
              >
                <component
                  :is="item.icon"
                  :class="[
                    'w-3.5 h-3.5 shrink-0 transition-colors',
                    isActive(item) ? 'text-blue-200' : 'text-blue-300/90 group-hover:text-blue-100'
                  ]"
                />
                <span>{{ item.name }}</span>
              </router-link>
            </div>
          </div>

          <!-- Bottom Actions -->
          <div class="p-2.5 border-t border-blue-800/50 bg-[#050d22]/80 backdrop-blur-md space-y-0.5">
            <router-link
              to="/"
              class="flex w-full items-center gap-2 rounded-lg px-3 py-1.5 text-[10px] font-bold text-cyan-300 hover:bg-blue-400/15 hover:text-white border border-transparent hover:border-blue-300/20 transition-all cursor-pointer"
              title="Switch to Client Website"
            >
              <Globe class="w-4 h-4 text-cyan-400" />
              <span>View Client Site</span>
            </router-link>

            <button @click="logout" class="flex w-full items-center gap-2 rounded-lg px-3 py-1.5 text-[10px] font-bold text-rose-300 hover:bg-rose-500/20 hover:text-rose-200 border border-transparent hover:border-rose-400/20 transition-all cursor-pointer">
              <LogOut class="w-3.5 h-3.5 text-rose-400" /> Sign Out
            </button>
          </div>
        </div>
      </aside>

      <!-- Main Content Area with Scroll View & Footer -->
      <div class="flex-1 h-full overflow-y-auto flex flex-col min-w-0 bg-slate-50/70 dark:bg-[#070c18]">
        <main class="admin-main flex-1 p-4 sm:p-6 lg:p-8">
          <div class="mx-auto max-w-screen-2xl">
            <slot />
          </div>
        </main>
        <AdminFooter />
      </div>
    </div>
  </div>
</template>
