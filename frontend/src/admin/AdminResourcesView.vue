<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import {
  Activity,
  Archive,
  BriefcaseBusiness,
  CheckCircle2,
  Clock3,
  FileText,
  FolderTree,
  Mail,
  Pencil,
  Plus,
  RefreshCw,
  Search,
  Tag,
  Trash2,
  Users,
  XCircle,
  ExternalLink,
  Phone,
  User,
  Calendar,
  Globe,
  MapPin,
  Download,
  Link,
  Eye,
} from 'lucide-vue-next'
import { adminApi } from '../api'
import StatusBadge from './StatusBadge.vue'
import PaginationControls from './PaginationControls.vue'

const route = useRoute()
const loading = ref(false)
const error = ref('')
const notice = ref('')
const formOpen = ref(false)
const editing = ref(null)
const submitting = ref(false)
const formError = ref('')
const detailOpen = ref(false)
const detailLoading = ref(false)
const detail = ref(null)
const query = ref('')
const status = ref('')
const records = ref([])
const pagination = reactive({ current_page: 1, last_page: 1, total: 0, per_page: 15 })
const skillCategoriesList = ref([])
const applicationOptions = ref([])
const applicationsLoading = ref(false)
const companyProfileAvailable = ref(false)
const isCompanyScopedUser = ['hr', 'company'].includes(JSON.parse(localStorage.getItem('admin_user') || localStorage.getItem('user') || 'null')?.role)

const configs = {
  applications: {
    title: 'Applications',
    description: 'Review applications across the platform.',
    icon: FileText,
    fetch: (params) => adminApi.getApplications(params),
    show: (item) => adminApi.showApplication(item.id),
    statusUpdate: (id, data) => adminApi.updateApplicationStatus(id, data),
    columns: ['candidate', 'job', 'status', 'created_at'],
  },
  interviews: {
    title: 'Interviews',
    description: 'Manage scheduled interviews across the platform.',
    icon: Clock3,
    fetch: (params) => adminApi.getInterviews(params),
    remove: (item) => adminApi.deleteInterview(item.id),
    create: (data) => adminApi.storeInterview(data),
    update: (id, data) => adminApi.updateInterview(id, data),
    cancel: (id) => adminApi.cancelInterview(id),
    columns: ['candidate', 'job', 'interview_type', 'status', 'scheduled_at'],
  },
  categories: {
    title: 'Job Categories',
    description: 'Maintain the categories used by job posts.',
    icon: Tag,
    fetch: (params) => adminApi.getJobCategories(params),
    show: (item) => adminApi.showJobCategory(item.id),
    remove: (item) => adminApi.deleteJobCategory(item.id),
    create: (data) => adminApi.storeJobCategory(data),
    update: (id, data) => adminApi.updateJobCategory(id, data),
    toggle: (id) => adminApi.toggleJobCategory(id),
    columns: ['name', 'slug', 'jobs_count', 'is_active', 'created_at'],
  },
  'skill-categories': {
    title: 'Skill Categories',
    description: 'Maintain the taxonomy categories used by candidate skills and job requirements.',
    icon: FolderTree,
    fetch: (params) => adminApi.getSkillCategories(params),
    show: (item) => adminApi.showSkillCategory(item.id),
    remove: (item) => adminApi.deleteSkillCategory(item.id),
    create: (data) => adminApi.storeSkillCategory(data),
    update: (id, data) => adminApi.updateSkillCategory(id, data),
    toggle: (id) => adminApi.toggleSkillCategory(id),
    columns: ['name', 'slug', 'skills_count', 'is_active', 'created_at'],
  },
  skills: {
    title: 'Skills Directory',
    description: 'Maintain the skill directory used by candidates and jobs.',
    icon: Activity,
    fetch: (params) => adminApi.getSkills(params),
    show: (item) => adminApi.showSkill(item.id),
    remove: (item) => adminApi.deleteSkill(item.id),
    create: (data) => adminApi.storeSkill(data),
    update: (id, data) => adminApi.updateSkill(id, data),
    columns: ['name', 'category', 'is_active', 'created_at'],
  },
  messages: {
    title: 'Inquiries & Messages',
    description: 'Contact form messages and support inquiries received from the frontend.',
    icon: Mail,
    fetch: (params) => adminApi.getContactMessages(params),
    show: (item) => adminApi.showContactMessage(item.id),
    remove: (item) => adminApi.deleteContactMessage(item.id),
    columns: ['name', 'email', 'phone', 'subject_type', 'subject', 'is_read', 'created_at'],
  },
  jobs: {
    title: 'Queue Jobs',
    description: 'Inspect queued jobs managed by the application.',
    icon: BriefcaseBusiness,
    fetch: (params) => adminApi.getJobs(params),
    remove: (item) => adminApi.deleteJob(item.id),
    create: (data) => adminApi.storeJob(data),
    update: (id, data) => adminApi.updateJob(id, data),
    columns: ['queue', 'payload', 'attempts', 'available_at'],
  },
  batches: {
    title: 'Job Batches',
    description: 'Inspect queue batch progress and completion.',
    icon: Archive,
    fetch: (params) => adminApi.getJobBatches(params),
    remove: (item) => adminApi.deleteJobBatch(item.id),
    create: (data) => adminApi.storeJobBatch(data),
    update: (id, data) => adminApi.updateJobBatch(id, data),
    columns: ['name', 'total_jobs', 'pending_jobs', 'failed_jobs', 'finished_at'],
  },
  'failed-jobs': {
    title: 'Failed Jobs',
    description: 'Review failures and retry or remove them.',
    icon: XCircle,
    fetch: (params) => adminApi.getFailedJobs(params),
    remove: (item) => adminApi.deleteFailedJob(item.id),
    create: (data) => adminApi.storeFailedJob(data),
    update: (id, data) => adminApi.updateFailedJob(id, data),
    columns: ['uuid', 'connection', 'queue', 'exception', 'failed_at'],
  },
}

