<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { 
  MapPin, DollarSign, Calendar, Eye, 
  Bookmark, Send, Sparkles, Users, ShieldAlert, Building2
} from 'lucide-vue-next'

const router = useRouter()

const props = defineProps({
  jobPosts: {
    type: Array,
    default: null
  }
})

// Sample fallback mock matching the exact database schema
const defaultJobPosts = [
  {
    id: 1,
    company_id: 101,
    category_id: 1,
    category_name: 'Software Engineering',
    company_name: 'TechMatrix Global',
    company_logo: 'https://images.unsplash.com/photo-1549923746-c502d488b3ea?w=150&h=150&fit=crop',
    title: 'Senior Full Stack Laravel & Vue.js Developer',
    slug: 'senior-full-stack-laravel-vue-developer',
    description: 'We are seeking an experienced Senior Full Stack Developer proficient in Laravel 11 and Vue 3...',
    requirements: '4+ years of professional Laravel and Vue.js experience.',
    benefits: 'Competitive salary, 13th month bonus, senior health insurance.',
    job_type: 'full_time',
    work_mode: 'hybrid',
    experience_level: 'senior',
    location: 'Monivong Blvd',
    city: 'Phnom Penh',
    country: 'Cambodia',
    salary_min: 1200.00,
    salary_max: 2200.00,
    salary_currency: 'USD',
    salary_period: 'monthly',
    is_salary_visible: true,
    vacancies: 3,
    deadline: '2026-04-30',
    status: 'published',
    is_featured: true,
    views_count: 542,
    published_at: '2026-09-01T08:00:00Z'
  },
  {
    id: 2,
    company_id: 102,
    category_id: 2,
    category_name: 'UI/UX Design',
    company_name: 'ABA Digital Tech',
    company_logo: 'https://images.unsplash.com/photo-1560179707-f14e90ef3623?w=150&h=150&fit=crop',
    title: 'Lead UI/UX Mobile Product Designer',
    slug: 'lead-ui-ux-mobile-product-designer',
    description: 'Lead our mobile banking user experience design across iOS and Android platforms...',
    requirements: '5+ years UI/UX experience with Figma design systems.',
    benefits: 'Flexible remote work, gym membership, health insurance.',
    job_type: 'full_time',
    work_mode: 'remote',
    experience_level: 'lead',
    location: 'Sihanouk Blvd',
    city: 'Phnom Penh',
    country: 'Cambodia',
    salary_min: 1500.00,
    salary_max: 2500.00,
    salary_currency: 'USD',
    salary_period: 'monthly',
    is_salary_visible: true,
    vacancies: 2,
    deadline: '2026-04-20',
    status: 'published',
    is_featured: true,
    views_count: 310,
    published_at: '2026-09-01T10:30:00Z'
  },
  {
    id: 3,
    company_id: 103,
    category_id: 3,
    category_name: 'IT Support',
    company_name: 'CambodiaTech Solutions',
    company_logo: 'https://images.unsplash.com/photo-1572021335469-31706a17aaef?w=150&h=150&fit=crop',
    title: 'Junior Network & System Support Specialist',
    slug: 'junior-network-system-support-specialist',
    description: 'Assist with office network infrastructure, server maintenance, and user support...',
    requirements: 'Degree in CS or IT, CCNA certification is a plus.',
    benefits: 'Training budget, career progression.',
    job_type: 'contract',
    work_mode: 'onsite',
    experience_level: 'junior',
    location: 'Toul Kork',
    city: 'Phnom Penh',
    country: 'Cambodia',
    salary_min: 500.00,
    salary_max: 800.00,
    salary_currency: 'USD',
    salary_period: 'monthly',
    is_salary_visible: false,
    vacancies: 1,
    deadline: '2026-04-15',
    status: 'published',
    is_featured: false,
    views_count: 185,
    published_at: '2026-08-28T09:00:00Z'
  }
]

const activePosts = computed(() => props.jobPosts || defaultJobPosts)

// Helper: Format Salary String
const formatSalary = (job) => {
  if (!job.is_salary_visible) return 'Negotiable Salary'
  const min = Number(job.salary_min).toLocaleString()
  const max = Number(job.salary_max).toLocaleString()
  const period = job.salary_period ? job.salary_period.replace('ly', '') : 'month'
  return `$${min} - $${max} ${job.salary_currency || 'USD'} / ${period}`
}

// Helper: Human-Readable Enum Formatting
const formatEnum = (val) => {
  if (!val) return ''
  return val.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')
}

// Helper: Company Initials
const getCompanyInitial = (name) => {
  if (!name) return 'C'
  const words = name.split(' ')
  if (words.length >= 2) {
    return (words[0][0] + words[1][0]).toUpperCase()
  }
  return name.slice(0, 2).toUpperCase()
}

// Helper: Badge Style mapping for job_type
const jobTypeBadgeStyle = (type) => {
  switch (type) {
    case 'full_time': return 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-100 dark:border-blue-900/50'
    case 'part_time': return 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-900/50'
    case 'contract': return 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-900/50'
    case 'remote': return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-900/50'
    case 'freelance': return 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-900/50'
    default: return 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700'
  }
}

// Helper: Badge Style for work_mode
const workModeBadgeStyle = (mode) => {
  switch (mode) {
    case 'remote': return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-900/50'
    case 'hybrid': return 'bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-900/50'
    default: return 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700'
  }
}

