<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'
import {
  Mail, Lock, Eye, EyeOff, Send, ArrowLeft, ShieldCheck, Sparkles,
  Loader2, CheckCircle2, XCircle, ExternalLink, RotateCcw,
} from 'lucide-vue-next'
import ThemeToggle from './common/ThemeToggle.vue'
import LanguageSwitcher from './common/LanguageSwitcher.vue'

const router = useRouter()
const loginMode = ref('standard') // 'standard' | 'telegram'

// Standard login fields
const email = ref('')
const password = ref('')
const showPassword = ref(false)

// Telegram login state
const telegramStatus = ref('idle') // 'idle' | 'waiting' | 'success' | 'error'

const isSubmitting = ref(false)
const loginError = ref('')

const {
  login,
  loginWithGoogle,
  loginWithTelegram,
  cancelTelegramLogin,
  handleOAuthCallback,
  dashboardPathFor,
} = useAuth()

// Single source of truth for role -> dashboard routing now lives in
// useAuth.js. Login.vue and Register.vue both defer to it so they can
// never disagree with each other again.
const goToDestination = (u) => router.push(dashboardPathFor(u))

onMounted(async () => {
  const callbackUser = await handleOAuthCallback()
  if (callbackUser) goToDestination(callbackUser)
})

onBeforeUnmount(() => {
  if (telegramStatus.value === 'waiting') cancelTelegramLogin()
})

// Standard Login Handler
const handleStandardLogin = async () => {
  isSubmitting.value = true
  loginError.value = ''
  try {
    const res = await login({ email: email.value, password: password.value })
    if (res?.token || localStorage.getItem('job_platform_token')) {
      goToDestination(res?.user)
    }
  } catch (error) {
    loginError.value = error.message || 'Login failed. Please verify your credentials.'
  } finally {
    isSubmitting.value = false
  }
}

// Telegram Login Handler — opens the bot, waits for the user to confirm inside Telegram
const handleTelegramLogin = async () => {
  telegramStatus.value = 'waiting'
  loginError.value = ''
  try {
    const user = await loginWithTelegram()
    telegramStatus.value = 'success'
    setTimeout(() => goToDestination(user), 500)
  } catch (error) {
    telegramStatus.value = 'error'
    if (error.message !== 'cancelled') {
      loginError.value = error.message || 'Telegram login failed.'
    }
  }
}

const cancelTelegram = () => {
  cancelTelegramLogin()
  telegramStatus.value = 'idle'
}

const retryTelegram = () => {
  telegramStatus.value = 'idle'
  loginError.value = ''
}

const switchToTelegram = () => {
  loginMode.value = 'telegram'
  telegramStatus.value = 'idle'
  loginError.value = ''
}

const switchToStandard = () => {
  if (telegramStatus.value === 'waiting') cancelTelegramLogin()
  loginMode.value = 'standard'
  telegramStatus.value = 'idle'
  loginError.value = ''
}
</script>

