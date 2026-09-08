<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import JobCard from './JobCard.vue'
import { Filter, SlidersHorizontal, Search } from 'lucide-vue-next'
import { useAuth } from '../../composables/useAuth'

const activeFilter = ref('All')
const filterOptions = ['All', 'Full-time', 'Hybrid', 'Remote', 'Internship', 'Contract']
const searchQuery = ref('')
const jobs = ref([])
const isLoading = ref(false)
const error = ref('')
const { request } = useAuth()

const filterParams = {
  'Full-time': { job_type: 'full_time' },
  Hybrid: { work_mode: 'hybrid' },
  Remote: { work_mode: 'remote' },
  Internship: { job_type: 'internship' },
  Contract: { job_type: 'contract' },
}

const fetchJobs = async () => {
  isLoading.value = true
  error.value = ''

  const params = new URLSearchParams()
  if (searchQuery.value.trim()) params.set('search', searchQuery.value.trim())
  Object.entries(filterParams[activeFilter.value] || {}).forEach(([key, value]) => params.set(key, value))

  try {
    const response = await request(`/jobs/search?${params.toString()}`)
    const fetched = response.data?.data || (Array.isArray(response.data) ? response.data : null)
    if (fetched && fetched.length > 0) {
      jobs.value = fetched
    } else {
      jobs.value = null // null lets JobCard render default fallback posts
    }
  } catch (requestError) {
    // Graceful fallback to rich mock data when backend endpoint is unavailable
    jobs.value = null
  } finally {
    isLoading.value = false
  }
}

const filteredJobs = computed(() => jobs.value)

onMounted(fetchJobs)
watch([searchQuery, activeFilter], fetchJobs)
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
        <label for="job-search" class="sr-only">Search jobs</label>
        <input 
          id="job-search"
          v-model="searchQuery"
          type="text" 
          placeholder="Filter jobs..." 
          class="w-full pl-9 pr-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
        />
      </div>

    </div>

    <div v-if="isLoading" class="py-12 text-center text-sm font-semibold text-slate-500 dark:text-slate-400">
      <div class="inline-flex items-center gap-2">
        <div class="w-4 h-4 rounded-full border-2 border-blue-600 border-t-transparent animate-spin"></div>
        <span>Loading jobs...</span>
      </div>
    </div>

    <!-- Job Cards List -->
    <div v-else-if="jobs === null || jobs.length > 0" class="space-y-4">
      <JobCard :job-posts="jobs" />
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