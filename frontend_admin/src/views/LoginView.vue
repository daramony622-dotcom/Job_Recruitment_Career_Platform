<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { LogIn, Loader2, Mail, Lock, ArrowLeft, CheckCircle2 } from 'lucide-vue-next'
import { useAuth } from '../stores/auth'

const router = useRouter()
const { login } = useAuth()

const email = ref('')
const password = ref('')
const remember = ref(false)
const loading = ref(false)
const error = ref('')
const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

async function submit() {
  loading.value = true
  error.value = ''
  try {
    await login(email.value.trim(), password.value)
    router.push('/')
  } catch (e) {
    console.error('Login failed:', e)
    const res = e.response
    if (res) {
      error.value = res.data?.message || res.statusText || 'Invalid credentials.'
    } else if (e.code === 'ERR_NETWORK' || e.message?.includes('Network Error')) {
      error.value = `Cannot reach the API at ${apiUrl} — make sure the backend server is running (php artisan serve).`
    } else {
      error.value = e.message || 'Something went wrong.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen w-full flex bg-slate-950 text-slate-100 font-sans">
    <!-- ផ្នែកខាងឆ្វេង៖ ពណ៌ខៀវ និង Branding ស្រដៀងទំព័រ User -->
    <div class="hidden lg:flex lg:w-1/2 bg-blue-600 p-12 flex-col justify-between relative overflow-hidden">
      <!-- Background decorative circles -->
      <div class="absolute -right-20 -top-20 w-96 h-96 rounded-full border border-blue-500/40 pointer-events-none"></div>
      <div class="absolute right-10 top-20 w-80 h-80 rounded-full border border-blue-400/30 pointer-events-none"></div>

      <!-- Top Header / Back Button -->
      <div class="flex items-center justify-between relative z-10">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-white text-blue-600 flex items-center justify-center font-black text-xl shadow-md">
            A
          </div>
          <span class="font-bold text-white text-lg">Recruit Admin</span>
        </div>
        <router-link
          to="/"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-sm font-semibold transition-colors backdrop-blur-sm"
        >
          <ArrowLeft class="w-4 h-4" />
          Back to Home
        </router-link>
      </div>

      <!-- Middle Content -->
      <div class="space-y-6 relative z-10 max-w-lg">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 text-white text-xs font-medium backdrop-blur-sm">
          <span>✨</span> Cambodia's #1 Career & Recruitment Platform
        </div>
        <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-white leading-tight">
          Welcome back to <br />your admin hub.
        </h1>
        <p class="text-blue-100 text-base leading-relaxed">
          Access platform analytics, manage job listings, review company verifications, and monitor user activities in real-time.
        </p>

        <!-- Feature Checklists -->
        <div class="space-y-3 pt-2">
          <div class="flex items-center gap-3 text-sm text-blue-50">
            <CheckCircle2 class="w-5 h-5 text-blue-200 shrink-0" />
            <span>Secure Role-Based Access Control & Permissions</span>
          </div>
          <div class="flex items-center gap-3 text-sm text-blue-50">
            <CheckCircle2 class="w-5 h-5 text-blue-200 shrink-0" />
            <span>Real-time platform overview and reporting</span>
          </div>
          <div class="flex items-center gap-3 text-sm text-blue-50">
            <CheckCircle2 class="w-5 h-5 text-blue-200 shrink-0" />
            <span>100% Free management tools for administrators</span>
          </div>
        </div>
      </div>

      <!-- Footer Copyright -->
      <div class="text-xs text-blue-200 relative z-10">
        © 2026 Recruit Admin. All rights reserved.
      </div>
    </div>

    <!-- ផ្នែកខាងស្តាំ៖ ទម្រង់ Login Form (Dark Theme) -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 bg-slate-950">
      <div class="w-full max-w-md space-y-6 bg-slate-900/60 border border-slate-800 p-8 rounded-3xl shadow-2xl backdrop-blur-md">
        
        <!-- Header -->
        <div>
          <h2 class="text-2xl font-bold tracking-tight text-slate-100">Sign In to Your Account</h2>
          <p class="text-sm text-slate-400 mt-1">Enter your credentials below to access your admin dashboard.</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Email Address</label>
            <div class="relative">
              <Mail class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
              <input
                v-model="email"
                type="email"
                required
                autocomplete="email"
                placeholder="admin@gmail.com"
                class="w-full bg-slate-950/60 border border-slate-800 rounded-xl py-3 pl-10 pr-4 text-sm text-slate-100 placeholder-slate-600 outline-none focus:border-blue-500 transition-all"
              />
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Password</label>
              <a href="#" class="text-xs text-blue-400 hover:underline">Forgot password?</a>
            </div>
            <div class="relative">
              <Lock class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
              <input
                v-model="password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                class="w-full bg-slate-950/60 border border-slate-800 rounded-xl py-3 pl-10 pr-4 text-sm text-slate-100 placeholder-slate-600 outline-none focus:border-blue-500 transition-all"
              />
            </div>
          </div>

          <!-- Remember me checkbox -->
          <div class="flex items-center gap-2">
            <input
              v-model="remember"
              type="checkbox"
              id="remember"
              class="rounded border-slate-800 bg-slate-950 text-blue-600 focus:ring-blue-500 w-4 h-4"
            />
            <label for="remember" class="text-sm text-slate-400 select-none">Remember me on this device</label>
          </div>

          <p v-if="error" class="text-sm text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">
            {{ error }}
          </p>

          <button
            type="submit"
            :disabled="loading"
            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-500 disabled:opacity-60 transition-all shadow-lg shadow-blue-600/20"
          >
            <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
            <LogIn v-else class="w-4 h-4" />
            {{ loading ? 'Signing in...' : 'Sign In to Account' }}
          </button>

          <!-- Default credentials & API info -->
          <div class="pt-4 border-t border-slate-800 text-center space-y-1">
            <p class="text-xs text-slate-500">
              Default admin: <span class="text-slate-300">admin@gmail.com</span> / <span class="text-slate-300">Admin@1234</span>
            </p>
            <p class="text-xs text-slate-500">
              API: <span class="text-slate-300">{{ apiUrl }}</span>
            </p>
          </div>
        </form>

      </div>
    </div>
  </div>
</template>