<template>
  <div class="min-h-screen w-full grid grid-cols-1 lg:grid-cols-2 font-sans bg-slate-50 dark:bg-[#070c16] transition-colors duration-300">

    <!-- LEFT COLUMN: Background Banner with Professional Business Image -->
    <div class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-[url('/business-professional-background.png')] bg-cover bg-center p-12 text-white order-first lg:order-first">
      <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/50 to-transparent z-0"></div>

      <div class="relative z-10 flex items-center justify-between">
        <router-link to="/" class="flex items-center gap-2 group">
          <img src="/logo.png" alt="Job Search Logo" class="h-14 w-auto object-contain" />
        </router-link>
        <router-link to="/" class="inline-flex items-center gap-2 text-xs font-bold text-white/80 hover:text-white bg-white/10 hover:bg-white/20 backdrop-blur-md px-4 py-2 rounded-xl border border-white/15 transition">
          <ArrowLeft class="w-4 h-4" />
          <span>Back to Home</span>
        </router-link>
      </div>

      <div class="relative z-10 space-y-6 max-w-lg my-auto py-12">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-blue-100">
          <Sparkles class="w-3.5 h-3.5 text-amber-400" />
          <span>Welcome back</span>
        </div>
        <h1 class="text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight text-white">
          Find your next opportunity
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-200 to-indigo-100">with confidence.</span>
        </h1>
        <p class="text-blue-100 text-sm leading-relaxed">
          Access your profile, track applications, and apply to jobs from one secure dashboard built for candidates.
        </p>
      </div>

      <div class="relative z-10 pt-6 border-t border-white/15 text-xs text-blue-200">
        Trusted by candidates and hiring teams across Southeast Asia.
      </div>
    </div>

    <!-- RIGHT COLUMN: Login Form Section -->
    <div class="flex flex-col justify-between p-6 sm:p-12 lg:p-16 relative order-last lg:order-last">
      <div class="flex items-center justify-between sm:justify-end gap-3 pb-6">
        <router-link to="/" class="flex items-center gap-2 group lg:hidden">
          <img src="/logo.png" alt="Job Search Logo" class="h-10 w-auto object-contain" />
        </router-link>
        <div class="flex items-center gap-2">
          <LanguageSwitcher />
          <ThemeToggle />
        </div>
      </div>

      <div class="max-w-md w-full mx-auto space-y-6 my-auto py-6">
        <div class="space-y-3">
          <div class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-300">
            <ShieldCheck class="h-3.5 w-3.5" /> Secure login
          </div>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Welcome back</h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Choose your preferred sign-in method below.</p>
        </div>

        <!-- Mode Toggle Tabs -->
        <div class="grid grid-cols-2 gap-2 p-1 bg-slate-200 dark:bg-slate-900 rounded-2xl">
          <button
            type="button"
            @click="switchToStandard"
            :class="loginMode === 'standard' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400'"
            class="py-2.5 text-xs font-bold rounded-xl transition cursor-pointer"
          >
            Standard Login
          </button>
          <button
            type="button"
            @click="switchToTelegram"
            :class="loginMode === 'telegram' ? 'bg-white dark:bg-slate-800 text-sky-500 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400'"
            class="py-2.5 text-xs font-bold rounded-xl transition cursor-pointer flex items-center justify-center gap-1.5"
          >
            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-[#229ED9] text-white text-[10px]">➤</span>
            <span>Telegram Login</span>
          </button>
        </div>

        <!-- Error Message (shared) -->
        <div v-if="loginError" class="p-3.5 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900/50 rounded-2xl text-xs font-bold text-rose-700 dark:text-rose-300">
          {{ loginError }}
        </div>

        <!-- Standard Login Form -->
        <form v-if="loginMode === 'standard'" @submit.prevent="handleStandardLogin" class="space-y-4">
          <div class="space-y-1">
            <label for="login-email" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Email Address</label>
            <div class="relative">
              <Mail class="w-4.5 h-4.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input id="login-email" v-model="email" type="email" placeholder="you@example.com" required class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 transition" />
            </div>
          </div>

          <div class="space-y-1">
            <div class="flex items-center justify-between">
              <label for="login-password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Password</label>
              <router-link to="/forgot-password" class="text-xs font-bold text-[#0b1329] dark:text-cyan-400 hover:underline">
                Forgot password?
              </router-link>
            </div>
            <div class="relative">
              <Lock class="w-4.5 h-4.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input id="login-password" v-model="password" :type="showPassword ? 'text' : 'password'" placeholder="••••••••" required class="w-full pl-10 pr-10 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-[#0b1329] focus:ring-4 focus:ring-[#0b1329]/15 transition" />
              <button type="button" @click="showPassword = !showPassword" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 cursor-pointer">
                <EyeOff v-if="!showPassword" class="w-4 h-4" /><Eye v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <button type="submit" :disabled="isSubmitting" class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-lg shadow-blue-500/25 transition disabled:opacity-60 cursor-pointer active:scale-95">
            <Send class="w-4 h-4" />
            <span>{{ isSubmitting ? 'Signing In...' : 'Sign In' }}</span>
          </button>
        </form>

        <!-- Telegram Login Panel -->
        <div v-else class="space-y-5">

          <!-- idle: show the 3-step guide + start button -->
          <div v-if="telegramStatus === 'idle'" class="space-y-4">
            <ol class="space-y-2.5">
              <li class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-3">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-sky-100 dark:bg-sky-950 text-[11px] font-bold text-sky-600 dark:text-sky-400">1</span>
                <span class="text-xs text-slate-600 dark:text-slate-300">Tap the button below to open our bot in Telegram</span>
              </li>
              <li class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-3">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-sky-100 dark:bg-sky-950 text-[11px] font-bold text-sky-600 dark:text-sky-400">2</span>
                <span class="text-xs text-slate-600 dark:text-slate-300">Press <strong class="font-bold text-slate-800 dark:text-slate-100">Start</strong>, then confirm with <strong class="font-bold text-slate-800 dark:text-slate-100">Yes</strong></span>
              </li>
              <li class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-3">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-sky-100 dark:bg-sky-950 text-[11px] font-bold text-sky-600 dark:text-sky-400">3</span>
                <span class="text-xs text-slate-600 dark:text-slate-300">You'll be signed in here automatically</span>
              </li>
            </ol>

            <button
              type="button"
              @click="handleTelegramLogin"
              class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-4 rounded-2xl bg-sky-500 hover:bg-sky-400 text-white text-sm font-bold shadow-lg shadow-sky-500/20 transition cursor-pointer"
            >
              <ExternalLink class="w-4 h-4" />
              <span>Continue with Telegram</span>
            </button>
          </div>

          <!-- waiting: polling for the Yes confirmation -->
          <div v-else-if="telegramStatus === 'waiting'" class="rounded-2xl border border-sky-200 dark:border-sky-900/50 bg-sky-50 dark:bg-sky-950/30 px-5 py-8 flex flex-col items-center text-center gap-3">
            <Loader2 class="w-7 h-7 text-sky-500 animate-spin" />
            <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Waiting for confirmation</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-[260px]">
              Check the Telegram tab we opened and tap <strong class="font-bold">Yes</strong> to finish signing in.
            </p>
            <button type="button" @click="cancelTelegram" class="mt-2 text-[11px] font-bold text-slate-500 dark:text-slate-400 hover:underline cursor-pointer">
              Cancel
            </button>
          </div>

          <!-- success: brief confirmation before redirect -->
          <div v-else-if="telegramStatus === 'success'" class="rounded-2xl border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50 dark:bg-emerald-950/30 px-5 py-8 flex flex-col items-center text-center gap-3">
            <CheckCircle2 class="w-7 h-7 text-emerald-500" />
            <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Signed in</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">Taking you to your dashboard...</p>
          </div>

          <!-- error: declined / expired / failed -->
          <div v-else-if="telegramStatus === 'error'" class="rounded-2xl border border-rose-200 dark:border-rose-900/50 bg-rose-50 dark:bg-rose-950/30 px-5 py-8 flex flex-col items-center text-center gap-3">
            <XCircle class="w-7 h-7 text-rose-500" />
            <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Couldn't sign you in</p>
            <button type="button" @click="retryTelegram" class="mt-1 inline-flex items-center gap-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline cursor-pointer">
              <RotateCcw class="w-3.5 h-3.5" /> Try again
            </button>
          </div>
        </div>

        <!-- Divider -->
        <div class="relative flex items-center justify-center my-4">
          <div class="border-t border-slate-200 dark:border-slate-800 w-full"></div>
          <span class="bg-slate-50 dark:bg-[#070c16] px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 absolute">
            OR Sign In With
          </span>
        </div>

        <!-- Social Login Buttons (Google & Telegram) -->
        <div class="grid grid-cols-2 gap-3">
          <button type="button" @click="loginWithGoogle" class="flex items-center justify-center gap-2 py-3 px-4 bg-slate-50 dark:bg-[#0d1526] border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800/60 rounded-2xl text-xs font-bold text-slate-700 dark:text-slate-200 transition cursor-pointer">
            <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true">
              <path fill="#4285F4" d="M21.35 12.23c0-.73-.07-1.43-.2-2.1H12v4h5.2a4.45 4.45 0 0 1-1.93 2.92v2.42h3.12c1.83-1.69 2.96-4.18 2.96-7.24Z"/>
              <path fill="#34A853" d="M12 21.8c2.61 0 4.8-.86 6.4-2.33l-3.12-2.42c-.86.58-1.96.93-3.28.93-2.52 0-4.66-1.7-5.43-3.99H3.35v2.5A9.67 9.67 0 0 0 12 21.8Z"/>
              <path fill="#FBBC05" d="M6.57 13.99a5.82 5.82 0 0 1 0-3.72v-2.5H3.35a9.8 9.8 0 0 0 0 8.72l3.22-2.5Z"/>
              <path fill="#EA4335" d="M12 6.28c1.42 0 2.69.49 3.69 1.45l2.77-2.77C16.8 3.43 14.61 2.55 12 2.55a9.67 9.67 0 0 0-8.65 5.22l3.22 2.5C7.34 7.98 9.48 6.28 12 6.28Z"/>
            </svg>
            <span>Google</span>
          </button>

          <button type="button" @click="switchToTelegram" class="flex items-center justify-center gap-2 py-3 px-4 bg-slate-50 dark:bg-[#0d1526] border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800/60 rounded-2xl text-xs font-bold text-slate-700 dark:text-slate-200 transition cursor-pointer">
            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-[#229ED9]" aria-hidden="true">
              <svg class="h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="currentColor">
                <path d="M21.4 4.6 18.2 19.7c-.24 1.07-.87 1.33-1.77.83l-4.86-3.58-2.35 2.26c-.26.26-.48.48-.98.48l.35-4.95 9.02-8.15c.39-.35-.09-.55-.61-.2L5.85 13.5 1.12 12.02c-1.03-.32-1.05-1.03.22-1.51L19.8 3.34c.86-.32 1.61.2 1.6 1.26Z"/>
              </svg>
            </span>
            <span>Telegram</span>
          </button>
        </div>

        <p class="text-center text-xs text-slate-500 dark:text-slate-400">
          Don't have an account?
          <router-link to="/register" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">Create one now</router-link>
        </p>
      </div>

      <div class="text-center text-[11px] text-slate-400">
        © 2026 Job Search. Protected by reCAPTCHA and Subject to Terms & Privacy.
      </div>
    </div>

  </div>
</template>