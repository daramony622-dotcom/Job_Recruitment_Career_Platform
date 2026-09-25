<script setup>
import { ref } from 'vue'
import Navbar from './layout/Navbar.vue'
import HeroBanner from './home/HeroBanner.vue'
import JobFilters from './jobs/JobFilters.vue'
import CategorySection from './home/CategorySection.vue'
import JobList from './jobs/JobList.vue'
import Footer from './layout/Footer.vue'
import { ArrowRight, UserCheck } from 'lucide-vue-next'

const selectedCategory = ref('')
const selectedSkill = ref('')
const selectedLocation = ref('')
const selectedSalary = ref('')
const selectedTime = ref('')


function handleCategorySelect(cat) {
  selectedCategory.value = cat.slug || cat.name
  // Smoothly scroll to jobs section if on homepage
  const el = document.getElementById('featured-jobs-section')
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

function handleClearFilters() {
  selectedCategory.value = ''
  selectedSkill.value = ''
  selectedLocation.value = ''
  selectedSalary.value = ''
  selectedTime.value = ''
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-[#070c16] font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors duration-300">
    <Navbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-14">
      <!-- 1. Hero Banner -->
      <HeroBanner />

      <!-- Filters -->
      <JobFilters
        v-model:category="selectedCategory"
        v-model:skill="selectedSkill"
        v-model:location="selectedLocation"
        v-model:salary="selectedSalary"
        v-model:time="selectedTime"
        :has-active-filters="Boolean(selectedCategory || selectedSkill || selectedLocation || selectedSalary || selectedTime)"
        @reset="handleClearFilters"
      />

      <!-- 2. Categories Section -->
      <CategorySection
        v-model="selectedCategory"
        :navigate-on-select="false"
        @select="handleCategorySelect"
      />

      <!-- 3. Featured & Latest Jobs -->
      <section id="featured-jobs-section" class="space-y-6 scroll-mt-24">
        <div>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Featured Opportunities</h2>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Discover recent job openings from top employers</p>
        </div>
        <JobList
          v-model:category="selectedCategory"
          v-model:skill="selectedSkill"
          v-model:location="selectedLocation"
          :salary="selectedSalary"
          @clear-filters="handleClearFilters"
        />
      </section>

      <!-- 4. Call to Action Banner -->
      <section class="site-banner site-banner-cta relative bg-gradient-to-br from-blue-900 via-indigo-900 to-blue-950 rounded-3xl p-8 sm:p-12 text-white shadow-2xl shadow-blue-950/20 grid grid-cols-1 lg:grid-cols-3 gap-8 items-center overflow-hidden border border-blue-700/50">
        <!-- Decorative glow blobs -->
        <div class="absolute -right-16 -bottom-16 w-72 h-72 bg-blue-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -top-16 w-64 h-64 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="lg:col-span-2 space-y-4 relative z-10">
          <span class="inline-block px-3.5 py-1 rounded-full bg-white/10 text-xs font-bold uppercase tracking-widest text-cyan-200 border border-white/15">
            Take Action
          </span>
          <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight leading-tight text-white">
            Ready to Take Your<br/>Next Career Step?
          </h2>
          <p class="text-sm text-slate-200 max-w-xl leading-relaxed">
            Create your free candidate account today to get matched with top hiring companies, upload your CV, and apply with 1-click.
          </p>
        </div>

        <div class="flex flex-col gap-3 relative z-10">
          <router-link
            to="/register"
            class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white text-blue-900 hover:bg-blue-50 rounded-2xl text-sm font-bold transition-all duration-200 shadow-lg active:scale-95 cursor-pointer"
          >
            <UserCheck class="w-4 h-4 text-blue-900" />
            <span>Create Free Account</span>
          </router-link>
          <router-link
            to="/jobs"
            class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white/15 hover:bg-white/25 border border-white/20 text-white rounded-2xl text-sm font-bold transition-all duration-200 active:scale-95 cursor-pointer"
          >
            <span>Browse 12,400+ Jobs</span>
            <ArrowRight class="w-4 h-4" />
          </router-link>
        </div>
      </section>

    </main>

    <Footer />
  </div>
</template>