const goToDetail = (id) => {
  router.push(`/jobs/${id}`)
}
</script>

<template>
  <div class="space-y-4">
    <div 
      v-for="job in activePosts" 
      :key="job.id"
      class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 md:p-7 shadow-xs hover:shadow-md transition duration-200 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden group"
    >
      <!-- Featured Top Badge -->
      <div v-if="job.is_featured" class="absolute top-0 right-0 bg-blue-600 text-white text-[10px] font-bold uppercase px-3.5 py-1 rounded-bl-2xl tracking-wider flex items-center gap-1 shadow-2xs">
        <Sparkles class="w-3 h-3 text-amber-300" /> Featured
      </div>

      <!-- Main Info Left -->
      <div class="flex items-start gap-4 flex-1 min-w-0">
        
        <!-- Company Image / Logo Avatar -->
        <div class="shrink-0 mt-0.5">
          <img 
            v-if="job.company_logo" 
            :src="job.company_logo" 
            :alt="job.company_name" 
            class="w-14 h-14 rounded-2xl object-cover border border-slate-200/80 dark:border-slate-700/80 shadow-2xs group-hover:scale-105 transition transform duration-200"
          />
          <div 
            v-else 
            class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white font-extrabold text-base flex items-center justify-center shadow-md shadow-blue-500/20 group-hover:scale-105 transition transform duration-200"
          >
            {{ getCompanyInitial(job.company_name) }}
          </div>
        </div>

        <div class="space-y-2.5 min-w-0 flex-1">
          
          <!-- Category & Status / Enum Badges -->
          <div class="flex items-center gap-2 flex-wrap">
            <span v-if="job.category_name" class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
              {{ job.category_name }}
            </span>
            <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold border" :class="jobTypeBadgeStyle(job.job_type)">
              {{ formatEnum(job.job_type) }}
            </span>
            <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold border" :class="workModeBadgeStyle(job.work_mode)">
              {{ formatEnum(job.work_mode) }}
            </span>
            <span v-if="job.experience_level" class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-900/50">
              {{ formatEnum(job.experience_level) }}
            </span>
            <span v-if="job.status && job.status !== 'published'" class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/50 flex items-center gap-1">
              <ShieldAlert class="w-3 h-3" /> {{ formatEnum(job.status) }}
            </span>
          </div>
          
          <!-- Job Title -->
          <router-link :to="`/jobs/${job.id}`">
            <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white tracking-tight truncate hover:text-blue-600 dark:hover:text-blue-400 transition cursor-pointer">
              {{ job.title }}
            </h2>
          </router-link>

          <!-- Company Name & Location -->
          <div class="flex items-center gap-4 flex-wrap text-xs text-slate-600 dark:text-slate-400 font-medium">
            <span v-if="job.company_name" class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1">
              <Building2 class="w-3.5 h-3.5 text-blue-600" />
              {{ job.company_name }}
            </span>
            <span class="flex items-center gap-1">
              <MapPin class="w-3.5 h-3.5 text-slate-400 shrink-0" />
              {{ job.location ? `${job.location}, ` : '' }}{{ job.city || 'Phnom Penh' }}, {{ job.country || 'Cambodia' }}
            </span>
          </div>

          <!-- Metadata Grid: Salary, Vacancies, Views, Deadline -->
          <div class="flex items-center gap-4 flex-wrap text-xs text-slate-500 dark:text-slate-400 pt-1 border-t border-slate-100 dark:border-slate-800/80">
            <span class="flex items-center gap-1 font-extrabold text-blue-600 dark:text-blue-400">
              <DollarSign class="w-3.5 h-3.5 text-emerald-500 shrink-0" />
              {{ formatSalary(job) }}
            </span>
            <span v-if="job.vacancies" class="flex items-center gap-1 font-medium">
              <Users class="w-3.5 h-3.5 text-slate-400 shrink-0" />
              {{ job.vacancies }} Vacanc{{ job.vacancies > 1 ? 'ies' : 'y' }}
            </span>
            <span v-if="job.deadline" class="flex items-center gap-1 font-medium">
              <Calendar class="w-3.5 h-3.5 text-rose-400 shrink-0" />
              Apply by: {{ job.deadline }}
            </span>
            <span class="flex items-center gap-1 font-medium ml-auto sm:ml-0">
              <Eye class="w-3.5 h-3.5 text-slate-400 shrink-0" />
              {{ job.views_count || 0 }} views
            </span>
          </div>

        </div>
      </div>

      <!-- Right: Action Buttons -->
      <div class="flex items-center gap-2 w-full md:w-auto justify-end pt-3 md:pt-0 border-t md:border-t-0 border-slate-100 dark:border-slate-800 shrink-0">
        <button 
          type="button" 
          class="p-3 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-2xl border border-slate-200 dark:border-slate-700 transition cursor-pointer" 
          title="Save Job"
        >
          <Bookmark class="w-4.5 h-4.5" />
        </button>
        <button 
          @click="goToDetail(job.id)"
          type="button"
          class="flex-1 md:flex-initial flex items-center justify-center gap-1.5 px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-bold transition shadow-md shadow-blue-500/20 active:scale-95 cursor-pointer"
        >
          <Send class="w-3.5 h-3.5" />
          <span>Apply Now</span>
        </button>
      </div>

    </div>
  </div>
</template>