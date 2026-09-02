<script setup>
import { ref } from 'vue'
import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
import { 
  User, Mail, Phone, MapPin, Briefcase, Calendar, 
  Camera, Edit3, ExternalLink, Award, FileText
} from 'lucide-vue-next'

const user = ref({
  firstName: 'John',
  lastName: 'Doe',
  email: 'john.doe@example.com',
  phone: '+855 12 345 678',
  location: 'Phnom Penh, Cambodia',
  title: 'Senior Full Stack Developer',
  experience: '4+ Years',
  about: 'Passionate full-stack developer with expertise in Laravel, Vue.js, and cloud architecture. Building scalable web platforms for Southeast Asian tech companies.',
  avatar: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=200&h=200&fit=crop&crop=face'
})

const stats = [
  { label: 'Applications Sent', value: '24' },
  { label: 'Profile Views', value: '148' },
  { label: 'Interviews', value: '6' },
  { label: 'Saved Jobs', value: '31' }
]
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors duration-200">
    <Navbar />

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

      <!-- Profile Hero Card -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
        <!-- Cover Banner -->
        <div class="h-36 sm:h-44 bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-800 relative">
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_20%,rgba(255,255,255,0.15),transparent_50%)]"></div>
        </div>
        
        <!-- Avatar & Info -->
        <div class="px-6 sm:px-8 pb-6 -mt-14 sm:-mt-16 flex flex-col sm:flex-row items-start sm:items-end gap-4 sm:gap-6">
          <div class="relative">
            <img :src="user.avatar" :alt="user.firstName" class="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl border-4 border-white dark:border-slate-900 object-cover shadow-lg" />
            <button class="absolute bottom-1 right-1 p-2 bg-blue-600 text-white rounded-xl shadow-md hover:bg-blue-700 transition cursor-pointer">
              <Camera class="w-4 h-4" />
            </button>
          </div>

          <div class="flex-1 space-y-1 pt-2">
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ user.firstName }} {{ user.lastName }}</h1>
            <p class="text-sm font-semibold text-blue-600 dark:text-blue-400 flex items-center gap-1.5">
              <Briefcase class="w-4 h-4" /> {{ user.title }}
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
              <MapPin class="w-3.5 h-3.5" /> {{ user.location }}
            </p>
          </div>

          <button class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-2xl shadow-md shadow-blue-500/20 transition active:scale-95 cursor-pointer shrink-0">
            <Edit3 class="w-4 h-4" />
            <span>Edit Profile</span>
          </button>
        </div>
      </div>

      <!-- Activity Stats -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div 
          v-for="stat in stats" 
          :key="stat.label"
          class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl text-center space-y-1"
        >
          <div class="text-xl font-extrabold text-slate-900 dark:text-white">{{ stat.value }}</div>
          <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ stat.label }}</div>
        </div>
      </div>

      <!-- About Section -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 space-y-5">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">About Me</h2>
        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{{ user.about }}</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-100 dark:border-slate-800">
          <div class="flex items-center gap-3 text-xs text-slate-600 dark:text-slate-300">
            <Mail class="w-4 h-4 text-blue-600 shrink-0" />
            <span>{{ user.email }}</span>
          </div>
          <div class="flex items-center gap-3 text-xs text-slate-600 dark:text-slate-300">
            <Phone class="w-4 h-4 text-blue-600 shrink-0" />
            <span>{{ user.phone }}</span>
          </div>
          <div class="flex items-center gap-3 text-xs text-slate-600 dark:text-slate-300">
            <Award class="w-4 h-4 text-blue-600 shrink-0" />
            <span>{{ user.experience }} Experience</span>
          </div>
          <div class="flex items-center gap-3 text-xs text-slate-600 dark:text-slate-300">
            <MapPin class="w-4 h-4 text-blue-600 shrink-0" />
            <span>{{ user.location }}</span>
          </div>
        </div>
      </div>

    </main>

    <Footer />
  </div>
</template>
