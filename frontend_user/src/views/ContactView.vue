<script setup>
import { ref } from 'vue'
import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
import { 
  Mail, Phone, MapPin, Send, Clock, MessageSquare, 
  Building2, Globe, Compass, CheckCircle2, Sparkles,
  HelpCircle, ShieldCheck, User, Tag, Navigation, ExternalLink
} from 'lucide-vue-next'

const name = ref('')
const email = ref('')
const phone = ref('')
const subjectType = ref('general')
const subject = ref('')
const message = ref('')
const isSubmitting = ref(false)
const showSuccess = ref(false)

const etecMapUrl = 'https://www.google.com/maps/place/ETEC+Center/@11.5607285,104.8896902,16.36z/data=!4m6!3m5!1s0x310951adb4d4041d:0x8a90e729f62ad800!8m2!3d11.562212!4d104.8905721!16s%2Fg%2F11s8j5cd77?entry=ttu&g_ep=EgoyMDI2MDkwMi4wIKXMDSoASAFQAw%3D%3D'
const etecEmbedUrl = 'https://maps.google.com/maps?q=11.562212,104.8905721&z=16&output=embed'

const inquiryTypes = [
  { id: 'general', label: 'General Inquiry' },
  { id: 'candidate', label: 'Candidate Support' },
  { id: 'employer', label: 'Employer / Hiring' },
  { id: 'tech', label: 'Technical Issue' }
]

const handleSubmit = () => {
  isSubmitting.value = true
  setTimeout(() => {
    isSubmitting.value = false
    showSuccess.value = true
    name.value = ''
    email.value = ''
    phone.value = ''
    subject.value = ''
    message.value = ''
    setTimeout(() => { showSuccess.value = false }, 4000)
  }, 1000)
}

