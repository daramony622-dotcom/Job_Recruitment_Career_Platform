<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import {
  Search,
  Building2,
  Plus,
  X,
  Loader2,
  CheckCircle2,
  AlertCircle,
  Camera,
  Upload,
  Image as ImageIcon,
  User,
  Globe,
  Mail,
  Phone,
  MapPin,
  Users as UsersIcon,
  CalendarDays,
  Trash2,
} from 'lucide-vue-next'
import CompanyCard from '../components/CompanyCard.vue'
import { adminApi } from '../api'
import { resolveMediaUrl } from '../utils/media'

const query = ref('')
const statusFilter = ref('all')
const industryFilter = ref('All')
const isCompanyScopedUser = computed(() => ['hr', 'company'].includes(JSON.parse(localStorage.getItem('admin_user') || 'null')?.role))

const companies = ref([])
const loading = ref(false)
const error = ref('')
const pagination = reactive({ current_page: 1, last_page: 1, total: 0, per_page: 12 })

const statusOptions = ['all', 'pending', 'approved', 'rejected', 'suspended']

const industries = computed(() => {
  const items = companies.value.map((company) => company.industry).filter(Boolean)
  return ['All', ...new Set(items)]
})

const filteredCompanies = computed(() => {
  const list = companies.value || []

  return list.filter((company) => {
    const q = query.value.trim().toLowerCase()
    const matchesQuery =
      q === '' ||
      (company.name || '').toLowerCase().includes(q) ||
      (company.industry || '').toLowerCase().includes(q) ||
      (company.city || '').toLowerCase().includes(q)

    const matchesStatus =
      statusFilter.value === 'all' || (company.status || '').toLowerCase() === statusFilter.value

    const matchesIndustry =
      industryFilter.value === 'All' || company.industry === industryFilter.value

    return matchesQuery && matchesStatus && matchesIndustry
  })
})

async function fetchCompanies() {
  loading.value = true
  error.value = ''

  try {
    const params = {
      per_page: pagination.per_page,
      page: pagination.current_page,
      search: query.value.trim() || undefined,
      status: statusFilter.value === 'all' ? undefined : statusFilter.value,
    }

    const { data } = await adminApi.getCompanies(params)
    const profile = data.data || data
    const pager = isCompanyScopedUser.value
      ? { data: profile?.id ? [profile] : [], current_page: 1, last_page: 1, total: profile?.id ? 1 : 0, per_page: 1 }
      : profile
    companies.value = pager.data || []

    Object.assign(pagination, {
      current_page: pager.current_page || 1,
      last_page: pager.last_page || 1,
      total: pager.total || 0,
      per_page: pager.per_page || 12,
    })
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to load companies.'
    companies.value = []
  } finally {
    loading.value = false
  }
}

watch([query, statusFilter], () => {
  pagination.current_page = 1
  fetchCompanies()
})

function goToPage(page) {
  if (page < 1 || page > pagination.last_page) return
  pagination.current_page = page
  fetchCompanies()
}

// ---- Add Company modal ----
const showModal = ref(false)
const editingCompany = ref(null)
const submitting = ref(false)
const submitError = ref('')
const submitSuccess = ref('')
const users = ref([])

const logoFile = ref(null)
const coverFile = ref(null)
const logoPreview = ref('')
const coverPreview = ref('')

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

function onLogoSelect(e) {
  const file = e.target.files?.[0]
  if (!file) return
  logoFile.value = file
  logoPreview.value = URL.createObjectURL(file)
}

function onCoverSelect(e) {
  const file = e.target.files?.[0]
  if (!file) return
  coverFile.value = file
  coverPreview.value = URL.createObjectURL(file)
}

function removeLogo() {
  logoFile.value = null
  logoPreview.value = ''
}

function removeCover() {
  coverFile.value = null
  coverPreview.value = ''
}

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
  logoFile.value = null
  coverFile.value = null
  logoPreview.value = ''
  coverPreview.value = ''
  submitError.value = ''
  submitSuccess.value = ''
}

async function fetchUsers() {
  try {
    const { data } = await adminApi.getUsers({ role: 'hr', per_page: 100 })
    const pager = data.data || data
    users.value = pager.data || []
  } catch (e) {
    users.value = []
  }
}

function openModal() {
  resetForm()
  editingCompany.value = null
  showModal.value = true
  if (!isCompanyScopedUser.value) fetchUsers()
}

