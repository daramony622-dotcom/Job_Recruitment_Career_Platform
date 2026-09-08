<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  ArrowLeft,
  Briefcase,
  MapPin,
  CalendarDays,
  Banknote,
  Users,
  Building2,
  Layers,
  BadgeCheck,
  Clock,
} from 'lucide-vue-next'
import { adminApi } from '../api'
import StatusBadge from '../components/StatusBadge.vue'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref('')
const job = ref(null)

async function fetchJobPost() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await adminApi.showJobPost(route.params.id)
    job.value = data.data || data
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to load job post.'
  } finally {
    loading.value = false
  }
}

function formatLabel(value, fallback = '—') {
  if (!value) return fallback
  return String(value).replace(/_/g, ' ')
}

onMounted(fetchJobPost)
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8 max-w-5xl mx-auto">
    <button
      class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 mb-6 transition-colors group"
      @click="router.push('/job-posts')"
    >
      <ArrowLeft class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" />
      Back to Job Posts
    </button>

    <p v-if="error" class="mb-4 text-sm text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">
      {{ error }}
    </p>

    <div v-if="loading" class="text-center py-20 text-slate-600 dark:text-slate-400">Loading job post...</div>

    <template v-else-if="job">
      <!-- Header -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-5 sm:px-8 py-7 sm:py-8">
        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
          <div
            class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 flex items-center justify-center text-3xl sm:text-4xl font-black text-white overflow-hidden shrink-0 shadow-lg"
          >
            <Briefcase class="w-10 h-10 text-blue-600 dark:text-blue-400" />
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2.5">
              <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-slate-50 truncate">{{ job.title }}</h2>
              <StatusBadge :value="job.status" />
              <span
                v-if="job.is_featured"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30"
              >
                <BadgeCheck class="w-3.5 h-3.5" /> Featured
              </span>
            </div>
            <p class="text-blue-600 dark:text-blue-400 font-medium mt-1.5">{{ job.company?.name || '—' }}</p>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-600 dark:text-slate-400 mt-1">
              <span class="inline-flex items-center gap-1.5">
                <MapPin class="w-3.5 h-3.5" />
                {{ job.city || job.location || '—' }}
              </span>
              <span class="inline-flex items-center gap-1.5 capitalize">
                <Clock class="w-3.5 h-3.5" />
                {{ formatLabel(job.job_type) }} · {{ formatLabel(job.work_mode) }}
              </span>
            </div>
          </div>
        </div>

        <div class="mt-7 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
          <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800">
            <Building2 class="w-4 h-4 text-slate-500 shrink-0" />
            <span class="text-slate-700 dark:text-slate-300 truncate">{{ job.category?.name || '—' }}</span>
          </div>
          <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800">
            <Users class="w-4 h-4 text-slate-500 shrink-0" />
            <span class="text-slate-700 dark:text-slate-300 truncate">{{ job.vacancies }} openings</span>
          </div>
          <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800">
            <Banknote class="w-4 h-4 text-slate-500 shrink-0" />
            <span class="text-slate-700 dark:text-slate-300 truncate">
              <template v-if="job.salary_min">
                {{ job.salary_currency }} {{ job.salary_min }}<span v-if="job.salary_max"> – {{ job.salary_max }}</span> / {{ formatLabel(job.salary_period) }}
              </template>
              <template v-else>N/A</template>
            </span>
          </div>
          <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800">
            <CalendarDays class="w-4 h-4 text-slate-500 shrink-0" />
            <span class="text-slate-700 dark:text-slate-300 truncate">
              <template v-if="job.deadline">Deadline {{ new Date(job.deadline).toLocaleDateString() }}</template>
              <template v-else>No deadline</template>
            </span>
          </div>
        </div>
      </div>

      <!-- Description -->
      <div class="mt-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2.5 mb-4">
          <Layers class="w-5 h-5 text-blue-600 dark:text-blue-400" />
          Description
        </h3>
        <p class="text-slate-700 dark:text-slate-300 leading-relaxed text-[15px] whitespace-pre-line">
          {{ job.description || 'No description available.' }}
        </p>
      </div>

      <!-- Requirements -->
      <div v-if="job.requirements" class="mt-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2.5 mb-4">
          <BadgeCheck class="w-5 h-5 text-blue-600 dark:text-blue-400" />
          Requirements
        </h3>
        <p class="text-slate-700 dark:text-slate-300 leading-relaxed text-[15px] whitespace-pre-line">{{ job.requirements }}</p>
      </div>

      <!-- Benefits -->
      <div v-if="job.benefits" class="mt-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2.5 mb-4">
          <Banknote class="w-5 h-5 text-blue-600 dark:text-blue-400" />
          Benefits
        </h3>
        <p class="text-slate-700 dark:text-slate-300 leading-relaxed text-[15px] whitespace-pre-line">{{ job.benefits }}</p>
      </div>

      <!-- Skills -->
      <div v-if="job.skills && job.skills.length" class="mt-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2.5 mb-4">
          <Briefcase class="w-5 h-5 text-blue-600 dark:text-blue-400" />
          Skills
        </h3>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="skill in job.skills"
            :key="skill.id"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300"
          >
            {{ skill.name }}
          </span>
        </div>
      </div>
    </template>
  </div>
</template>
