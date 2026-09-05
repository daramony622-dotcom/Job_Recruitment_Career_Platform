<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { LogIn, Loader2 } from 'lucide-vue-next'
import { useAuth } from '../stores/auth'

const router = useRouter()
const { login } = useAuth()

const email = ref('')
const password = ref('')
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
      error.value =
        `Cannot reach the API at ${apiUrl} — make sure the backend server is running (php artisan serve).`
    } else {
      error.value = e.message || 'Something went wrong.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-950 flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-xl">
      <div class="flex items-center gap-3 mb-8">
        <div class="w-11 h-11 rounded-xl bg-blue-600 flex items-center justify-center font-black text-white text-xl">A</div>
        <div>
          <h1 class="text-xl font-bold text-slate-100">Recruit Admin</h1>
          <p class="text-sm text-slate-400">Sign in to continue</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm text-slate-400 mb-1.5">Email</label>
          <input
            v-model="email"
            type="email"
            required
            autocomplete="email"
            placeholder="admin@gmail.com"
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm text-slate-400 mb-1.5">Password</label>
          <input
            v-model="password"
            type="password"
            required
            autocomplete="current-password"
            placeholder="••••••••"
            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500"
          />
        </div>

        <p v-if="error" class="text-sm text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">
          {{ error }}
        </p>

        <button
          type="submit"
          :disabled="loading"
          class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 disabled:opacity-60 transition-colors"
        >
          <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
          <LogIn v-else class="w-4 h-4" />
          {{ loading ? 'Signing in...' : 'Sign in' }}
        </button>

        <p class="text-center text-xs text-slate-500">
          Default admin: <span class="text-slate-400">admin@gmail.com</span> / <span class="text-slate-400">Admin@1234</span>
          <br />
          API: {{ apiUrl }}
        </p>
      </form>
    </div>
  </div>
</template>