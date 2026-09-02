<script setup>
import { ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import Navbar from "../components/layout/Navbar.vue";
import Footer from "../components/layout/Footer.vue";
import JobCard from "../components/jobs/JobCard.vue";
import {
  Building2,
  MapPin,
  Globe,
  Mail,
  Phone,
  Users,
  Calendar,
  ShieldCheck,
  ArrowLeft,
  Briefcase,
  Navigation,
  Compass,
  Sparkles,
} from "lucide-vue-next";

const route = useRoute();
const router = useRouter();

// Sample company profile matching schema
const company = ref({
  id: route.params.id || 1,
  user_id: 10,
  name: "TechMatrix Global",
  slug: "techmatrix-global",
  logo: "https://images.unsplash.com/photo-1549923746-c502d488b3ea?w=150&h=150&fit=crop",
  cover_image:
    "https://images.unsplash.com/photo-1497366216548-37526070297c?w=1200&auto=format&fit=crop&q=80",
  website: "https://techmatrix.example.com",
  email: "careers@techmatrix.com",
  phone: "+855 23 999 888",
  description: `TechMatrix Global is a leading software innovation and digital transformation company headquartered in Phnom Penh, Cambodia. We specialize in building enterprise cloud architectures, high-performance mobile applications, and micro-services for financial, e-commerce, and logistics industries.

Founded in 2019, our mission is to empower Southeast Asian organizations with top-tier technology and engineering excellence. We champion modern tech stacks including Vue.js, Laravel, Flutter, Kubernetes, and AWS Cloud Infrastructure.`,
  industry: "Software & Information Technology",
  company_size: "51-200",
  founded_year: 2019,
  country: "Cambodia",
  city: "Phnom Penh",
  address: "Monivong Blvd, Khan Daun Penh",
  status: "approved",
  is_verified: true,
  verified_at: "2026-01-15T00:00:00Z",
  open_jobs_count: 3,
});

// Sample open job posts for this company
const companyJobs = ref([
  {
    id: 1,
    company_id: 1,
    category_id: 1,
    category_name: "Software Engineering",
    company_name: "TechMatrix Global",
    company_logo:
      "https://images.unsplash.com/photo-1549923746-c502d488b3ea?w=150&h=150&fit=crop",
    title: "Senior Full Stack Laravel & Vue.js Developer",
    slug: "senior-full-stack-laravel-vue-developer",
    description:
      "We are seeking an experienced Senior Full Stack Developer proficient in Laravel 11 and Vue 3...",
    job_type: "full_time",
    work_mode: "hybrid",
    experience_level: "senior",
    location: "Monivong Blvd",
    city: "Phnom Penh",
    country: "Cambodia",
    salary_min: 1200.0,
    salary_max: 2200.0,
    salary_currency: "USD",
    salary_period: "monthly",
    is_salary_visible: true,
    vacancies: 3,
    deadline: "2026-04-30",
    status: "published",
    is_featured: true,
    views_count: 542,
  },
]);
</script>

<template>
  <div
    class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors duration-200"
  >
    <Navbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <!-- Back Button -->
      <button
        @click="router.back()"
        class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition cursor-pointer"
      >
        <ArrowLeft class="w-4 h-4" />
        <span>Back to Companies Directory</span>
      </button>

      <!-- Company Cover Banner & Profile Card -->
      <div
        class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden shadow-xs relative space-y-6"
      >
        <!-- Cover Photo Banner -->
        <div
          class="h-44 sm:h-56 w-full relative overflow-hidden bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-900"
        >
          <img
            v-if="company.cover_image"
            :src="company.cover_image"
            :alt="company.name"
            class="w-full h-full object-cover opacity-90"
          />
          <div
            class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-transparent"
          ></div>

          <div
            v-if="company.is_verified"
            class="absolute top-4 right-4 bg-blue-600/90 backdrop-blur-md text-white text-xs font-bold uppercase px-3.5 py-1 rounded-full border border-white/20 flex items-center gap-1.5 shadow-md"
          >
            <ShieldCheck class="w-4 h-4 text-amber-300" /> Verified Company
            Profile
          </div>
        </div>

        <!-- Header Info Row -->
        <div
          class="px-6 md:px-8 pb-8 -mt-16 relative z-10 flex flex-col md:flex-row items-start md:items-end justify-between gap-6"
        >
          <div class="flex items-start md:items-end gap-5">
            <img
              v-if="company.logo"
              :src="company.logo"
              :alt="company.name"
              class="w-24 h-24 md:w-28 md:h-28 rounded-3xl object-cover border-4 border-white dark:border-slate-900 shadow-xl bg-white shrink-0"
            />
            <div
              v-else
              class="w-24 h-24 md:w-28 md:h-28 rounded-3xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white font-extrabold text-3xl flex items-center justify-center border-4 border-white dark:border-slate-900 shadow-xl shrink-0"
            >
              {{ company.name.charAt(0) }}
            </div>

            <div class="space-y-1 pt-2">
              <div class="flex items-center gap-2 flex-wrap">
                <h1
                  class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight"
                >
                  {{ company.name }}
                </h1>
                <ShieldCheck
                  v-if="company.is_verified"
                  class="w-5 h-5 text-blue-600"
                  title="Verified Employer"
                />
              </div>

              <p
                class="text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 flex items-center gap-1.5"
              >
                <Building2 class="w-4 h-4" /> {{ company.industry }}
              </p>

              <p
                class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5"
              >
                <MapPin class="w-4 h-4 text-slate-400" />
                {{ company.address }}, {{ company.city }}, {{ company.country }}
              </p>
            </div>
          </div>

          <!-- Website Action Button -->
          <a
            v-if="company.website"
            :href="company.website"
            target="_blank"
            rel="noopener noreferrer"
            class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-bold transition shadow-md shadow-blue-500/20 active:scale-95 shrink-0 cursor-pointer"
          >
            <Globe class="w-4 h-4" />
            <span>Visit Company Website</span>
          </a>
        </div>
      </div>

      <!-- Specs Overview Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div
          class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl space-y-1"
        >
          <div
            class="flex items-center gap-2 text-xs font-semibold text-slate-400"
          >
            <Users class="w-4 h-4 text-blue-500" /> Company Size
          </div>
          <p class="text-sm font-extrabold text-slate-900 dark:text-white">
            {{ company.company_size }}
          </p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">
            Employees
          </p>
        </div>

        <div
          class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl space-y-1"
        >
          <div
            class="flex items-center gap-2 text-xs font-semibold text-slate-400"
          >
            <Calendar class="w-4 h-4 text-amber-500" /> Founded Year
          </div>
          <p class="text-sm font-extrabold text-slate-900 dark:text-white">
            {{ company.founded_year || "N/A" }}
          </p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">
            Established
          </p>
        </div>

        <div
          class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl space-y-1"
        >
          <div
            class="flex items-center gap-2 text-xs font-semibold text-slate-400"
          >
            <Briefcase class="w-4 h-4 text-emerald-500" /> Open Positions
          </div>
          <p class="text-sm font-extrabold text-slate-900 dark:text-white">
            {{ company.open_jobs_count }} Roles
          </p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">
            Hiring now
          </p>
        </div>

        <div
          class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl space-y-1"
        >
          <div
            class="flex items-center gap-2 text-xs font-semibold text-slate-400"
          >
            <ShieldCheck class="w-4 h-4 text-purple-500" /> Status
          </div>
          <p
            class="text-sm font-extrabold text-slate-900 dark:text-white capitalize"
          >
            {{ company.status }}
          </p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">
            Verified Platform Employer
          </p>
        </div>
      </div>

      <!-- Main Grid: Description & Jobs -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
          <!-- About Company Card -->
          <div
            class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 md:p-8 rounded-3xl space-y-4"
          >
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
              About {{ company.name }}
            </h2>
            <p
              class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line"
            >
              {{ company.description }}
            </p>
          </div>

          <!-- Open Positions Section -->
          <div class="space-y-4">
            <h2
              class="text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2"
            >
              <Briefcase class="w-5 h-5 text-blue-600" />
              <span>Active Job Opportunities ({{ companyJobs.length }})</span>
            </h2>

            <JobCard :jobPosts="companyJobs" />
          </div>
        </div>

        <!-- Sidebar: Contact Info & Location Map Card -->
        <div class="space-y-6">
          <div
            class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 rounded-3xl space-y-5"
          >
            <h3
              class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3"
            >
              Company Contact Info
            </h3>

            <div class="space-y-3 text-xs text-slate-600 dark:text-slate-300">
              <div v-if="company.email" class="flex items-center gap-3">
                <Mail class="w-4 h-4 text-blue-600 shrink-0" />
                <span>{{ company.email }}</span>
              </div>

              <div v-if="company.phone" class="flex items-center gap-3">
                <Phone class="w-4 h-4 text-blue-600 shrink-0" />
                <span>{{ company.phone }}</span>
              </div>

              <div class="flex items-center gap-3">
                <MapPin class="w-4 h-4 text-blue-600 shrink-0" />
                <span
                  >{{ company.address }}, {{ company.city }},
                  {{ company.country }}</span
                >
              </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
              <a
                :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(company.address + ', ' + company.city + ', ' + company.country)}`"
                target="_blank"
                rel="noopener noreferrer"
                class="w-full flex items-center justify-center gap-2 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-xl text-xs font-bold transition"
              >
                <Compass class="w-4 h-4 text-blue-600" />
                <span>Get Office Directions</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </main>

    <Footer />
  </div>
</template>
