<script setup>
import { ref } from 'vue'
import { SlidersHorizontal, ChevronDown, RotateCcw, Check, X, MapPin, DollarSign, Clock } from 'lucide-vue-next'

const location = defineModel('location', { type: String, default: '' })
const salary = defineModel('salary', { type: String, default: '' })
const time = defineModel('time', { type: String, default: '' })

defineProps({
  totalJobs: { type: Number, required: true },
  hasActiveFilters: { type: Boolean, required: true }
})

const locations = [
  "Khan Boeung Keng Kang",
  "Khan Chamkar Mon",
  "Khan Chbar Ampov",
  "Khan Chroy Changvar",
  "Khan Dangkao",
  "Khan Daun Penh",
  "Khan Kamboul",
  "Khan Mean Chey",
  "Khan Pou Senchey",
  "Khan Prampi Makara",
  "Khan Prek Pnov",
  "Khan Russey Keo",
  "Khan Sen Sok",
  "Khan Tuol Kouk",
]

const salaryRanges = [
  '200$-500$',
  '500$-1000$',
  '1000$-1500$',
  '1500$-2000$',
  '2000$-3000$'
]

const timeRanges = [
  '1 day ago',
  '2 days ago',
  '3 days ago',
  '1 week ago',
  '2 weeks ago',
  '1 month ago'
]

const emit = defineEmits(['reset'])

const openDropdown = ref(null)

const toggleDropdown = (name) => {
  openDropdown.value = openDropdown.value === name ? null : name
}
</script>

