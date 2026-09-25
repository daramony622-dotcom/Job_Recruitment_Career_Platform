<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import {
  Briefcase,
  Building2,
  CalendarClock,
  CircleDot,
  ClipboardList,
  MapPin,
  DollarSign,
  Layers3,
  UsersRound,
  Search,
  Plus,
  RefreshCw,
  Eye,
  Pencil,
  Trash2,
  X,
  Loader2,
  CheckCircle2,
  AlertCircle,
  Star,
} from 'lucide-vue-next'
import { adminApi } from '../api'
import StatusBadge from './StatusBadge.vue'

const router = useRouter()
const isCompanyScopedUser = computed(() => ['hr', 'company'].includes(JSON.parse(localStorage.getItem('admin_user') || 'null')?.role))

const loading = ref(false)
const error = ref('')
const jobPosts = ref([])
const pagination = reactive({ current_page: 1, last_page: 1, total: 0, per_page: 15 })

const jobTypes = ['full_time', 'part_time', 'contract', 'internship', 'freelance', 'remote']
const workModes = ['onsite', 'remote', 'hybrid']
const experienceLevels = ['entry', 'junior', 'mid', 'senior', 'lead', 'executive']
const currencies = ['USD', 'EUR', 'GBP', 'KHR']
const salaryPeriods = ['hourly', 'daily', 'monthly', 'yearly']
const statuses = ['draft', 'published', 'closed', 'suspended']

