<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
import { 
  MapPin, DollarSign, Calendar, Eye, 
  Bookmark, Send, ArrowLeft, Building2, Globe, 
  Users, CheckCircle2, Share2, Sparkles,
  GraduationCap, ShieldCheck, ShieldAlert, Navigation, Compass
} from 'lucide-vue-next'
import { useAuth, resolveAssetUrl } from '../composables/useAuth'
import { useJobSeekerApi } from '../composables/useJobSeekerApi'

const route = useRoute()
const router = useRouter()
const { isAuthenticated } = useAuth()
const { request, listSavedJobs, saveJob, removeSavedJob, applyToJob } = useJobSeekerApi()

const isBookmarked = ref(false)
const isApplied = ref(false)
const showShareSuccess = ref(false)
const loading = ref(true)
const error = ref('')
const savedJobId = ref(null)
const submittingApplication = ref(false)

const job = ref({})

function normalizeJob(data) {
  const source = data?.data && !Array.isArray(data.data) ? data.data : data
  if (!source || typeof source !== 'object') return null
  const company = source.company || {}
  const category = source.category || {}
  return {
    ...source,
    company_name: company.name || 'Employer',
    company_logo: resolveAssetUrl(company.logo),
    category_name: category.name || '',
    company_info: {
      name: company.name,
      industry: company.industry,
      employees: company.company_size,
      founded: company.founded_year,
      website: company.website,
      about: company.description,
    },
  }
}

async function fetchJob() {
  loading.value = true
  try {
    const response = await request(`/jobs/${route.params.id}`)
    job.value = normalizeJob(response.data || response)
    if (isAuthenticated.value) {
      const savedResponse = await listSavedJobs()
      const saved = (savedResponse?.data || savedResponse || []).find((item) => Number(item.job_post_id) === Number(job.value.id))
      savedJobId.value = saved?.id || null
      isBookmarked.value = Boolean(saved)
    }
  } catch (requestError) {
    error.value = requestError.message || 'Unable to load this job.'
  } finally {
    loading.value = false
  }
}

// Helper: Salary Formatting
const formatSalary = (jobObj) => {
  if (!jobObj || !jobObj.is_salary_visible) return 'Negotiable'
  const min = Number(jobObj.salary_min).toLocaleString()
  const max = Number(jobObj.salary_max).toLocaleString()
  const period = jobObj.salary_period ? jobObj.salary_period.replace('ly', '') : 'month'
  return `$${min} - $${max} ${jobObj.salary_currency || 'USD'} / ${period}`
}

// Helper: Enum Label Formatting
const formatEnum = (val) => {
  if (!val) return ''
  return val.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')
}

const toggleBookmark = async () => {
  if (!isAuthenticated.value) {
    router.push({ path: '/login', query: { redirect: route.fullPath } })
    return
  }
  if (savedJobId.value) {
    await removeSavedJob(savedJobId.value)
    savedJobId.value = null
    isBookmarked.value = false
  } else {
    const response = await saveJob(job.value.id)
    const saved = response?.data || response
    savedJobId.value = saved.id
    isBookmarked.value = true
  }
}

const handleApply = async () => {
  if (!isAuthenticated.value) {
    router.push({ path: '/login', query: { redirect: route.fullPath } })
    return
  }
  submittingApplication.value = true
  try {
    await applyToJob(job.value.id)
    isApplied.value = true
  } catch (requestError) {
    error.value = requestError.message || 'Unable to submit your application.'
  } finally {
    submittingApplication.value = false
  }
}

const copyShareLink = () => {
  navigator.clipboard.writeText(window.location.href)
  showShareSuccess.value = true
  setTimeout(() => { showShareSuccess.value = false }, 2500)
}

