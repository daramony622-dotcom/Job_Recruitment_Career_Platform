<script setup>
import { computed, ref } from 'vue'
import { Search, Building2, Plus } from 'lucide-vue-next'
import CompanyCard from '../components/CompanyCard.vue'
import { sampleCompanies, statusStyles } from '../data/companies'

const query = ref('')
const statusFilter = ref('All')
const industryFilter = ref('All')

const industries = computed(() =>
  ['All', ...new Set(sampleCompanies.map((c) => c.industry))],
)

const statuses = ['All', ...Object.keys(statusStyles)]

const filteredCompanies = computed(() => {
  return sampleCompanies.filter((c) => {
    const matchesQuery =
      query.value.trim() === '' ||
      c.name.toLowerCase().includes(query.value.toLowerCase()) ||
      c.industry.toLowerCase().includes(query.value.toLowerCase()) ||
      c.city.toLowerCase().includes(query.value.toLowerCase())
    const matchesStatus = statusFilter.value === 'All' || c.status === statusFilter.value
    const matchesIndustry = industryFilter.value === 'All' || c.industry === industryFilter.value
    return matchesQuery && matchesStatus && matchesIndustry
  })
})

function onView(company) {
  console.log('View profile:', company.name)
}
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8">
    <!-- Page header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-100 flex items-center gap-2">
          <Building2 class="w-6 h-6 text-blue-500" />
          Companies
        </h2>
        <p class="text-sm text-slate-400 mt-1">
          {{ filteredCompanies.length }} of {{ sampleCompanies.length }} companies listed
        </p>
      </div>
      <button
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors"
      >
        <Plus class="w-4 h-4" />
        Add Company
      </button>
    </div>

    <!-- Filter bar -->
    <div class="flex flex-col lg:flex-row gap-3 mb-6">
      <div class="flex-1 flex items-center gap-2 bg-slate-900 border border-slate-800 rounded-xl px-3 py-2.5">
        <Search class="w-4 h-4 text-slate-500" />
        <input
          v-model="query"
          type="text"
          placeholder="Search companies by name, industry, or city..."
          class="bg-transparent outline-none text-sm text-slate-100 placeholder-slate-500 w-full"
        />
      </div>

      <div class="flex gap-3">
        <select
          v-model="industryFilter"
          class="bg-slate-900 border border-slate-800 rounded-xl px-3 py-2.5 text-sm text-slate-200 outline-none focus:border-blue-500"
        >
          <option v-for="industry in industries" :key="industry" :value="industry">
            {{ industry }}
          </option>
        </select>

        <select
          v-model="statusFilter"
          class="bg-slate-900 border border-slate-800 rounded-xl px-3 py-2.5 text-sm text-slate-200 outline-none focus:border-blue-500"
        >
          <option v-for="status in statuses" :key="status" :value="status">
            {{ status }}
          </option>
        </select>
      </div>
    </div>

    <!-- Empty state -->
    <div
      v-if="filteredCompanies.length === 0"
      class="text-center py-20 text-slate-500"
    >
      <Building2 class="w-12 h-12 mx-auto mb-3 opacity-50" />
      <p class="font-medium">No companies found</p>
      <p class="text-sm">Try adjusting your search or filters.</p>
    </div>

    <!-- Card grid -->
    <div
      v-else
      class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
    >
      <CompanyCard
        v-for="company in filteredCompanies"
        :key="company.id"
        :company="company"
        @view="onView"
      />
    </div>
  </div>
</template>