const resource = computed(() => configs[route.params.resource] || configs.applications)

const form = reactive({ name: '', slug: '', category: '', category_id: '', description: '', icon: '', is_active: true })
const interviewForm = reactive({ application_id: '', interview_type: 'video', title: '', scheduled_at: '', duration_minutes: 60, location: '', meeting_link: '', notes_for_candidate: '', internal_notes: '' })
const queueForm = reactive({
  id: '', queue: 'default', payload: '', attempts: 0, available_at: 0, created_at: 0,
  name: '', total_jobs: 0, pending_jobs: 0, failed_jobs: 0, failed_job_ids: '', options: '',
  uuid: '', connection: 'database', exception: '',
})

function unwrap(data) {
  const value = data?.data ?? data
  if (Array.isArray(value)) return value
  if (Array.isArray(value?.data)) return value.data
  return []
}

async function fetchSkillCategoriesList() {
  try {
    const res = await adminApi.getSkillCategories({ per_page: 100 })
    skillCategoriesList.value = unwrap(res.data)
  } catch {
    // Non-blocking
  }
}

async function fetchApplicationOptions() {
  applicationsLoading.value = true
  try {
    const response = await adminApi.getApplications({ per_page: 100 })
    applicationOptions.value = unwrap(response.data)
  } catch {
    applicationOptions.value = []
  } finally {
    applicationsLoading.value = false
  }
}

async function checkCompanyProfile() {
  if (!isCompanyScopedUser || !['interviews', 'applications'].includes(route.params.resource)) return
  try {
    await adminApi.getCompanyProfile()
    companyProfileAvailable.value = true
  } catch {
    companyProfileAvailable.value = false
  }
}

async function fetchRecords() {
  loading.value = true
  error.value = ''
  try {
    const response = await resource.value.fetch({
      search: query.value.trim() || undefined,
      status: status.value || undefined,
      page: pagination.current_page,
      per_page: pagination.per_page,
    })
    records.value = unwrap(response.data)
    const payload = response.data?.data && !Array.isArray(response.data.data) ? response.data.data : response.data
    Object.assign(pagination, {
      current_page: Number(payload?.current_page || 1),
      last_page: Number(payload?.last_page || 1),
      total: Number(payload?.total || records.value.length),
      per_page: Number(payload?.per_page || pagination.per_page),
    })
  } catch (e) {
    error.value = e.response?.data?.message || `Failed to load ${resource.value.title.toLowerCase()}.`
    records.value = []
  } finally {
    loading.value = false
  }
}

watch(() => route.params.resource, () => {
  query.value = ''
  status.value = ''
  pagination.current_page = 1
  fetchRecords()
  checkCompanyProfile()
  if (route.params.resource === 'skills' || route.params.resource === 'skill-categories') {
    fetchSkillCategoriesList()
  }
})

watch([query, status], () => {
  pagination.current_page = 1
  fetchRecords()
})

function goToPage(page) {
  if (page < 1 || page > pagination.last_page || page === pagination.current_page) return
  pagination.current_page = page
  fetchRecords()
}

onMounted(() => {
  fetchRecords()
  fetchSkillCategoriesList()
  checkCompanyProfile()
})

