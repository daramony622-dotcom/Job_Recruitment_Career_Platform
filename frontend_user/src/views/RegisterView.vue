<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { 
  User, Mail, Phone, Lock, Eye, EyeOff, Send, ArrowLeft, 
  CheckCircle2, ShieldCheck, Sparkles 
} from 'lucide-vue-next'
import ThemeToggle from '../components/common/ThemeToggle.vue'
import LanguageSwitcher from '../components/common/LanguageSwitcher.vue'

const router = useRouter()
const firstName = ref('')
const lastName = ref('')
const email = ref('')
const phoneNumber = ref('')
const password = ref('')
const confirmPassword = ref('')
const acceptTerms = ref(false)

const showPassword = ref(false)
const showConfirmPassword = ref(false)
const errorMessage = ref('')
const isSubmitting = ref(false)

const handleRegister = () => {
  if (password.value !== confirmPassword.value) {
    errorMessage.value = 'Passwords do not match!'
    return
  }
  if (!acceptTerms.value) {
    errorMessage.value = 'You must agree to the Terms of Service.'
    return
  }

  errorMessage.value = ''
  isSubmitting.value = true
  
  setTimeout(() => {
    isSubmitting.value = false
    router.push('/profile')
  }, 1000)
}
</script>

<template>
  <div class="min-h-screen w-full grid grid-cols-1 lg:grid-cols-2 font-sans bg-slate-50 dark:bg-slate-950 transition-colors duration-200">
    
    <!-- LEFT COLUMN: Full-Page Split-Screen Branding Panel -->
    <div class="relative hidden lg:flex flex-col justify-between p-12 bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-900 text-white overflow-hidden">
      
      <!-- Background Graphic Pattern -->
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_30%,rgba(255,255,255,0.15),transparent_50%)] pointer-events-none"></div>
      <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>

      <!-- Top Branding Logo & Back Button -->
      <div class="relative z-10 flex items-center justify-between">
        <router-link to="/" class="flex items-center gap-2 group">
          <img src="/logo.png" alt="Job Search Logo" class="h-14 w-auto object-contain bg-white/10 rounded-xl p-1 backdrop-blur-md border border-white/20 transition-transform group-hover:scale-105" />
        </router-link>

        <router-link to="/" class="inline-flex items-center gap-2 text-xs font-bold text-white/80 hover:text-white bg-white/10 hover:bg-white/20 backdrop-blur-md px-4 py-2 rounded-xl border border-white/15 transition">
          <ArrowLeft class="w-4 h-4" />
          <span>Back to Home</span>
        </router-link>
      </div>

      <!-- Center Feature Content -->
      <div class="relative z-10 space-y-6 max-w-lg my-auto py-12">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-blue-100">
          <Sparkles class="w-3.5 h-3.5 text-amber-400" />
          <span>Join Over 890,000 Candidates</span>
        </div>

        <h1 class="text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight text-white">
          Start your career journey <br />
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-200 to-indigo-100">with top employers.</span>
        </h1>

        <p class="text-blue-100 text-sm leading-relaxed">
          Create a free candidate account in less than 2 minutes. Build your professional online resume and apply for top tech, business, and finance roles.
        </p>

        <!-- Feature Points -->
        <div class="space-y-3 pt-2">
          <div class="flex items-center gap-3 text-xs font-semibold text-white/90">
            <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
            <span>Instant CV Builder & Portfolio Showcase</span>
          </div>
          <div class="flex items-center gap-3 text-xs font-semibold text-white/90">
            <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
            <span>Direct Recruiter Chat & Application Tracking</span>
          </div>
          <div class="flex items-center gap-3 text-xs font-semibold text-white/90">
            <ShieldCheck class="w-4 h-4 text-amber-300 shrink-0" />
            <span>Zero Fees — 100% Free Forever</span>
          </div>
        </div>
      </div>

      <!-- Footer Info -->
      <div class="relative z-10 pt-6 border-t border-white/15 text-xs text-blue-200">
        Trusted by 3,200+ hiring organizations across Southeast Asia.
      </div>

    </div>

    <!-- RIGHT COLUMN: Registration Form Panel -->
    <div class="flex flex-col justify-between p-6 sm:p-12 lg:p-16 relative">
      
      <!-- Top Action Bar -->
      <div class="flex items-center justify-between sm:justify-end gap-3 pb-6">
        <router-link to="/" class="lg:hidden flex items-center gap-2">
          <img src="/logo.png" alt="Logo" class="h-10 w-auto object-contain" />
        </router-link>

        <div class="flex items-center gap-2">
          <LanguageSwitcher />
          <ThemeToggle />
        </div>
      </div>

      <!-- Main Form Box -->
      <div class="max-w-md w-full mx-auto space-y-6 my-auto py-6">
        
        <div class="space-y-2">
          <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Create Free Account</h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Fill in your information to register as a candidate.</p>
        </div>

        <!-- Error Feedback -->
        <div v-if="errorMessage" class="p-3.5 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900/50 rounded-2xl text-xs font-bold text-rose-700 dark:text-rose-300">
          {{ errorMessage }}
        </div>

        <form @submit.prevent="handleRegister" class="space-y-4">
          
          <!-- First Name & Last Name -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div class="space-y-1">
              <label for="firstName" class="block text-xs font-bold text-slate-700 dark:text-slate-300">First Name</label>
              <div class="relative">
                <User class="w-4.5 h-4.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input 
                  id="firstName"
                  v-model="firstName"
                  type="text" 
                  placeholder="John" 
                  required
                  class="w-full pl-10 pr-3 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition duration-200"
                />
              </div>
            </div>

            <div class="space-y-1">
              <label for="lastName" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Last Name</label>
              <div class="relative">
                <User class="w-4.5 h-4.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input 
                  id="lastName"
                  v-model="lastName"
                  type="text" 
                  placeholder="Doe" 
                  required
                  class="w-full pl-10 pr-3 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition duration-200"
                />
              </div>
            </div>
          </div>

          <!-- Email Address -->
          <div class="space-y-1">
            <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Email Address</label>
            <div class="relative">
              <Mail class="w-4.5 h-4.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input 
                id="email"
                v-model="email"
                type="email" 
                placeholder="john.doe@example.com" 
                required
                class="w-full pl-10 pr-3 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition duration-200"
              />
            </div>
          </div>

          <!-- Phone Number -->
          <div class="space-y-1">
            <label for="phoneNumber" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Phone Number</label>
            <div class="relative">
              <Phone class="w-4.5 h-4.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input 
                id="phoneNumber"
                v-model="phoneNumber"
                type="tel" 
                placeholder="+855 12 345 678" 
                required
                class="w-full pl-10 pr-3 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition duration-200"
              />
            </div>
          </div>

          <!-- Password & Confirm Password -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div class="space-y-1">
              <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Password</label>
              <div class="relative">
                <Lock class="w-4.5 h-4.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input 
                  id="password"
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'" 
                  placeholder="••••••••" 
                  required
                  class="w-full pl-10 pr-10 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition duration-200"
                />
                <button 
                  type="button" 
                  @click="showPassword = !showPassword"
                  class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition cursor-pointer"
                >
                  <EyeOff v-if="!showPassword" class="w-4 h-4" />
                  <Eye v-else class="w-4 h-4" />
                </button>
              </div>
            </div>

            <div class="space-y-1">
              <label for="confirmPassword" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Confirm Password</label>
              <div class="relative">
                <Lock class="w-4.5 h-4.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input 
                  id="confirmPassword"
                  v-model="confirmPassword"
                  :type="showConfirmPassword ? 'text' : 'password'" 
                  placeholder="••••••••" 
                  required
                  class="w-full pl-10 pr-10 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition duration-200"
                />
                <button 
                  type="button" 
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition cursor-pointer"
                >
                  <EyeOff v-if="!showConfirmPassword" class="w-4 h-4" />
                  <Eye v-else class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          <!-- Accept Terms Checkbox -->
          <div class="flex items-center gap-2 pt-1">
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input v-model="acceptTerms" type="checkbox" required class="w-4 h-4 text-blue-600 rounded border-slate-300 dark:border-slate-700 focus:ring-blue-500/20" />
              <span class="text-xs font-medium text-slate-600 dark:text-slate-400">
                I agree to the <a href="#" class="text-blue-600 dark:text-blue-400 font-bold hover:underline">Terms of Service</a> & <a href="#" class="text-blue-600 dark:text-blue-400 font-bold hover:underline">Privacy Policy</a>
              </span>
            </label>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            :disabled="isSubmitting"
            class="w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl text-xs sm:text-sm transition duration-200 active:scale-[0.99] shadow-lg shadow-blue-500/25 disabled:opacity-50 cursor-pointer mt-2"
          >
            {{ isSubmitting ? 'Creating Account...' : 'Create Free Account' }}
          </button>
        </form>

        <!-- Login Link Navigation -->
        <div class="text-center pt-2">
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Already have an account? 
            <router-link to="/login" class="font-extrabold text-blue-600 dark:text-blue-400 hover:underline transition ml-1">
              Sign In
            </router-link>
          </p>
        </div>

      </div>

      <!-- Bottom Terms Footer -->
      <div class="text-center text-[11px] text-slate-400">
        © 2026 Job Search. Protected by reCAPTCHA and Subject to Terms & Privacy.
      </div>

    </div>

  </div>
</template>
