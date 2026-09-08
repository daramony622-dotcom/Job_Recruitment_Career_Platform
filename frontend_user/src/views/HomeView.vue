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
  <div class="min-h-screen bg-slate-50 dark:bg-[#070c16] font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors duration-300">
    <Navbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-14">
      <!-- 1. Hero Banner -->
      <HeroBanner />

      <!-- Filters -->
      <JobFilters/>

      <!-- 2. Categories Section -->
      <CategorySection />

      <!-- 3. Featured & Latest Jobs -->
      <section class="space-y-6">
        <div>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Featured Opportunities</h2>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Discover recent job openings from top employers</p>
        </div>
        <JobList />
      </section>

      <!-- 4. Call to Action Banner -->
      <section class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 dark:from-blue-950 dark:via-indigo-950 dark:to-slate-950 rounded-3xl p-8 sm:p-12 text-white shadow-2xl shadow-blue-500/15 dark:shadow-blue-950/30 grid grid-cols-1 lg:grid-cols-3 gap-8 items-center overflow-hidden border border-white/10 dark:border-blue-900/30">
        <!-- Decorative glow blobs -->
        <div class="absolute -right-16 -bottom-16 w-72 h-72 bg-white/8 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -top-16 w-64 h-64 bg-indigo-400/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="lg:col-span-2 space-y-4 relative z-10">
          <span class="inline-block px-3.5 py-1 rounded-full bg-white/15 text-xs font-bold uppercase tracking-widest text-blue-100 border border-white/10">
            Take Action
          </span>
          <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight leading-tight">
            Ready to Take Your<br/>Next Career Step?
          </h2>
          <p class="text-sm text-blue-100/80 max-w-xl leading-relaxed">
            Create your free candidate account today to get matched with top hiring companies, upload your CV, and apply with 1-click.
          </p>
        </div>

        <div class="flex flex-col gap-3 relative z-10">
          <router-link
            to="/register"
            class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white text-blue-700 hover:bg-blue-50 rounded-2xl text-sm font-bold transition-all duration-200 shadow-lg active:scale-95"
          >
            <UserCheck class="w-4 h-4" />
            <span>Create Free Account</span>
          </router-link>
          <router-link
            to="/jobs"
            class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-2xl text-sm font-bold transition-all duration-200 active:scale-95"
          >
            <span>Browse 12,400+ Jobs</span>
            <ArrowRight class="w-4 h-4" />
          </router-link>
        </div>
      </section>
    </main>

    <Footer />
  </div>
</template>

