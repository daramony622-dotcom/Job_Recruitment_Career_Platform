<script setup>
import { MapPin, Users, CalendarDays, Globe, Briefcase, ArrowRight, BadgeCheck } from 'lucide-vue-next'

const props = defineProps({
  company: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['view'])
</script>

<template>
  <article
    class="group bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden hover:border-blue-600/60 hover:shadow-lg hover:shadow-blue-900/20 transition-all duration-300 flex flex-col"
  >
    <!-- Cover image -->
    <div class="relative h-28 overflow-hidden">
      <img
        v-if="company.cover_image"
        :src="company.cover_image"
        :alt="`${company.name} cover`"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
      />
      <div
        v-else
        class="w-full h-full"
        :style="{ background: company.coverGradient }"
      ></div>

      <!-- Verified Employer badge -->
      <span
        v-if="company.is_verified"
        class="absolute top-3 left-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/90 text-white shadow"
      >
        <BadgeCheck class="w-3.5 h-3.5" />
        Verified Employer
      </span>

      <span
        class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-900/80 backdrop-blur text-slate-200"
      >
        {{ company.status }}
      </span>
    </div>

    <!-- Logo -->
    <div class="flex items-center justify-center">
      <div
        class="relative -mt-7 w-14 h-14 rounded-xl bg-slate-800 border border-slate-700 overflow-hidden shadow group-hover:border-blue-500 transition-colors"
      >
        <img
          v-if="company.logo"
          :src="company.logo"
          :alt="company.name"
          class="w-full h-full object-cover"
        />
        <div
          v-else
          class="w-full h-full flex items-center justify-center text-lg font-black text-white"
          :style="{ background: company.logoGradient }"
        >
          {{ company.name.charAt(0) }}
        </div>
      </div>
    </div>

    <!-- Body -->
    <div class="px-5 pb-5 flex flex-col flex-1">
      <h3 class="mt-3 text-lg font-bold text-slate-100 text-center">{{ company.name }}</h3>
      <p class="text-sm text-blue-400 text-center">{{ company.industry }}</p>

      <p class="mt-3 text-sm text-slate-400 leading-relaxed line-clamp-2 text-center">
        {{ company.description }}
      </p>

      <!-- Key details -->
      <div class="mt-4 space-y-2.5 text-sm text-slate-300">
        <p class="flex items-center gap-2.5">
          <MapPin class="w-4 h-4 text-slate-500 shrink-0" />
          <span>{{ company.city }}, {{ company.country }}</span>
        </p>
        <p class="flex items-center gap-2.5">
          <Users class="w-4 h-4 text-slate-500 shrink-0" />
          <span>{{ company.company_size }} employees</span>
        </p>
        <p class="flex items-center gap-2.5">
          <CalendarDays class="w-4 h-4 text-slate-500 shrink-0" />
          <span>Founded {{ company.founded_year }}</span>
        </p>
        <p class="flex items-center gap-2.5">
          <Globe class="w-4 h-4 text-slate-500 shrink-0" />
          <a :href="company.website" target="_blank" rel="noopener"
             class="text-blue-400 hover:text-blue-300 hover:underline truncate">
            {{ company.website }}
          </a>
        </p>
      </div>

      <!-- Action buttons -->
      <div class="mt-5 pt-4 border-t border-slate-800 grid grid-cols-1 gap-2.5 sm:grid-cols-2">
        <router-link
          :to="`/companies/${company.id}`"
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors"
        >
          <Briefcase class="w-4 h-4" />
          <span>Active Openings</span>
          <span class="ml-auto bg-blue-500 rounded-full px-1.5 text-xs">{{ company.active_openings }}</span>
        </router-link>

        <button
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-700 text-slate-200 text-sm font-semibold hover:border-blue-500 hover:text-blue-400 transition-colors"
          @click="emit('view', company)"
        >
          <span>View Company Profile</span>
          <ArrowRight class="w-4 h-4" />
        </button>
      </div>
    </div>
  </article>
</template>
