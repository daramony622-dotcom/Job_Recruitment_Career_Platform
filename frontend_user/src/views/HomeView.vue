<script setup>
import Navbar from '../components/layout/Navbar.vue'
import HeroBanner from '../components/home/HeroBanner.vue'
import JobFilters from '../components/jobs/JobFilters.vue'
import CategorySection from '../components/home/CategorySection.vue'
import JobList from '../components/jobs/JobList.vue'
import Footer from '../components/layout/Footer.vue'
import { ArrowRight, UserCheck } from 'lucide-vue-next'
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors duration-200">
    <Navbar />

    <main class="max-w-9xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-12">
      <!-- 1. Hero Banner -->
      <HeroBanner />

      <!-- filters -->
      <JobFilters/>

      <!-- 2. Categories Section -->
      <CategorySection />

      <!-- 3. Featured & Latest Jobs List -->
      <section class="space-y-6">
        <div>
          <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Featured Opportunities</h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Discover recent job openings from top employers</p>
        </div>
        <JobList />
      </section>

<!-- 4. Call to Action Banner -->
      <section class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl p-8 sm:p-12 text-white shadow-xl shadow-blue-500/10 grid grid-cols-1 lg:grid-cols-3 gap-8 items-center relative overflow-hidden">
        <!-- Subtle decorative background glow -->
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="lg:col-span-2 space-y-3 relative z-10">
          <span class="px-3.5 py-1 rounded-full bg-white/20 text-xs font-bold uppercase tracking-wider text-white">Take Action</span>
          <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
            Ready to Take Your Next Career Step?
          </h2>
          <p class="text-xs sm:text-sm text-blue-100 max-w-xl leading-relaxed">
            Create your free candidate account today to get matched with top hiring companies, upload your CV, and apply with 1-click.
          </p>
        </div>

        <div class="flex flex-col sm:flex-row lg:flex-col gap-3 justify-end relative z-10">
          <router-link to="/register" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white text-blue-600 hover:bg-blue-50 rounded-2xl text-xs font-bold transition shadow-md active:scale-95">
            <UserCheck class="w-4 h-4" />
            <span>Create Free Account</span>
          </router-link>
          <router-link to="/jobs" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-blue-700/40 hover:bg-blue-700/60 border border-white/20 text-white rounded-2xl text-xs font-bold transition active:scale-95">
            <span>Browse All 12,400+ Jobs</span>
            <ArrowRight class="w-4 h-4" />
          </router-link>
        </div>
      </section>
    </main>

    <Footer />
  </div>
</template>
