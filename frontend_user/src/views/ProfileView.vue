<script setup>
import { ref, computed } from 'vue'
import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
import { 
  User, Mail, Phone, MapPin, Briefcase, Calendar, 
  Camera, Edit3, ExternalLink, Award, FileText, Download, 
  Eye, EyeOff, Globe, Github, Linkedin, CheckCircle2, 
  DollarSign, Sparkles, Clock, ShieldCheck, X, Upload, Check
} from 'lucide-vue-next'

// Mock Profile State matching database schema
const profile = ref({
  id: 1,
  user_id: 101,
  user_name: 'Sokha Kim',
  email: 'sokha.kim@example.com',
  avatar: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=200&h=200&fit=crop&crop=face',
  headline: 'Senior Full Stack Laravel & Vue.js Engineer',
  bio: 'Passionate full-stack software engineer with 5+ years of experience designing and architecting web applications, APIs, and micro-services. Specialized in Vue 3, Laravel 11, Tailwind CSS, PostgreSQL, and AWS Cloud.',
  phone: '+855 12 345 678',
  date_of_birth: '1996-08-15',
  gender: 'male',
  nationality: 'Cambodian',
  country: 'Cambodia',
  city: 'Phnom Penh',
  address: 'Street 271, Khan Sen Sok',
  linkedin_url: 'https://linkedin.com/in/sokha-kim',
  github_url: 'https://github.com/sokhakim',
  portfolio_url: 'https://sokhakim.dev',
  cv_path: '/uploads/cv/sokha_kim_resume.pdf',
  cv_original_name: 'Sokha_Kim_Senior_Developer_CV.pdf',
  cv_uploaded_at: '2026-08-25T10:30:00Z',
  availability: 'immediately',
  expected_salary_min: 1500.00,
  expected_salary_max: 2500.00,
  salary_currency: 'USD',
  is_open_to_work: true,
  is_profile_visible: true,
  profile_views: 148
})

// UI State
const isEditing = ref(false)
const showUploadModal = ref(false)
const isSaving = ref(false)
const saveSuccess = ref(false)

// Edit Form Draft State
const editForm = ref({ ...profile.value })

const openEditModal = () => {
  editForm.value = JSON.parse(JSON.stringify(profile.value))
  isEditing.value = true
}

const closeEditModal = () => {
  isEditing.value = false
}

const saveProfile = () => {
  isSaving.value = true
  setTimeout(() => {
    profile.value = JSON.parse(JSON.stringify(editForm.value))
    isSaving.value = false
    isEditing.value = false
    saveSuccess.value = true
    setTimeout(() => { saveSuccess.value = false }, 3500)
  }, 1000)
}

// Helpers
const formatAvailability = (val) => {
  switch (val) {
    case 'immediately': return 'Immediately Available'
    case 'within_1_month': return 'Available in 1 Month'
    case 'within_3_months': return 'Available in 3 Months'
    case 'not_available': return 'Not Currently Available'
    default: return val
  }
}

const availabilityBadgeStyle = (val) => {
  switch (val) {
    case 'immediately': return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/50'
    case 'within_1_month': return 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-900/50'
    case 'within_3_months': return 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-900/50'
    default: return 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700'
  }
}