async function openDetails(item) {
  detailLoading.value = true
  detailOpen.value = true
  detail.value = null
  try {
    const response = await resource.value.show(item)
    detail.value = response.data?.data || response.data
    if (route.params.resource === 'messages') {
      const target = records.value.find((r) => r.id === item.id)
      if (target) target.is_read = true
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to load details.'
    detailOpen.value = false
  } finally {
    detailLoading.value = false
  }
}

function formatSubjectType(type) {
  switch (type) {
    case 'candidate': return 'Candidate Support'
    case 'employer': return 'Employer / Hiring'
    case 'tech': return 'Technical Issue'
    default: return 'General Inquiry'
  }
}

function applicationApplicant(application) {
  return application?.job_seeker || application?.jobSeeker || application?.applicant || application?.user || {}
}

function applicationProfile(application) {
  const applicant = applicationApplicant(application)
  return applicant.profile || application?.profile || {}
}

function applicationJob(application) {
  return application?.job_post || application?.jobPost || application?.job || {}
}

function formatDate(value, fallback = 'Not provided') {
  if (!value) return fallback
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? String(value) : date.toLocaleString()
}

function displayValue(value, fallback = 'Not provided') {
  return value === null || value === undefined || value === '' ? fallback : value
}

function valueFor(item, column) {
  if (column === 'candidate') return item.job_seeker?.name || item.jobSeeker?.name || item.applicant?.name || item.applicant?.user?.name || '—'
  if (column === 'job') return item.job_post?.title || item.jobPost?.title || item.job?.title || '—'
  if (column === 'payload' || column === 'exception') return String(item[column] || '—').slice(0, 90)
  if (column === 'skills_count') return item.skills_count ?? item.skills?.length ?? 0
  if (column === 'jobs_count') return item.jobs_count ?? item.job_posts_count ?? 0
  if (column === 'category') return item.skill_category?.name || item.category || '—'
  if (column === 'subject_type') return formatSubjectType(item[column])
  if (column === 'is_read') return item[column] ? 'Read' : 'New'
  if (column.endsWith('_at') && item[column]) return new Date(item[column]).toLocaleString()
  if (column === 'is_active') return item[column] ? 'Active' : 'Inactive'
  return item[column] ?? '—'
}

function labelFor(column) {
  if (column === 'skills_count') return 'Skills Total'
  if (column === 'jobs_count') return 'Jobs Total'
  if (column === 'subject_type') return 'Inquiry Type'
  if (column === 'is_read') return 'Status'
  return column.replace(/_/g, ' ').replace(/\b\w/g, (letter) => letter.toUpperCase())
}

function filterSimpleFields(obj) {
  if (!obj || typeof obj !== 'object') return {}
  const res = {}
  for (const [key, val] of Object.entries(obj)) {
    if (val === null || val === undefined || typeof val === 'string' || typeof val === 'number' || typeof val === 'boolean') {
      res[key] = val
    }
  }
  return res
}

async function remove(item) {
  if (!resource.value.remove || !confirm(`Delete this ${resource.value.title.toLowerCase().replace(/s$/, '')}?`)) return
  try {
    await resource.value.remove(item)
    notice.value = 'Record deleted successfully.'
    await fetchRecords()
  } catch (e) {
    error.value = e.response?.data?.message || 'Delete failed.'
  }
}

async function retry(item) {
  try {
    await adminApi.retryFailedJob(item.id)
    notice.value = 'Failed job sent for retry.'
    await fetchRecords()
  } catch (e) {
    error.value = e.response?.data?.message || 'Retry failed.'
  }
}

async function cancelInterview(item) {
  try {
    await resource.value.cancel(item.id)
    notice.value = 'Interview cancelled successfully.'
    await fetchRecords()
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to cancel interview.'
  }
}

async function updateApplicationStatus(item, nextStatus) {
  if (!resource.value.statusUpdate || !nextStatus || nextStatus === item.status) return

  const payload = { status: nextStatus }
  if (nextStatus === 'rejected') {
    const rejectionReason = window.prompt('Reason for rejecting this application:')
    if (rejectionReason === null) return
    payload.rejection_reason = rejectionReason.trim()
  }

  try {
    await resource.value.statusUpdate(item.id, payload)
    item.status = nextStatus
    notice.value = 'Application status updated successfully.'
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to update application status.'
  }
}

async function toggleCategory(item) {
  try {
    await resource.value.toggle(item.id)
    notice.value = 'Category status updated.'
    await fetchRecords()
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to update category status.'
  }
}

async function flushFailedJobs() {
  if (!confirm('Remove all failed jobs?')) return
  try {
    await adminApi.flushFailedJobs()
    notice.value = 'Failed jobs cleared.'
    await fetchRecords()
  } catch (e) {
    error.value = e.response?.data?.message || 'Could not clear failed jobs.'
  }
}

function openCreate() {
  editing.value = null
  Object.assign(form, { name: '', slug: '', category: '', category_id: '', description: '', icon: '', is_active: true })
  formError.value = ''
  Object.assign(interviewForm, { application_id: '', interview_type: 'video', title: '', scheduled_at: '', duration_minutes: 60, location: '', meeting_link: '', notes_for_candidate: '', internal_notes: '' })
  Object.assign(queueForm, { id: '', queue: 'default', payload: '', attempts: 0, available_at: 0, created_at: 0, name: '', total_jobs: 0, pending_jobs: 0, failed_jobs: 0, failed_job_ids: '', options: '', uuid: '', connection: 'database', exception: '' })
  formOpen.value = true
  if (route.params.resource === 'interviews') fetchApplicationOptions()
}

function openEdit(item) {
  editing.value = item
  Object.assign(form, {
    name: item.name || '',
    slug: item.slug || '',
    category: item.category || '',
    category_id: item.category_id || (item.skill_category?.id ?? ''),
    description: item.description || '',
    icon: item.icon || '',
    is_active: item.is_active !== false,
  })
  formError.value = ''
  Object.assign(interviewForm, {
    application_id: item.application_id || item.application?.id || '',
    interview_type: item.interview_type || 'video',
    title: item.title || '',
    scheduled_at: item.scheduled_at ? String(item.scheduled_at).slice(0, 16) : '',
    duration_minutes: item.duration_minutes || 60,
    location: item.location || '',
    meeting_link: item.meeting_link || '',
    notes_for_candidate: item.notes_for_candidate || '',
    internal_notes: item.internal_notes || '',
  })
  Object.assign(queueForm, item)
  formOpen.value = true
}

async function saveResource() {
  submitting.value = true
  formError.value = ''
  try {
    const currentRes = route.params.resource
    if (['jobs', 'batches', 'failed-jobs'].includes(currentRes)) {
      let payload
      if (currentRes === 'jobs') {
        payload = { queue: queueForm.queue, payload: queueForm.payload, attempts: Number(queueForm.attempts || 0), available_at: Number(queueForm.available_at || 0), created_at: Number(queueForm.created_at || Math.floor(Date.now() / 1000)) }
        if (!editing.value) payload.id = queueForm.id || undefined
      } else if (currentRes === 'batches') {
        payload = { id: queueForm.id || undefined, name: queueForm.name, total_jobs: Number(queueForm.total_jobs || 0), pending_jobs: Number(queueForm.pending_jobs || 0), failed_jobs: Number(queueForm.failed_jobs || 0), failed_job_ids: queueForm.failed_job_ids || '', options: queueForm.options || undefined, created_at: Number(queueForm.created_at || Math.floor(Date.now() / 1000)) }
      } else {
        payload = { uuid: queueForm.uuid || undefined, connection: queueForm.connection, queue: queueForm.queue, payload: queueForm.payload, exception: queueForm.exception }
      }
      if (editing.value) await resource.value.update(editing.value.id, payload)
      else await resource.value.create(payload)
    } else if (currentRes === 'interviews') {
      const payload = { ...interviewForm }
      if (editing.value) await resource.value.update(editing.value.id, payload)
      else await resource.value.create(payload)
    } else if (currentRes === 'skill-categories') {
      const payload = {
        name: form.name,
        slug: form.slug || undefined,
        description: form.description || undefined,
        icon: form.icon || undefined,
        is_active: form.is_active,
      }
      if (editing.value) await resource.value.update(editing.value.id, payload)
      else await resource.value.create(payload)
      await fetchSkillCategoriesList()
    } else if (currentRes === 'categories') {
      const payload = {
        name: form.name,
        slug: form.slug || undefined,
        description: form.description || undefined,
        is_active: form.is_active,
      }
      if (editing.value) await resource.value.update(editing.value.id, payload)
      else await resource.value.create(payload)
    } else if (currentRes === 'skills') {
      const payload = {
        name: form.name,
        category: form.category || undefined,
        category_id: form.category_id || undefined,
        description: form.description || undefined,
        is_active: form.is_active,
      }
      if (editing.value) await resource.value.update(editing.value.id, payload)
      else await resource.value.create(payload)
    }

    formOpen.value = false
    notice.value = editing.value ? 'Record updated successfully.' : 'Record created successfully.'
    await fetchRecords()
  } catch (e) {
    const response = e.response?.data
    formError.value = response?.errors ? Object.values(response.errors).flat().join(' ') : response?.message || 'Save failed.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div class="flex items-start gap-3">
        <div class="w-11 h-11 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
          <component :is="resource.icon" class="w-5 h-5" />
        </div>
        <div>
          <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ resource.title }}</h2>
          <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ resource.description }}</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          v-if="resource.create && (route.params.resource !== 'interviews' || !isCompanyScopedUser || companyProfileAvailable)"
          class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 shadow-md shadow-blue-600/20 cursor-pointer"
          @click="openCreate"
        >
          <Plus class="w-4 h-4" /> Add {{ resource.title.replace(/s$/, '').replace(/ies$/, 'y') }}
        </button>
        <button
          v-if="route.params.resource === 'failed-jobs'"
          class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl border border-rose-300 dark:border-rose-800 text-rose-600 dark:text-rose-400 text-sm font-semibold hover:bg-rose-500/10 cursor-pointer"
          @click="flushFailedJobs"
        >
          <Trash2 class="w-4 h-4" /> Flush
        </button>
        <button
          class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold hover:border-blue-500 cursor-pointer"
          @click="fetchRecords"
        >
          <RefreshCw class="w-4 h-4" :class="loading ? 'animate-spin' : ''" /> Refresh
        </button>
      </div>
    </div>

    <!-- Filter & Search toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 mb-5">
      <div class="flex-1 flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2.5 shadow-sm">
        <Search class="w-4 h-4 text-slate-400" />
        <label for="resource-search" class="sr-only">Search records</label>
        <input id="resource-search" v-model="query" class="bg-transparent outline-none text-sm w-full text-slate-900 dark:text-slate-100 placeholder-slate-400" placeholder="Search records..." />
      </div>
      <label v-if="['applications', 'interviews'].includes(route.params.resource)" for="resource-status" class="sr-only">Filter by status</label>
      <select v-if="['applications', 'interviews'].includes(route.params.resource)" id="resource-status" v-model="status" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2.5 text-sm text-slate-700 dark:text-slate-300">
        <option value="">All statuses</option>
        <option value="pending">Pending</option>
        <option value="scheduled">Scheduled</option>
        <option value="completed">Completed</option>
        <option value="rejected">Rejected</option>
      </select>
    </div>

    <!-- Alert Notices -->
    <p v-if="error" class="mb-4 text-sm text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">{{ error }}</p>
    <p v-if="notice" class="mb-4 text-sm text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3">{{ notice }}</p>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
          <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 text-[10px] font-extrabold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
            <tr>
              <th v-for="column in resource.columns" :key="column" class="px-4 py-2.5 font-bold">{{ labelFor(column) }}</th>
              <th class="px-4 py-2.5 font-bold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-if="loading">
              <td :colspan="resource.columns.length + 1" class="px-4 py-8 text-center text-slate-500">
                <RefreshCw class="w-4 h-4 animate-spin mx-auto mb-2 text-blue-500" />
                Loading records...
              </td>
            </tr>
            <tr v-else-if="records.length === 0">
              <td :colspan="resource.columns.length + 1" class="px-4 py-8 text-center text-slate-500">
                No records found.
              </td>
            </tr>
            <tr v-for="item in records" v-else :key="item.id || item.uuid" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
              <td v-for="column in resource.columns" :key="column" class="px-4 py-2.5 max-w-xs text-slate-700 dark:text-slate-300 font-medium">
                <StatusBadge v-if="column === 'status'" :value="valueFor(item, column)" />
                <span v-else-if="column === 'is_read'" class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold" :class="item.is_read ? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300'">
                  <span class="notranslate" translate="no">{{ valueFor(item, column) }}</span>
                </span>
                <span v-else-if="column === 'is_active'" class="inline-flex items-center gap-1.5 font-semibold text-xs">
                  <CheckCircle2 class="w-3.5 h-3.5" :class="item.is_active ? 'text-emerald-500' : 'text-slate-400'" />
                  <span class="notranslate" translate="no">{{ valueFor(item, column) }}</span>
                </span>
                <span v-else-if="column === 'skills_count' || column === 'jobs_count'" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-extrabold bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-cyan-300">
                  <span class="notranslate" translate="no">{{ valueFor(item, column) }}</span>
                </span>
                <span v-else class="block truncate" :title="String(valueFor(item, column))">
                  <span class="notranslate" translate="no">{{ valueFor(item, column) }}</span>
                </span>
              </td>
              <td class="px-4 py-2.5">
                <div class="flex items-center justify-end gap-1">
                  <label v-if="resource.statusUpdate" class="sr-only" :for="`application-status-${item.id}`">Update application status</label>
                  <select
                    v-if="resource.statusUpdate && (route.params.resource !== 'applications' || !isCompanyScopedUser || companyProfileAvailable)"
                    :id="`application-status-${item.id}`"
                    :value="item.status"
                    class="max-w-32 rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                    title="Update application status"
                    @change="updateApplicationStatus(item, $event.target.value)"
                  >
                    <option value="pending">Pending</option>
                    <option value="reviewing">Reviewing</option>
                    <option value="shortlisted">Shortlisted</option>
                    <option value="interview">Interview</option>
                    <option value="offered">Offered</option>
                    <option value="hired">Hired</option>
                    <option value="rejected">Rejected</option>
                    <option value="withdrawn">Withdrawn</option>
                  </select>
                  <button
                    v-if="resource.show"
                    class="p-1.5 text-blue-600 dark:text-cyan-400 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer"
                    title="View details"
                    @click="openDetails(item)"
                  >
                    <component :is="route.params.resource === 'messages' ? Mail : Users" class="w-3.5 h-3.5" />
                  </button>
                  <button
                    v-if="route.params.resource === 'failed-jobs'"
                    class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer"
                    title="Retry"
                    @click="retry(item)"
                  >
                    <RefreshCw class="w-3.5 h-3.5" />
                  </button>
                  <button
                    v-if="resource.cancel && ['scheduled', 'pending'].includes(String(item.status).toLowerCase())"
                    class="p-1.5 text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer"
                    title="Cancel interview"
                    @click="cancelInterview(item)"
                  >
                    <XCircle class="w-3.5 h-3.5" />
                  </button>
                  <button
                    v-if="resource.toggle"
                    class="p-1.5 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer"
                    :title="item.is_active ? 'Deactivate' : 'Activate'"
                    @click="toggleCategory(item)"
                  >
                    <CheckCircle2 class="w-3.5 h-3.5" />
                  </button>
                  <button
                    v-if="resource.update"
                    class="p-1.5 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer"
                    title="Edit"
                    @click="openEdit(item)"
                  >
                    <Pencil class="w-3.5 h-3.5" />
                  </button>
                  <button
                    v-if="resource.remove"
                    class="p-1.5 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer"
                    title="Delete"
                    @click="remove(item)"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <PaginationControls
      :current-page="pagination.current_page"
      :last-page="pagination.last_page"
      :total="pagination.total"
      :per-page="pagination.per_page"
      @update:page="goToPage"
    />

    <!-- Detail Modal -->
    <div v-if="detailOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="detailOpen = false"></div>
      <div class="relative w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-2xl">
        <div class="flex items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
          <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <component :is="route.params.resource === 'messages' ? Mail : FileText" class="w-5 h-5 text-blue-600" />
            {{ route.params.resource === 'messages' ? 'Message & Inquiry Details' : 'Details' }}
          </h3>
          <button class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 text-sm font-semibold cursor-pointer" @click="detailOpen = false">
            Close
          </button>
        </div>

        <div v-if="detailLoading" class="py-12 text-center text-slate-500">
          <RefreshCw class="w-5 h-5 animate-spin mx-auto mb-2 text-blue-500" />
          Loading details...
        </div>

        <!-- Custom Contact Message Details View -->
        <div v-else-if="route.params.resource === 'messages' && detail" class="mt-5 space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 border border-slate-100 dark:border-slate-800">
            <div>
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sender Name</p>
              <p class="text-sm font-bold text-slate-900 dark:text-slate-100 mt-0.5 flex items-center gap-1.5">
                <User class="w-4 h-4 text-blue-500" />
                {{ detail.name || '—' }}
              </p>
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sender Email</p>
              <a :href="`mailto:${detail.email}?subject=Re: ${encodeURIComponent(detail.subject || 'Your inquiry')}`" class="text-sm font-bold text-blue-600 dark:text-blue-400 hover:underline mt-0.5 flex items-center gap-1.5">
                <Mail class="w-4 h-4" />
                {{ detail.email || '—' }}
              </a>
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Phone</p>
              <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 mt-0.5 flex items-center gap-1.5">
                <Phone class="w-4 h-4 text-slate-400" />
                {{ detail.phone || 'Not provided' }}
              </p>
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Inquiry Type</p>
              <span class="inline-block mt-1 px-2.5 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300">
                {{ formatSubjectType(detail.subject_type) }}
              </span>
            </div>
          </div>

          <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Subject</p>
            <p class="text-base font-bold text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5">
              {{ detail.subject }}
            </p>
          </div>

          <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Message Content</p>
            <div class="bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-800 rounded-xl p-4 text-sm text-slate-800 dark:text-slate-200 whitespace-pre-wrap leading-relaxed">
              {{ detail.message }}
            </div>
          </div>

          <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100 dark:border-slate-800">
            <span class="flex items-center gap-1"><Calendar class="w-3.5 h-3.5" /> Received: {{ new Date(detail.created_at).toLocaleString() }}</span>
            <span v-if="detail.ip_address" class="flex items-center gap-1"><Globe class="w-3.5 h-3.5" /> IP: {{ detail.ip_address }}</span>
          </div>

          <div class="pt-3 flex justify-end gap-2">
            <a
              :href="`mailto:${detail.email}?subject=Re: ${encodeURIComponent(detail.subject || 'Inquiry Response')}`"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 shadow-md shadow-blue-600/20"
            >
              <Mail class="w-4 h-4" /> Reply via Email
            </a>
          </div>
        </div>

        <!-- Applicant-friendly application details -->
        <div v-else-if="route.params.resource === 'applications' && detail" class="mt-5 space-y-5">
          <div class="flex items-start gap-4 rounded-2xl bg-linear-to-br from-blue-50 to-slate-50 dark:from-blue-950/40 dark:to-slate-800/60 border border-blue-100 dark:border-blue-900/50 p-5">
            <img
              v-if="applicationApplicant(detail).avatar_url || applicationApplicant(detail).avatar"
              :src="applicationApplicant(detail).avatar_url || applicationApplicant(detail).avatar"
              :alt="`${applicationApplicant(detail).name || 'Applicant'} avatar`"
              class="w-16 h-16 rounded-2xl object-cover border-2 border-white dark:border-slate-700 shadow-sm"
            />
            <div v-else class="w-16 h-16 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-2xl font-bold shadow-sm">
              {{ (applicationApplicant(detail).name || 'A').charAt(0).toUpperCase() }}
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2">
                <h4 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ displayValue(applicationApplicant(detail).name, 'Applicant') }}</h4>
                <StatusBadge :value="detail.status || 'pending'" />
              </div>
              <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Application #{{ detail.id }} · Submitted {{ formatDate(detail.created_at) }}</p>
              <p v-if="applicationProfile(detail).headline" class="text-sm font-medium text-blue-700 dark:text-blue-300 mt-2">{{ applicationProfile(detail).headline }}</p>
            </div>
          </div>

          <section class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-4">
              <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Contact information</p>
              <div class="space-y-2.5 text-sm">
                <a :href="applicationApplicant(detail).email ? `mailto:${applicationApplicant(detail).email}` : undefined" class="flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:underline">
                  <Mail class="w-4 h-4 shrink-0" /> {{ displayValue(applicationApplicant(detail).email) }}
                </a>
                <a :href="applicationApplicant(detail).phone ? `tel:${applicationApplicant(detail).phone}` : undefined" class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                  <Phone class="w-4 h-4 shrink-0 text-slate-400" /> {{ displayValue(applicationApplicant(detail).phone || applicationProfile(detail).phone) }}
                </a>
                <p class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                  <MapPin class="w-4 h-4 shrink-0 text-slate-400" /> {{ displayValue([applicationProfile(detail).city, applicationProfile(detail).country].filter(Boolean).join(', ')) }}
                </p>
              </div>
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-4">
              <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Candidate profile</p>
              <div class="space-y-2 text-sm text-slate-700 dark:text-slate-300">
                <p><span class="text-slate-500">Availability:</span> {{ displayValue(applicationProfile(detail).availability) }}</p>
                <p><span class="text-slate-500">Experience:</span> {{ displayValue(applicationProfile(detail).experience_level || applicationApplicant(detail).experience_level) }}</p>
                <div class="flex flex-wrap gap-2 pt-1">
                  <a v-if="applicationProfile(detail).linkedin_url" :href="applicationProfile(detail).linkedin_url" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:underline"><Link class="w-3.5 h-3.5" /> LinkedIn</a>
                  <a v-if="applicationProfile(detail).github_url" :href="applicationProfile(detail).github_url" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:underline"><Link class="w-3.5 h-3.5" /> GitHub</a>
                  <a v-if="applicationProfile(detail).portfolio_url" :href="applicationProfile(detail).portfolio_url" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:underline"><Globe class="w-3.5 h-3.5" /> Portfolio</a>
                </div>
              </div>
            </div>
          </section>

          <section class="rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800 p-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Applied position</p>
                <h4 class="text-base font-bold text-slate-900 dark:text-slate-100 mt-1">{{ displayValue(applicationJob(detail).title, 'Job unavailable') }}</h4>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ displayValue(applicationJob(detail).company?.name) }} · {{ displayValue(applicationJob(detail).location || applicationJob(detail).city) }}</p>
              </div>
              <BriefcaseBusiness class="w-5 h-5 text-blue-500 shrink-0" />
            </div>
          </section>

          <section v-if="detail.cover_letter" class="space-y-1.5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cover letter</p>
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-4 text-sm leading-relaxed text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ detail.cover_letter }}</div>
          </section>

          <div class="flex flex-wrap items-center justify-between gap-3 pt-1 border-t border-slate-100 dark:border-slate-800">
            <div class="text-xs text-slate-500 space-y-1">
              <p v-if="detail.shortlisted_at">Shortlisted: {{ formatDate(detail.shortlisted_at) }}</p>
              <p v-if="detail.rejected_at">Rejected: {{ formatDate(detail.rejected_at) }}</p>
              <p v-if="detail.hired_at">Hired: {{ formatDate(detail.hired_at) }}</p>
              <p v-if="detail.rejection_reason">Reason: {{ detail.rejection_reason }}</p>
            </div>
            <a v-if="detail.cv_path || detail.cv_original_name" :href="detail.cv_path" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
              <Download class="w-4 h-4" /> {{ detail.cv_original_name || 'View CV' }}
            </a>
          </div>
        </div>

        <!-- Skill Categories Detail View -->
        <div v-else-if="route.params.resource === 'skill-categories' && detail" class="mt-5 space-y-6">
          <div class="rounded-2xl bg-linear-to-br from-blue-500/10 via-slate-50 to-slate-100 dark:from-blue-950/40 dark:via-slate-900 dark:to-slate-800/80 border border-blue-500/20 dark:border-blue-500/30 p-5 shadow-xs">
            <div class="flex items-start justify-between gap-4">
              <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-600/30 shrink-0">
                  <FolderTree class="w-6 h-6" />
                </div>
                <div>
                  <div class="flex items-center gap-2.5 flex-wrap">
                    <h4 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ detail.name }}</h4>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold" :class="detail.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-300 dark:border-slate-700'">
                      <CheckCircle2 class="w-3.5 h-3.5" :class="detail.is_active ? 'text-emerald-500' : 'text-slate-400'" />
                      {{ detail.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                  <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-1">Slug: <span class="text-blue-600 dark:text-cyan-400 font-semibold">{{ detail.slug }}</span></p>
                </div>
              </div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-200/80 dark:border-slate-800/80">
              <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Description</p>
              <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed font-medium">{{ detail.description || 'No description provided for this skill category.' }}</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4 pt-4 border-t border-slate-200/80 dark:border-slate-800/80 text-xs">
              <div>
                <span class="text-slate-500 dark:text-slate-400">Total Skills:</span>
                <p class="text-sm font-extrabold text-blue-600 dark:text-cyan-400">{{ detail.skills?.length || 0 }} skills</p>
              </div>
              <div>
                <span class="text-slate-500 dark:text-slate-400">Created:</span>
                <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ formatDate(detail.created_at) }}</p>
              </div>
              <div>
                <span class="text-slate-500 dark:text-slate-400">Last Updated:</span>
                <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ formatDate(detail.updated_at) }}</p>
              </div>
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between mb-3">
              <h5 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <Activity class="w-4 h-4 text-blue-500" />
                Associated Skills
              </h5>
              <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-cyan-300">
                {{ detail.skills?.length || 0 }} skills
              </span>
            </div>

            <div v-if="detail.skills && detail.skills.length > 0" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
              <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                  <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 text-[10px] font-extrabold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                      <th class="px-4 py-2.5 font-bold">Skill Name</th>
                      <th class="px-4 py-2.5 font-bold">Slug</th>
                      <th class="px-4 py-2.5 font-bold">Status</th>
                      <th class="px-4 py-2.5 font-bold">Description</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="sk in detail.skills" :key="sk.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                      <td class="px-4 py-3 font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <Tag class="w-3.5 h-3.5 text-blue-500" />
                        {{ sk.name }}
                      </td>
                      <td class="px-4 py-3 font-mono text-slate-500 dark:text-slate-400">{{ sk.slug }}</td>
                      <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 font-semibold text-xs" :class="sk.is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'">
                          <CheckCircle2 class="w-3.5 h-3.5" />
                          {{ sk.is_active ? 'Active' : 'Inactive' }}
                        </span>
                      </td>
                      <td class="px-4 py-3 text-slate-600 dark:text-slate-400 truncate max-w-xs">{{ sk.description || '—' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div v-else class="text-center py-8 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
              <FolderTree class="w-8 h-8 text-slate-400 mx-auto mb-2 opacity-50" />
              <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">No skills currently assigned to this category.</p>
            </div>
          </div>
        </div>

        <!-- Skills Directory Detail View -->
        <div v-else-if="route.params.resource === 'skills' && detail" class="mt-5 space-y-6">
          <div class="rounded-2xl bg-linear-to-br from-blue-500/10 via-slate-50 to-slate-100 dark:from-blue-950/40 dark:via-slate-900 dark:to-slate-800/80 border border-blue-500/20 dark:border-blue-500/30 p-5 shadow-xs">
            <div class="flex items-start justify-between gap-4">
              <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-600/30 shrink-0">
                  <Activity class="w-6 h-6" />
                </div>
                <div>
                  <div class="flex items-center gap-2.5 flex-wrap">
                    <h4 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ detail.name }}</h4>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold" :class="detail.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-300 dark:border-slate-700'">
                      <CheckCircle2 class="w-3.5 h-3.5" :class="detail.is_active ? 'text-emerald-500' : 'text-slate-400'" />
                      {{ detail.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                  <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-1">Slug: <span class="text-blue-600 dark:text-cyan-400 font-semibold">{{ detail.slug }}</span></p>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 pt-4 border-t border-slate-200/80 dark:border-slate-800/80">
              <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Skill Category</p>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                  <FolderTree class="w-3.5 h-3.5" />
                  {{ detail.skill_category?.name || detail.category || 'Uncategorized' }}
                </span>
              </div>
              <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Description</p>
                <p class="text-sm text-slate-700 dark:text-slate-300 font-medium">{{ detail.description || 'No description provided.' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Job Categories Detail View -->
        <div v-else-if="route.params.resource === 'categories' && detail" class="mt-5 space-y-6">
          <div class="rounded-2xl bg-linear-to-br from-blue-500/10 via-slate-50 to-slate-100 dark:from-blue-950/40 dark:via-slate-900 dark:to-slate-800/80 border border-blue-500/20 dark:border-blue-500/30 p-5 shadow-xs">
            <div class="flex items-start justify-between gap-4">
              <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-600/30 shrink-0">
                  <Tag class="w-6 h-6" />
                </div>
                <div>
                  <div class="flex items-center gap-2.5 flex-wrap">
                    <h4 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ detail.name }}</h4>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold" :class="detail.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-300 dark:border-slate-700'">
                      <CheckCircle2 class="w-3.5 h-3.5" :class="detail.is_active ? 'text-emerald-500' : 'text-slate-400'" />
                      {{ detail.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                  <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-1">Slug: <span class="text-blue-600 dark:text-cyan-400 font-semibold">{{ detail.slug }}</span></p>
                </div>
              </div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-200/80 dark:border-slate-800/80">
              <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Description</p>
              <p class="text-sm text-slate-700 dark:text-slate-300 font-medium">{{ detail.description || 'No description provided.' }}</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4 pt-4 border-t border-slate-200/80 dark:border-slate-800/80 text-xs">
              <div>
                <span class="text-slate-500 dark:text-slate-400">Associated Job Posts:</span>
                <p class="text-sm font-extrabold text-blue-600 dark:text-cyan-400">{{ detail.jobs_count ?? detail.job_posts_count ?? 0 }} jobs</p>
              </div>
              <div>
                <span class="text-slate-500 dark:text-slate-400">Created:</span>
                <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ formatDate(detail.created_at) }}</p>
              </div>
              <div>
                <span class="text-slate-500 dark:text-slate-400">Last Updated:</span>
                <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ formatDate(detail.updated_at) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Generic Structured Detail Fallback (No raw JSON code block) -->
        <div v-else-if="detail && typeof detail === 'object'" class="mt-5 space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-200 dark:border-slate-800">
            <div v-for="(val, key) in filterSimpleFields(detail)" :key="key" class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 shadow-xs">
              <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ labelFor(key) }}</p>
              <div class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">
                <span v-if="typeof val === 'boolean'" class="inline-flex items-center gap-1 font-bold text-xs" :class="val ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'">
                  <CheckCircle2 class="w-3.5 h-3.5" />
                  {{ val ? 'Active / Yes' : 'Inactive / No' }}
                </span>
                <span v-else-if="key.endsWith('_at') && val" class="text-slate-700 dark:text-slate-300 text-xs font-medium">
                  {{ formatDate(val) }}
                </span>
                <span v-else class="wrap-break-word">
                  {{ val !== null && val !== undefined && val !== '' ? val : '—' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Form Modal -->
    <div v-if="formOpen" class="resource-modal fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.esc="formOpen = false">
      <div class="resource-modal-backdrop absolute inset-0" @click="formOpen = false"></div>
      <form class="resource-modal-card relative w-full max-w-lg" @submit.prevent="saveResource">
        <div class="resource-modal-header">
          <div>
            <p class="resource-modal-kicker">{{ editing ? 'Update workspace data' : 'Create workspace data' }}</p>
            <h3>{{ editing ? 'Edit' : 'Add' }} {{ resource.title.replace(/s$/, '').replace(/ies$/, 'y') }}</h3>
          </div>
          <button type="button" class="resource-modal-close" aria-label="Close dialog" @click="formOpen = false">
            <XCircle class="w-5 h-5" />
          </button>
        </div>
        <p v-if="formError" class="resource-modal-error">{{ formError }}</p>
        
        <div class="resource-modal-body">
          <!-- Queue Forms -->
          <template v-if="['jobs', 'batches', 'failed-jobs'].includes(route.params.resource)">
            <template v-if="route.params.resource === 'jobs'">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Queue<input v-model="queueForm.queue" required class="setting-input mt-1.5" /></label>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Payload<textarea v-model="queueForm.payload" required rows="3" class="setting-input mt-1.5 resize-none" /></label>
              <div class="grid grid-cols-2 gap-3">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Attempts<input v-model="queueForm.attempts" type="number" min="0" class="setting-input mt-1.5" /></label>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Available at<input v-model="queueForm.available_at" required type="number" min="0" class="setting-input mt-1.5" /></label>
              </div>
            </template>
            <template v-else-if="route.params.resource === 'batches'">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Batch name<input v-model="queueForm.name" required class="setting-input mt-1.5" /></label>
              <div class="grid grid-cols-3 gap-3">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Total<input v-model="queueForm.total_jobs" type="number" min="0" class="setting-input mt-1.5" /></label>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Pending<input v-model="queueForm.pending_jobs" type="number" min="0" class="setting-input mt-1.5" /></label>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Failed<input v-model="queueForm.failed_jobs" type="number" min="0" class="setting-input mt-1.5" /></label>
              </div>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Failed job IDs<textarea v-model="queueForm.failed_job_ids" required rows="2" class="setting-input mt-1.5 resize-none" /></label>
            </template>
            <template v-else>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Connection<input v-model="queueForm.connection" required class="setting-input mt-1.5" /></label>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Queue<input v-model="queueForm.queue" required class="setting-input mt-1.5" /></label>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Payload<textarea v-model="queueForm.payload" required rows="2" class="setting-input mt-1.5 resize-none" /></label>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Exception<textarea v-model="queueForm.exception" required rows="3" class="setting-input mt-1.5 resize-none" /></label>
            </template>
          </template>

          <!-- Interview Form -->
          <template v-else-if="route.params.resource === 'interviews'">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
              Applicant / Applied Job
              <select v-model="interviewForm.application_id" required class="setting-input mt-1.5" :disabled="applicationsLoading">
                <option value="" disabled>{{ applicationsLoading ? 'Loading applicants...' : 'Select an applicant who applied' }}</option>
                <option v-for="application in applicationOptions" :key="application.id" :value="application.id">
                  #{{ application.id }} - {{ application.job_seeker?.name || application.jobSeeker?.name || application.applicant?.name || 'Applicant' }} - {{ application.job_post?.title || application.jobPost?.title || 'Job' }} ({{ application.status || 'pending' }})
                </option>
              </select>
              <span v-if="!applicationsLoading && applicationOptions.length === 0" class="text-xs font-medium text-amber-600 dark:text-amber-400">
                No applications are available yet.
              </span>
            </label>
            <div class="grid grid-cols-2 gap-3">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Type
                <select v-model="interviewForm.interview_type" class="setting-input mt-1.5">
                  <option value="phone">Phone</option>
                  <option value="video">Video</option>
                  <option value="onsite">On-site</option>
                  <option value="technical">Technical</option>
                  <option value="panel">Panel</option>
                </select>
              </label>
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Scheduled at<input v-model="interviewForm.scheduled_at" required type="datetime-local" class="setting-input mt-1.5" /></label>
            </div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Title<input v-model="interviewForm.title" class="setting-input mt-1.5" /></label>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Meeting link<input v-model="interviewForm.meeting_link" type="url" class="setting-input mt-1.5" /></label>
          </template>

          <!-- Standard Categories / Skill Categories / Skills Forms -->
          <template v-else>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
              Name
              <input v-model="form.name" required class="setting-input mt-1.5" placeholder="e.g. Frontend Development" />
            </label>

            <label v-if="route.params.resource === 'categories' || route.params.resource === 'skill-categories'" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
              Slug (URL Identifier)
              <input v-model="form.slug" class="setting-input mt-1.5" placeholder="e.g. frontend-development (leave blank to auto-generate)" />
            </label>

            <!-- Skill Category Selector for Skills resource -->
            <template v-if="route.params.resource === 'skills'">
              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                Skill Category
                <select v-model="form.category_id" class="setting-input mt-1.5">
                  <option value="">-- Select Skill Category --</option>
                  <option v-for="cat in skillCategoriesList" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                  </option>
                </select>
              </label>

              <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                Or Category Name (Fallback)
                <input v-model="form.category" class="setting-input mt-1.5" placeholder="e.g. Frontend Development" />
              </label>
            </template>

            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
              Description
              <textarea v-model="form.description" rows="3" class="setting-input mt-1.5 resize-none" placeholder="Short description of this category or skill..."></textarea>
            </label>

            <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300 pt-1">
              <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded accent-blue-600" />
              Active and visible across platform
            </label>
          </template>
        </div>

        <div class="resource-modal-actions">
          <button type="button" class="settings-secondary-button cursor-pointer" @click="formOpen = false">Cancel</button>
          <button type="submit" class="settings-primary-button cursor-pointer" :disabled="submitting">
            {{ submitting ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