const contactCards = [
  { 
    title: 'Customer Email Support', 
    value: 'support@jobsearch.com.kh', 
    icon: Mail, 
    desc: 'Our support team responds within 24 hours.',
    action: 'mailto:support@jobsearch.com.kh',
    actionText: 'Send Email'
  },
  { 
    title: 'Hotline Phone', 
    value: '+855 (0)23 456 789', 
    icon: Phone, 
    desc: 'Monday to Saturday, 8:00 AM — 6:00 PM ICT.',
    action: 'tel:+85523456789',
    actionText: 'Call Hotline'
  },
  { 
    title: 'ETEC Center Location', 
    value: 'ETEC Center, Phnom Penh', 
    icon: MapPin, 
    desc: 'Main Training & Technology Center Campus.',
    action: etecMapUrl,
    actionText: 'Get Directions'
  },
  { 
    title: 'Office Support Hours', 
    value: 'Mon – Sat: 8:00 AM – 6:00 PM', 
    icon: Clock, 
    desc: 'Closed on Sundays and Public Holidays.',
    action: null,
    actionText: null
  }
]
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors duration-200">
    <Navbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
      
      <!-- ─── 1. Page Header ─────────────────────────────────────────────── -->
      <div class="text-center max-w-3xl mx-auto space-y-4">
        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-xs font-bold border border-blue-100 dark:border-blue-900/50 shadow-2xs">
          <Sparkles class="w-3.5 h-3.5" />
          <span>24/7 Candidate & Employer Support</span>
        </span>

        <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
          Get in Touch with <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400">ETEC Center Team</span>
        </h1>

        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 max-w-xl mx-auto">
          Have questions about job applications, hiring solutions, or platform features? Visit ETEC Center or message our team.
        </p>
      </div>

      <!-- ─── 2. Top Info Cards Grid ─────────────────────────────────────── -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div 
          v-for="card in contactCards"
          :key="card.title"
          class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 rounded-3xl space-y-4 shadow-xs hover:shadow-md transition duration-200 flex flex-col justify-between"
        >
          <div class="space-y-3">
            <div class="p-3 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 rounded-2xl w-12 h-12 flex items-center justify-center">
              <component :is="card.icon" class="w-6 h-6" />
            </div>
            <h3 class="font-extrabold text-slate-900 dark:text-white text-base">{{ card.title }}</h3>
            <p class="text-xs text-blue-600 dark:text-blue-400 font-bold break-words">{{ card.value }}</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ card.desc }}</p>
          </div>

          <div v-if="card.action" class="pt-2">
            <a 
              :href="card.action" 
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition"
            >
              <span>{{ card.actionText }}</span>
              <Compass class="w-3.5 h-3.5" />
            </a>
          </div>
        </div>
      </div>

      <!-- ─── 3. Main Split Grid: Contact Form & ETEC Location Map ───────── -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left Side: Contact Form (7 cols) -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-10 shadow-xs space-y-6">
          
          <div class="space-y-1.5 border-b border-slate-100 dark:border-slate-800 pb-4">
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
              <MessageSquare class="w-5 h-5 text-blue-600" />
              <span>Send Us a Direct Message</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Fill out the form below and our support team at ETEC Center will get back to you.</p>
          </div>

          <!-- Success Alert -->
          <div v-if="showSuccess" class="p-4 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-900/50 rounded-2xl text-xs font-bold text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
            <CheckCircle2 class="w-4.5 h-4.5 text-emerald-500 shrink-0" />
            <span>Thank you! Your message has been sent successfully to ETEC Center. We'll reply within 24 hours.</span>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-4">
            
            <!-- Inquiry Category Selector -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Inquiry Type</label>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <button
                  v-for="type in inquiryTypes"
                  :key="type.id"
                  type="button"
                  @click="subjectType = type.id"
                  class="py-2 px-3 rounded-xl text-xs font-bold border transition text-center cursor-pointer"
                  :class="subjectType === type.id 
                    ? 'bg-blue-600 text-white border-blue-600 shadow-xs' 
                    : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100'"
                >
                  {{ type.label }}
                </button>
              </div>
            </div>

            <!-- Full Name & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Full Name</label>
                <div class="relative">
                  <User class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                  <input 
                    v-model="name"
                    type="text" 
                    placeholder="Sokha Kim" 
                    required
                    class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition"
                  />
                </div>
              </div>

              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Email Address</label>
                <div class="relative">
                  <Mail class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                  <input 
                    v-model="email"
                    type="email" 
                    placeholder="sokha@example.com" 
                    required
                    class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition"
                  />
                </div>
              </div>
            </div>

            <!-- Phone & Subject -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Phone Number (Optional)</label>
                <div class="relative">
                  <Phone class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                  <input 
                    v-model="phone"
                    type="tel" 
                    placeholder="+855 12 345 678" 
                    class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition"
                  />
                </div>
              </div>

              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Subject</label>
                <div class="relative">
                  <Tag class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                  <input 
                    v-model="subject"
                    type="text" 
                    placeholder="How can we help you?" 
                    required
                    class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition"
                  />
                </div>
              </div>
            </div>

            <!-- Message Textarea -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Message Content</label>
              <textarea 
                v-model="message"
                rows="5" 
                placeholder="Please describe your questions, feedback, or inquiry in detail..." 
                required
                class="w-full p-4 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition resize-none"
              ></textarea>
            </div>

            <!-- Submit Button -->
            <button 
              type="submit"
              :disabled="isSubmitting"
              class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl text-xs sm:text-sm transition duration-200 shadow-md shadow-blue-500/25 active:scale-95 disabled:opacity-50 cursor-pointer"
            >
              <Send class="w-4 h-4" />
              <span>{{ isSubmitting ? 'Sending Message...' : 'Send Message' }}</span>
            </button>

          </form>
        </div>

        <!-- Right Side: ETEC Center Location & Embedded Interactive Map (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
          
          <!-- ETEC Location Overview Card -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs space-y-6">
            
            <div class="space-y-2 border-b border-slate-100 dark:border-slate-800 pb-4">
              <span class="px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-[11px] font-bold uppercase tracking-wider">
                Headquarters & Training Campus
              </span>
              <h2 class="text-xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <Building2 class="w-5 h-5 text-blue-600" />
                <span>ETEC Center Location</span>
              </h2>
            </div>

            <!-- Address Breakdown -->
            <div class="space-y-4 text-xs text-slate-600 dark:text-slate-300">
              
              <div class="flex items-start gap-3">
                <div class="p-2.5 bg-blue-50 dark:bg-blue-950/60 text-blue-600 rounded-xl shrink-0 mt-0.5">
                  <MapPin class="w-5 h-5" />
                </div>
                <div class="space-y-1">
                  <h4 class="font-extrabold text-slate-900 dark:text-white text-sm">ETEC Center</h4>
                  <p class="text-xs text-slate-600 dark:text-slate-300 font-medium leading-relaxed">
                    Phnom Penh, Cambodia.<br />
                    Coordinates: 11.562212, 104.8905721
                  </p>
                </div>
              </div>

              <div class="flex items-start gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 rounded-xl shrink-0 mt-0.5">
                  <Clock class="w-5 h-5" />
                </div>
                <div class="space-y-1">
                  <h4 class="font-extrabold text-slate-900 dark:text-white text-xs">Working Hours</h4>
                  <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                    Monday – Saturday: 8:00 AM – 6:00 PM (ICT)<br />
                    Sunday & Holidays: Closed
                  </p>
                </div>
              </div>

            </div>

            <!-- Live Embedded Interactive Google Map Frame -->
            <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 space-y-3">
              <iframe 
                :src="etecEmbedUrl" 
                width="100%" 
                height="220" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade"
                class="w-full rounded-t-2xl"
              ></iframe>

              <div class="p-4 pt-1 text-center">
                <a 
                  :href="etecMapUrl" 
                  target="_blank"
                  rel="noopener noreferrer"
                  class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md transition active:scale-95 cursor-pointer"
                >
                  <Navigation class="w-4 h-4" />
                  <span>Open ETEC Center on Google Maps</span>
                  <ExternalLink class="w-3.5 h-3.5" />
                </a>
              </div>
            </div>

          </div>

          <!-- Quick Protection Notice -->
          <div class="bg-blue-50/60 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/50 p-5 rounded-3xl space-y-2">
            <h4 class="text-xs font-bold text-blue-900 dark:text-blue-300 flex items-center gap-2">
              <ShieldCheck class="w-4 h-4 text-blue-600" />
              Official ETEC Center Verification
            </h4>
            <p class="text-[11px] text-blue-700 dark:text-blue-400 leading-relaxed">
              Official campus and recruitment support center for Job Search. For walk-in inquiries, visit our support office at ETEC Center.
            </p>
          </div>

        </div>

      </div>

    </main>

    <Footer />
  </div>
</template>