const formatDate = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const handleFileUpload = (e) => {
  const file = e.target.files[0]
  if (file) {
    profile.value.cv_original_name = file.name
    profile.value.cv_uploaded_at = new Date().toISOString()
    showUploadModal.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors duration-200">
    <Navbar />

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

      <!-- Success Notification Alert -->
      <div v-if="saveSuccess" class="p-4 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-900/50 rounded-2xl text-xs font-bold text-emerald-700 dark:text-emerald-300 flex items-center justify-between shadow-xs">
        <span class="flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 text-emerald-500" />
          <span>Profile changes saved successfully!</span>
        </span>
        <button @click="saveSuccess = false" class="text-emerald-600 hover:text-emerald-800"><X class="w-4 h-4" /></button>
      </div>

      <!-- ─── 1. Header Profile Hero Banner Card ──────────────────────── -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden shadow-xs relative">
        
        <!-- Cover Photo Gradient Banner -->
        <div class="h-40 sm:h-48 w-full bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-900 relative">
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_20%,rgba(255,255,255,0.15),transparent_50%)]"></div>

          <!-- Open To Work Pill Top Right -->
          <div class="absolute top-4 right-4 flex items-center gap-2">
            <span 
              v-if="profile.is_open_to_work" 
              class="bg-emerald-500 text-white text-[11px] font-extrabold uppercase px-3.5 py-1 rounded-full border border-white/20 shadow-md flex items-center gap-1.5"
            >
              <Sparkles class="w-3.5 h-3.5" /> Open to Work
            </span>
            <span 
              class="text-[11px] font-bold px-3 py-1 rounded-full backdrop-blur-md text-white border border-white/20"
              :class="profile.is_profile_visible ? 'bg-blue-600/80' : 'bg-slate-800/80'"
            >
              {{ profile.is_profile_visible ? 'Public to HR' : 'Private Profile' }}
            </span>
          </div>
        </div>

        <!-- Avatar & Basic Details Row -->
        <div class="px-6 md:px-8 pb-8 -mt-16 relative z-10 flex flex-col md:flex-row items-start md:items-end justify-between gap-6">
          
          <div class="flex items-start md:items-end gap-5">
            <!-- Avatar Image -->
            <div class="relative group">
              <img 
                :src="profile.avatar" 
                :alt="profile.user_name" 
                class="w-28 h-28 md:w-32 md:h-32 rounded-3xl object-cover border-4 border-white dark:border-slate-900 shadow-xl bg-white shrink-0" 
              />
              <button 
                @click="openEditModal" 
                class="absolute bottom-1 right-1 p-2 bg-blue-600 text-white rounded-xl shadow-md hover:bg-blue-700 transition cursor-pointer" 
                title="Change Avatar"
              >
                <Camera class="w-4 h-4" />
              </button>
            </div>

            <div class="space-y-1.5 pt-2">
              <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                  {{ profile.user_name }}
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold border" :class="availabilityBadgeStyle(profile.availability)">
                  {{ formatAvailability(profile.availability) }}
                </span>
              </div>

              <p class="text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 flex items-center gap-1.5">
                <Briefcase class="w-4 h-4" /> {{ profile.headline }}
              </p>

              <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                <MapPin class="w-4 h-4 text-slate-400" />
                {{ profile.address ? `${profile.address}, ` : '' }}{{ profile.city }}, {{ profile.country }}
              </p>
            </div>
          </div>

          <!-- Edit Profile Action Button -->
          <button 
            @click="openEditModal"
            class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-2xl shadow-md shadow-blue-500/20 transition active:scale-95 cursor-pointer shrink-0"
          >
            <Edit3 class="w-4 h-4" />
            <span>Edit Candidate Profile</span>
          </button>

        </div>

      </div>

      <!-- ─── 2. Quick Specs & Stats Grid ─────────────────────────────── -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl space-y-1">
          <div class="flex items-center gap-2 text-xs font-semibold text-slate-400"><DollarSign class="w-4 h-4 text-emerald-500" /> Expected Salary</div>
          <p class="text-sm font-extrabold text-slate-900 dark:text-white">
            ${{ Number(profile.expected_salary_min).toLocaleString() }} - ${{ Number(profile.expected_salary_max).toLocaleString() }}
          </p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ profile.salary_currency || 'USD' }} / month</p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl space-y-1">
          <div class="flex items-center gap-2 text-xs font-semibold text-slate-400"><Clock class="w-4 h-4 text-blue-500" /> Availability</div>
          <p class="text-sm font-extrabold text-slate-900 dark:text-white truncate capitalize">{{ formatAvailability(profile.availability) }}</p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">Notice period</p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl space-y-1">
          <div class="flex items-center gap-2 text-xs font-semibold text-slate-400"><Eye class="w-4 h-4 text-indigo-500" /> Recruiter Views</div>
          <p class="text-sm font-extrabold text-slate-900 dark:text-white">{{ profile.profile_views }} Views</p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">HR searches</p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl space-y-1">
          <div class="flex items-center gap-2 text-xs font-semibold text-slate-400"><Globe class="w-4 h-4 text-purple-500" /> Nationality</div>
          <p class="text-sm font-extrabold text-slate-900 dark:text-white">{{ profile.nationality || 'Cambodian' }}</p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ profile.city }}</p>
        </div>

      </div>

      <!-- ─── 3. Main Grid Layout ─────────────────────────────────────── -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-8">
          
          <!-- About Me & Bio Card -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 md:p-8 rounded-3xl space-y-4">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">About Candidate</h2>
            <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
              {{ profile.bio || 'No bio specified yet.' }}
            </p>
          </div>

          <!-- Resume / CV Document Management Card -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 md:p-8 rounded-3xl space-y-5">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <FileText class="w-5 h-5 text-blue-600" />
                <span>Resume / CV Document</span>
              </h2>

              <label class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 text-blue-600 dark:text-blue-400 text-xs font-bold rounded-xl border border-blue-100 dark:border-blue-900/50 transition cursor-pointer">
                <Upload class="w-3.5 h-3.5" />
                <span>Upload New CV</span>
                <input type="file" accept=".pdf,.doc,.docx" class="hidden" @change="handleFileUpload" />
              </label>
            </div>

            <div v-if="profile.cv_original_name" class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 p-4 rounded-2xl flex items-center justify-between gap-4">
              <div class="flex items-center gap-3 min-w-0">
                <div class="p-3 bg-red-50 dark:bg-red-950/60 text-red-600 rounded-xl shrink-0">
                  <FileText class="w-6 h-6" />
                </div>
                <div class="min-w-0">
                  <h4 class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm truncate">{{ profile.cv_original_name }}</h4>
                  <p class="text-[11px] text-slate-500 dark:text-slate-400">Uploaded on {{ formatDate(profile.cv_uploaded_at) }}</p>
                </div>
              </div>

              <div class="flex items-center gap-2 shrink-0">
                <a 
                  :href="profile.cv_path" 
                  download 
                  class="p-2.5 bg-white dark:bg-slate-900 hover:bg-slate-100 text-slate-700 dark:text-slate-200 rounded-xl border border-slate-200 dark:border-slate-700 transition cursor-pointer"
                  title="Download Resume"
                >
                  <Download class="w-4 h-4" />
                </a>
              </div>
            </div>

            <div v-else class="text-center py-6 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl text-xs text-slate-500">
              No CV uploaded yet. Upload your PDF resume to apply for jobs faster.
            </div>
          </div>

        </div>

        <!-- ─── Sidebar: Personal Details & Professional Links ──────────── -->
        <div class="space-y-6">
          
          <!-- Personal Information Card -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 rounded-3xl space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3">
              Personal Information
            </h3>

            <div class="space-y-3 text-xs text-slate-600 dark:text-slate-300">
              <div class="flex items-center gap-3">
                <Mail class="w-4 h-4 text-blue-600 shrink-0" />
                <span class="truncate">{{ profile.email }}</span>
              </div>

              <div class="flex items-center gap-3">
                <Phone class="w-4 h-4 text-blue-600 shrink-0" />
                <span>{{ profile.phone || 'Not specified' }}</span>
              </div>

              <div class="flex items-center gap-3">
                <Calendar class="w-4 h-4 text-blue-600 shrink-0" />
                <span>Born {{ formatDate(profile.date_of_birth) }}</span>
              </div>

              <div class="flex items-center gap-3 capitalize">
                <User class="w-4 h-4 text-blue-600 shrink-0" />
                <span>Gender: {{ profile.gender || 'Not specified' }}</span>
              </div>

              <div class="flex items-center gap-3">
                <MapPin class="w-4 h-4 text-blue-600 shrink-0" />
                <span>{{ profile.address }}, {{ profile.city }}, {{ profile.country }}</span>
              </div>
            </div>
          </div>

          <!-- Professional Links Card -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 rounded-3xl space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3">
              Social & Portfolio Links
            </h3>

            <div class="space-y-2.5">
              <a 
                v-if="profile.linkedin_url" 
                :href="profile.linkedin_url" 
                target="_blank" 
                rel="noopener noreferrer"
                class="flex items-center justify-between p-3 rounded-2xl bg-blue-50/60 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 hover:bg-blue-100 transition text-xs font-semibold"
              >
                <span class="flex items-center gap-2">
                  <Linkedin class="w-4 h-4" />
                  <span>LinkedIn Profile</span>
                </span>
                <ExternalLink class="w-3.5 h-3.5" />
              </a>

              <a 
                v-if="profile.github_url" 
                :href="profile.github_url" 
                target="_blank" 
                rel="noopener noreferrer"
                class="flex items-center justify-between p-3 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition text-xs font-semibold"
              >
                <span class="flex items-center gap-2">
                  <Github class="w-4 h-4" />
                  <span>GitHub Repositories</span>
                </span>
                <ExternalLink class="w-3.5 h-3.5" />
              </a>

              <a 
                v-if="profile.portfolio_url" 
                :href="profile.portfolio_url" 
                target="_blank" 
                rel="noopener noreferrer"
                class="flex items-center justify-between p-3 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 transition text-xs font-semibold"
              >
                <span class="flex items-center gap-2">
                  <Globe class="w-4 h-4" />
                  <span>Personal Portfolio</span>
                </span>
                <ExternalLink class="w-3.5 h-3.5" />
              </a>
            </div>
          </div>

        </div>

      </div>

    </main>

    <!-- ─── Edit Profile Modal Drawer ─────────────────────────────────── -->
    <div v-if="isEditing" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 space-y-6 shadow-2xl">
        
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
          <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Edit Candidate Profile</h3>
          <button @click="closeEditModal" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><X class="w-5 h-5" /></button>
        </div>

        <form @submit.prevent="saveProfile" class="space-y-4 text-xs">
          
          <!-- Headline -->
          <div class="space-y-1">
            <label class="font-bold text-slate-700 dark:text-slate-300">Professional Headline</label>
            <input 
              v-model="editForm.headline" 
              type="text" 
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" 
            />
          </div>

          <!-- Bio -->
          <div class="space-y-1">
            <label class="font-bold text-slate-700 dark:text-slate-300">Bio (About Me)</label>
            <textarea 
              v-model="editForm.bio" 
              rows="4" 
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 resize-none" 
            ></textarea>
          </div>

          <!-- Phone & DOB -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1">
              <label class="font-bold text-slate-700 dark:text-slate-300">Phone Number</label>
              <input v-model="editForm.phone" type="text" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
            <div class="space-y-1">
              <label class="font-bold text-slate-700 dark:text-slate-300">Date of Birth</label>
              <input v-model="editForm.date_of_birth" type="date" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
          </div>

          <!-- Gender & Nationality -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1">
              <label class="font-bold text-slate-700 dark:text-slate-300">Gender</label>
              <select v-model="editForm.gender" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100">
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
                <option value="prefer_not_to_say">Prefer not to say</option>
              </select>
            </div>
            <div class="space-y-1">
              <label class="font-bold text-slate-700 dark:text-slate-300">Nationality</label>
              <input v-model="editForm.nationality" type="text" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
          </div>

          <!-- Location (Address, City, Country) -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="space-y-1">
              <label class="font-bold text-slate-700 dark:text-slate-300">Address</label>
              <input v-model="editForm.address" type="text" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
            <div class="space-y-1">
              <label class="font-bold text-slate-700 dark:text-slate-300">City</label>
              <input v-model="editForm.city" type="text" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
            <div class="space-y-1">
              <label class="font-bold text-slate-700 dark:text-slate-300">Country</label>
              <input v-model="editForm.country" type="text" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
          </div>

          <!-- Availability & Salary -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="space-y-1">
              <label class="font-bold text-slate-700 dark:text-slate-300">Availability</label>
              <select v-model="editForm.availability" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100">
                <option value="immediately">Immediately</option>
                <option value="within_1_month">Within 1 Month</option>
                <option value="within_3_months">Within 3 Months</option>
                <option value="not_available">Not Available</option>
              </select>
            </div>
            <div class="space-y-1">
              <label class="font-bold text-slate-700 dark:text-slate-300">Min Salary (USD)</label>
              <input v-model="editForm.expected_salary_min" type="number" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
            <div class="space-y-1">
              <label class="font-bold text-slate-700 dark:text-slate-300">Max Salary (USD)</label>
              <input v-model="editForm.expected_salary_max" type="number" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
          </div>

          <!-- Social Links -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="space-y-1">
              <label class="font-bold text-slate-700 dark:text-slate-300">LinkedIn URL</label>
              <input v-model="editForm.linkedin_url" type="url" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
            <div class="space-y-1">
              <label class="font-bold text-slate-700 dark:text-slate-300">GitHub URL</label>
              <input v-model="editForm.github_url" type="url" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
            <div class="space-y-1">
              <label class="font-bold text-slate-700 dark:text-slate-300">Portfolio URL</label>
              <input v-model="editForm.portfolio_url" type="url" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
          </div>

          <!-- Toggles: Open to Work & Profile Visibility -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input v-model="editForm.is_open_to_work" type="checkbox" class="w-4 h-4 text-blue-600 rounded" />
              <span class="font-bold text-slate-700 dark:text-slate-300">Open to Work (Show badge to recruiters)</span>
            </label>

            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input v-model="editForm.is_profile_visible" type="checkbox" class="w-4 h-4 text-blue-600 rounded" />
              <span class="font-bold text-slate-700 dark:text-slate-300">Visible to HR & Employer Search</span>
            </label>
          </div>

          <!-- Modal Actions -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <button type="button" @click="closeEditModal" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 rounded-xl text-slate-700 dark:text-slate-300 font-bold">
              Cancel
            </button>
            <button type="submit" :disabled="isSaving" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md shadow-blue-500/20">
              {{ isSaving ? 'Saving...' : 'Save Profile Changes' }}
            </button>
          </div>

        </form>

      </div>
    </div>

    <Footer />
  </div>
</template>