function openEdit(company) {
  editingCompany.value = company
  Object.assign(form, {
    user_id: company.user_id || company.user?.id || '',
    name: company.name || '',
    website: company.website || '',
    email: company.email || '',
    phone: company.phone || '',
    description: company.description || '',
    industry: company.industry || '',
    company_size: company.company_size || '',
    founded_year: company.founded_year || '',
    country: company.country || '',
    city: company.city || '',
    address: company.address || '',
  })
  logoFile.value = null
  coverFile.value = null
  logoPreview.value = company.logo ? resolveMediaUrl(company.logo) : ''
  coverPreview.value = company.cover_image ? resolveMediaUrl(company.cover_image) : ''
  submitError.value = ''
  submitSuccess.value = ''
  showModal.value = true
}

function closeModal() {
  if (submitting.value) return
  showModal.value = false
  editingCompany.value = null
  resetForm()
}

async function submitCompany() {
  submitting.value = true
  submitError.value = ''
  submitSuccess.value = ''
  try {
    if (isCompanyScopedUser.value) {
      const payload = { ...form }
      delete payload.user_id
      if (editingCompany.value) {
        await adminApi.updateCompanyProfile(payload)
      } else {
        await adminApi.storeCompanyProfile(payload)
      }
      submitSuccess.value = 'Company profile saved successfully.'
      await fetchCompanies()
      setTimeout(closeModal, 1200)
      return
    }

    const formData = new FormData()
    if (form.user_id && !editingCompany.value) formData.append('user_id', form.user_id)
    if (form.name) formData.append('name', form.name)
    if (form.website) formData.append('website', form.website)
    if (form.email) formData.append('email', form.email)
    if (form.phone) formData.append('phone', form.phone)
    if (form.description) formData.append('description', form.description)
    if (form.industry) formData.append('industry', form.industry)
    if (form.company_size) formData.append('company_size', form.company_size)
    if (form.founded_year) formData.append('founded_year', form.founded_year)
    if (form.country) formData.append('country', form.country)
    if (form.city) formData.append('city', form.city)
    if (form.address) formData.append('address', form.address)

    if (logoFile.value) formData.append('logo', logoFile.value)
    if (coverFile.value) formData.append('cover_image', coverFile.value)

    if (editingCompany.value) {
      await adminApi.updateCompany(editingCompany.value.id, formData)
      submitSuccess.value = 'Company updated successfully.'
    } else {
      await adminApi.storeCompany(formData)
      submitSuccess.value = 'Company created successfully.'
    }
    await fetchCompanies()
    setTimeout(closeModal, 1200)
  } catch (e) {
    const err = e.response?.data
    if (err && err.errors) {
      submitError.value = Object.values(err.errors).flat().join(' ')
    } else {
      submitError.value = err?.message || 'Failed to save company.'
    }
  } finally {
    submitting.value = false
  }
}

async function deleteCompany(company) {
  if (!confirm(`Delete company "${company.name}"? This action cannot be undone.`)) return
  try {
    if (isCompanyScopedUser.value) await adminApi.deleteCompanyProfile()
    else await adminApi.deleteCompany(company.id)
    await fetchCompanies()
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to delete company.'
  }
}

async function updateCompanyStatus(company) {
  const statuses = ['pending', 'approved', 'rejected', 'suspended']
  const current = String(company.status || 'pending').toLowerCase()
  const next = statuses[(statuses.indexOf(current) + 1) % statuses.length]
  try {
    await adminApi.updateCompanyStatus(company.id, next)
    await fetchCompanies()
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to update company status.'
  }
}

