<script setup>
import Navbar from '../components/layout/Navbar.vue'
import JobList from '../components/jobs/JobList.vue'
import Footer from '../components/layout/Footer.vue'
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors duration-200">
    <Navbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <div class="space-y-2">
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Find Jobs & Career Opportunities</h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Discover full-time, hybrid, remote, and internship roles from top employers.</p>
      </div>

      <JobList />
    </main>

    <Footer />
  </div>
</template>
