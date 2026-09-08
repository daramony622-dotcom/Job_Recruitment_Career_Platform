<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import {
  Search,
  Building2,
  Plus,
  X,
  Loader2,
  CheckCircle2,
  AlertCircle,
} from 'lucide-vue-next'
import CompanyCard from '../components/CompanyCard.vue'
import { sampleCompanies, statusStyles } from '../data/companies'
import { adminApi } from '../api'

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

// ---- Add Company modal ----
const showModal = ref(false)
const submitting = ref(false)
const submitError = ref('')
const submitSuccess = ref('')
const users = ref([])

const sizeOptions = [
  '1-10',
  '11-50',
  '51-200',
  '201-500',
  '501-1000',
  '1000+',
]

const form = reactive({
  user_id: '',
  name: '',
  website: '',
  email: '',
  phone: '',
  description: '',
  industry: '',
  company_size: '',
  founded_year: '',
  country: '',
  city: '',
  address: '',
})

function resetForm() {
  form.user_id = ''
  form.name = ''
  form.website = ''
  form.email = ''
  form.phone = ''
  form.description = ''
  form.industry = ''
  form.company_size = ''
  form.founded_year = ''
  form.country = ''
  form.city = ''
  form.address = ''
  submitError.value = ''
  submitSuccess.value = ''
}

async function fetchUsers() {
  try {
    const { data } = await adminApi.getCandidates({ per_page: 100 })
    const pager = data.data || data
    users.value = pager.data || []
  } catch (e) {
    users.value = []
  }
}

function openModal() {
  resetForm()
  showModal.value = true
  fetchUsers()
}

function closeModal() {
  if (submitting.value) return
  showModal.value = false
  resetForm()
}

async function submitCompany() {
  submitting.value = true
  submitError.value = ''
  submitSuccess.value = ''
  try {
    const payload = { ...form }
    if (!payload.user_id) delete payload.user_id
    if (!payload.website) delete payload.website
    if (!payload.email) delete payload.email
    if (!payload.phone) delete payload.phone
    if (!payload.description) delete payload.description
    if (!payload.industry) delete payload.industry
    if (!payload.company_size) delete payload.company_size
    if (!payload.founded_year) delete payload.founded_year
    if (!payload.country) delete payload.country
    if (!payload.city) delete payload.city
    if (!payload.address) delete payload.address

    await adminApi.storeCompany(payload)
    submitSuccess.value = 'Company created successfully.'
    setTimeout(closeModal, 1200)
  } catch (e) {
    const err = e.response?.data
    submitError.value = err?.message || 'Failed to create company.'
  } finally {
    submitting.value = false
  }
}

onMounted(fetchUsers)
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8">
    <!-- Page header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <Building2 class="w-6 h-6 text-blue-600 dark:text-blue-500" />
          Companies
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
          {{ filteredCompanies.length }} of {{ sampleCompanies.length }} companies listed
        </p>
      </div>
      <button
        @click="openModal"
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors"
      >
        <Plus class="w-4 h-4" />
        Add Company
      </button>
    </div>

    <!-- Filter bar -->
    <div class="flex flex-col lg:flex-row gap-3 mb-6">
      <div class="flex-1 flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2.5">
        <Search class="w-4 h-4 text-slate-500" />
        <input
          v-model="query"
          type="text"
          placeholder="Search companies by name, industry, or city..."
          class="bg-transparent outline-none text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 w-full"
        />
      </div>

      <div class="flex gap-3">
        <select
          v-model="industryFilter"
          class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2.5 text-sm text-slate-800 dark:text-slate-200 outline-none focus:border-blue-500"
        >
          <option v-for="industry in industries" :key="industry" :value="industry">
            {{ industry }}
          </option>
        </select>

        <select
          v-model="statusFilter"
          class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2.5 text-sm text-slate-800 dark:text-slate-200 outline-none focus:border-blue-500"
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
      />
    </div>

    <!-- ===== Add Company Modal ===== -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
      <div
        class="absolute inset-0 bg-black/60 backdrop-blur-sm"
        @click="closeModal"
      ></div>

      <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200 dark:border-slate-800 sticky top-0 bg-white dark:bg-slate-900">
          <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
              <Building2 class="w-5 h-5 text-blue-600 dark:text-blue-400" />
              Add Company
            </h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">Register a new company on behalf of a user.</p>
          </div>
          <button
            @click="closeModal"
            class="p-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-5 space-y-5">
          <p v-if="submitError" class="flex items-start gap-2 text-sm text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">
            <AlertCircle class="w-4 h-4 mt-0.5 shrink-0" />
            <span>{{ submitError }}</span>
          </p>
          <p v-if="submitSuccess" class="flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3">
            <CheckCircle2 class="w-4 h-4 shrink-0" />
            <span>{{ submitSuccess }}</span>
          </p>

          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
              Owner Account <span class="text-rose-600 dark:text-rose-400">*</span>
            </label>
            <select
              v-model="form.user_id"
              class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none focus:border-blue-500"
            >
              <option value="" disabled>Select the user who owns this company</option>
              <option v-for="u in users" :key="u.id" :value="u.id">
                {{ u.name }} ({{ u.email }})
              </option>
            </select>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Company Name <span class="text-rose-600 dark:text-rose-400">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                placeholder="e.g. TechNova Solutions"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Industry</label>
              <input
                v-model="form.industry"
                type="text"
                placeholder="e.g. Information Technology"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              placeholder="Short description about the company..."
              class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500 resize-none"
            ></textarea>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
              <input
                v-model="form.email"
                type="email"
                placeholder="contact@company.com"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Phone</label>
              <input
                v-model="form.phone"
                type="text"
                placeholder="+855 12 345 678"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Website</label>
            <input
              v-model="form.website"
              type="url"
              placeholder="https://company.com"
              class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Company Size</label>
              <select
                v-model="form.company_size"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none focus:border-blue-500"
              >
                <option value="" disabled>Select size</option>
                <option v-for="size in sizeOptions" :key="size" :value="size">{{ size }} employees</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Founded Year</label>
              <input
                v-model="form.founded_year"
                type="number"
                placeholder="e.g. 2015"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Country</label>
              <input
                v-model="form.country"
                type="text"
                placeholder="e.g. Cambodia"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">City</label>
              <input
                v-model="form.city"
                type="text"
                placeholder="e.g. Phnom Penh"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Address</label>
            <input
              v-model="form.address"
              type="text"
              placeholder="Street address"
              class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500"
            />
          </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-200 dark:border-slate-800 sticky bottom-0 bg-white dark:bg-slate-900">
          <button
            @click="closeModal"
            class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:border-slate-400 dark:hover:border-slate-500 hover:text-slate-900 dark:hover:text-slate-100 transition-colors"
            :disabled="submitting"
          >
            Cancel
          </button>
          <button
            @click="submitCompany"
            :disabled="submitting"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors"
          >
            <Loader2 v-if="submitting" class="w-4 h-4 animate-spin" />
            <CheckCircle2 v-else class="w-4 h-4" />
            {{ submitting ? 'Creating...' : 'Create Company' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
