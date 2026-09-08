<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  ArrowLeft,
  Mail,
  Phone,
  MapPin,
  Briefcase,
  CalendarDays,
  Building2,
  Globe,
  BadgeCheck,
  CircleDot,
} from 'lucide-vue-next'
import { adminApi } from '../api'
import StatusBadge from '../components/StatusBadge.vue'
import { profileImage } from '../utils/media'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref('')
const candidate = ref(null)
const candidateAvatar = computed(() => profileImage(candidate.value))

async function fetchCandidate() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await adminApi.showCandidate(route.params.id)
    candidate.value = data.data || data
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to load candidate.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchCandidate)
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8 max-w-5xl mx-auto">
    <button
      class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 mb-6 transition-colors group"
      @click="router.push('/candidates')"
    >
      <ArrowLeft class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" />
      Back to Candidates
    </button>

    <p v-if="error" class="mb-4 text-sm text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">
      {{ error }}
    </p>

    <div v-if="loading" class="text-center py-20 text-slate-600 dark:text-slate-400">Loading candidate...</div>

    <template v-else-if="candidate">
      <!-- Header -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-5 sm:px-8 py-7 sm:py-8">
        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
          <div
            class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 flex items-center justify-center text-3xl sm:text-4xl font-black text-white overflow-hidden shrink-0 shadow-lg"
          >
            <img v-if="candidateAvatar" :src="candidateAvatar" :alt="candidate.name || 'Profile'" class="w-full h-full object-cover" />
            <template v-else>{{ (candidate.name || '?').charAt(0).toUpperCase() }}</template>
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2.5">
              <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-slate-50 truncate">{{ candidate.name }}</h2>
              <StatusBadge :value="candidate.is_active ? 'active' : 'inactive'" />
              <span
                v-if="candidate.profile?.is_open_to_work"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-500/15 text-blue-600 dark:text-blue-400 border border-blue-500/30"
              >
                <CircleDot class="w-3.5 h-3.5" /> Open to Work
              </span>
            </div>
            <p class="text-blue-600 dark:text-blue-400 font-medium mt-1.5">{{ candidate.profile?.headline || 'Candidate' }}</p>
          </div>
        </div>

        <div class="mt-7 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
          <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800">
            <Mail class="w-4 h-4 text-slate-500 shrink-0" />
            <span class="text-slate-700 dark:text-slate-300 truncate">{{ candidate.email }}</span>
          </div>
          <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800">
            <Phone class="w-4 h-4 text-slate-500 shrink-0" />
            <span class="text-slate-700 dark:text-slate-300 truncate">{{ candidate.profile?.phone || candidate.phone || 'Not provided' }}</span>
          </div>
          <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800">
            <MapPin class="w-4 h-4 text-slate-500 shrink-0" />
            <span class="text-slate-700 dark:text-slate-300 truncate">{{ candidate.profile?.city }}{{ candidate.profile?.city && candidate.profile?.country ? ', ' : '' }}{{ candidate.profile?.country || '—' }}</span>
          </div>
          <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800">
            <CalendarDays class="w-4 h-4 text-slate-500 shrink-0" />
            <span class="text-slate-700 dark:text-slate-300 truncate">Joined {{ new Date(candidate.created_at).toLocaleDateString() }}</span>
          </div>
        </div>
      </div>

      <!-- About -->
      <div class="mt-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2.5 mb-4">
          <Briefcase class="w-5 h-5 text-blue-600 dark:text-blue-400" />
          About
        </h3>
        <p class="text-slate-700 dark:text-slate-300 leading-relaxed text-[15px]">
          {{ candidate.profile?.bio || 'No profile description available.' }}
        </p>
      </div>

      <!-- Skills -->
      <div class="mt-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2.5 mb-4">
          <BadgeCheck class="w-5 h-5 text-blue-600 dark:text-blue-400" />
          Skills
        </h3>
        <div v-if="candidate.skills && candidate.skills.length" class="flex flex-wrap gap-2">
          <span
            v-for="skill in candidate.skills"
            :key="skill.id"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300"
          >
            {{ skill.name }}
          </span>
        </div>
        <p v-else class="text-slate-500 text-sm">No skills listed.</p>
      </div>

      <!-- Company -->
      <div v-if="candidate.company" class="mt-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2.5 mb-4">
          <Building2 class="w-5 h-5 text-blue-600 dark:text-blue-400" />
          Company
        </h3>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 flex items-center justify-center font-black text-white">
            {{ candidate.company.name?.charAt(0) }}
          </div>
          <div>
            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ candidate.company.name }}</p>
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ candidate.company.industry || '' }}</p>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
