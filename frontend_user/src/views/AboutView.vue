<script setup>
import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
import { 
  ShieldCheck, Zap, Globe, Heart, Briefcase, Users, Award, Clock 
} from 'lucide-vue-next'

const stats = [
  { label: 'Active Job Posts', value: '12,400+', icon: Briefcase },
  { label: 'Registered Companies', value: '3,200+', icon: Users },
  { label: 'Monthly Visitors', value: '890k+', icon: Globe },
  { label: 'Successful Placements', value: '145,000+', icon: Award }
]

const values = [
  { title: 'Transparency First', desc: 'Clear salary ranges, genuine company reviews, and upfront job requirements for every listing.', icon: ShieldCheck },
  { title: 'Fast & Seamless', desc: 'Instant 1-click applications with real-time application status tracking.', icon: Zap },
  { title: 'Global Reach', desc: 'Connecting Cambodian talent with local and international employers across 42 countries.', icon: Globe },
  { title: 'People Focused', desc: 'We champion candidates at every step — career advice, CV reviews, and interview prep resources.', icon: Heart }
]
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors duration-200">
    <Navbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">
      
      <!-- Hero Section -->
      <div class="text-center max-w-3xl mx-auto space-y-4">
        <span class="px-3.5 py-1.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-xs font-bold border border-blue-100 dark:border-blue-900/50">
          About Job Search
        </span>
        <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
          Empowering Careers, <br />
          <span class="text-blue-600 dark:text-blue-400">Connecting Great Teams.</span>
        </h1>
        <p class="text-base text-slate-600 dark:text-slate-300 leading-relaxed">
          Job Search is Cambodia's premier career recruitment platform. We empower candidates to discover meaningful work while helping innovative organizations hire top talent seamlessly.
        </p>
      </div>

      <!-- Live Stats Banner -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div 
          v-for="st in stats" 
          :key="st.label"
          class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 rounded-3xl text-center space-y-2 shadow-xs"
        >
          <div class="p-3 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 rounded-2xl w-12 h-12 mx-auto flex items-center justify-center">
            <component :is="st.icon" class="w-6 h-6" />
          </div>
          <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">{{ st.value }}</div>
          <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ st.label }}</div>
        </div>
      </div>

      <!-- Core Mission Section -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-8 sm:p-12 shadow-sm grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
        <div class="space-y-4">
          <div class="inline-flex items-center gap-2 text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">
            <Clock class="w-4 h-4" />
            <span>Our Mission</span>
          </div>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
            Building Cambodia's <span class="text-blue-600 dark:text-blue-400">Largest</span> Career Network
          </h2>
          <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
            We believe everyone deserves access to meaningful work opportunities. Our platform bridges the gap between talented individuals and forward-thinking organizations across Southeast Asia.
          </p>
        </div>
        <div class="bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-800 rounded-2xl p-8 text-white text-center space-y-3 shadow-lg">
          <h3 class="text-3xl font-extrabold">890K+</h3>
          <p class="text-xs text-blue-200 font-semibold">Registered candidates trust Job Search with their career growth.</p>
        </div>
      </div>

      <!-- Core Values Grid -->
      <div class="space-y-8">
        <div class="text-center max-w-2xl mx-auto space-y-2">
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Our Driving Principles</h2>
          <p class="text-xs text-slate-500 dark:text-slate-400">The core values guiding everything we build at Job Search.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div 
            v-for="val in values" 
            :key="val.title"
            class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 rounded-3xl space-y-3 shadow-xs hover:shadow-md transition"
          >
            <div class="p-3 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 rounded-2xl w-12 h-12 flex items-center justify-center">
              <component :is="val.icon" class="w-6 h-6" />
            </div>
            <h3 class="font-bold text-slate-900 dark:text-white text-base">{{ val.title }}</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ val.desc }}</p>
          </div>
        </div>
      </div>

    </main>

    <Footer />
  </div>
</template>
