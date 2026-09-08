<script setup>
import { ref } from 'vue'
import { Briefcase, Users, Building2, FileText, ArrowRight, TrendingUp, RefreshCw } from 'lucide-vue-next'

// ទិន្នន័យស្ថិតិគំរូ
const stats = ref({
  jobPosts: 0,
  candidates: 1,
  companies: 0,
  applications: 0
})
</script>

<template>
  <div class="p-6 sm:p-8 space-y-6 max-w-7xl mx-auto">
    <!-- Header Title & Refresh -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Dashboard</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Overview of the recruitment platform and key statistics.</p>
      </div>
      <button class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-medium transition-colors shadow-sm">
        <RefreshCw class="w-4 h-4 text-slate-500 dark:text-slate-400" />
        Refresh
      </button>
    </div>

    <!-- Top Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Card 1 -->
      <div class="bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800/80 p-5 rounded-2xl relative overflow-hidden backdrop-blur-md shadow-sm dark:shadow-lg group hover:border-blue-500/50 transition-all">
        <div class="flex items-center justify-between">
          <div class="w-10 h-10 rounded-xl bg-blue-600/10 border border-blue-500/20 flex items-center justify-center text-blue-600 dark:text-blue-400">
            <Briefcase class="w-5 h-5" />
          </div>
          <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-500/10 px-2.5 py-1 rounded-full">Active</span>
        </div>
        <div class="mt-4">
          <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ stats.jobPosts }}</p>
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">Total Job Posts</p>
        </div>
        <router-link to="/job-posts" class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300">
          <span>View details</span>
          <ArrowRight class="w-3.5 h-3.5" />
        </router-link>
      </div>

      <!-- Card 2 -->
      <div class="bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800/80 p-5 rounded-2xl relative overflow-hidden backdrop-blur-md shadow-sm dark:shadow-lg group hover:border-emerald-500/50 transition-all">
        <div class="flex items-center justify-between">
          <div class="w-10 h-10 rounded-xl bg-emerald-600/10 border border-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
            <Users class="w-5 h-5" />
          </div>
          <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-full">+100%</span>
        </div>
        <div class="mt-4">
          <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ stats.candidates }}</p>
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">Total Candidates</p>
        </div>
        <router-link to="/candidates" class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 dark:hover:text-emerald-300">
          <span>View details</span>
          <ArrowRight class="w-3.5 h-3.5" />
        </router-link>
      </div>

      <!-- Card 3 -->
      <div class="bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800/80 p-5 rounded-2xl relative overflow-hidden backdrop-blur-md shadow-sm dark:shadow-lg group hover:border-amber-500/50 transition-all">
        <div class="flex items-center justify-between">
          <div class="w-10 h-10 rounded-xl bg-amber-600/10 border border-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400">
            <Building2 class="w-5 h-5" />
          </div>
          <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-full">Verified</span>
        </div>
        <div class="mt-4">
          <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ stats.companies }}</p>
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">Total Companies</p>
        </div>
        <router-link to="/companies" class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between text-xs font-semibold text-amber-600 dark:text-amber-400 hover:text-amber-500 dark:hover:text-amber-300">
          <span>View details</span>
          <ArrowRight class="w-3.5 h-3.5" />
        </router-link>
      </div>

      <!-- Card 4 -->
      <div class="bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800/80 p-5 rounded-2xl relative overflow-hidden backdrop-blur-md shadow-sm dark:shadow-lg group hover:border-purple-500/50 transition-all">
        <div class="flex items-center justify-between">
          <div class="w-10 h-10 rounded-xl bg-purple-600/10 border border-purple-500/20 flex items-center justify-center text-purple-600 dark:text-purple-400">
            <FileText class="w-5 h-5" />
          </div>
          <span class="text-xs font-semibold text-purple-600 dark:text-purple-400 bg-purple-500/10 px-2.5 py-1 rounded-full">Total</span>
        </div>
        <div class="mt-4">
          <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ stats.applications }}</p>
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">Applications Sent</p>
        </div>
        <router-link to="/reports" class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between text-xs font-semibold text-purple-600 dark:text-purple-400 hover:text-purple-500 dark:hover:text-purple-300">
          <span>View details</span>
          <ArrowRight class="w-3.5 h-3.5" />
        </router-link>
      </div>
    </div>

    <!-- Middle Section: Analytics & Progress -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Publishing Overview (Progress Bars) -->
      <div class="bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800/80 p-6 rounded-2xl backdrop-blur-md shadow-sm dark:shadow-lg lg:col-span-2 space-y-5">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <TrendingUp class="w-4 h-4 text-blue-600 dark:text-blue-500" /> Publishing & Activity Overview
          </h2>
          <span class="text-xs text-slate-500 dark:text-slate-400">Real-time metrics</span>
        </div>

        <div class="space-y-4 pt-2">
          <div>
            <div class="flex justify-between text-xs font-medium mb-1.5">
              <span class="text-slate-700 dark:text-slate-300">Published Jobs Rate</span>
              <span class="text-blue-600 dark:text-blue-400">0%</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-2.5 border border-slate-200 dark:border-slate-800">
              <div class="bg-blue-600 h-2 rounded-full w-0 transition-all duration-500"></div>
            </div>
          </div>

          <div>
            <div class="flex justify-between text-xs font-medium mb-1.5">
              <span class="text-slate-700 dark:text-slate-300">Candidate Profile Completion</span>
              <span class="text-emerald-600 dark:text-emerald-400">75%</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-2.5 border border-slate-200 dark:border-slate-800">
              <div class="bg-emerald-500 h-2 rounded-full w-3/4 transition-all duration-500"></div>
            </div>
          </div>

          <div>
            <div class="flex justify-between text-xs font-medium mb-1.5">
              <span class="text-slate-700 dark:text-slate-300">Company Approvals</span>
              <span class="text-amber-600 dark:text-amber-400">50%</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-2.5 border border-slate-200 dark:border-slate-800">
              <div class="bg-amber-500 h-2 rounded-full w-1/2 transition-all duration-500"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Job Posts Status Box -->
      <div class="bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800/80 p-6 rounded-2xl backdrop-blur-md shadow-sm dark:shadow-lg flex flex-col justify-between">
        <div>
          <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-1">Job Posts by Status</h2>
          <p class="text-xs text-slate-500 dark:text-slate-400">Distribution of active vs pending listings.</p>
        </div>
        <div class="py-8 text-center">
          <div class="w-24 h-24 mx-auto rounded-full border-4 border-blue-600/20 border-t-blue-500 flex items-center justify-center animate-spin-slow">
            <span class="text-sm font-bold text-slate-800 dark:text-slate-200">0 Active</span>
          </div>
          <p class="text-xs text-slate-500 mt-4">No pending approvals required right now.</p>
        </div>
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800/60 text-xs text-slate-500 dark:text-slate-400 flex justify-between">
          <span>Total Checked</span>
          <span class="text-slate-800 dark:text-slate-200 font-semibold">100%</span>
        </div>
      </div>

    </div>

    <!-- Bottom Tables / Recent Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800/80 p-6 rounded-2xl backdrop-blur-md shadow-sm dark:shadow-lg">
        <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4">Recent Job Posts</h2>
        <div class="text-center py-8 text-slate-500 text-sm">
          No recent job posts found.
        </div>
      </div>

      <div class="bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800/80 p-6 rounded-2xl backdrop-blur-md shadow-sm dark:shadow-lg">
        <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4">Recent Applications</h2>
        <div class="text-center py-8 text-slate-500 text-sm">
          No recent applications found.
        </div>
      </div>
    </div>
  </div>
</template>