<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
import { useAuth, resolveAssetUrl } from '../composables/useAuth'
import { 
  Search, MapPin, Building2, Globe, Mail, Phone, 
  Users, CheckCircle2, ShieldCheck, Calendar, ArrowRight,
  Filter, Sparkles, Building, ChevronRight, X
} from 'lucide-vue-next'

const router = useRouter()
const { request } = useAuth()

const searchQuery = ref('')
const selectedIndustry = ref('All')
const selectedSize = ref('All')
const onlyVerified = ref(false)

const industries = [
  'All',
  'Software & IT',
  'Banking & Finance',
  'E-Commerce & Retail',
  'Healthcare & Medical',
  'Telecommunications',
  'Marketing & Media'
]

const companySizes = ['All', '1-10', '11-50', '51-200', '201-500', '501-1000', '1000+']

const companies = ref([])
const isLoading = ref(true)
const loadError = ref('')

const loadCompanies = async () => {
  isLoading.value = true
  loadError.value = ''

  try {
    const response = await request('/companies')
    companies.value = (response.data?.data || response.data || []).map((company) => ({
      ...company,
      logo: resolveAssetUrl(company.logo),
      cover_image: resolveAssetUrl(company.cover_image),
    }))
  } catch (error) {
    loadError.value = error.message || 'Unable to load companies.'
  } finally {
    isLoading.value = false
  }
}

onMounted(loadCompanies)

const filteredCompanies = computed(() => {
  return companies.value.filter(c => {
    const matchesIndustry = selectedIndustry.value === 'All' || c.industry === selectedIndustry.value
    const matchesSize = selectedSize.value === 'All' || c.company_size === selectedSize.value
    const matchesVerified = !onlyVerified.value || c.is_verified
    const matchesQuery = !searchQuery.value || 
      c.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      c.city?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      c.description?.toLowerCase().includes(searchQuery.value.toLowerCase())

    return matchesIndustry && matchesSize && matchesVerified && matchesQuery
  })
})

const hasActiveFilters = computed(() => {
  return searchQuery.value !== '' || selectedIndustry.value !== 'All' || selectedSize.value !== 'All' || onlyVerified.value
})

const resetAllFilters = () => {
  searchQuery.value = ''
  selectedIndustry.value = 'All'
  selectedSize.value = 'All'
  onlyVerified.value = false
}

const goToCompanyDetail = (id) => {
  router.push(`/companies/${id}`)
}
</script>

