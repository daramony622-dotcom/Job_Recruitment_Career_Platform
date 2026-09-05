<script setup>
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
  </div>
</template>
