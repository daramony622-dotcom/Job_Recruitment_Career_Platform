<script setup>
import { onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import {
  Briefcase,
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
} from 'lucide-vue-next'
import { adminApi } from '../api'
import StatusBadge from '../components/StatusBadge.vue'

const router = useRouter()

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

const showModal = ref(false)
const submitting = ref(false)
const submitError = ref('')
const submitSuccess = ref('')

const companies = ref([])
const categories = ref([])
const skills = ref([])
const selectedSkills = ref([])

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
    const [companyRes, categoryRes, skillRes] = await Promise.all([
      adminApi.getCompanies({ per_page: 100 }),
      adminApi.getJobCategories({ per_page: 100 }),
      adminApi.getSkills({ per_page: 100 }),
    ])

    const pager = (res) => (res?.data?.data || res?.data || [])
    companies.value = pager(companyRes.data)
    categories.value = pager(categoryRes.data)
    skills.value = pager(skillRes.data)
  } catch (e) {
    companies.value = []
    categories.value = []
    skills.value = []
  }
}

async function openModal() {
  resetForm()
  showModal.value = true
  if (companies.value.length === 0 || categories.value.length === 0 || skills.value.length === 0) {
    await fetchLookups()
  }
}

function closeModal() {
  if (submitting.value) return
  showModal.value = false
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

    await adminApi.storeJobPost(payload)
    submitSuccess.value = 'Job post created successfully.'
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

function goToPage(page) {
  if (page < 1 || page > pagination.last_page) return
  filters.page = page
  fetchJobPosts()
}

function onView(job) {
  alert(`View job post: ${job.title}`)
}

function onEdit(job) {
  alert(`Edit job post: ${job.title}`)
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

onMounted(fetchJobPosts)
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <Briefcase class="w-6 h-6 text-blue-600 dark:text-blue-500" />
          Job Posts
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ pagination.total }} job posts in total</p>
      </div>
      <button
        @click="openModal"
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors"
      >
        <Plus class="w-4 h-4" />
        New Job Post
      </button>
    </div>

    <div class="flex flex-col lg:flex-row gap-3 mb-6">
      <div class="flex-1 flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2.5 max-w-xl">
        <Search class="w-4 h-4 text-slate-500" />
        <input
          v-model="filters.search"
          type="text"
          placeholder="Search by title, description, or company..."
          class="bg-transparent outline-none text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 w-full"
        />
      </div>

      <div class="flex gap-3">
        <select
          v-model="filters.status"
          class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2.5 text-sm text-slate-800 dark:text-slate-200 outline-none focus:border-blue-500"
        >
          <option value="">All statuses</option>
          <option value="draft">Draft</option>
          <option value="published">Published</option>
          <option value="closed">Closed</option>
          <option value="suspended">Suspended</option>
        </select>
        <button
          @click="fetchJobPosts"
          class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
        >
          <RefreshCw class="w-4 h-4" />
          <span class="hidden sm:inline">Refresh</span>
        </button>
      </div>
    </div>

    <p v-if="error" class="mb-4 text-sm text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">
      {{ error }}
    </p>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 text-xs uppercase border-b border-slate-200 dark:border-slate-800">
            <tr>
              <th class="px-5 py-3.5 font-semibold">Job Title</th>
              <th class="px-5 py-3.5 font-semibold">Company</th>
              <th class="px-5 py-3.5 font-semibold">Location</th>
              <th class="px-5 py-3.5 font-semibold">Type / Mode</th>
              <th class="px-5 py-3.5 font-semibold">Category</th>
              <th class="px-5 py-3.5 font-semibold">Salary</th>
              <th class="px-5 py-3.5 font-semibold">Status</th>
              <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-if="loading" class="hover:bg-slate-100 dark:hover:bg-slate-800/30">
              <td colspan="8" class="px-5 py-10 text-center text-slate-600 dark:text-slate-400">Loading job posts...</td>
            </tr>
            <tr v-else-if="jobPosts.length === 0" class="hover:bg-slate-100 dark:hover:bg-slate-800/30">
              <td colspan="8" class="px-5 py-10 text-center text-slate-600 dark:text-slate-400">No job posts found.</td>
            </tr>
            <tr
              v-for="job in jobPosts"
              :key="job.id"
              class="hover:bg-slate-100 dark:hover:bg-slate-800/30 transition-colors"
            >
              <td class="px-5 py-4">
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ job.title }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ job.views_count }} views · {{ job.vacancies }} openings</p>
              </td>
              <td class="px-5 py-4 text-slate-700 dark:text-slate-300">{{ job.company?.name || '—' }}</td>
              <td class="px-5 py-4 text-slate-700 dark:text-slate-300">{{ job.city || job.location || '—' }}</td>
              <td class="px-5 py-4 text-slate-700 dark:text-slate-300 capitalize">
                {{ (job.job_type || '—').replace('_', ' ') }}
                <span class="text-slate-500">·</span>
                {{ (job.work_mode || '—').replace('_', ' ') }}
              </td>
              <td class="px-5 py-4 text-slate-700 dark:text-slate-300">{{ job.category?.name || '—' }}</td>
              <td class="px-5 py-4 text-slate-700 dark:text-slate-300">
                <span v-if="job.salary_min">
                  {{ job.salary_currency }} {{ job.salary_min }}<span v-if="job.salary_max"> – {{ job.salary_max }}</span>
                </span>
                <span v-else>N/A</span>
              </td>
              <td class="px-5 py-4"><StatusBadge :value="job.status" /></td>
              <td class="px-5 py-4">
                <div class="flex items-center justify-end gap-1">
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
              New Job Post
            </h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">Create a new job post for any company.</p>
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
              <select
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
            {{ submitting ? 'Creating...' : 'Create Job Post' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
