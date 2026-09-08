<script setup>
import {
  MapPin,
  Users,
  CalendarDays,
  Globe,
  Briefcase,
  ArrowRight,
  BadgeCheck,
} from 'lucide-vue-next'

const props = defineProps({
  company: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <article
    class="group bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden hover:border-blue-600/60 hover:shadow-lg hover:shadow-blue-900/20 transition-all duration-300 flex flex-col"
  >
    <!-- Banner cover image -->
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

      <!-- VERIFIED EMPLOYER badge (top right) -->
      <span
        v-if="company.is_verified"
        class="absolute top-3 right-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-emerald-500 text-white shadow"
      >
        <BadgeCheck class="w-3.5 h-3.5" />
        Verified Employer
      </span>
    </div>

    <!-- Company logo overlapping banner + body -->
    <div class="flex justify-center">
      <div
        class="relative -mt-7 w-14 h-14 rounded-xl bg-slate-100 dark:bg-slate-800 ring-4 ring-white dark:ring-slate-900 overflow-hidden shadow-lg"
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
    <div class="px-5 pb-4 flex flex-col flex-1 text-center">
      <!-- Company name + verification checkmark -->
      <h3 class="mt-2.5 text-lg font-bold text-slate-900 dark:text-slate-100 inline-flex items-center justify-center gap-1.5">
        <span class="truncate">{{ company.name }}</span>
        <BadgeCheck v-if="company.is_verified" class="w-4.5 h-4.5 text-emerald-600 dark:text-emerald-400 shrink-0" />
      </h3>

      <!-- Industry category -->
      <p class="text-sm text-blue-600 dark:text-blue-400 mt-0.5">{{ company.industry }}</p>

      <!-- Description snippet -->
      <p class="mt-3 text-sm text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-2">
        {{ company.description }}
      </p>

      <!-- Two-column info grid -->
      <div class="mt-4 grid grid-cols-2 gap-x-3 gap-y-2.5 text-sm text-left">
        <p class="flex items-center gap-2 text-slate-700 dark:text-slate-300 min-w-0">
          <MapPin class="w-4 h-4 text-slate-500 shrink-0" />
          <span class="truncate">{{ company.city }}, {{ company.country }}</span>
        </p>
        <p class="flex items-center gap-2 text-slate-700 dark:text-slate-300 min-w-0">
          <Users class="w-4 h-4 text-slate-500 shrink-0" />
          <span class="truncate">{{ company.company_size }}</span>
        </p>
        <p class="flex items-center gap-2 text-slate-700 dark:text-slate-300 min-w-0">
          <CalendarDays class="w-4 h-4 text-slate-500 shrink-0" />
          <span class="truncate">Founded {{ company.founded_year }}</span>
        </p>
        <a
          v-if="company.website"
          :href="company.website"
          target="_blank"
          rel="noopener"
          class="flex items-center gap-2 text-blue-600 dark:text-blue-400 min-w-0 group/link"
        >
          <Globe class="w-4 h-4 shrink-0" />
          <span class="truncate underline underline-offset-2 decoration-blue-500/40 group-hover/link:text-blue-700 dark:group-hover/link:text-blue-300">Website</span>
        </a>
        <p v-else class="flex items-center gap-2 text-slate-400 min-w-0">
          <Globe class="w-4 h-4 shrink-0" />
          <span class="truncate">Website</span>
        </p>
      </div>
    </div>

    <!-- Footer -->
    <div class="mt-auto px-5 py-3.5 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3">
      <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300">
        <Briefcase class="w-4 h-4 text-blue-600 dark:text-blue-400" />
        {{ company.active_openings }} Openings
      </span>
      <router-link
        :to="`/companies/${company.id}`"
        class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors group/link"
      >
        <span>View Company Profile</span>
        <ArrowRight class="w-4 h-4 transition-transform group-hover/link:translate-x-0.5" />
      </router-link>
    </div>
  </article>
</template>