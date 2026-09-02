<script setup>
import { ref } from 'vue'
import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
import { 
  Mail, Phone, MapPin, Send, Clock, MessageCircle 
} from 'lucide-vue-next'

const name = ref('')
const email = ref('')
const subject = ref('')
const message = ref('')
const isSubmitting = ref(false)
const showSuccess = ref(false)

const handleSubmit = () => {
  isSubmitting.value = true
  setTimeout(() => {
    isSubmitting.value = false
    showSuccess.value = true
    name.value = ''
    email.value = ''
    subject.value = ''
    message.value = ''
    setTimeout(() => { showSuccess.value = false }, 4000)
  }, 1000)
}

const contactCards = [
  { title: 'Email Us', value: 'support@jobsearch.com.kh', icon: Mail, desc: 'We reply within 24 hours.' },
  { title: 'Call Us', value: '+855 23 456 789', icon: Phone, desc: 'Mon — Fri, 8AM — 6PM (ICT).' },
  { title: 'Visit Us', value: 'Monivong Blvd, Khan Daun Penh, Phnom Penh', icon: MapPin, desc: 'Walk-in appointments welcome.' }
]
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors duration-200">
    <Navbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
      
      <!-- Header -->
      <div class="text-center max-w-3xl mx-auto space-y-3">
        <span class="px-3.5 py-1.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-xs font-bold border border-blue-100 dark:border-blue-900/50">
          Get In Touch
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
          Contact <span class="text-blue-600 dark:text-blue-400">Our Team</span>
        </h1>
        <p class="text-sm text-slate-600 dark:text-slate-400">Have questions or need help? Our support team is ready to assist you.</p>
      </div>

      <!-- Contact Info Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div 
          v-for="card in contactCards"
          :key="card.title"
          class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 rounded-3xl text-center space-y-3 shadow-xs"
        >
          <div class="p-3 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 rounded-2xl w-12 h-12 mx-auto flex items-center justify-center">
            <component :is="card.icon" class="w-6 h-6" />
          </div>
          <h3 class="font-bold text-slate-900 dark:text-white text-sm">{{ card.title }}</h3>
          <p class="text-xs text-blue-600 dark:text-blue-400 font-semibold">{{ card.value }}</p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ card.desc }}</p>
        </div>
      </div>

      <!-- Contact Form -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-8 sm:p-12 shadow-sm max-w-3xl mx-auto space-y-8">
        <div class="flex items-center gap-3 text-blue-600 dark:text-blue-400">
          <MessageCircle class="w-5 h-5" />
          <h2 class="text-lg font-bold text-slate-900 dark:text-white">Send Us a Message</h2>
        </div>

        <!-- Success Alert -->
        <div v-if="showSuccess" class="p-4 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-900/50 rounded-2xl text-xs font-bold text-emerald-700 dark:text-emerald-300">
          ✅ Your message has been sent successfully! We'll get back to you within 24 hours.
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Full Name</label>
              <input 
                v-model="name"
                type="text" 
                placeholder="Your full name" 
                required
                class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition"
              />
            </div>
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Email Address</label>
              <input 
                v-model="email"
                type="email" 
                placeholder="your.email@example.com" 
                required
                class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition"
              />
            </div>
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Subject</label>
            <input 
              v-model="subject"
              type="text" 
              placeholder="How can we help you?" 
              required
              class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition"
            />
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Message</label>
            <textarea 
              v-model="message"
              rows="5" 
              placeholder="Tell us more about your inquiry..." 
              required
              class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition resize-none"
            ></textarea>
          </div>

          <button 
            type="submit"
            :disabled="isSubmitting"
            class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl text-xs sm:text-sm transition shadow-md shadow-blue-500/20 active:scale-95 disabled:opacity-50 cursor-pointer"
          >
            <Send class="w-4 h-4" />
            <span>{{ isSubmitting ? 'Sending...' : 'Send Message' }}</span>
          </button>
        </form>
      </div>

    </main>

    <Footer />
  </div>
</template>