onMounted(() => {
  if (!isCompanyScopedUser.value) fetchUsers()
  fetchCompanies()
})
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
          {{ isCompanyScopedUser ? 'Manage your company profile and public details.' : `${filteredCompanies.length} of ${pagination.total || 0} companies listed` }}
        </p>
      </div>
      <button
        @click="openModal"
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors"
      >
        <Plus class="w-4 h-4" />
        {{ isCompanyScopedUser ? (companies.length ? 'Edit Company' : 'Create Company') : (editingCompany ? 'Edit Company' : 'Add Company') }}
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
        <select v-if="!isCompanyScopedUser"
          v-model="industryFilter"
          class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2.5 text-sm text-slate-800 dark:text-slate-200 outline-none focus:border-blue-500"
        >
          <option v-for="industry in industries" :key="industry" :value="industry">
            {{ industry }}
          </option>
        </select>

        <select v-if="!isCompanyScopedUser"
          v-model="statusFilter"
          class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2.5 text-sm text-slate-800 dark:text-slate-200 outline-none focus:border-blue-500"
        >
          <option v-for="status in statusOptions" :key="status" :value="status">
            {{ status === 'all' ? 'All' : status.charAt(0).toUpperCase() + status.slice(1) }}
          </option>
        </select>
      </div>
    </div>

    <p v-if="error" class="mb-4 text-sm text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">
      {{ error }}
    </p>

    <div v-if="loading" class="text-center py-10 text-slate-600 dark:text-slate-400">Loading companies...</div>

    <!-- Empty state -->
    <div
      v-else-if="filteredCompanies.length === 0"
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
        :show-status="!isCompanyScopedUser"
        :show-view="!isCompanyScopedUser"
        @edit="openEdit"
        @delete="deleteCompany"
        @status="updateCompanyStatus"
      />
    </div>

    <div v-if="pagination.last_page > 1" class="flex items-center justify-between px-1 py-4 mt-6">
      <p class="text-xs text-slate-500">
        Page {{ pagination.current_page }} of {{ pagination.last_page }}
      </p>
      <div class="flex items-center gap-2">
        <button
          class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
          :disabled="pagination.current_page <= 1"
          @click="goToPage(pagination.current_page - 1)"
        >
          Prev
        </button>
        <button
          class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
          :disabled="pagination.current_page >= pagination.last_page"
          @click="goToPage(pagination.current_page + 1)"
        >
          Next
        </button>
      </div>
    </div>

    <!-- ===== Add / Edit Company Modal ===== -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
      <div
        class="absolute inset-0 bg-black/60 backdrop-blur-sm"
        @click="closeModal"
      ></div>

      <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4.5 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shrink-0">
          <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
              <Building2 class="w-5 h-5 text-blue-600 dark:text-blue-400" />
              {{ editingCompany ? 'Edit Company Profile' : 'Add New Company' }}
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage branding, owner, details, and location.</p>
          </div>
          <button
            @click="closeModal"
            class="p-2 text-slate-500 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Body Scroll Area -->
        <div class="p-6 space-y-6 overflow-y-auto">
          <p v-if="submitError" class="flex items-start gap-2 text-sm text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">
            <AlertCircle class="w-4 h-4 mt-0.5 shrink-0" />
            <span>{{ submitError }}</span>
          </p>
          <p v-if="submitSuccess" class="flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3">
            <CheckCircle2 class="w-4 h-4 shrink-0" />
            <span>{{ submitSuccess }}</span>
          </p>

          <!-- 1. Media & Branding Section -->
          <div class="space-y-4 bg-slate-50 dark:bg-slate-800/40 p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800">
            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-2">
              <ImageIcon class="w-4 h-4 text-blue-600 dark:text-blue-400" />
              Company Media &amp; Branding
            </h4>

            <!-- Cover Image Dropzone -->
            <div>
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Cover Banner</label>
              <div class="relative h-28 w-full rounded-xl overflow-hidden bg-slate-200 dark:bg-slate-700/60 border-2 border-dashed border-slate-300 dark:border-slate-600 group hover:border-blue-500 transition-colors flex items-center justify-center">
                <img v-if="coverPreview" :src="coverPreview" alt="Cover preview" class="w-full h-full object-cover" />
                <div v-else class="text-center p-3">
                  <Upload class="w-6 h-6 mx-auto text-slate-400 mb-1 group-hover:scale-110 transition-transform" />
                  <p class="text-xs text-slate-500 font-medium">Click to upload cover banner (JPEG/PNG)</p>
                </div>
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/jpg,image/webp"
                  @change="onCoverSelect"
                  class="absolute inset-0 opacity-0 cursor-pointer"
                />
                <button
                  v-if="coverPreview"
                  type="button"
                  @click.stop="removeCover"
                  class="absolute top-2 right-2 p-1.5 bg-black/60 hover:bg-black/80 text-white rounded-lg transition-colors"
                  title="Remove cover image"
                >
                  <X class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>

            <!-- Logo Dropzone -->
            <div class="flex items-center gap-4">
              <div class="relative w-16 h-16 rounded-2xl overflow-hidden bg-slate-200 dark:bg-slate-700 border-2 border-slate-300 dark:border-slate-600 shrink-0 flex items-center justify-center shadow-sm">
                <img v-if="logoPreview" :src="logoPreview" alt="Logo preview" class="w-full h-full object-cover" />
                <Camera v-else class="w-6 h-6 text-slate-400" />
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/jpg,image/webp"
                  @change="onLogoSelect"
                  class="absolute inset-0 opacity-0 cursor-pointer"
                />
              </div>
              <div class="flex-1">
                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Company Logo</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Recommended square aspect ratio (e.g. 200x200px).</p>
                <div class="flex items-center gap-2 mt-2">
                  <label class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold cursor-pointer transition-colors inline-flex items-center gap-1.5">
                    <Upload class="w-3.5 h-3.5" /> Upload Logo
                    <input type="file" accept="image/jpeg,image/png,image/jpg,image/webp" @change="onLogoSelect" class="hidden" />
                  </label>
                  <button v-if="logoPreview" type="button" @click="removeLogo" class="px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                    Remove
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- 2. General Information Section -->
          <div class="space-y-4">
            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-2">
              <Building2 class="w-4 h-4 text-blue-600 dark:text-blue-400" />
              General Details
            </h4>

            <!-- Owner Account -->
            <div v-if="!editingCompany">
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Owner Account <span class="text-rose-600 dark:text-rose-400">*</span>
                <span class="text-xs text-slate-400 font-normal ml-1">(HR role only)</span>
              </label>
              <div class="relative">
                <User class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <select
                  v-model="form.user_id"
                  class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl pl-10 pr-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none focus:border-blue-500 transition-colors"
                >
                  <option value="" disabled>Select an HR user who owns this company</option>
                  <option v-for="u in users" :key="u.id" :value="u.id">
                    {{ u.name }} ({{ u.email }})
                  </option>
                </select>
              </div>
              <p v-if="users.length === 0 && showModal" class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                No HR users found. Create an HR account first in Users &amp; HR.
              </p>
            </div>

            <!-- Name & Industry -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                  Company Name <span class="text-rose-600 dark:text-rose-400">*</span>
                </label>
                <div class="relative">
                  <Building2 class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                  <input
                    v-model="form.name"
                    type="text"
                    placeholder="e.g. TechNova Solutions"
                    class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl pl-10 pr-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 outline-none focus:border-blue-500 transition-colors"
                  />
                </div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Industry</label>
                <input
                  v-model="form.industry"
                  type="text"
                  placeholder="e.g. Information Technology"
                  class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 outline-none focus:border-blue-500 transition-colors"
                />
              </div>
            </div>

            <!-- Description -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                placeholder="Short description about the company..."
                class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 outline-none focus:border-blue-500 resize-none transition-colors"
              ></textarea>
            </div>

            <!-- Size & Founded Year -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Company Size</label>
                <div class="relative">
                  <UsersIcon class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                  <select
                    v-model="form.company_size"
                    class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl pl-10 pr-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none focus:border-blue-500 transition-colors"
                  >
                    <option value="" disabled>Select employee count</option>
                    <option v-for="size in sizeOptions" :key="size" :value="size">{{ size }} employees</option>
                  </select>
                </div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Founded Year</label>
                <div class="relative">
                  <CalendarDays class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                  <input
                    v-model="form.founded_year"
                    type="number"
                    placeholder="e.g. 2015"
                    class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl pl-10 pr-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 outline-none focus:border-blue-500 transition-colors"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- 3. Contact & Location Section -->
          <div class="space-y-4 pt-2 border-t border-slate-200 dark:border-slate-800">
            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-2">
              <MapPin class="w-4 h-4 text-blue-600 dark:text-blue-400" />
              Contact &amp; Location
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                <div class="relative">
                  <Mail class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                  <input
                    v-model="form.email"
                    type="email"
                    placeholder="contact@company.com"
                    class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl pl-10 pr-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 outline-none focus:border-blue-500 transition-colors"
                  />
                </div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Phone</label>
                <div class="relative">
                  <Phone class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                  <input
                    v-model="form.phone"
                    type="text"
                    placeholder="+855 12 345 678"
                    class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl pl-10 pr-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 outline-none focus:border-blue-500 transition-colors"
                  />
                </div>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Website URL</label>
              <div class="relative">
                <Globe class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input
                  v-model="form.website"
                  type="url"
                  placeholder="https://company.com"
                  class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl pl-10 pr-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 outline-none focus:border-blue-500 transition-colors"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Country</label>
                <input
                  v-model="form.country"
                  type="text"
                  placeholder="e.g. Cambodia"
                  class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 outline-none focus:border-blue-500 transition-colors"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">City</label>
                <input
                  v-model="form.city"
                  type="text"
                  placeholder="e.g. Phnom Penh"
                  class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 outline-none focus:border-blue-500 transition-colors"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Address</label>
              <input
                v-model="form.address"
                type="text"
                placeholder="Street address"
                class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 outline-none focus:border-blue-500 transition-colors"
              />
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shrink-0">
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
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed shadow-md shadow-blue-600/20 transition-all active:scale-95"
          >
            <Loader2 v-if="submitting" class="w-4 h-4 animate-spin" />
            <CheckCircle2 v-else class="w-4 h-4" />
            {{ submitting ? 'Saving...' : (editingCompany ? 'Save Company' : 'Create Company') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