const statusSummary = computed(() => [
  { label: 'All listings', value: pagination.total, icon: ClipboardList, iconClass: 'bg-blue-500/10 text-blue-600 dark:text-blue-400' },
  { label: 'Published', value: jobPosts.value.filter((job) => job.status === 'published').length, icon: CircleDot, iconClass: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' },
  { label: 'Drafts', value: jobPosts.value.filter((job) => job.status === 'draft').length, icon: Pencil, iconClass: 'bg-amber-500/10 text-amber-600 dark:text-amber-400' },
  { label: 'Closing soon', value: jobPosts.value.filter((job) => job.deadline && new Date(job.deadline) >= new Date() && new Date(job.deadline) <= new Date(Date.now() + 14 * 86400000)).length, icon: CalendarClock, iconClass: 'bg-rose-500/10 text-rose-600 dark:text-rose-400' },
])

const showModal = ref(false)
const editingJob = ref(null)
const submitting = ref(false)
const submitError = ref('')
const submitSuccess = ref('')

const companies = ref([])
const activeCompany = ref(null)
const categories = ref([])
const skills = ref([])
const canCreateJobPost = computed(() => !isCompanyScopedUser.value || companies.value.length > 0)
const selectedSkills = ref([])
const activeCompanyId = () => localStorage.getItem('active_company_id') || JSON.parse(localStorage.getItem('admin_user') || 'null')?.company_id

const form = reactive({
  company_id: '',
  category_id: '',
  title: '',
  description: '',
  requirements: '',
  benefits: '',
  job_type: 'full_time',
  work_mode: 'onsite',
  experience_level: '',
  location: '',
  country: '',
  city: '',
  salary_min: '',
  salary_max: '',
  salary_currency: 'USD',
  salary_period: 'monthly',
  is_salary_visible: true,
  vacancies: 1,
  deadline: '',
  status: 'draft',
  is_featured: false,
})

function resetForm() {
  Object.assign(form, {
    company_id: '',
    category_id: '',
    title: '',
    description: '',
    requirements: '',
    benefits: '',
    job_type: 'full_time',
    work_mode: 'onsite',
    experience_level: '',
    location: '',
    country: '',
    city: '',
    salary_min: '',
    salary_max: '',
    salary_currency: 'USD',
    salary_period: 'monthly',
    is_salary_visible: true,
    vacancies: 1,
    deadline: '',
    status: 'draft',
    is_featured: false,
  })
  selectedSkills.value = []
  submitError.value = ''
  submitSuccess.value = ''
}

function toggleSkill(skill) {
  const index = selectedSkills.value.findIndex((s) => s.id === skill.id)
  if (index === -1) {
    selectedSkills.value.push({ id: skill.id, name: skill.name, level: '', is_required: true })
  } else {
    selectedSkills.value.splice(index, 1)
  }
}

function setSkillLevel(skill, level) {
  const found = selectedSkills.value.find((s) => s.id === skill.id)
  if (found) found.level = level
}

async function fetchLookups() {
  try {
    const [companyRes, activeCompanyRes, categoryRes, skillRes] = await Promise.all([
      isCompanyScopedUser.value ? adminApi.getManagedCompanies() : adminApi.getCompanies({ per_page: 100 }),
      isCompanyScopedUser.value ? adminApi.getCompanyProfile() : Promise.resolve(null),
      adminApi.getJobCategories({ per_page: 100 }),
      adminApi.getSkills({ per_page: 100 }),
    ])

    const pager = (res) => (res?.data?.data || res?.data || [])
    const companyData = pager(companyRes.data)
    companies.value = Array.isArray(companyData) ? companyData : (companyData ? [companyData] : [])
    if (isCompanyScopedUser.value && companies.value.length) {
      const activeCompanyData = activeCompanyRes?.data?.data || activeCompanyRes?.data
      activeCompany.value = activeCompanyData || companies.value.find((company) => String(company.id) === String(activeCompanyId())) || companies.value[0]
      form.company_id = activeCompany.value.id
      if (form.company_id) localStorage.setItem('active_company_id', String(form.company_id))
    }
    categories.value = pager(categoryRes.data)
    skills.value = pager(skillRes.data)
  } catch (e) {
    companies.value = []
    activeCompany.value = null
    categories.value = []
    skills.value = []
  }
}

async function openModal() {
  resetForm()
  editingJob.value = null
  showModal.value = true
  if (isCompanyScopedUser.value || companies.value.length === 0 || categories.value.length === 0 || skills.value.length === 0) {
    await fetchLookups()
  }
  if (isCompanyScopedUser.value && companies.value.length && !form.company_id) {
    form.company_id = companies.value.find((company) => String(company.id) === String(activeCompanyId()))?.id || companies.value[0].id
  }
}

async function openEditModal(job) {
  resetForm()
  editingJob.value = job
  showModal.value = true
  await fetchLookups()
  try {
    const response = await adminApi.showJobPost(job.id)
    const detail = response.data?.data || response.data
    Object.assign(form, {
      company_id: detail.company_id || detail.company?.id || '', category_id: detail.category_id || detail.category?.id || '',
      title: detail.title || '', description: detail.description || '', requirements: detail.requirements || '', benefits: detail.benefits || '',
      job_type: detail.job_type || 'full_time', work_mode: detail.work_mode || 'onsite', experience_level: detail.experience_level || '',
      location: detail.location || '', country: detail.country || '', city: detail.city || '', salary_min: detail.salary_min ?? '', salary_max: detail.salary_max ?? '',
      salary_currency: detail.salary_currency || 'USD', salary_period: detail.salary_period || 'monthly', is_salary_visible: detail.is_salary_visible !== false,
      vacancies: detail.vacancies || 1, deadline: detail.deadline ? String(detail.deadline).slice(0, 10) : '', status: detail.status || 'draft', is_featured: detail.is_featured === true,
    })
    selectedSkills.value = (detail.skills || []).map((skill) => ({ id: skill.id, name: skill.name, level: skill.pivot?.level || '', is_required: skill.pivot?.is_required !== false }))
  } catch (e) {
    submitError.value = e.response?.data?.message || 'Failed to load job post for editing.'
  }
}

function closeModal() {
  if (submitting.value) return
  showModal.value = false
  editingJob.value = null
  resetForm()
}

async function submitJobPost() {
  submitting.value = true
  submitError.value = ''
  submitSuccess.value = ''
  try {
    const payload = { ...form }
    if (!payload.experience_level) delete payload.experience_level
    if (!payload.requirements) delete payload.requirements
    if (!payload.benefits) delete payload.benefits
    if (!payload.country) delete payload.country
    if (!payload.city) delete payload.city
    if (payload.salary_min === '' || payload.salary_min === null) delete payload.salary_min
    if (payload.salary_max === '' || payload.salary_max === null) delete payload.salary_max
    if (!payload.deadline) delete payload.deadline
    payload.is_salary_visible = Boolean(payload.is_salary_visible)
    payload.is_featured = Boolean(payload.is_featured)
    payload.vacancies = payload.vacancies ? Number(payload.vacancies) : 1
    payload.skills = selectedSkills.value.map((s) => {
      const item = { id: s.id, is_required: Boolean(s.is_required) }
      if (s.level) item.level = s.level
      return item
    })

    if (editingJob.value) {
      await adminApi.updateJobPost(editingJob.value.id, payload)
      submitSuccess.value = 'Job post updated successfully.'
    } else {
      await adminApi.storeJobPost(payload)
      submitSuccess.value = 'Job post created successfully.'
    }
    await fetchJobPosts()
    setTimeout(closeModal, 1100)
  } catch (e) {
    const err = e.response?.data
    if (err && err.errors) {
      submitError.value = Object.values(err.errors).flat().join(' ')
    } else {
      submitError.value = err?.message || 'Failed to create job post.'
    }
  } finally {
    submitting.value = false
  }
}

const filters = reactive({
  search: '',
  status: '',
  per_page: 15,
  page: 1,
})

async function fetchJobPosts() {
  loading.value = true
  error.value = ''
  try {
    const params = { ...filters }
    if (!params.status) delete params.status
    if (!params.search) delete params.search

    const { data } = await adminApi.getJobPosts(params)
    jobPosts.value = data.data || []
    Object.assign(pagination, {
      current_page: data.current_page,
      last_page: data.last_page,
      total: data.total,
      per_page: data.per_page,
    })
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to load job posts.'
  } finally {
    loading.value = false
  }
}

watch(
  () => [filters.search, filters.status],
  () => {
    filters.page = 1
    fetchJobPosts()
  },
)

watch(
  () => filters.per_page,
  () => {
    filters.page = 1
    fetchJobPosts()
  },
)

function goToPage(page) {
  if (page < 1 || page > pagination.last_page) return
  filters.page = page
  fetchJobPosts()
}

function onView(job) {
  router.push(`/job-posts/${job.id}`)
}

function onEdit(job) {
  openEditModal(job)
}

async function onDelete(job) {
  if (!confirm(`Delete job post "${job.title}"?`)) return
  try {
    await adminApi.deleteJobPost(job.id)
    fetchJobPosts()
  } catch (e) {
    error.value = e.response?.data?.message || 'Delete failed.'
  }
}

async function onToggleFeatured(job) {
  try {
    await adminApi.toggleFeaturedJobPost(job.id)
    await fetchJobPosts()
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to update featured status.'
  }
}

onMounted(async () => {
  if (isCompanyScopedUser.value) await fetchLookups()
  await fetchJobPosts()
})
</script>

<template>
  <div class="min-h-full bg-slate-50/70 p-4 sm:p-6 lg:p-8 dark:bg-slate-950/30">
    <div class="mb-7 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
      <div>
        <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-400">
          <Briefcase class="h-4 w-4" /> Recruitment workspace
        </div>
        <h2 class="flex items-center gap-2 text-3xl font-black tracking-tight text-slate-950 dark:text-slate-50">
          Job posts
        </h2>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Create, publish, and manage every opportunity across the platform.</p>
      </div>
      <button
        v-if="canCreateJobPost"
        @click="openModal"
        class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700"
      >
        <Plus class="w-4 h-4" />
        New job post
      </button>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 xl:grid-cols-4">
      <div v-for="item in statusSummary" :key="item.label" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between">
          <div class="flex h-9 w-9 items-center justify-center rounded-xl" :class="item.iconClass">
            <component :is="item.icon" class="h-4 w-4" />
          </div>
          <span class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ item.value }}</span>
        </div>
        <p class="mt-3 text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">{{ item.label }}</p>
      </div>
    </div>

    <div class="mb-5 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:flex-row">
      <div class="flex min-w-0 flex-1 items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-950">
          <Search class="w-4 h-4 text-slate-600 dark:text-slate-300" />
        <input
          v-model="filters.search"
          type="text"
          placeholder="Search by title, description, or company..."
          class="bg-transparent outline-none text-sm font-medium text-slate-900 dark:text-slate-100 placeholder:text-slate-500 dark:placeholder:text-slate-400 w-full"
        />
      </div>

      <div class="flex flex-wrap gap-2">
        <select
          v-model="filters.status"
          class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
        >
          <option value="">All statuses</option>
          <option value="draft">Draft</option>
          <option value="published">Published</option>
          <option value="closed">Closed</option>
          <option value="suspended">Suspended</option>
        </select>
        <select
          v-model="filters.per_page"
          class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
          aria-label="Rows per page"
        >
          <option :value="15">15 rows</option>
          <option :value="30">30 rows</option>
          <option :value="50">50 rows</option>
        </select>
        <button
          @click="fetchJobPosts"
          class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-blue-500 hover:text-blue-600 dark:border-slate-700 dark:text-slate-300 dark:hover:text-blue-400"
          title="Refresh job posts"
        >
          <RefreshCw class="w-4 h-4" />
          <span class="hidden sm:inline">Refresh</span>
        </button>
      </div>
    </div>

    <p v-if="error" class="mb-4 text-sm text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">
      {{ error }}
    </p>

    <div class="w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-900">
      <div class="w-full overflow-x-auto">
        <table class="w-full text-xs text-left">
          <thead class="border-b border-slate-200 bg-slate-50/80 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/80 dark:text-slate-400">
            <tr>
              <th class="px-4 py-2.5 font-bold"><span class="inline-flex items-center gap-1.5"><Briefcase class="h-3.5 w-3.5 text-blue-500" /> Opportunity</span></th>
              <th class="px-4 py-2.5 font-bold"><span class="inline-flex items-center gap-1.5"><Building2 class="h-3.5 w-3.5 text-slate-400" /> Company</span></th>
              <th class="px-4 py-2.5 font-bold"><span class="inline-flex items-center gap-1.5"><MapPin class="h-3.5 w-3.5 text-slate-400" /> Location</span></th>
              <th class="px-4 py-2.5 font-bold"><span class="inline-flex items-center gap-1.5"><Layers3 class="h-3.5 w-3.5 text-slate-400" /> Type / Mode</span></th>
              <th class="px-4 py-2.5 font-bold"><span class="inline-flex items-center gap-1.5"><ClipboardList class="h-3.5 w-3.5 text-slate-400" /> Category</span></th>
              <th class="px-4 py-2.5 font-bold"><span class="inline-flex items-center gap-1.5"><DollarSign class="h-3.5 w-3.5 text-slate-400" /> Salary</span></th>
              <th class="px-4 py-2.5 font-bold"><span class="inline-flex items-center gap-1.5"><CircleDot class="h-3.5 w-3.5 text-slate-400" /> Status</span></th>
              <th class="px-4 py-2.5 text-right font-bold"><span class="inline-flex items-center gap-1.5"><UsersRound class="h-3.5 w-3.5 text-slate-400" /> Manage</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-if="loading" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
              <td colspan="8" class="px-4 py-8 text-center text-slate-500">Loading job posts...</td>
            </tr>
            <tr v-else-if="jobPosts.length === 0" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
              <td colspan="8" class="px-4 py-8 text-center text-slate-500">No job posts found.</td>
            </tr>
            <tr
              v-for="job in jobPosts"
              :key="job.id"
              class="group transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
            >
              <td class="px-4 py-2.5 align-top">
                <div class="flex min-w-48 items-start gap-2.5">
                  <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600 shadow-2xs dark:border-blue-900/40 dark:bg-blue-500/10 dark:text-cyan-400"><Briefcase class="h-4 w-4" /></div>
                  <div class="min-w-0">
                    <p class="max-w-56 truncate font-bold text-slate-900 dark:text-slate-100 text-xs leading-tight">{{ job.title }}</p>
                    <p class="mt-1 flex items-center gap-1.5 text-[11px] font-medium text-slate-500 dark:text-slate-400"><span>{{ job.views_count || 0 }} views</span><span class="text-slate-400">•</span><span class="inline-flex items-center gap-1"><UsersRound class="h-3 w-3" />{{ job.vacancies || 0 }} openings</span></p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-2.5 align-top"><div class="flex min-w-32 items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300"><Building2 class="h-3.5 w-3.5 shrink-0 text-slate-400" /><span class="truncate">{{ job.company?.name || '—' }}</span></div></td>
              <td class="px-4 py-2.5 align-top"><div class="flex min-w-28 items-center gap-1.5 text-xs font-medium text-slate-800 dark:text-slate-200"><MapPin class="h-3.5 w-3.5 shrink-0 text-slate-400" /><span class="truncate">{{ job.city || job.location || '—' }}</span></div></td>
              <td class="px-4 py-2.5 align-top text-xs font-medium text-slate-800 dark:text-slate-200 capitalize">
                <div class="flex flex-col items-start gap-1">
                  <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-bold dark:bg-slate-800">{{ (job.job_type || '—').replace('_', ' ') }}</span>
                  <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">{{ (job.work_mode || '—').replace('_', ' ') }}</span>
                </div>
              </td>
              <td class="px-4 py-2.5 align-top text-slate-700 dark:text-slate-300"><span class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300"><ClipboardList class="h-3.5 w-3.5" />{{ job.category?.name || 'Uncategorized' }}</span></td>
              <td class="px-4 py-2.5 align-top text-xs font-medium text-slate-800 dark:text-slate-200">
                <span v-if="job.salary_min" class="inline-flex items-center gap-1 whitespace-nowrap text-xs font-bold text-slate-800 dark:text-slate-200">
                  <DollarSign class="h-3.5 w-3.5 text-emerald-500" />
                  {{ job.salary_currency }} {{ job.salary_min }}<span v-if="job.salary_max"> – {{ job.salary_max }}</span>
                </span>
                <span v-else class="text-xs font-semibold text-slate-500 dark:text-slate-400">Salary not set</span>
              </td>
              <td class="px-5 py-4 align-top"><StatusBadge :value="job.status" /></td>
              <td class="px-5 py-4 align-top">
                <div class="flex items-center justify-end gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1 dark:border-slate-700 dark:bg-slate-800/60">
                  <button
                    class="p-2 text-slate-600 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                    :title="job.is_featured ? 'Remove featured' : 'Mark featured'"
                    @click="onToggleFeatured(job)"
                  >
                    <Star class="w-4 h-4" :class="job.is_featured ? 'fill-amber-400 text-amber-500' : ''" />
                  </button>
                  <button
                    class="p-2 text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                    title="View"
                    @click="onView(job)"
                  >
                    <Eye class="w-4 h-4" />
                  </button>
                  <button
                    class="p-2 text-slate-600 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                    title="Edit"
                    @click="onEdit(job)"
                  >
                    <Pencil class="w-4 h-4" />
                  </button>
                  <button
                    class="p-2 text-slate-600 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                    title="Delete"
                    @click="onDelete(job)"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div
        v-if="pagination.last_page > 1"
        class="flex items-center justify-between px-5 py-3 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900"
      >
        <p class="text-xs font-medium text-slate-600 dark:text-slate-300">
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
    </div>

    <!-- ===== New Job Post Modal ===== -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
      <div
        class="absolute inset-0 bg-black/60 backdrop-blur-sm"
        @click="closeModal"
      ></div>

      <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto shadow-2xl">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200 dark:border-slate-800 sticky top-0 bg-white dark:bg-slate-900 z-10">
          <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
              <Briefcase class="w-5 h-5 text-blue-600 dark:text-blue-400" />
              {{ editingJob ? 'Edit Job Post' : 'New Job Post' }}
            </h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">{{ editingJob ? 'Update this job post and its publishing settings.' : (isCompanyScopedUser ? 'Create a job post for your company.' : 'Create a new job post for any company.') }}</p>
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

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Company <span class="text-rose-600 dark:text-rose-400">*</span>
              </label>
              <div v-if="isCompanyScopedUser" class="flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-3.5 py-2.5 text-sm font-semibold text-blue-800 dark:border-blue-900/50 dark:bg-blue-950/30 dark:text-blue-300">
                <Building2 class="h-4 w-4 shrink-0" />
                {{ activeCompany?.name || 'Loading company...' }}
              </div>
              <select v-else
                v-model="form.company_id"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none focus:border-blue-500"
              >
                <option value="" disabled>Select company</option>
                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Category <span class="text-rose-600 dark:text-rose-400">*</span>
              </label>
              <select
                v-model="form.category_id"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none focus:border-blue-500"
              >
                <option value="" disabled>Select category</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
              Job Title <span class="text-rose-600 dark:text-rose-400">*</span>
            </label>
            <input
              v-model="form.title"
              type="text"
              placeholder="e.g. Senior Frontend Developer"
              class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
              Description <span class="text-rose-600 dark:text-rose-400">*</span>
            </label>
            <textarea
              v-model="form.description"
              rows="4"
              placeholder="Describe the role and responsibilities..."
              class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500 resize-none"
            ></textarea>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Requirements</label>
              <textarea
                v-model="form.requirements"
                rows="3"
                placeholder="Required skills and qualifications..."
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500 resize-none"
              ></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Benefits</label>
              <textarea
                v-model="form.benefits"
                rows="3"
                placeholder="Benefits and perks..."
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500 resize-none"
              ></textarea>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Job Type <span class="text-rose-600 dark:text-rose-400">*</span>
              </label>
              <select
                v-model="form.job_type"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 capitalize outline-none focus:border-blue-500"
              >
                <option v-for="t in jobTypes" :key="t" :value="t">{{ t.replace('_', ' ') }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Work Mode <span class="text-rose-600 dark:text-rose-400">*</span>
              </label>
              <select
                v-model="form.work_mode"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 capitalize outline-none focus:border-blue-500"
              >
                <option v-for="m in workModes" :key="m" :value="m">{{ m }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Experience Level</label>
              <select
                v-model="form.experience_level"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 capitalize outline-none focus:border-blue-500"
              >
                <option value="">Not specified</option>
                <option v-for="el in experienceLevels" :key="el" :value="el">{{ el }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Location <span class="text-rose-600 dark:text-rose-400">*</span>
              </label>
              <input
                v-model="form.location"
                type="text"
                placeholder="e.g. Phnom Penh"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500"
              />
            </div>
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

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Salary Min</label>
              <input
                v-model="form.salary_min"
                type="number"
                step="0.01"
                placeholder="e.g. 1000"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Salary Max</label>
              <input
                v-model="form.salary_max"
                type="number"
                step="0.01"
                placeholder="e.g. 3000"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 outline-none focus:border-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Currency</label>
              <select
                v-model="form.salary_currency"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none focus:border-blue-500"
              >
                <option v-for="cur in currencies" :key="cur" :value="cur">{{ cur }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Period</label>
              <select
                v-model="form.salary_period"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 capitalize outline-none focus:border-blue-500"
              >
                <option v-for="p in salaryPeriods" :key="p" :value="p">{{ p }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Vacancies</label>
              <input
                v-model="form.vacancies"
                type="number"
                min="1"
                max="100"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none focus:border-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Application Deadline</label>
              <input
                v-model="form.deadline"
                type="date"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none focus:border-blue-500"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Status <span class="text-rose-600 dark:text-rose-400">*</span>
              </label>
              <select
                v-model="form.status"
                class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 capitalize outline-none focus:border-blue-500"
              >
                <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
              </select>
            </div>
            <div class="flex items-end gap-6 pb-2">
              <label class="flex items-center gap-2.5 text-sm text-slate-700 dark:text-slate-300 cursor-pointer">
                <input
                  v-model="form.is_salary_visible"
                  type="checkbox"
                  class="w-4 h-4 rounded accent-blue-600"
                />
                Salary visible
              </label>
              <label class="flex items-center gap-2.5 text-sm text-slate-700 dark:text-slate-300 cursor-pointer">
                <input
                  v-model="form.is_featured"
                  type="checkbox"
                  class="w-4 h-4 rounded accent-blue-600"
                />
                Featured
              </label>
            </div>
          </div>

          <!-- Skills -->
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Skills</label>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="skill in skills"
                :key="skill.id"
                type="button"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border transition-colors"
                :class="selectedSkills.some((s) => s.id === skill.id)
                  ? 'bg-blue-600 border-blue-600 text-white'
                  : 'bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400'"
                @click="toggleSkill(skill)"
              >
                {{ skill.name }}
              </button>
            </div>

            <div v-if="selectedSkills.length > 0" class="mt-4 space-y-3">
              <div
                v-for="sel in selectedSkills"
                :key="sel.id"
                class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700"
              >
                <span class="text-sm font-medium text-slate-800 dark:text-slate-200 flex-1">{{ sel.name }}</span>
                <select
                  v-model="sel.level"
                  class="bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200 capitalize outline-none focus:border-blue-500"
                >
                  <option value="">Any level</option>
                  <option value="beginner">Beginner</option>
                  <option value="intermediate">Intermediate</option>
                  <option value="advanced">Advanced</option>
                  <option value="expert">Expert</option>
                </select>
                <label class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400 cursor-pointer">
                  <input
                    v-model="sel.is_required"
                    type="checkbox"
                    class="w-3.5 h-3.5 rounded accent-blue-600"
                  />
                  Required
                </label>
              </div>
            </div>
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
            @click="submitJobPost"
            :disabled="submitting"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors"
          >
            <Loader2 v-if="submitting" class="w-4 h-4 animate-spin" />
            <CheckCircle2 v-else class="w-4 h-4" />
            {{ submitting ? 'Saving...' : (editingJob ? 'Save Job Post' : 'Create Job Post') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
