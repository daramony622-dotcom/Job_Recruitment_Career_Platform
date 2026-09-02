<script setup>
import { Bell, LogIn, UserPlus } from 'lucide-vue-next'
import { useRoute } from 'vue-router'
import ThemeToggle from '../common/ThemeToggle.vue'
import LanguageSwitcher from '../common/LanguageSwitcher.vue'

const route = useRoute()

const navItems = [
  { name: 'Find Jobs', path: '/jobs' },
  { name: 'Companies', path: '/companies' },
  { name: 'About', path: '/about' },
  { name: 'Contact', path: '/contact' }
]
</script>

<template>
  <header class="w-full bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800/80 px-4 sm:px-8 py-2.5 flex items-center justify-between sticky top-0 z-50 transition-colors">
    
    <!-- Logo & Nav Links -->
    <div class="flex items-center gap-8 md:gap-10">
      <router-link to="/" class="flex items-center gap-2 cursor-pointer group shrink-0">
        <img
          src="/logo.png"
          alt="Job Search Logo"
          class="h-11 sm:h-13 md:h-14 w-auto object-contain transition-transform duration-300 group-hover:scale-105"
        />
      </router-link>

      <nav class="hidden md:flex items-center gap-7 text-sm font-semibold">
        <router-link
          v-for="item in navItems"
          :key="item.path"
          :to="item.path"
          class="transition-colors duration-200 py-1 relative"
          :class="route.path === item.path ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400'"
        >
          <span>{{ item.name }}</span>
          <span v-if="route.path === item.path" class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 dark:bg-blue-400 rounded-full"></span>
        </router-link>
      </nav>
    </div>

    <!-- Right Side Actions -->
    <div class="flex items-center gap-2.5 text-sm font-medium text-slate-600 dark:text-slate-300">
      
      <!-- Language Switcher Component -->
      <LanguageSwitcher />

      <!-- Click Dark Mode Toggle Button Component -->
      <ThemeToggle />

      <!-- Notifications Bell → links to /notifications -->
      <router-link to="/notifications" class="p-2.5 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition relative cursor-pointer" aria-label="Notifications">
        <Bell class="w-5 h-5" />
        <span class="absolute top-2 right-2 w-2 h-2 bg-blue-600 rounded-full ring-2 ring-white dark:ring-slate-900"></span>
      </router-link>

      <!-- Auth Action Buttons -->
      <div class="flex items-center gap-2 ml-1">
        <router-link to="/login" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center gap-1.5">
          <LogIn class="w-4 h-4" />
          <span>Login</span>
        </router-link>
        <router-link to="/register" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-blue-700 transition-all shadow-md shadow-blue-500/20 active:scale-95 flex items-center gap-1.5">
          <UserPlus class="w-4 h-4" />
          <span>Register</span>
        </router-link>
      </div>

    </div>
  </header>
</template>
