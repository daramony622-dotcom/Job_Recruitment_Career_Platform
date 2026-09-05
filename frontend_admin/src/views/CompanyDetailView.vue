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
} from 'lucide-vue-next'
import { sampleCompanies } from '../data/companies'

const route = useRoute()
const router = useRouter()

const company = computed(() =>
  sampleCompanies.find((c) => c.id === Number(route.params.id)),
)
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8" v-if="company">
    <button
      class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-100 mb-5 transition-colors"
      @click="router.push('/companies')"
    >
      <ArrowLeft class="w-4 h-4" />
      Back to Companies
    </button>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
      <div class="h-40" :style="{ background: company.coverGradient }"></div>

      <div class="px-6 pb-6 -mt-9">
        <div class="flex flex-col sm:flex-row sm:items-end gap-4">
          <div
            class="w-18 h-18 sm:w-20 sm:h-20 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-3xl font-black text-white overflow-hidden"
            :style="{ background: company.logoGradient }"
          >
            {{ company.name.charAt(0) }}
          </div>
          <div class="sm:pb-1">
            <div class="flex items-center gap-2">
              <h2 class="text-2xl font-bold text-slate-100">{{ company.name }}</h2>
              <span
                v-if="company.is_verified"
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30"
              >
                <BadgeCheck class="w-3.5 h-3.5" /> Verified
              </span>
            </div>
            <p class="text-blue-400">{{ company.industry }}</p>
          </div>
        </div>

        <p class="mt-5 text-slate-300 leading-relaxed max-w-3xl">{{ company.description }}</p>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-slate-300">
          <p class="flex items-center gap-2.5"><MapPin class="w-4 h-4 text-slate-500" /> {{ company.city }}, {{ company.country }}</p>
          <p class="flex items-center gap-2.5"><Users class="w-4 h-4 text-slate-500" /> {{ company.company_size }} employees</p>
          <p class="flex items-center gap-2.5"><CalendarDays class="w-4 h-4 text-slate-500" /> Founded {{ company.founded_year }}</p>
          <a :href="company.website" target="_blank" rel="noopener" class="flex items-center gap-2.5 text-blue-400 hover:underline">
            <Globe class="w-4 h-4 text-slate-500" /> {{ company.website }}
          </a>
        </div>

        <div class="mt-6 pt-5 border-t border-slate-800 flex flex-wrap gap-3">
          <span class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold">
            <Briefcase class="w-4 h-4" /> {{ company.active_openings }} Active Openings
          </span>
          <span
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-700 text-slate-300 text-sm font-semibold"
          >
            <Building2 class="w-4 h-4" /> {{ company.status }}
          </span>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="p-8 text-center text-slate-400">
    Company not found.
  </div>
</template>