onMounted(fetchJob)
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors duration-200">
    <Navbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <div v-if="loading" class="py-24 text-center text-sm font-semibold text-slate-500">Loading job details...</div>
      <div v-else-if="error" class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">{{ error }}</div>
      <template v-else-if="job">
      
      <!-- Back Navigation Button -->
      <button 
        @click="router.back()"
        class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition cursor-pointer"
      >
        <ArrowLeft class="w-4 h-4" />
        <span>Back to Job Board</span>
      </button>

      <!-- Main Job Hero Header Banner -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 md:p-8 shadow-xs relative overflow-hidden space-y-6">
        
        <!-- Featured Badge -->
        <div v-if="job.is_featured" class="absolute top-0 right-0 bg-blue-600 text-white text-[11px] font-bold uppercase px-4 py-1 rounded-bl-2xl tracking-wider flex items-center gap-1.5 shadow-2xs">
          <Sparkles class="w-3.5 h-3.5 text-amber-300" /> Featured Position
        </div>

        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 pt-2">
          
          <div class="flex items-start gap-4 flex-1 min-w-0">
            <!-- Company Logo -->
            <img 
              v-if="job.company_logo" 
              :src="job.company_logo" 
              :alt="job.company_name" 
              class="w-16 h-16 md:w-20 md:h-20 rounded-2xl object-cover border border-slate-100 dark:border-slate-800 shrink-0" 
            />
            <div 
              v-else 
              class="w-16 h-16 md:w-20 md:h-20 rounded-2xl bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 border border-slate-100 dark:border-slate-800 font-extrabold text-xl"
            >
              {{ job.company_name?.charAt(0) || 'C' }}
            </div>

            <div class="space-y-2.5 min-w-0 flex-1">
              
              <!-- Category & Badges -->
              <div class="flex items-center gap-2 flex-wrap">
                <span v-if="job.category_name" class="px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                  {{ job.category_name }}
                </span>
                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/50">
                  {{ formatEnum(job.job_type) }}
                </span>
                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/50">
                  {{ formatEnum(job.work_mode) }}
                </span>
                <span v-if="job.experience_level" class="px-3 py-1 rounded-lg text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-900/50">
                  {{ formatEnum(job.experience_level) }}
                </span>
                <span v-if="job.status && job.status !== 'published'" class="px-3 py-1 rounded-lg text-xs font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/50 flex items-center gap-1">
                  <ShieldAlert class="w-3.5 h-3.5" /> Status: {{ formatEnum(job.status) }}
                </span>
              </div>

              <!-- Title -->
              <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                {{ job.title }}
              </h1>

              <!-- Company & Location Meta -->
              <div class="flex items-center gap-4 flex-wrap text-xs font-medium text-slate-500 dark:text-slate-400">
                <span class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300 font-semibold">
                  <Building2 class="w-4 h-4 text-blue-600" />
                  {{ job.company_name }}
                </span>
                <span class="flex items-center gap-1.5 text-blue-600 dark:text-blue-400 font-semibold">
                  <MapPin class="w-4 h-4" />
                  {{ job.location ? `${job.location}, ` : '' }}{{ job.city }}, {{ job.country }}
                </span>
                <span class="flex items-center gap-1.5">
                  <Eye class="w-4 h-4 text-slate-400" />
                  {{ job.views_count }} views
                </span>
              </div>

            </div>
          </div>

          <!-- Top Actions -->
          <div class="flex items-center gap-3 w-full md:w-auto justify-stretch md:justify-end pt-4 md:pt-0 border-t md:border-t-0 border-slate-100 dark:border-slate-800 shrink-0">
            <button 
              @click="toggleBookmark" 
              class="p-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-2xl transition cursor-pointer"
              title="Bookmark Job"
            >
              <Bookmark class="w-5 h-5" :class="{ 'fill-blue-600 text-blue-600 dark:fill-blue-400 dark:text-blue-400': isBookmarked }" />
            </button>

            <button 
              @click="copyShareLink" 
              class="p-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-2xl transition cursor-pointer relative"
              title="Share Job"
            >
              <Share2 class="w-5 h-5" />
              <span v-if="showShareSuccess" class="absolute -top-9 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] py-1 px-2.5 rounded-md whitespace-nowrap">Copied!</span>
            </button>

            <button 
              @click="handleApply" 
              :disabled="isApplied || submittingApplication" 
              class="flex-1 md:flex-initial flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl text-xs transition shadow-md shadow-blue-500/20 active:scale-95 disabled:opacity-80 cursor-pointer"
            >
              <CheckCircle2 v-if="isApplied" class="w-4 h-4 text-emerald-300" />
              <Send v-else class="w-4 h-4" />
              <span>{{ isApplied ? 'Application Submitted!' : 'Apply Now' }}</span>
            </button>
          </div>

        </div>

      </div>
      </template>

      <!-- Main Grid: Specifications, Description & Sidebar -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2-Column Main Details -->
        <div class="lg:col-span-2 space-y-8">
          
          <!-- Quick Overview Cards (mapped from schema) -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl space-y-1">
              <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                <DollarSign class="w-4 h-4 text-emerald-500" /> Salary
              </div>
              <p class="text-sm font-extrabold text-slate-900 dark:text-white">
                {{ formatSalary(job) }}
              </p>
              <p class="text-[11px] text-slate-500 dark:text-slate-400">
                {{ job.is_salary_visible ? 'Visible Rate' : 'Negotiable' }}
              </p>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl space-y-1">
              <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                <Users class="w-4 h-4 text-blue-500" /> Vacancies
              </div>
              <p class="text-sm font-extrabold text-slate-900 dark:text-white">
                {{ job.vacancies }} Openings
              </p>
              <p class="text-[11px] text-slate-500 dark:text-slate-400">Available positions</p>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl space-y-1">
              <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                <Calendar class="w-4 h-4 text-rose-500" /> Deadline
              </div>
              <p class="text-sm font-extrabold text-slate-900 dark:text-white">
                {{ job.deadline || 'Open until filled' }}
              </p>
              <p class="text-[11px] text-slate-500 dark:text-slate-400">Application deadline</p>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl space-y-1">
              <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                <GraduationCap class="w-4 h-4 text-indigo-500" /> Level
              </div>
              <p class="text-sm font-extrabold text-slate-900 dark:text-white capitalize">
                {{ formatEnum(job.experience_level) }}
              </p>
              <p class="text-[11px] text-slate-500 dark:text-slate-400">Experience required</p>
            </div>

          </div>

          <!-- Detailed Job Description Card -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 md:p-8 rounded-3xl space-y-8">
            
            <!-- Description -->
            <div class="space-y-3">
              <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">Job Overview & Description</h2>
              <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                {{ job.description }}
              </p>
            </div>

            <!-- Requirements -->
            <div v-if="job.requirements" class="space-y-3 pt-6 border-t border-slate-100 dark:border-slate-800">
              <h3 class="text-xs font-extrabold text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                Candidate Requirements
              </h3>
              <ul v-if="Array.isArray(job.requirements)" class="space-y-2.5">
                <li v-for="(req, index) in job.requirements" :key="index" class="flex items-start gap-3 text-xs sm:text-sm text-slate-700 dark:text-slate-300">
                  <CheckCircle2 class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                  <span>{{ req }}</span>
                </li>
              </ul>
              <p v-else class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                {{ job.requirements }}
              </p>
            </div>

            <!-- Benefits -->
            <div v-if="job.benefits" class="space-y-3 pt-6 border-t border-slate-100 dark:border-slate-800">
              <h3 class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
                Perks & Benefits
              </h3>
              <ul v-if="Array.isArray(job.benefits)" class="space-y-2.5">
                <li v-for="(ben, index) in job.benefits" :key="index" class="flex items-start gap-3 text-xs sm:text-sm text-slate-700 dark:text-slate-300">
                  <CheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
                  <span>{{ ben }}</span>
                </li>
              </ul>
              <p v-else class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                {{ job.benefits }}
              </p>
            </div>

            <!-- Job Work Location Section -->
            <div class="space-y-4 pt-6 border-t border-slate-100 dark:border-slate-800">
              <div class="flex items-center justify-between">
                <h3 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                  <MapPin class="w-4 h-4 text-blue-600" />
                  Job Work Location
                </h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/50">
                  {{ formatEnum(job.work_mode) }} Mode
                </span>
              </div>

              <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 p-5 rounded-2xl space-y-3">
                <div class="flex items-start gap-3">
                  <div class="p-2.5 bg-blue-600 text-white rounded-xl shrink-0 mt-0.5">
                    <Navigation class="w-4 h-4" />
                  </div>
                  <div class="space-y-1">
                    <h4 class="font-extrabold text-slate-900 dark:text-white text-sm">
                      {{ job.location ? job.location : 'Main Office' }}
                    </h4>
                    <p class="text-xs text-slate-600 dark:text-slate-300 font-medium">
                      {{ job.city }}, {{ job.country }}
                    </p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 pt-0.5">
                      Work Arrangement: {{ formatEnum(job.work_mode) }} ({{ formatEnum(job.job_type) }})
                    </p>
                  </div>
                </div>

                <div class="pt-2 flex items-center gap-3">
                  <a 
                    :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent((job.location ? job.location + ', ' : '') + job.city + ', ' + job.country)}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl transition shadow-2xs"
                  >
                    <Compass class="w-4 h-4 text-blue-600" />
                    <span>View on Map / Get Directions</span>
                  </a>
                </div>
              </div>
            </div>

          </div>

        </div>

        <!-- Right Sidebar (Company Summary & Safety Notice) -->
        <div class="space-y-6">
          
          <!-- Company Info Card -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 rounded-3xl space-y-5">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3">
              Employer Profile
            </h3>
            
            <div class="flex items-center gap-3">
              <img 
                v-if="job.company_logo" 
                :src="job.company_logo" 
                :alt="job.company_name" 
                class="w-12 h-12 rounded-xl object-cover border border-slate-100 dark:border-slate-800" 
              />
              <div v-else class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center font-bold">
                {{ job.company_name?.charAt(0) || 'C' }}
              </div>

              <div>
                <h4 class="font-bold text-slate-900 dark:text-white text-sm">{{ job.company_name }}</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ job.company_info?.industry || 'Technology' }}</p>
              </div>
            </div>

            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
              {{ job.company_info?.about || 'Top rated employer hiring on Job Search platform.' }}
            </p>

            <div class="space-y-2 text-xs text-slate-500 dark:text-slate-400 pt-2 border-t border-slate-100 dark:border-slate-800">
              <div class="flex justify-between">
                <span>Location</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ job.city }}, {{ job.country }}</span>
              </div>
              <div class="flex justify-between">
                <span>Team Size</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ job.company_info?.employees || '50-200' }}</span>
              </div>
            </div>

            <a 
              v-if="job.company_info?.website"
              :href="job.company_info.website" 
              target="_blank" 
              rel="noopener noreferrer"
              class="w-full flex items-center justify-center gap-2 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-xl text-xs font-semibold transition"
            >
              <Globe class="w-4 h-4 text-blue-500" />
              <span>Visit Website</span>
            </a>
          </div>

          <!-- Safety Notice Card -->
          <div class="bg-blue-50/60 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/50 p-5 rounded-3xl space-y-2">
            <h4 class="text-xs font-bold text-blue-900 dark:text-blue-300 flex items-center gap-2">
              <ShieldCheck class="w-4 h-4 text-blue-600" />
              Candidate Protection Notice
            </h4>
            <p class="text-[11px] text-blue-700 dark:text-blue-400 leading-relaxed">
              Job Search strictly prohibits employers from asking candidates for money or recruitment fees. Always report suspicious listings immediately.
            </p>
          </div>

        </div>

      </div>

    </main>

    <Footer />
  </div>
</template>
