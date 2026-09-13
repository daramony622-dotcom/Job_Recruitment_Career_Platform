<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'
import {
  Mail, Lock, Eye, EyeOff, Send, ArrowLeft,
  CheckCircle2, ShieldCheck, Sparkles
} from 'lucide-vue-next'
import ThemeToggle from '../components/common/ThemeToggle.vue'
import LanguageSwitcher from '../components/common/LanguageSwitcher.vue'

const router = useRouter()
const email = ref('')
const password = ref('')
const rememberMe = ref(false)
const showPassword = ref(false)
const isSubmitting = ref(false)
const loginError = ref('')

const {
  login,
  loginWithGoogle,
  requestTelegramOtp,
  verifyTelegramOtp,
  handleOAuthCallback,
  fetchCurrentUser,
  user
} = useAuth()

const telegramPhone = ref('')
const telegramCode = ref('')
const telegramOtpStep = ref(false)
const telegramOtpPending = ref(false)

const isAdminDashboardRole = (userData) =>
  Boolean(userData && (['admin', 'hr', 'company'].includes(userData.role) || userData.is_admin))

const redirectAfterLogin = (userData) => {
  const urlParams = new URLSearchParams(window.location.search)
  const redirectParam = urlParams.get('redirect')

  if (isAdminDashboardRole(userData) || redirectParam === 'admin') {
    const adminBase = (import.meta.env.VITE_ADMIN_URL || 'http://localhost:5174').replace(/\/$/, '')
    const userToken =
      localStorage.getItem('job_platform_token') ||
      localStorage.getItem('admin_token') ||
      localStorage.getItem('auth_token')

    const targetUrl = new URL('/dashboard', adminBase)
    if (userToken) {
      targetUrl.searchParams.set('token', userToken)
    }

    window.location.href = targetUrl.toString()
    return
  }

  if (redirectParam && redirectParam !== 'admin') {
    router.push(redirectParam)
    return
  }

  router.push('/profile')
}

onMounted(async () => {
  const callbackUser = await handleOAuthCallback()
  if (callbackUser) {
    redirectAfterLogin(callbackUser)
    return
  }

  const urlParams = new URLSearchParams(window.location.search)
  const existingToken =
    localStorage.getItem('job_platform_token') ||
    localStorage.getItem('admin_token')

  if (existingToken && urlParams.get('redirect') === 'admin') {
    const sessionUser = await fetchCurrentUser()
    if (isAdminDashboardRole(sessionUser || user.value)) {
      redirectAfterLogin(sessionUser || user.value)
    }
  }
})

const handleLogin = async () => {
  isSubmitting.value = true
  loginError.value = ''

  try {
    const authenticatedUser = await login(email.value, password.value)
    redirectAfterLogin(authenticatedUser)
  } catch (error) {
    loginError.value = error.message || 'Login failed. Please verify your credentials.'
  } finally {
    isSubmitting.value = false
  }
}

const requestTelegramLogin = async () => {
  try {
    const response = await requestTelegramOtp(telegramPhone.value)
    telegramOtpPending.value = true
    telegramOtpStep.value = true
    loginError.value = response.message || 'Telegram code sent.'
  } catch (error) {
    loginError.value = error.message || 'Unable to request Telegram code.'
  }
}

const verifyTelegramLogin = async () => {
  try {
    const verifiedUser = await verifyTelegramOtp(telegramPhone.value, telegramCode.value)
    redirectAfterLogin(verifiedUser)
  } catch (error) {
    loginError.value = error.message || 'The Telegram verification code is invalid.'
  }
}
</script>