<template>
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-3 rounded-2xl border border-slate-200/80 shadow-sm relative">
    <div class="flex flex-wrap items-center gap-2">

      <div class="flex items-center gap-2 px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 text-sm font-semibold select-none">
        <SlidersHorizontal class="w-4 h-4 text-slate-600" />
        <span>Filters</span>
      </div>

      <!-- Location Dropdown -->
      <div class="relative">
        <button 
          @click="toggleDropdown('location')"
          type="button"
          class="flex items-center justify-between gap-2 rounded-xl px-3.5 py-2 text-sm font-medium transition cursor-pointer"
          :class="location 
            ? 'bg-slate-100 text-blue-600 border border-transparent' 
            : 'bg-white border border-slate-200 hover:border-slate-300 text-slate-700'"
        >
          <div class="flex items-center gap-2 truncate">
            <MapPin v-if="location" class="w-4 h-4 text-blue-500 shrink-0" />
            <span class="truncate">{{ location || 'Location' }}</span>
          </div>
          <div class="flex items-center gap-1 shrink-0">
            <span 
              v-if="location" 
              @click.stop="location = ''"
              class="p-0.5 hover:bg-slate-200 rounded-md text-slate-800 transition"
              title="Clear location"
            >
              <X class="w-4 h-4" />
            </span>
            <ChevronDown v-else class="w-4 h-4 text-slate-400" />
          </div>
        </button>

        <div v-if="openDropdown === 'location'" class="absolute left-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-lg z-50 overflow-hidden">
          <div class="max-h-52 overflow-y-auto p-1">
            <button
              @click="location = ''; openDropdown = null"
              class="w-full text-left px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-100 text-slate-700"
            >
              Location (All)
            </button>
            <button
              v-for="loc in locations"
              :key="loc"
              @click="location = loc; openDropdown = null"
              class="w-full text-left px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-100 flex items-center justify-between"
              :class="location === loc ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-700'"
            >
              <span class="truncate">{{ loc }}</span>
              <Check v-if="location === loc" class="w-4 h-4 text-blue-600 shrink-0" />
            </button>
          </div>
        </div>
      </div>

      <!-- Salary Dropdown -->
      <div class="relative">
        <button 
          @click="toggleDropdown('salary')"
          type="button"
          class="flex items-center justify-between gap-2 rounded-xl px-3.5 py-2 text-sm font-medium transition cursor-pointer"
          :class="salary 
            ? 'bg-slate-100 text-blue-600 border border-transparent' 
            : 'bg-white border border-slate-200 hover:border-slate-300 text-slate-700'"
        >
          <div class="flex items-center gap-2 truncate">
            <DollarSign v-if="salary" class="w-4 h-4 text-blue-500 shrink-0" />
            <span class="truncate">{{ salary || 'Any salary' }}</span>
          </div>
          <div class="flex items-center gap-1 shrink-0">
            <span 
              v-if="salary" 
              @click.stop="salary = ''"
              class="p-0.5 hover:bg-slate-200 rounded-md text-slate-800 transition"
              title="Clear salary"
            >
              <X class="w-4 h-4" />
            </span>
            <ChevronDown v-else class="w-4 h-4 text-slate-400" />
          </div>
        </button>

        <div v-if="openDropdown === 'salary'" class="absolute left-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-lg z-50 overflow-hidden">
          <div class="max-h-52 overflow-y-auto p-1">
            <button
              @click="salary = ''; openDropdown = null"
              class="w-full text-left px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-100 text-slate-700"
            >
              Any salary
            </button>
            <button
              v-for="sal in salaryRanges"
              :key="sal"
              @click="salary = sal; openDropdown = null"
              class="w-full text-left px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-100 flex items-center justify-between"
              :class="salary === sal ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-700'"
            >
              <span>{{ sal }}</span>
              <Check v-if="salary === sal" class="w-4 h-4 text-blue-600 shrink-0" />
            </button>
          </div>
        </div>
      </div>

      <!-- Time Dropdown -->
      <div class="relative">
        <button 
          @click="toggleDropdown('time')"
          type="button"
          class="flex items-center justify-between gap-2 rounded-xl px-3.5 py-2 text-sm font-medium transition cursor-pointer"
          :class="time 
            ? 'bg-slate-100 text-blue-600 border border-transparent' 
            : 'bg-white border border-slate-200 hover:border-slate-300 text-slate-700'"
        >
          <div class="flex items-center gap-2 truncate">
            <Clock v-if="time" class="w-4 h-4 text-blue-500 shrink-0" />
            <span class="truncate">{{ time || 'Any time' }}</span>
          </div>
          <div class="flex items-center gap-1 shrink-0">
            <span 
              v-if="time" 
              @click.stop="time = ''"
              class="p-0.5 hover:bg-slate-200 rounded-md text-slate-800 transition"
              title="Clear time"
            >
              <X class="w-4 h-4" />
            </span>
            <ChevronDown v-else class="w-4 h-4 text-slate-400" />
          </div>
        </button>

        <div v-if="openDropdown === 'time'" class="absolute left-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-lg z-50 overflow-hidden">
          <div class="max-h-52 overflow-y-auto p-1">
            <button
              @click="time = ''; openDropdown = null"
              class="w-full text-left px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-100 text-slate-700"
            >
              Any time
            </button>
            <button
              v-for="t in timeRanges"
              :key="t"
              @click="time = t; openDropdown = null"
              class="w-full text-left px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-100 flex items-center justify-between"
              :class="time === t ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-700'"
            >
              <span>{{ t }}</span>
              <Check v-if="time === t" class="w-4 h-4 text-blue-600 shrink-0" />
            </button>
          </div>
        </div>
      </div>

      <!-- Clear All Button -->
      <button 
        v-if="hasActiveFilters"
        @click="emit('reset')"
        type="button"
        class="flex items-center gap-1.5 px-3 py-2 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl text-xs font-semibold transition active:scale-95"
      >
        <RotateCcw class="w-3.5 h-3.5" />
        <span>Clear Filters</span>
      </button>

    </div>

    <div class="text-xs font-semibold text-slate-500 self-end md:self-center pr-1">
      {{ totalJobs }} jobs found
    </div>
  </div>
</template>