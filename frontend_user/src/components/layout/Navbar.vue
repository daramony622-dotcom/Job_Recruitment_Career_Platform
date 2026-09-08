<script setup>
import { ref, computed } from 'vue'
import { Bell, LogIn, UserPlus, LogOut, Menu, X, ChevronDown } from 'lucide-vue-next'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '../../composables/useAuth'
import LanguageSwitcher from '../common/LanguageSwitcher.vue'
import ThemeToggle from '../common/ThemeToggle.vue'

const route = useRoute()
const router = useRouter()
const { user, isAuthenticated, logout } = useAuth()
const mobileOpen = ref(false)

const handleLogout = async () => {
  mobileOpen.value = false
  await logout()
  router.push('/login')
}

const navItems = [
  { name: 'Find Jobs',  path: '/jobs' },
  { name: 'Companies', path: '/companies' },
  { name: 'About',     path: '/about' },
  { name: 'Contact',   path: '/contact' }
]

const isActive = (path) => route.path === path || route.path.startsWith(path + '/')
</script>

<template>
  <header class="w-full sticky top-0 z-50 transition-colors duration-300">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-white/92 dark:bg-slate-950/92 backdrop-blur-xl border-b border-slate-200/70 dark:border-slate-800/60 pointer-events-none"></div>

    <div class="relative max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">

        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-2 shrink-0 group" @click="mobileOpen = false">
          <img
            src="/logo.png"
            alt="Job Search"
            class="h-10 sm:h-11 w-auto object-contain transition-transform duration-300 group-hover:scale-105"
          />
        </router-link>

        <!-- Desktop Nav -->
        <nav class="hidden md:flex items-center gap-1">
          <router-link
            v-for="item in navItems"
            :key="item.path"
            :to="item.path"
            class="relative px-3.5 py-2 rounded-xl text-sm font-semibold transition-all duration-200"
            :class="isActive(item.path)
              ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/50'
              : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/80 dark:hover:bg-slate-800/60'"
          >
            {{ item.name }}
            <span
              v-if="isActive(item.path)"
              class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-4 h-0.5 bg-blue-600 dark:bg-blue-400 rounded-full"
            ></span>
          </router-link>
        </nav>

        <!-- Right Actions -->
        <div class="flex items-center gap-1.5 sm:gap-2">
          <LanguageSwitcher />
          <ThemeToggle />

          <!-- Notifications -->
          <router-link
            to="/notifications"
            class="relative p-2.5 rounded-xl text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all duration-200"
            aria-label="Notifications"
          >
            <Bell class="w-5 h-5" />
            <span class="absolute top-2 right-2 w-2 h-2 bg-blue-500 rounded-full ring-2 ring-white dark:ring-slate-950"></span>
          </router-link>

          <!-- Auth — Desktop -->
          <template v-if="!isAuthenticated">
            <router-link
              to="/login"
              class="hidden sm:flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all duration-200"
            >
              <LogIn class="w-4 h-4" />
              <span>Login</span>
            </router-link>
            <router-link
              to="/register"
              class="hidden sm:flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-bold bg-blue-600 hover:bg-blue-500 text-white shadow-md shadow-blue-500/25 hover:shadow-blue-500/40 transition-all duration-200 active:scale-95"
            >
              <UserPlus class="w-4 h-4" />
              <span>Register</span>
            </router-link>
          </template>
          <template v-else>
            <router-link
              to="/profile"
              class="hidden sm:flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all duration-200"
            >
              <div class="w-6 h-6 rounded-full bg-blue-600 text-white text-[10px] font-black flex items-center justify-center">
                {{ (user?.name || 'U')[0].toUpperCase() }}
              </div>
              <span>{{ user?.name || 'Profile' }}</span>
            </router-link>
            <button
              type="button"
              @click="handleLogout"
              class="hidden sm:flex p-2.5 rounded-xl text-slate-500 dark:text-slate-400 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-all duration-200"
              aria-label="Log out"
            >
              <LogOut class="w-5 h-5" />
            </button>
          </template>

          <!-- Mobile Hamburger -->
          <button
            type="button"
            @click="mobileOpen = !mobileOpen"
            class="md:hidden p-2.5 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-all duration-200"
            :aria-expanded="mobileOpen"
            aria-label="Toggle menu"
          >
            <Menu v-if="!mobileOpen" class="w-5 h-5" />
            <X v-else class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div
        v-if="mobileOpen"
        class="md:hidden relative border-t border-slate-200/60 dark:border-slate-800/50 bg-white/98 dark:bg-slate-950/98 backdrop-blur-xl px-4 pb-5 pt-3 space-y-1"
      >
        <router-link
          v-for="item in navItems"
          :key="item.path"
          :to="item.path"
          @click="mobileOpen = false"
          class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-150"
          :class="isActive(item.path)
            ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40'
            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'"
        >
          {{ item.name }}
        </router-link>

        <div class="border-t border-slate-200/60 dark:border-slate-800/50 pt-3 mt-3 flex flex-col gap-2">
          <template v-if="!isAuthenticated">
            <router-link to="/login" @click="mobileOpen = false"
              class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-all">
              <LogIn class="w-4 h-4" /> Login
            </router-link>
            <router-link to="/register" @click="mobileOpen = false"
              class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold shadow-md shadow-blue-500/25 transition-all active:scale-95">
              <UserPlus class="w-4 h-4" /> Create Account
            </router-link>
          </template>
          <template v-else>
            <router-link to="/profile" @click="mobileOpen = false"
              class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-all">
              <div class="w-8 h-8 rounded-full bg-blue-600 text-white text-xs font-black flex items-center justify-center">
                {{ (user?.name || 'U')[0].toUpperCase() }}
              </div>
              <span>{{ user?.name || 'My Profile' }}</span>
            </router-link>
            <button type="button" @click="handleLogout"
              class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-red-100 dark:border-red-900/40 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 text-sm font-semibold transition-all">
              <LogOut class="w-4 h-4" /> Sign Out
            </button>
          </template>
        </div>
      </div>
    </Transition>
  </header>
</template>

