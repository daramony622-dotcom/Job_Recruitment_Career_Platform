<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'
import {
  Mail, Lock, Eye, EyeOff, Send, ArrowLeft, ShieldCheck, Sparkles,
  KeyRound, CheckCircle2, AlertCircle, RefreshCw
} from 'lucide-vue-next'
import ThemeToggle from './common/ThemeToggle.vue'
import LanguageSwitcher from './common/LanguageSwitcher.vue'

const router = useRouter()
const { forgotPassword, resetPassword } = useAuth()

// Wizard Steps: 1 = Request Code, 2 = Verify Code & Reset, 3 = Success
const step = ref(1)

// Form State
const email = ref('')
const code = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const isSubmitting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

// Step 1: Send Reset Code
const handleSendCode = async () => {
  if (!email.value) {
    errorMessage.value = 'Please enter your registered email address.'
    return
  }
  errorMessage.value = ''
  successMessage.value = ''
  isSubmitting.value = true

  try {
    const res = await forgotPassword(email.value)
    successMessage.value = res?.message || 'Verification code sent to your email.'
    step.value = 2
  } catch (err) {
    errorMessage.value = err.message || 'Unable to send reset code. Please check your email address.'
  } finally {
    isSubmitting.value = false
  }
}

// Step 2: Reset Password
const handleResetPassword = async () => {
  if (!code.value || code.value.length !== 6) {
    errorMessage.value = 'Please enter the 6-digit code sent to your email.'
    return
  }
  if (!newPassword.value || newPassword.value.length < 6) {
    errorMessage.value = 'Password must be at least 6 characters long.'
    return
  }
  if (newPassword.value !== confirmPassword.value) {
    errorMessage.value = 'Passwords do not match!'
    return
  }

  errorMessage.value = ''
  successMessage.value = ''
  isSubmitting.value = true

  try {
    const res = await resetPassword({
      email: email.value,
      code: code.value,
      password: newPassword.value,
      password_confirmation: confirmPassword.value,
    })
    successMessage.value = res?.message || 'Your password has been reset successfully!'
    step.value = 3
  } catch (err) {
    errorMessage.value = err.message || 'Failed to reset password. Please check your verification code.'
  } finally {
    isSubmitting.value = false
  }
}

// Resend Code
const handleResendCode = async () => {
  errorMessage.value = ''
  successMessage.value = ''
  isSubmitting.value = true
  try {
    const res = await forgotPassword(email.value)
    successMessage.value = res?.message || 'A new verification code has been sent to your email.'
  } catch (err) {
    errorMessage.value = err.message || 'Failed to resend verification code.'
  } finally {
    isSubmitting.value = false
  }
}

