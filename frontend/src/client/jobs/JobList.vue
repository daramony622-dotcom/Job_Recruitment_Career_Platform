<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import JobCard from './JobCard.vue'
import { Filter, SlidersHorizontal, Search, X, FolderTree, Tag, MapPin, RotateCcw } from 'lucide-vue-next'
import { useAuth } from '../../composables/useAuth'

const props = defineProps({
  category: {
    type: String,
    default: ''
  },
  skill: {
    type: String,
    default: ''
  },
  location: {
    type: String,
    default: ''
  },
  salary: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:category', 'update:skill', 'update:location', 'update:salary', 'clear-filters'])

const route = useRoute()
const router = useRouter()
const { request } = useAuth()

const activeFilter = ref('All')
const filterOptions = ['All', 'Full-time', 'Hybrid', 'Remote', 'Internship', 'Contract']
const searchQuery = ref('')
const jobs = ref([])
const totalCount = ref(0)
const isLoading = ref(false)
const error = ref('')

const filterParams = {
  'Full-time': { job_type: 'full_time' },
  Hybrid: { work_mode: 'hybrid' },
  Remote: { work_mode: 'remote' },
  Internship: { job_type: 'internship' },
  Contract: { job_type: 'contract' },
}

const currentCategory = computed(() => props.category || route.query.category || '')
const currentSkill = computed(() => props.skill || route.query.skill || '')
const currentLocation = computed(() => props.location || route.query.location || '')
const currentSearch = computed(() => searchQuery.value || route.query.search || '')

const hasActiveCategoryOrFilter = computed(() => {
  return Boolean(currentCategory.value || currentSkill.value || currentLocation.value || currentSearch.value || activeFilter.value !== 'All')
})

const fetchJobs = async () => {
  isLoading.value = true
  error.value = ''

  const params = new URLSearchParams()
  
  if (currentSearch.value.trim()) {
    params.set('search', currentSearch.value.trim())
  }
  if (currentCategory.value) {
    params.set('category', currentCategory.value)
  }
  if (currentSkill.value) {
    params.set('skill', currentSkill.value)
  }
  if (currentLocation.value) {
    params.set('location', currentLocation.value)
  }

  // Type or mode filters
  Object.entries(filterParams[activeFilter.value] || {}).forEach(([key, value]) => {
    params.set(key, value)
  })

  try {
    const response = await request(`/jobs/search?${params.toString()}`)
    const fetched = response.data?.data || (Array.isArray(response.data) ? response.data : null)
    if (fetched && fetched.length > 0) {
      jobs.value = fetched
      totalCount.value = response.data?.meta?.total || fetched.length
    } else {
      // If user had active filter query, show 0 results; if empty initial load, show fallback
      if (hasActiveCategoryOrFilter.value) {
        jobs.value = []
        totalCount.value = 0
      } else {
        jobs.value = null // null lets JobCard render rich default cards
        totalCount.value = 0
      }
    }
  } catch (requestError) {
    jobs.value = hasActiveCategoryOrFilter.value ? [] : null
  } finally {
    isLoading.value = false
  }
}

function clearCategory() {
  emit('update:category', '')
  if (route.query.category) {
    const query = { ...route.query }
    delete query.category
    router.replace({ query })
  }
}

function clearSkill() {
  emit('update:skill', '')
  if (route.query.skill) {
    const query = { ...route.query }
    delete query.skill
    router.replace({ query })
  }
}

function clearLocation() {
  emit('update:location', '')
  if (route.query.location) {
    const query = { ...route.query }
    delete query.location
    router.replace({ query })
  }
}

function clearSearch() {
  searchQuery.value = ''
  if (route.query.search) {
    const query = { ...route.query }
    delete query.search
    router.replace({ query })
  }
}

function clearAllFilters() {
  activeFilter.value = 'All'
  searchQuery.value = ''
  emit('clear-filters')
  router.replace({ path: route.path, query: {} })
}

onMounted(() => {
  if (route.query.search) {
    searchQuery.value = String(route.query.search)
  }
  fetchJobs()
})

watch([() => props.category, () => props.location, () => props.skill, () => route.query, activeFilter, searchQuery], () => {
  fetchJobs()
})
</script>

<template>
  <div class="space-y-6">
    
    <!-- Filter Tabs Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-[#0d1526] border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-xs">
      
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
          placeholder="Filter by skill, title..." 
          class="w-full pl-9 pr-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
        />
      </div>

    </div>

    <!-- Active Filter Chips Bar -->
    <div v-if="hasActiveCategoryOrFilter && (currentCategory || currentSkill || currentLocation || currentSearch)" class="flex flex-wrap items-center gap-2 px-1">
      <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Active filters:</span>

      <span
        v-if="currentCategory"
        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/70 dark:text-blue-300 border border-blue-200 dark:border-blue-800"
      >
        <FolderTree class="w-3.5 h-3.5" />
        <span>Category: {{ currentCategory }}</span>
        <button type="button" @click="clearCategory" class="p-0.5 hover:bg-blue-200 dark:hover:bg-blue-800 rounded-full transition cursor-pointer">
          <X class="w-3 h-3" />
        </button>
      </span>

      <span
        v-if="currentSkill"
        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 dark:bg-purple-950/70 dark:text-purple-300 border border-purple-200 dark:border-purple-800"
      >
        <Tag class="w-3.5 h-3.5" />
        <span>Skill: {{ currentSkill }}</span>
        <button type="button" @click="clearSkill" class="p-0.5 hover:bg-purple-200 dark:hover:bg-purple-800 rounded-full transition cursor-pointer">
          <X class="w-3 h-3" />
        </button>
      </span>

      <span
        v-if="currentLocation"
        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800"
      >
        <MapPin class="w-3.5 h-3.5" />
        <span>Location: {{ currentLocation }}</span>
        <button type="button" @click="clearLocation" class="p-0.5 hover:bg-emerald-200 dark:hover:bg-emerald-800 rounded-full transition cursor-pointer">
          <X class="w-3 h-3" />
        </button>
      </span>

      <span
        v-if="currentSearch"
        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700"
      >
        <Search class="w-3.5 h-3.5" />
        <span>Keyword: "{{ currentSearch }}"</span>
        <button type="button" @click="clearSearch" class="p-0.5 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-full transition cursor-pointer">
          <X class="w-3 h-3" />
        </button>
      </span>

      <button
        type="button"
        @click="clearAllFilters"
        class="text-xs font-bold text-rose-600 dark:text-rose-400 hover:underline ml-1 inline-flex items-center gap-1 cursor-pointer"
      >
        <RotateCcw class="w-3 h-3" />
        <span>Clear all</span>
      </button>
    </div>

    <!-- Loading Spinner State -->
    <div v-if="isLoading" class="py-12 text-center text-sm font-semibold text-slate-500 dark:text-slate-400">
      <div class="inline-flex items-center gap-2">
        <div class="w-4 h-4 rounded-full border-2 border-blue-600 border-t-transparent animate-spin"></div>
        <span>Searching jobs matching categories & skills...</span>
      </div>
    </div>

    <!-- Job Cards List -->
    <div v-else-if="jobs === null || jobs.length > 0" class="space-y-4">
      <JobCard :job-posts="jobs" />
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white dark:bg-[#0d1526] border border-slate-200/80 dark:border-slate-800 rounded-2xl p-12 text-center space-y-3 shadow-xs">
      <div class="p-3 bg-slate-100 dark:bg-slate-800 text-slate-400 rounded-2xl w-12 h-12 mx-auto flex items-center justify-center">
        <Filter class="w-6 h-6" />
      </div>
      <h3 class="font-bold text-slate-900 dark:text-white text-base">No matching jobs found</h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
        We couldn't find any openings matching your selected category, skills, or location filters.
      </p>
      <div class="pt-2">
        <button
          type="button"
          @click="clearAllFilters"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-md shadow-blue-500/20 cursor-pointer"
        >
          <RotateCcw class="w-3.5 h-3.5" />
          <span>Reset All Filters</span>
        </button>
      </div>
    </div>

  </div>
</template>