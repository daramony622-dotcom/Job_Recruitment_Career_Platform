<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  Building2,
  MapPin,
  Users,
  CalendarDays,
  Globe,
  ArrowLeft,
  Briefcase,
  BadgeCheck,
  ShieldCheck,
  CircleDot,
} from 'lucide-vue-next'
import { adminApi } from '../api'
import { gradientSeed } from '../data/companies'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref('')
const company = ref(null)

async function fetchCompany() {
  loading.value = true
  error.value = ''

  try {
    const { data } = await adminApi.showCompany(route.params.id)
    company.value = data.data || data
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to load company details.'
    company.value = null
  } finally {
    loading.value = false
  }
}

const statusMeta = {
  approved: { dot: 'bg-emerald-500', text: 'text-emerald-600 dark:text-emerald-400' },
  pending: { dot: 'bg-amber-500', text: 'text-amber-600 dark:text-amber-400' },
  rejected: { dot: 'bg-rose-500', text: 'text-rose-600 dark:text-rose-400' },
  suspended: { dot: 'bg-rose-500', text: 'text-rose-600 dark:text-rose-400' },
}

const status = computed(() => statusMeta[(company.value?.status || '').toLowerCase()] || {
  dot: 'bg-slate-500',
  text: 'text-slate-600 dark:text-slate-400',
})

const detailCards = computed(() => [
  {
    label: 'Company Size',
    value: company.value?.company_size || '—',
    sub: 'Employees',
    icon: Users,
    iconClass: 'bg-indigo-500/15 text-indigo-400',
  },
  {
    label: 'Founded Year',
    value: company.value?.founded_year || '—',
    sub: 'Established',
    icon: CalendarDays,
    iconClass: 'bg-amber-500/15 text-amber-400',
  },
  {
    label: 'Open Positions',
    value: company.value?.jobs?.length || 0,
    sub: (company.value?.jobs?.length || 0) > 0 ? 'Roles hiring now' : 'No openings',
    pill: (company.value?.jobs?.length || 0) > 0,
    icon: Briefcase,
    iconClass: 'bg-blue-500/15 text-blue-400',
  },
  {
    label: 'Status',
    value: company.value?.is_verified ? 'Approved' : company.value?.status || 'Pending',
    sub: company.value?.is_verified ? 'Verified Platform Employer' : 'Awaiting verification',
    icon: ShieldCheck,
    iconClass: company.value?.is_verified
      ? 'bg-emerald-500/15 text-emerald-400'
      : 'bg-slate-500/15 text-slate-400',
  },
])

const coverBackground = computed(() => {
  if (company.value?.cover_image) return ''
  const seed = Number(company.value?.id || 0)
  return gradientSeed[seed % gradientSeed.length]
})

const logoBackground = computed(() => {
  const seed = Number(company.value?.id || 0)
  return company.value?.logo ? '' : gradientSeed[seed % gradientSeed.length]
})

onMounted(fetchCompany)
</script>

