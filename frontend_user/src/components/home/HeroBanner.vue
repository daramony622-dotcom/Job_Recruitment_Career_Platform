<script setup>
import { ref } from 'vue'
import { 
  Search, 
  MapPin, 
  Briefcase, 
  Building2, 
  Users, 
  Globe,
  X,
  Sparkles
} from 'lucide-vue-next'

// Search Inputs State
const jobTitle = ref('')
const location = ref('')

// Popular Searches Tags
const popularTags = [
  'Laravel Developer',
  'Vue.js Specialist',
  'UI/UX Designer',
  'Data Analyst',
  'Network Engineer'
]

// Handle Search Action
const handleSearch = () => {
  console.log('Search Triggered:', { 
    jobTitle: jobTitle.value, 
    location: location.value 
  })
}

// Clear Inputs Action
const clearInputs = () => {
  jobTitle.value = ''
  location.value = ''
}

// Select Popular Tag Action
const selectTag = (tag) => {
  jobTitle.value = tag
}
</script>

<template>
  <section class="relative min-h-[540px] w-full bg-cover bg-center flex items-center px-4 sm:px-8 py-16 text-white overflow-hidden rounded-3xl bg-[url('https://images.unsplash.com/photo-1521737711867-e3b97375f902?q=80&w=1920&auto=format&fit=crop')] shadow-xl my-2">
    
    <!-- Dark Gradient Overlay -->
    <div class="absolute inset-0 bg-gradient-to-r from-slate-950/95 via-slate-900/90 to-blue-950/75"></div>

    <div class="relative z-10 max-w-5xl mx-auto w-full space-y-8">
      
      <!-- Top Badge -->
      <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-slate-200 shadow-sm">
        <span class="flex h-2 w-2 relative">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-400"></span>
        </span>
        <span>Over 12,400 active job opportunities posted this month</span>
      </div>

      <!-- Main Title -->
      <div class="space-y-4 max-w-3xl">
        <h1 class="text-3xl sm:text-5xl md:text-6xl font-extrabold tracking-tight text-white leading-tight">
          Discover your next <br />
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-sky-300 to-indigo-300">dream career role</span>
        </h1>

        <p class="text-slate-300 text-sm sm:text-base md:text-lg max-w-2xl font-medium leading-relaxed">
          Search thousands of verified full-time, remote, and hybrid positions across top tech & business employers.
        </p>
      </div>

      <!-- Glassmorphism Search Box Form -->
      <form @submit.prevent="handleSearch" class="bg-white/95 dark:bg-slate-900/95 p-2.5 rounded-2xl shadow-2xl backdrop-blur-xl border border-white/20 dark:border-slate-800 flex flex-col md:flex-row items-center gap-2 max-w-3xl">
        
        <!-- Job Title Input -->
        <div class="flex items-center gap-3 px-3.5 py-2.5 w-full md:w-1/2 bg-slate-50 dark:bg-slate-800/80 md:bg-transparent rounded-xl">
          <Search class="w-5 h-5 text-slate-400 shrink-0" />
          <input 
            v-model="jobTitle"
            type="text" 
            placeholder="Job title, skill, or company..." 
            class="w-full bg-transparent text-slate-800 dark:text-slate-100 placeholder-slate-400 text-xs sm:text-sm focus:outline-none font-medium"
          />
        </div>

        <div class="hidden md:block w-px h-8 bg-slate-200 dark:bg-slate-700"></div>

        <!-- Location Input -->
        <div class="flex items-center gap-3 px-3.5 py-2.5 w-full md:w-1/2 bg-slate-50 dark:bg-slate-800/80 md:bg-transparent rounded-xl">
          <MapPin class="w-5 h-5 text-slate-400 shrink-0" />
          <input 
            v-model="location"
            type="text" 
            placeholder="City, region, or Remote..." 
            class="w-full bg-transparent text-slate-800 dark:text-slate-100 placeholder-slate-400 text-xs sm:text-sm focus:outline-none font-medium"
          />
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 w-full md:w-auto shrink-0">
          <button 
            v-if="jobTitle || location"
            @click="clearInputs"
            type="button"
            class="p-3 text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition flex items-center justify-center cursor-pointer"
            title="Clear inputs"
          >
            <X class="w-5 h-5" />
          </button>

          <button 
            type="submit"
            class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-xl transition flex items-center justify-center gap-2 text-xs sm:text-sm shadow-md shadow-blue-500/25 active:scale-95 cursor-pointer shrink-0"
          >
            <Search class="w-4 h-4" />
            <span>Search Jobs</span>
          </button>
        </div>
      </form>

      <!-- Popular Tags -->
      <div class="flex flex-wrap items-center gap-2 text-xs text-slate-300">
        <span class="text-slate-400 font-semibold flex items-center gap-1">
          <Sparkles class="w-3.5 h-3.5 text-amber-400" /> Popular:
        </span>
        <button 
          v-for="tag in popularTags" 
          :key="tag"
          @click="selectTag(tag)"
          type="button"
          class="px-3 py-1 rounded-full bg-white/10 hover:bg-white/20 border border-white/15 text-slate-200 transition font-medium cursor-pointer"
        >
          {{ tag }}
        </button>
      </div>

      <!-- Live Stats Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-6 border-t border-white/15 max-w-3xl">
        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-white/10 rounded-xl text-blue-400 shrink-0">
            <Briefcase class="w-5 h-5" />
          </div>
          <div>
            <div class="text-base font-extrabold text-white">12,400+</div>
            <div class="text-[11px] text-slate-400 font-medium">Live jobs</div>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-white/10 rounded-xl text-blue-400 shrink-0">
            <Building2 class="w-5 h-5" />
          </div>
          <div>
            <div class="text-base font-extrabold text-white">3,200+</div>
            <div class="text-[11px] text-slate-400 font-medium">Companies</div>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-white/10 rounded-xl text-blue-400 shrink-0">
            <Users class="w-5 h-5" />
          </div>
          <div>
            <div class="text-base font-extrabold text-white">890k+</div>
            <div class="text-[11px] text-slate-400 font-medium">Candidates</div>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-white/10 rounded-xl text-blue-400 shrink-0">
            <Globe class="w-5 h-5" />
          </div>
          <div>
            <div class="text-base font-extrabold text-white">42</div>
            <div class="text-[11px] text-slate-400 font-medium">Countries</div>
          </div>
        </div>
      </div>

    </div>
  </section>
</template>