<template>
  <div class="min-h-screen bg-slate-50/60 dark:bg-[#070c16] font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors duration-300">
    <Navbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
      
      <!-- ─── Page Hero Banner Header ───────────────────────────────────── -->
      <div class="text-center max-w-3xl mx-auto space-y-4">
        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-xs font-bold border border-blue-100 dark:border-blue-900/50 shadow-2xs">
          <Building2 class="w-3.5 h-3.5" />
          <span>Top Hiring Employers</span>
        </span>
        
        <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
          Discover Great <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400">Companies</span>
        </h1>
        
        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 max-w-xl mx-auto">
          Explore verified employer profiles, culture, company size, and active open roles across Cambodia and Southeast Asia.
        </p>
      </div>

      <!-- ─── Search & Filters Bar ──────────────────────────────────────── -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 sm:p-6 rounded-3xl shadow-xs space-y-4">
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
          
          <!-- Search Input with Clear Button -->
          <div class="relative md:col-span-2">
            <Search class="w-4.5 h-4.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input 
              id="company-search"
              aria-label="Search companies"
              v-model="searchQuery"
              type="text"
              placeholder="Search company name, city, or keywords..."
              class="w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition"
            />
            <button 
              v-if="searchQuery"
              @click="searchQuery = ''"
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition p-1 rounded-full hover:bg-slate-200/50 dark:hover:bg-slate-700/50"
              title="Clear search"
            >
              <X class="w-4 h-4" />
            </button>
          </div>

          <!-- Industry Filter with Clear Action / Reset Option -->
          <div class="relative">
            <select
              id="company-industry"
              aria-label="Filter by industry"
              v-model="selectedIndustry"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:border-blue-600 transition font-medium cursor-pointer"
            >
              <option v-for="ind in industries" :key="ind" :value="ind">
                {{ ind === 'All' ? 'All Industries' : ind }}
              </option>
            </select>
            <button 
              v-if="selectedIndustry !== 'All'"
              @click="selectedIndustry = 'All'"
              type="button"
              class="absolute right-8 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition p-1"
              title="Clear industry filter"
            >
              <X class="w-3.5 h-3.5" />
            </button>
          </div>

          <!-- Company Size Filter with Clear Action / Reset Option -->
          <div class="relative">
            <select
              id="company-size"
              aria-label="Filter by company size"
              v-model="selectedSize"
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:border-blue-600 transition font-medium cursor-pointer"
            >
              <option v-for="sz in companySizes" :key="sz" :value="sz">
                {{ sz === 'All' ? 'All Sizes' : `${sz} employees` }}
              </option>
            </select>
            <button 
              v-if="selectedSize !== 'All'"
              @click="selectedSize = 'All'"
              type="button"
              class="absolute right-8 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition p-1"
              title="Clear size filter"
            >
              <X class="w-3.5 h-3.5" />
            </button>
          </div>

        </div>

        <!-- Secondary Filter Controls -->
        <div class="flex items-center justify-between gap-4 pt-2 border-t border-slate-100 dark:border-slate-800 flex-wrap text-xs">
          <label class="flex items-center gap-2 cursor-pointer select-none font-semibold text-slate-700 dark:text-slate-300">
            <input 
              v-model="onlyVerified"
              type="checkbox" 
              class="w-4 h-4 text-blue-600 rounded border-slate-300 dark:border-slate-700 focus:ring-blue-500/20" 
            />
            <span class="flex items-center gap-1">
              <ShieldCheck class="w-4 h-4 text-blue-600" />
              <span>Show Verified Companies Only</span>
            </span>
          </label>

          <div class="flex items-center gap-4">
            <button 
              v-if="hasActiveFilters"
              @click="resetAllFilters"
              type="button"
              class="text-blue-600 dark:text-blue-400 font-semibold hover:underline flex items-center gap-1"
            >
              <X class="w-3.5 h-3.5" />
              <span>Clear All Filters</span>
            </button>

            <span class="text-slate-500 dark:text-slate-400 font-medium">
              Showing <strong class="text-slate-900 dark:text-white">{{ filteredCompanies.length }}</strong> companies
            </span>
          </div>
        </div>

      </div>

      <!-- ─── Companies Grid Layout ──────────────────────────────────────── -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <div 
          v-for="comp in filteredCompanies" 
          :key="comp.id"
          @click="goToCompanyDetail(comp.id)"
          class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden shadow-xs hover:shadow-lg transition duration-300 flex flex-col justify-between relative group cursor-pointer"
        >
          <!-- Top Cover Banner -->
          <div class="h-28 sm:h-32 w-full relative overflow-hidden bg-gradient-to-r from-blue-700 to-indigo-900">
            <img 
              v-if="comp.cover_image" 
              :src="comp.cover_image" 
              :alt="comp.name" 
              class="w-full h-full object-cover group-hover:scale-105 transition transform duration-500 opacity-90"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-transparent"></div>

            <!-- Verified Badge Top Right -->
            <div 
              v-if="comp.is_verified" 
              class="absolute top-3 right-3 bg-blue-600/90 backdrop-blur-md text-white text-[10px] font-extrabold uppercase px-3 py-1 rounded-full tracking-wider flex items-center gap-1 shadow-md border border-white/20"
            >
              <ShieldCheck class="w-3.5 h-3.5 text-amber-300" /> Verified Employer
            </div>
          </div>

          <!-- Content Padding Box -->
          <div class="p-6 space-y-4 -mt-10 relative z-10 flex-1 flex flex-col justify-between">
            
            <div class="space-y-3">
              
              <!-- Logo + Header Title -->
              <div class="flex items-end gap-3.5">
                <img 
                  v-if="comp.logo" 
                  :src="comp.logo" 
                  :alt="comp.name" 
                  class="w-16 h-16 rounded-2xl object-cover border-2 border-white dark:border-slate-900 shadow-md shrink-0 bg-white"
                />
                <div 
                  v-else 
                  class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white font-extrabold text-xl flex items-center justify-center border-2 border-white dark:border-slate-900 shadow-md shrink-0"
                >
                  {{ comp.name.charAt(0) }}
                </div>

                <div class="space-y-0.5 min-w-0 flex-1">
                  <div class="flex items-center gap-1.5">
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-base sm:text-lg truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                      {{ comp.name }}
                    </h3>
                    <ShieldCheck v-if="comp.is_verified" class="w-4 h-4 text-blue-600 shrink-0" title="Verified Employer" />
                  </div>
                  
                  <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 flex items-center gap-1">
                    <Building class="w-3.5 h-3.5" /> {{ comp.industry || 'Technology' }}
                  </p>
                </div>
              </div>

              <!-- Description -->
              <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed line-clamp-2 pt-1">
                {{ comp.description }}
              </p>

              <!-- Specs Grid: Size, Location, Founded -->
              <div class="grid grid-cols-2 gap-2 pt-2 text-xs text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-1.5">
                  <MapPin class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                  <span class="truncate">{{ comp.city }}, {{ comp.country }}</span>
                </div>

                <div class="flex items-center gap-1.5">
                  <Users class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                  <span>{{ comp.company_size }} employees</span>
                </div>

                <div v-if="comp.founded_year" class="flex items-center gap-1.5">
                  <Calendar class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                  <span>Founded {{ comp.founded_year }}</span>
                </div>

                <div v-if="comp.website" class="flex items-center gap-1.5 truncate">
                  <Globe class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                  <span class="truncate">{{ comp.website.replace('https://', '') }}</span>
                </div>
              </div>

            </div>

            <!-- Footer: Open Jobs Count & View Action -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3 text-xs font-bold mt-auto">
              <span class="px-3 py-1 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/50">
                {{ comp.open_jobs_count || 0 }} Active Openings
              </span>

              <span class="text-blue-600 dark:text-blue-400 group-hover:translate-x-1 transition transform flex items-center gap-1">
                <span>View Company Profile</span>
                <ChevronRight class="w-4 h-4" />
              </span>
            </div>

          </div>
        </div>

      </div>

    </main>

    <Footer />
  </div>
</template>