<template>
  <div v-if="loading" class="p-4 sm:p-6 lg:p-8 max-w-5xl mx-auto text-center text-slate-600 dark:text-slate-400">
    Loading company details...
  </div>

  <div v-else-if="error" class="p-4 sm:p-6 lg:p-8 max-w-5xl mx-auto">
    <p class="text-sm text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">
      {{ error }}
    </p>
  </div>

  <div class="p-4 sm:p-6 lg:p-8 max-w-5xl mx-auto" v-else-if="company">
    <!-- Back navigation -->
    <button
      class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 mb-6 transition-colors group"
      @click="router.push('/companies')"
    >
      <ArrowLeft class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" />
      Back to Companies Directory
    </button>

    <!-- ===== Cover banner (real cover photo + dark overlay) ===== -->
    <div class="relative h-52 sm:h-64 lg:h-72 rounded-3xl overflow-hidden shadow-md">
      <img
        v-if="company.cover_image"
        :src="company.cover_image"
        :alt="`${company.name} cover`"
        class="w-full h-full object-cover"
      />
      <div
        v-else
        class="w-full h-full"
        :style="{ background: coverBackground }"
      ></div>
      <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/25 to-black/5"></div>
      <!-- Verified Company Profile badge -->
      <div class="absolute top-4 right-4 z-20">
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold shadow-lg backdrop-blur-sm">
          <ShieldCheck class="w-4 h-4" />
          VERIFIED COMPANY PROFILE
        </span>
      </div>
    </div>

    <!-- ===== Floating profile card (overlaps banner) ===== -->
    <div class="relative -mt-20 sm:-mt-24 z-10 mx-4 sm:mx-8">
      <div
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-xl p-6 sm:p-8"
      >
        <div class="flex flex-col sm:flex-row sm:items-center gap-5 sm:gap-6">
          <!-- Company logo -->
          <div
            class="w-20 h-20 sm:w-24 sm:h-24 shrink-0 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 overflow-hidden shadow-lg flex items-center justify-center text-3xl sm:text-4xl font-black text-white"
          >
            <img
              v-if="company.logo"
              :src="company.logo"
              :alt="company.name"
              class="w-full h-full object-cover"
            />
            <template v-else>
              <div class="w-full h-full flex items-center justify-center" :style="{ background: logoBackground }">
                {{ company.name.charAt(0) }}
              </div>
            </template>
          </div>

          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2.5">
              <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white truncate">
                {{ company.name }}
              </h2>
              <span
                v-if="company.is_verified"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30"
              >
                <BadgeCheck class="w-3.5 h-3.5" /> Verified Platform Employer
              </span>
            </div>
            <p class="text-blue-600 dark:text-blue-400 font-medium mt-1.5">{{ company.industry }}</p>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1 inline-flex items-center gap-1.5">
              <MapPin class="w-3.5 h-3.5" />
              {{ company.city || '—' }}{{ company.city && company.country ? ', ' : '' }}{{ company.country || '' }}
            </p>
          </div>

          <!-- Website CTA -->
          <a
            :href="company.website"
            target="_blank"
            rel="noopener"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors shrink-0"
            v-if="company.website"
          >
            <Globe class="w-4 h-4" />
            Visit Company Website
          </a>
        </div>

        <!-- Description -->
        <p class="mt-6 text-slate-700 dark:text-slate-300 leading-relaxed text-[15px] max-w-3xl">
          {{ company.description || 'No company description added yet.' }}
        </p>
      </div>
    </div>

    <!-- ===== Metric cards (4-column grid) ===== -->
    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="card in detailCards"
        :key="card.label"
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 flex items-start gap-3.5 hover:border-blue-600/60 hover:shadow-lg hover:shadow-blue-900/20 transition-all"
      >
        <div
          class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
          :class="card.iconClass"
        >
          <component :is="card.icon" class="w-5 h-5" />
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate">
            {{ card.label }}
          </p>
          <div class="flex items-center gap-2 mt-1 flex-wrap">
            <p class="text-xl font-bold text-slate-900 dark:text-slate-100 truncate">{{ card.value }}</p>
            <span
              v-if="card.pill"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30"
            >
              <CircleDot class="w-3 h-3" /> Hiring now
            </span>
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">{{ card.sub }}</p>
        </div>
      </div>
    </div>

    <!-- ===== Status verification strip ===== -->
    <div
      class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 dark:border-slate-800 px-5 py-4"
      :class="company.is_verified ? 'bg-emerald-500/5' : 'bg-amber-500/5'"
    >
      <div class="flex items-center gap-3">
        <span
          class="w-10 h-10 rounded-xl flex items-center justify-center"
          :class="company.is_verified ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/15 text-amber-600 dark:text-amber-400'"
        >
          <ShieldCheck class="w-5 h-5" />
        </span>
        <div>
          <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">
            {{ company.is_verified ? 'Approved Platform Employer' : 'Verification pending' }}
          </p>
          <p class="text-xs text-slate-600 dark:text-slate-400">
            {{ company.is_verified ? 'This company is verified by the recruitment platform.' : 'This company profile has not been verified yet.' }}
          </p>
        </div>
      </div>
      <span
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold"
        :class="status.text"
      >
        <span class="w-2 h-2 rounded-full" :class="status.dot"></span>
        {{ (company.status || 'pending').toString().replace(/_/g, ' ') }}
      </span>
    </div>

    <!-- ===== Active Openings Section ===== -->
    <div class="mt-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2.5">
            <Briefcase class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            Active Openings
          </h3>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Current job openings listed for this company.
          </p>
        </div>
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold">
          <Building2 class="w-4 h-4" />
          {{ company.jobs?.length || 0 }} Openings
        </span>
      </div>

      <div v-if="(company.jobs?.length || 0) > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="n in (company.jobs?.length || 0)"
          :key="n"
          class="group bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-xl p-5 hover:border-blue-600/50 transition-colors"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 flex items-center justify-center shrink-0">
              <Briefcase class="w-4 h-4 text-blue-600 dark:text-blue-400" />
            </div>
            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">#{{ n }}</span>
          </div>
          <p class="mt-4 text-sm font-semibold text-slate-800 dark:text-slate-200">
            Open Position {{ n }}
          </p>
          <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
            {{ company.industry }}
          </p>
          <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs text-slate-600 dark:text-slate-400">
            <span>Apply now</span>
            <span class="text-blue-600 dark:text-blue-400 group-hover:translate-x-0.5 transition-transform inline-flex items-center gap-1">
              <CircleDot class="w-3 h-3" /> Active
            </span>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-12 text-slate-500 bg-slate-100 dark:bg-slate-800/30 rounded-xl border border-dashed border-slate-300 dark:border-slate-700">
        <Building2 class="w-10 h-10 mx-auto mb-3 opacity-50" />
        <p class="font-medium text-slate-700 dark:text-slate-300">No active openings</p>
        <p class="text-sm mt-1">There are currently no job openings for this company.</p>
      </div>
    </div>
  </div>

  <div v-else class="p-4 sm:p-6 lg:p-8 max-w-5xl mx-auto text-center text-slate-600 dark:text-slate-400">
    Company not found.
  </div>
</template>