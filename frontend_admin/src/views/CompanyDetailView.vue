<script setup>
import { computed } from 'vue'
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
  Mail,
  Phone,
  Layers,
  CircleDot,
} from 'lucide-vue-next'
import { sampleCompanies } from '../data/companies'
import StatusBadge from '../components/StatusBadge.vue'

const route = useRoute()
const router = useRouter()

const company = computed(() =>
  sampleCompanies.find((c) => c.id === Number(route.params.id)),
)

const stats = computed(() => [
  {
    label: 'Active Openings',
    value: company.value?.active_openings ?? 0,
    icon: Briefcase,
    iconClass: 'bg-blue-500/15 text-blue-400',
  },
  {
    label: 'Team Size',
    value: company.value?.company_size ?? '—',
    icon: Users,
    iconClass: 'bg-indigo-500/15 text-indigo-400',
  },
  {
    label: 'Founded',
    value: company.value?.founded_year ?? '—',
    icon: CalendarDays,
    iconClass: 'bg-amber-500/15 text-amber-400',
  },
  {
    label: 'Employees',
    value: `${company.value?.company_size ?? '—'}`,
    icon: Layers,
    iconClass: 'bg-emerald-500/15 text-emerald-400',
  },
])
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8 max-w-5xl mx-auto" v-if="company">
    <button
      class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-100 mb-6 transition-colors group"
      @click="router.push('/companies')"
    >
      <ArrowLeft class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" />
      Back to Companies
    </button>

    <!-- ===== Header / Profile Card ===== -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
      <div
        class="h-36 sm:h-44 relative"
        :style="{ background: company.coverGradient }"
      >
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
      </div>

      <div class="px-5 sm:px-8 py-7 sm:py-8">
        <!-- Identity header (sits cleanly below the banner) -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6">
          <div
            class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-3xl sm:text-4xl font-black text-white overflow-hidden shrink-0 shadow-lg"
            :style="{ background: company.logoGradient }"
          >
            {{ company.name.charAt(0) }}
          </div>

          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2.5">
              <h2 class="text-2xl sm:text-3xl font-bold text-slate-50 truncate">{{ company.name }}</h2>
              <StatusBadge :value="company.status" />
              <span
                v-if="company.is_verified"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30"
              >
                <BadgeCheck class="w-3.5 h-3.5" /> Verified
              </span>
            </div>
            <p class="text-blue-400 font-medium mt-1.5">{{ company.industry }}</p>
            <p class="text-sm text-slate-400 mt-1 inline-flex items-center gap-1.5">
              <MapPin class="w-3.5 h-3.5" />
              {{ company.city }}, {{ company.country }}
            </p>
          </div>
        </div>

        <!-- Description -->
        <p class="mt-7 text-slate-300 leading-relaxed max-w-3xl text-[15px]">
          {{ company.description }}
        </p>

        <!-- Quick contact / facts -->
        <div class="mt-7 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
          <a
            :href="company.website"
            target="_blank"
            rel="noopener"
            class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-800 hover:border-blue-600/60 hover:bg-slate-800 transition-colors"
          >
            <Globe class="w-4 h-4 text-blue-400 shrink-0" />
            <span class="text-slate-300 truncate">{{ company.website }}</span>
          </a>
          <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-800">
            <Mail class="w-4 h-4 text-slate-500 shrink-0" />
            <span class="text-slate-300 truncate">{{ company.email || 'Not provided' }}</span>
          </div>
          <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-800">
            <Phone class="w-4 h-4 text-slate-500 shrink-0" />
            <span class="text-slate-300 truncate">{{ company.phone || 'Not provided' }}</span>
          </div>
          <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-800">
            <Building2 class="w-4 h-4 text-slate-500 shrink-0" />
            <span class="text-slate-300 truncate">{{ company.company_size }} employees</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== Stats Row ===== -->
    <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="flex items-center gap-4 p-5 rounded-2xl bg-slate-900 border border-slate-800"
      >
        <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" :class="stat.iconClass">
          <component :is="stat.icon" class="w-5 h-5" />
        </div>
        <div class="min-w-0">
          <p class="text-2xl font-bold text-slate-50 truncate">{{ stat.value }}</p>
          <p class="text-xs text-slate-400 mt-0.5 truncate">{{ stat.label }}</p>
        </div>
      </div>
    </div>

    <!-- ===== Active Openings Section ===== -->
    <div class="mt-6 bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-lg font-bold text-slate-50 flex items-center gap-2.5">
            <Briefcase class="w-5 h-5 text-blue-400" />
            Active Openings
          </h3>
          <p class="text-sm text-slate-400 mt-1">
            Current job openings listed for this company.
          </p>
        </div>
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold">
          <CircleDot class="w-4 h-4" />
          {{ company.active_openings }} Openings
        </span>
      </div>

      <div v-if="company.active_openings > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="n in company.active_openings"
          :key="n"
          class="group bg-slate-800/50 border border-slate-800 rounded-xl p-5 hover:border-blue-600/50 transition-colors"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center shrink-0">
              <Briefcase class="w-4 h-4 text-blue-400" />
            </div>
            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">#{{ n }}</span>
          </div>
          <p class="mt-4 text-sm font-semibold text-slate-200">
            Open Position {{ n }}
          </p>
          <p class="mt-1 text-xs text-slate-400">
            {{ company.industry }}
          </p>
          <div class="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
            <span>Apply now</span>
            <span class="text-blue-400 group-hover:translate-x-0.5 transition-transform inline-flex items-center gap-1">
              <CircleDot class="w-3 h-3" /> Active
            </span>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-12 text-slate-500 bg-slate-800/30 rounded-xl border border-dashed border-slate-700">
        <CircleDot class="w-10 h-10 mx-auto mb-3 opacity-50" />
        <p class="font-medium text-slate-300">No active openings</p>
        <p class="text-sm mt-1">There are currently no job openings for this company.</p>
      </div>
    </div>
  </div>

  <div v-else class="p-8 text-center text-slate-400">
    Company not found.
  </div>
</template>