const goToLogin = () => {
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen w-full grid grid-cols-1 lg:grid-cols-2 font-sans bg-slate-50 dark:bg-[#070c16] transition-colors duration-300">
    
    <!-- LEFT COLUMN: Background Banner with Professional Business Image -->
    <div class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-[url('/business-professional-background.png')] bg-cover bg-center p-12 text-white order-first">
      <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/55 to-transparent z-0"></div>

      <div class="relative z-10 flex items-center justify-between">
        <router-link to="/" class="flex items-center gap-2 group">
          <img src="/logo.png" alt="Job Search Logo" class="h-14 w-auto object-contain" />
        </router-link>
        <router-link to="/login" class="inline-flex items-center gap-2 text-xs font-bold text-white/80 hover:text-white bg-white/10 hover:bg-white/20 backdrop-blur-md px-4 py-2 rounded-xl border border-white/15 transition">
          <ArrowLeft class="w-4 h-4" />
          <span>Back to Login</span>
        </router-link>
      </div>

      <div class="relative z-10 space-y-6 max-w-lg my-auto py-12">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-cyan-200">
          <Sparkles class="w-3.5 h-3.5 text-amber-400" />
          <span>Password Recovery</span>
        </div>
        <h1 class="text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight text-white">
          Restore account access <br />
          <span class="text-cyan-300">quickly & securely.</span>
        </h1>
        <p class="text-slate-200 text-sm leading-relaxed">
          Verify your identity via standard 6-digit email OTP security code and update your account password instantly.
        </p>
      </div>

      <div class="relative z-10 pt-6 border-t border-white/15 text-xs text-slate-300">
        Job Search Account Security System — Powered by Encrypted Verification.
      </div>
    </div>

    <!-- RIGHT COLUMN: Forgot / Reset Password Form -->
    <div class="flex flex-col justify-between p-6 sm:p-12 lg:p-16 relative order-last">
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
        
        <!-- Header -->
        <div class="space-y-3">
          <div class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-[#0b1329] dark:border-blue-900/50 dark:bg-slate-800 dark:text-cyan-300">
            <KeyRound class="h-3.5 w-3.5" /> Password reset
          </div>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
            {{ step === 1 ? 'Forgot your password?' : step === 2 ? 'Enter verification code' : 'Password Reset Complete' }}
          </h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
            {{ step === 1 ? 'Enter your registered email address and we will send a 6-digit code.' : step === 2 ? `Enter the 6-digit code sent to ${email} and your new password.` : 'Your account password has been updated successfully.' }}
          </p>
        </div>

        <!-- Feedback Alerts -->
        <div v-if="errorMessage" class="p-3.5 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900/50 rounded-2xl text-xs font-bold text-rose-700 dark:text-rose-300 flex items-center gap-2">
          <AlertCircle class="w-4.5 h-4.5 shrink-0" />
          <span>{{ errorMessage }}</span>
        </div>

        <div v-if="successMessage && step !== 3" class="p-3.5 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-900/50 rounded-2xl text-xs font-bold text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
          <CheckCircle2 class="w-4.5 h-4.5 shrink-0 text-emerald-500" />
          <span>{{ successMessage }}</span>
        </div>

        <!-- STEP 1: Request Code Form -->
        <form v-if="step === 1" @submit.prevent="handleSendCode" class="space-y-4">
          <div class="space-y-1">
            <label for="forgot-email" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Email Address</label>
            <div class="relative">
              <Mail class="w-4.5 h-4.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input 
                id="forgot-email"
                v-model="email" 
                type="email" 
                placeholder="you@example.com" 
                required 
                class="w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-[#0b1329] focus:ring-4 focus:ring-[#0b1329]/15 transition" 
              />
            </div>
          </div>

          <button 
            type="submit" 
            :disabled="isSubmitting" 
            class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-lg shadow-blue-500/25 transition disabled:opacity-60 cursor-pointer active:scale-95"
          >
            <Send class="w-4 h-4" />
            <span>{{ isSubmitting ? 'Sending Code...' : 'Send Reset Code' }}</span>
          </button>
        </form>

        <!-- STEP 2: Verify Code & Reset Password Form -->
        <form v-else-if="step === 2" @submit.prevent="handleResetPassword" class="space-y-4">
          
          <div class="space-y-1">
            <label for="reset-code" class="block text-xs font-bold text-slate-700 dark:text-slate-300">6-Digit Verification Code</label>
            <div class="relative">
              <KeyRound class="w-4.5 h-4.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input 
                id="reset-code"
                v-model="code" 
                type="text" 
                maxlength="6"
                placeholder="123456" 
                required 
                class="w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl text-xs sm:text-sm font-mono tracking-widest text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-[#0b1329] focus:ring-4 focus:ring-[#0b1329]/15 transition" 
              />
            </div>
          </div>

          <div class="space-y-1">
            <label for="new-password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">New Password</label>
            <div class="relative">
              <Lock class="w-4.5 h-4.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input 
                id="new-password"
                v-model="newPassword" 
                :type="showNewPassword ? 'text' : 'password'" 
                placeholder="At least 8 characters" 
                required 
                class="w-full pl-10 pr-10 py-3 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-[#0b1329] focus:ring-4 focus:ring-[#0b1329]/15 transition" 
              />
              <button type="button" @click="showNewPassword = !showNewPassword" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 cursor-pointer">
                <EyeOff v-if="!showNewPassword" class="w-4 h-4" /><Eye v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div class="space-y-1">
            <label for="confirm-password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Confirm New Password</label>
            <div class="relative">
              <Lock class="w-4.5 h-4.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input 
                id="confirm-password"
                v-model="confirmPassword" 
                :type="showConfirmPassword ? 'text' : 'password'" 
                placeholder="Confirm password" 
                required 
                class="w-full pl-10 pr-10 py-3 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-[#0b1329] focus:ring-4 focus:ring-[#0b1329]/15 transition" 
              />
              <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 cursor-pointer">
                <EyeOff v-if="!showConfirmPassword" class="w-4 h-4" /><Eye v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <button 
            type="submit" 
            :disabled="isSubmitting" 
            class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-lg shadow-blue-500/25 transition disabled:opacity-60 cursor-pointer active:scale-95"
          >
            <ShieldCheck class="w-4 h-4" />
            <span>{{ isSubmitting ? 'Resetting Password...' : 'Reset Password' }}</span>
          </button>

          <div class="text-center pt-2">
            <button 
              type="button" 
              @click="handleResendCode" 
              :disabled="isSubmitting"
              class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0b1329] dark:text-cyan-400 hover:underline cursor-pointer"
            >
              <RefreshCw class="w-3.5 h-3.5" />
              <span>Didn't receive code? Resend Code</span>
            </button>
          </div>
        </form>

        <!-- STEP 3: Success Screen -->
        <div v-else-if="step === 3" class="space-y-6 text-center py-4">
          <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 rounded-full mx-auto flex items-center justify-center shadow-md">
            <CheckCircle2 class="w-10 h-10" />
          </div>
          <div class="space-y-2">
            <h3 class="text-xl font-extrabold text-slate-900 dark:text-white">Password Changed Successfully!</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs mx-auto">
              Your account password has been reset. You can now log in with your new credentials.
            </p>
          </div>

          <button 
            type="button" 
            @click="goToLogin" 
            class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-lg shadow-blue-500/25 transition cursor-pointer active:scale-95"
          >
            <span>Proceed to Sign In</span>
            <ArrowLeft class="w-4 h-4 rotate-180" />
          </button>
        </div>

        <p v-if="step !== 3" class="text-center text-xs text-slate-500 dark:text-slate-400">
          Remember your password?
          <router-link to="/login" class="font-bold text-[#0b1329] dark:text-cyan-400 hover:underline">Back to Sign In</router-link>
        </p>

      </div>

      <div class="text-center text-[11px] text-slate-400">
        © 2026 Job Search. Protected by reCAPTCHA and Subject to Terms & Privacy.
      </div>
    </div>

  </div>
</template>