<template>
  <div class="min-h-screen w-full grid grid-cols-1 lg:grid-cols-2 font-sans bg-slate-50 dark:bg-[#070c16] transition-colors duration-300">
    <div class="relative hidden lg:flex flex-col justify-between p-12 bg-linear-to-br from-blue-700 via-blue-600 to-indigo-900 text-white overflow-hidden">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_30%,rgba(255,255,255,0.15),transparent_50%)] pointer-events-none"></div>
      <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>

      <div class="relative z-10 flex items-center justify-between">
        <router-link to="/" class="flex items-center gap-2 group">
          <img src="/logo.png" alt="Job Search Logo" class="h-14 w-auto object-contain bg-white/10 rounded-xl p-1 backdrop-blur-md border border-white/20 transition-transform group-hover:scale-105" />
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
          <span class="text-transparent bg-clip-text bg-linear-to-r from-blue-200 to-indigo-100">with confidence.</span>
        </h1>

        <p class="text-blue-100 text-sm leading-relaxed">
          Access your profile, track applications, and apply to jobs from one secure dashboard built for candidates.
        </p>

        <div class="space-y-3 pt-2">
          <div class="flex items-center gap-3 text-xs font-semibold text-white/90">
            <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
            <span>Saved jobs and application tracking</span>
          </div>
          <div class="flex items-center gap-3 text-xs font-semibold text-white/90">
            <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
            <span>Profile-based job recommendations</span>
          </div>
          <div class="flex items-center gap-3 text-xs font-semibold text-white/90">
            <ShieldCheck class="w-4 h-4 text-amber-300 shrink-0" />
            <span>Fast, secure access for candidates and employers</span>
          </div>
        </div>
      </div>

      <div class="relative z-10 pt-6 border-t border-white/15 text-xs text-blue-200">
        Trusted by candidates and hiring teams across Southeast Asia.
      </div>
    </div>

    <div class="flex flex-col justify-between p-6 sm:p-12 lg:p-16 relative">
      <div class="flex items-center justify-between sm:justify-end gap-3 pb-6">
        <router-link to="/" class="lg:hidden flex items-center gap-2">
          <img src="/logo.png" alt="Logo" class="h-10 w-auto object-contain" />
        </router-link>

        <div class="flex items-center gap-2">
          <LanguageSwitcher />
          <ThemeToggle />
        </div>
      </div>

      <div class="max-w-md w-full mx-auto space-y-6 my-auto py-6">
        <div class="space-y-2">
          <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Welcome back</h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Log in to continue your job search and manage your profile.</p>
        </div>

        <div v-if="loginError" class="p-3.5 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900/50 rounded-2xl text-xs font-bold text-rose-700 dark:text-rose-300">
          {{ loginError }}
        </div>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div class="space-y-1">
            <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Email Address</label>
            <div class="relative">
              <Mail class="w-4.5 h-4.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input
                id="email"
                v-model="email"
                type="email"
                placeholder="you@example.com"
                required
                class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/15 transition duration-200"
              />
            </div>
          </div>

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

          <div class="flex items-center justify-between gap-2 text-xs">
            <label class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-300 font-medium">
              <input v-model="rememberMe" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
              <span>Remember me</span>
            </label>
            <router-link to="/forgot-password" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">Forgot password?</router-link>
          </div>

          <button
            type="submit"
            :disabled="isSubmitting"
            class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-4 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold shadow-lg shadow-blue-500/20 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <Send class="w-4 h-4" />
            <span>{{ isSubmitting ? 'Signing In...' : 'Sign In' }}</span>
          </button>
        </form>

        <div class="relative my-4">
          <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200 dark:border-slate-800"></div></div>
          <div class="relative flex justify-center text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
            <span class="bg-slate-50 dark:bg-[#070c16] px-3">Or continue with</span>
          </div>
        </div>

        <div class="space-y-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-3 shadow-sm">
          <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
            <Smartphone class="w-3.5 h-3.5" />
            Telegram OTP
          </div>

          <div v-if="!telegramOtpStep" class="space-y-3">
            <label for="telegram-phone-login" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Phone number linked to Telegram</label>
            <div class="relative">
              <Smartphone class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input
                id="telegram-phone-login"
                v-model="telegramPhone"
                type="tel"
                placeholder="+855 12 345 678"
                class="w-full pl-10 pr-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10"
              />
            </div>
            <button
              type="button"
              @click="requestTelegramLogin"
              class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl border border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-900/70 dark:bg-sky-950/40 dark:text-sky-300 font-bold text-xs shadow-sm hover:bg-sky-100 dark:hover:bg-sky-900/50 transition"
            >
              <KeyRound class="w-4 h-4" />
              Send Telegram OTP
            </button>
          </div>

          <div v-else class="space-y-3">
            <label for="telegram-code-login" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Verification code</label>
            <div class="relative">
              <KeyRound class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input
                id="telegram-code-login"
                v-model="telegramCode"
                type="text"
                inputmode="numeric"
                maxlength="6"
                placeholder="Enter 6-digit code"
                class="w-full pl-10 pr-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10"
              />
            </div>
            <button
              type="button"
              @click="verifyTelegramLogin"
              class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-md shadow-sky-500/20 transition"
            >
              <CheckCircle2 class="w-4 h-4" />
              Verify OTP
            </button>
            <button
              type="button"
              @click="telegramOtpStep = false; telegramCode = ''; telegramOtpPending = false"
              class="w-full text-center text-[11px] font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
            >
              Change phone number
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <button
            type="button"
            @click="loginWithGoogle"
            class="inline-flex items-center justify-center gap-2 py-3 px-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 font-bold text-xs sm:text-sm shadow-sm hover:border-blue-300 hover:bg-blue-50 dark:hover:bg-slate-800 transition"
          >
            <span class="inline-block h-4 w-4 rounded-full bg-linear-to-br from-red-500 via-yellow-400 to-blue-600"></span>
            Google
          </button>

          <button
            type="button"
            @click="loginWithTelegram(telegramPhone)"
            class="inline-flex items-center justify-center gap-2 py-3 px-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 font-bold text-xs sm:text-sm shadow-sm hover:border-blue-300 hover:bg-blue-50 dark:hover:bg-slate-800 transition"
          >
            <span class="inline-block h-4 w-4 rounded-full bg-linear-to-br from-sky-500 to-indigo-600"></span>
            Telegram
          </button>
        </div>

        <p class="text-center text-xs text-slate-500 dark:text-slate-400">
          Don’t have an account?
          <router-link to="/register" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">Create one now</router-link>
        </p>
      </div>
    </div>
  </div>
</template>
