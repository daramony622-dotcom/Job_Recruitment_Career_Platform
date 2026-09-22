<script setup>
import { ref } from 'vue'
import Navbar from './layout/Navbar.vue'
import JobFilters from './jobs/JobFilters.vue'
import JobList from './jobs/JobList.vue'
import Footer from './layout/Footer.vue'
import { Briefcase } from 'lucide-vue-next'

const selectedCategory = ref('')
const selectedSkill = ref('')
const selectedLocation = ref('')
const selectedSalary = ref('')
const selectedTime = ref('')

function handleClearFilters() {
  selectedCategory.value = ''
  selectedSkill.value = ''
  selectedLocation.value = ''
  selectedSalary.value = ''
  selectedTime.value = ''
}
</script>

<template>
  <div class="min-h-screen bg-slate-50/60 dark:bg-[#070c16] font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors duration-300">
    <Navbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      <!-- Hero Banner for Jobs Page -->
      <section class="site-banner site-banner-compact relative rounded-3xl overflow-hidden bg-cover bg-center px-6 sm:px-10 py-10 sm:py-12 text-white shadow-xl bg-[url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=1920&auto=format&fit=crop')] border border-blue-900/40">
        <div class="absolute inset-0 bg-gradient-to-r from-black/65 via-black/45 to-black/25"></div>
        <div class="relative z-10 max-w-3xl space-y-3">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 text-xs font-bold uppercase tracking-wider text-cyan-200 border border-white/15">
            <Briefcase class="w-3.5 h-3.5" /> Career Opportunities
          </span>
          <h1 class="site-banner-title font-extrabold tracking-tight text-white">
            Find Jobs & Career Opportunities
          </h1>
          <p class="site-banner-copy text-slate-200 font-medium">
            Discover verified full-time, hybrid, remote, and internship roles from top hiring companies and team environments.
          </p>
        </div>
      </section>


      <JobFilters
        v-model:category="selectedCategory"
        v-model:skill="selectedSkill"
        v-model:location="selectedLocation"
        v-model:salary="selectedSalary"
        v-model:time="selectedTime"
        :has-active-filters="Boolean(selectedCategory || selectedSkill || selectedLocation || selectedSalary || selectedTime)"
        @reset="handleClearFilters"
      />

      <JobList
        v-model:category="selectedCategory"
        v-model:skill="selectedSkill"
        v-model:location="selectedLocation"
        :salary="selectedSalary"
        @clear-filters="handleClearFilters"
      />
    </main>

    <Footer />
  </div>
</template>
