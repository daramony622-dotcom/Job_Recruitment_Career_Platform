<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { 
  Mail, Lock, Eye, EyeOff, Send, ArrowLeft, 
  Briefcase, CheckCircle2, ShieldCheck, Sparkles 
} from 'lucide-vue-next'
import ThemeToggle from '../components/common/ThemeToggle.vue'
import LanguageSwitcher from '../components/common/LanguageSwitcher.vue'

const router = useRouter()
const email = ref('')
const password = ref('')
const rememberMe = ref(false)
const showPassword = ref(false)
const isSubmitting = ref(false)

const handleLogin = () => {
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
          <span>Cambodia's #1 Career & Recruitment Platform</span>
        </div>

        <h1 class="text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight text-white">
          Welcome back to <br />
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-200 to-indigo-100">your career hub.</span>
        </h1>

        <p class="text-blue-100 text-sm leading-relaxed">
          Access thousands of curated job listings, track your job applications in real-time, and get direct messages from verified employers.
        </p>

        <!-- Key Feature Badges -->
        <div class="space-y-3 pt-2">
          <div class="flex items-center gap-3 text-xs font-semibold text-white/90">
            <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
            <span>1-Click Application with Saved CV</span>
          </div>
          <div class="flex items-center gap-3 text-xs font-semibold text-white/90">
            <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
            <span>Verified Salaries & Company Reviews</span>
          </div>
          <div class="flex items-center gap-3 text-xs font-semibold text-white/90">
            <ShieldCheck class="w-4 h-4 text-amber-300 shrink-0" />
            <span>100% Free for Candidates & Job Seekers</span>
          </div>
        </div>
      </div>

      <!-- Footer Testimonial Quote -->
      <div class="relative z-10 pt-6 border-t border-white/15 flex items-center gap-4">
        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center font-bold text-sm">SK</div>
        <div>
          <p class="text-xs font-bold text-white">Sokha Kim</p>
          <p class="text-[11px] text-blue-200">Senior Developer at TechMatrix Global</p>
        </div>
      </div>

    </div>

    <!-- RIGHT COLUMN: Form Panel -->
    <div class="flex flex-col justify-between p-6 sm:p-12 lg:p-16 relative">
      
      <!-- Top Action Bar (Theme & Language) -->
      <div class="flex items-center justify-between sm:justify-end gap-3 pb-6">
        <router-link to="/" class="lg:hidden flex items-center gap-2">
          <img src="/logo.png" alt="Logo" class="h-10 w-auto object-contain" />
        </router-link>

        <div class="flex items-center gap-2">
          <LanguageSwitcher />
          <ThemeToggle />
        </div>
      </div>

      <!-- Main Login Form Box -->
      <div class="max-w-md w-full mx-auto space-y-8 my-auto py-6">
        
        <div class="space-y-2">
          <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Sign In to Your Account</h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Enter your credentials below to access your candidate dashboard.</p>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-5">
          
          <!-- Email Field -->
          <div class="space-y-1.5">
            <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
              Email Address
            </label>
            <div class="relative">
              <Mail class="w-4.5 h-4.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input 
                id="email"
                v-model="email"
                type="email" 
                placeholder="your.email@example.com" 
                required
                class="w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition duration-200"
              />
            </div>
          </div>

          <!-- Password Field -->
          <div class="space-y-1.5">
            <div class="flex justify-between items-center">
              <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Password
              </label>
              <a href="#" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                Forgot password?
              </a>
            </div>

            <div class="relative">
              <Lock class="w-4.5 h-4.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input 
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'" 
                placeholder="••••••••" 
                required
                class="w-full pl-10 pr-11 py-3 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition duration-200"
              />
              <button 
                type="button" 
                @click="showPassword = !showPassword"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition cursor-pointer"
                aria-label="Toggle password visibility"
              >
                <EyeOff v-if="!showPassword" class="w-4.5 h-4.5" />
                <Eye v-else class="w-4.5 h-4.5" />
              </button>
            </div>
          </div>

          <!-- Remember Me Checkbox -->
          <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input v-model="rememberMe" type="checkbox" class="w-4 h-4 text-blue-600 rounded border-slate-300 dark:border-slate-700 focus:ring-blue-500/20" />
              <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Remember me on this device</span>
            </label>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            :disabled="isSubmitting"
            class="w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl text-xs sm:text-sm transition duration-200 active:scale-[0.99] shadow-lg shadow-blue-500/25 disabled:opacity-50 cursor-pointer"
          >
            {{ isSubmitting ? 'Signing in...' : 'Sign In to Account' }}
          </button>
        </form>

        <!-- Divider -->
        <div class="relative flex items-center justify-center my-6">
          <div class="border-t border-slate-200 dark:border-slate-800 w-full"></div>
          <span class="bg-slate-50 dark:bg-slate-950 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 absolute">
            OR Continue With
          </span>
        </div>

        <!-- Social Login Buttons -->
        <div class="grid grid-cols-2 gap-3">
          <button 
            type="button"
            class="flex items-center justify-center gap-2 py-3 px-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl text-xs font-bold text-slate-700 dark:text-slate-200 transition active:scale-[0.99] shadow-2xs cursor-pointer"
          >
            <svg class="w-4 h-4" viewBox="0 0 24 24">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            <span>Google</span>
          </button>

          <button 
            type="button"
            class="flex items-center justify-center gap-2 py-3 px-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl text-xs font-bold text-slate-700 dark:text-slate-200 transition active:scale-[0.99] shadow-2xs cursor-pointer"
          >
            <Send class="w-4 h-4 text-sky-500 fill-sky-500" />
            <span>Telegram</span>
          </button>
        </div>

        <!-- Register Link Navigation -->
        <div class="text-center pt-4">
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Don't have an account yet? 
            <router-link to="/register" class="font-extrabold text-blue-600 dark:text-blue-400 hover:underline transition ml-1">
              Create Account
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
