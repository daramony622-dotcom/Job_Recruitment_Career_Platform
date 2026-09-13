<script setup>
import { ref } from 'vue'
import Navbar from '../components/layout/Navbar.vue'
import JobFilters from '../components/jobs/JobFilters.vue'
import JobList from '../components/jobs/JobList.vue'
import Footer from '../components/layout/Footer.vue'

const selectedCategory = ref('')
const selectedLocation = ref('')
const selectedSalary = ref('')
const selectedTime = ref('')

function handleClearFilters() {
  selectedCategory.value = ''
  selectedLocation.value = ''
  selectedSalary.value = ''
  selectedTime.value = ''
}
</script>

<template>
  <div class="min-h-screen bg-slate-50/60 dark:bg-[#070c16] font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors duration-300">
    <Navbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      <div class="space-y-2">
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Find Jobs & Career Opportunities</h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Discover full-time, hybrid, remote, and internship roles from top employers.</p>
      </div>

      <JobFilters
        v-model:category="selectedCategory"
        v-model:location="selectedLocation"
        v-model:salary="selectedSalary"
        v-model:time="selectedTime"
        :has-active-filters="Boolean(selectedCategory || selectedLocation || selectedSalary || selectedTime)"
        @reset="handleClearFilters"
      />

      <JobList
        v-model:category="selectedCategory"
        v-model:location="selectedLocation"
        :salary="selectedSalary"
        @clear-filters="handleClearFilters"
      />
    </main>

    <Footer />
  </div>
</template>
