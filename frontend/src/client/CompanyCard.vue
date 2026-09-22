<script setup>
import { computed } from 'vue'
import {
  MapPin,
  Users,
  CalendarDays,
  Globe,
  Briefcase,
  ArrowRight,
  BadgeCheck,
  Pencil,
  ShieldCheck,
  Trash2,
} from 'lucide-vue-next'
import { gradientSeed } from '../data/companies'
import { resolveMediaUrl } from '../utils/media'

const props = defineProps({
  company: {
    type: Object,
    required: true,
  },
  showStatus: { type: Boolean, default: true },
  showView: { type: Boolean, default: true },
})

const emit = defineEmits(['edit', 'delete', 'status'])

const logoUrl = computed(() => resolveMediaUrl(props.company.logo))
const coverUrl = computed(() => resolveMediaUrl(props.company.cover_image))

const coverBackground = () =>
  coverUrl.value ? '' : gradientSeed[(Number(props.company.id) || 0) % gradientSeed.length]

const logoBackground = () =>
  logoUrl.value ? '' : gradientSeed[(Number(props.company.id) || 0) % gradientSeed.length]
</script>

<template>
  <article
    class="group bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden hover:border-blue-600/60 hover:shadow-lg hover:shadow-blue-900/20 transition-all duration-300 flex flex-col"
  >
    <!-- Banner cover image -->
    <div class="relative h-28 overflow-hidden bg-slate-100 dark:bg-slate-800">
      <img
        v-if="coverUrl"
        :src="coverUrl"
        :alt="`${company.name} cover`"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
      />
      <div
        v-else
        class="w-full h-full"
        :style="{ background: coverBackground() }"
      ></div>

      <div
        v-if="company.is_verified"
        class="absolute top-3 right-3 inline-flex items-center gap-1.5 rounded-full bg-blue-600/90 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white shadow-md border border-white/20"
      >
        <BadgeCheck class="w-3.5 h-3.5 text-amber-300" />
        Verified Employer
      </div>
    </div>

    <!-- Company logo overlapping banner + body -->
    <div class="flex justify-center">
      <div
        class="relative -mt-7 w-14 h-14 rounded-xl bg-white dark:bg-slate-800 ring-4 ring-white dark:ring-slate-900 overflow-hidden shadow-lg flex items-center justify-center"
      >
        <img
          v-if="logoUrl"
          :src="logoUrl"
          :alt="company.name"
          class="w-full h-full object-cover"
        />
        <div
          v-else
          class="w-full h-full flex items-center justify-center text-lg font-black text-white"
          :style="{ background: logoBackground() }"
        >
          {{ (company.name || 'C').charAt(0) }}
        </div>
      </div>
    </div>

    <!-- Body -->
    <div class="px-5 pb-4 flex flex-col flex-1 text-center">
      <!-- Company name + verification checkmark -->
      <h3 class="mt-2.5 text-lg font-bold text-slate-900 dark:text-slate-100 inline-flex items-center justify-center gap-1.5">
        <span class="notranslate truncate" translate="no">{{ company.name }}</span>
        <BadgeCheck v-if="company.is_verified" class="w-4.5 h-4.5 text-emerald-600 dark:text-emerald-400 shrink-0" />
      </h3>

      <!-- Industry category -->
      <p class="notranslate text-sm text-blue-600 dark:text-blue-400 mt-0.5" translate="no">{{ company.industry }}</p>

      <!-- Description snippet -->
      <p class="mt-3 text-sm text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-2">
        <span v-if="company.description" class="notranslate" translate="no">{{ company.description }}</span>
        <span v-else>No company description added yet.</span>
      </p>

      <!-- Two-column info grid -->
      <div class="mt-4 grid grid-cols-2 gap-x-3 gap-y-2.5 text-sm text-left">
        <p class="flex items-center gap-2 text-slate-700 dark:text-slate-300 min-w-0">
          <MapPin class="w-4 h-4 text-slate-500 shrink-0" />
          <span class="notranslate truncate" translate="no">{{ company.city || '—' }}{{ company.city && company.country ? ', ' : '' }}{{ company.country || '' }}</span>
        </p>
        <p class="flex items-center gap-2 text-slate-700 dark:text-slate-300 min-w-0">
          <Users class="w-4 h-4 text-slate-500 shrink-0" />
          <span class="notranslate truncate" translate="no">{{ company.company_size || '—' }}</span>
        </p>
        <p class="flex items-center gap-2 text-slate-700 dark:text-slate-300 min-w-0">
          <CalendarDays class="w-4 h-4 text-slate-500 shrink-0" />
          <span class="truncate"><span>Founded </span><span class="notranslate" translate="no">{{ company.founded_year || '—' }}</span></span>
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

    <div class="px-5 py-3.5 border-t border-slate-200 dark:border-slate-800">
      <div class="flex items-center justify-between gap-3">
        <span class="inline-flex items-center gap-1.5 rounded-xl bg-blue-50 dark:bg-blue-950/60 px-3 py-1.5 text-xs font-bold text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/50">
          <Briefcase class="w-3.5 h-3.5" />
          {{ company.open_jobs_count || 0 }} Active Openings
        </span>
        <router-link
          v-if="showView"
          :to="`/admin-companies/${company.id}`"
          class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors group/link"
        >
          <span>View</span>
          <ArrowRight class="w-4 h-4 transition-transform group-hover/link:translate-x-0.5" />
        </router-link>
      </div>

      <div class="mt-3 flex items-center justify-end gap-1.5">
        <button class="p-2 rounded-lg text-amber-600 hover:bg-amber-500/10" title="Edit company" @click="emit('edit', company)"><Pencil class="w-4 h-4" /></button>
        <button v-if="showStatus" class="p-2 rounded-lg text-blue-600 hover:bg-blue-500/10" title="Approve or suspend company" @click="emit('status', company)"><ShieldCheck class="w-4 h-4" /></button>
        <button class="p-2 rounded-lg text-rose-600 hover:bg-rose-500/10" title="Delete company" @click="emit('delete', company)"><Trash2 class="w-4 h-4" /></button>
      </div>
    </div>
  </article>
</template>