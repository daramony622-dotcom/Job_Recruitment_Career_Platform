<script setup>
import { ref, computed } from 'vue'
import JobCard from './JobCard.vue'
import { Filter, SlidersHorizontal, Search } from 'lucide-vue-next'

const activeFilter = ref('All')
const filterOptions = ['All', 'Full-time', 'Hybrid', 'Remote', 'Internship', 'Contract']
const searchQuery = ref('')

// Sample jobs list
const jobs = ref([
  {
    id: 1,
    title: 'Senior Full Stack Laravel & Vue.js Developer',
    slug: 'senior-full-stack-laravel-vue-developer',
    description: 'We are looking for an experienced developer to build high-performance web applications using Laravel 11 and Vue 3.',
    job_type: 'Full-time',
    work_mode: 'Hybrid',
    experience_level: 'Senior Level',
    location: 'Monivong Blvd',
    city: 'Phnom Penh',
    country: 'Cambodia',
    salary_min: 1200,
    salary_max: 2200,
    salary_currency: 'USD',
    salary_period: 'monthly',
    is_salary_visible: true,
    vacancies: 3,
    deadline: '2026-04-30',
    status: 'published',
    is_featured: true,
    views_count: 542,
    published_at: '2026-03-01'
  },
  {
    id: 2,
    title: 'Junior Network & Support Engineer',
    slug: 'junior-network-support-engineer',
    description: 'Seeking a motivated IT student or junior engineer to assist with network routing, hardware support, and system maintenance.',
    job_type: 'Contract',
    work_mode: 'Onsite',
    experience_level: 'Junior Level',
    location: 'Toul Kork',
    city: 'Phnom Penh',
    country: 'Cambodia',
    salary_min: 500,
    salary_max: 800,
    salary_currency: 'USD',
    salary_period: 'monthly',
    is_salary_visible: true,
    vacancies: 1,
    deadline: '2026-04-20',
    status: 'published',
    is_featured: false,
    views_count: 230,
    published_at: '2026-03-05'
  },
  {
    id: 3,
    title: 'Lead UI/UX Product Designer',
    slug: 'lead-ui-ux-product-designer',
    description: 'Design intuitive, modern web and mobile user interfaces for our fast-growing career and recruitment platform.',
    job_type: 'Full-time',
    work_mode: 'Remote',
    experience_level: 'Mid Level',
    location: 'BKK1',
    city: 'Phnom Penh',
    country: 'Cambodia',
    salary_min: 900,
    salary_max: 1600,
    salary_currency: 'USD',
    salary_period: 'monthly',
    is_salary_visible: true,
    vacancies: 2,
    deadline: '2026-05-10',
    status: 'published',
    is_featured: true,
    views_count: 412,
    published_at: '2026-03-08'
  }
])

const filteredJobs = computed(() => {
  return jobs.value.filter(job => {
    const matchesFilter = activeFilter.value === 'All' || job.job_type === activeFilter.value || job.work_mode === activeFilter.value
    const matchesQuery = !searchQuery.value || job.title.toLowerCase().includes(searchQuery.value.toLowerCase()) || job.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    return matchesFilter && matchesQuery
  })
})
</script>

<template>
  <div class="space-y-6">
    
    <!-- Filter Tabs Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-xs">
      
      <!-- Tabs -->
      <div class="flex items-center gap-1.5 flex-wrap">
        <button 
          v-for="opt in filterOptions" 
          :key="opt"
          @click="activeFilter = opt"
          class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer"
          :class="activeFilter === opt ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'"
        >
          {{ opt }}
        </button>
      </div>

      <!-- Quick Search Bar -->
      <div class="relative w-full sm:w-64">
        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Filter jobs..." 
          class="w-full pl-9 pr-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
        />
      </div>

    </div>

    <!-- Job Cards List -->
    <div v-if="filteredJobs.length > 0" class="space-y-4">
      <JobCard :job-posts="filteredJobs" />
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-12 text-center space-y-3">
      <div class="p-3 bg-slate-100 dark:bg-slate-800 text-slate-400 rounded-2xl w-12 h-12 mx-auto flex items-center justify-center">
        <Filter class="w-6 h-6" />
      </div>
      <h3 class="font-bold text-slate-900 dark:text-white text-base">No jobs found</h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
        Try adjusting your filter parameters or search terms to find relevant career opportunities.
      </p>
    </div>

  </div>
</template>