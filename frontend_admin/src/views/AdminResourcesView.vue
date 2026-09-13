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
} from 'lucide-vue-next'
import { adminApi } from '../api'
import StatusBadge from '../components/StatusBadge.vue'

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
const skillCategoriesList = ref([])

const configs = {
  applications: {
    title: 'Applications',
    description: 'Review applications across the platform.',
    icon: FileText,
    fetch: (params) => adminApi.getApplications(params),
    show: (item) => adminApi.showApplication(item.id),
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

async function fetchRecords() {
  loading.value = true
  error.value = ''
  try {
    const response = await resource.value.fetch({
      search: query.value.trim() || undefined,
      status: status.value || undefined,
      per_page: 30,
    })
    records.value = unwrap(response.data)
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
  fetchRecords()
  if (route.params.resource === 'skills' || route.params.resource === 'skill-categories') {
    fetchSkillCategoriesList()
  }
})

watch([query, status], fetchRecords)

onMounted(() => {
  fetchRecords()
  fetchSkillCategoriesList()
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
          v-if="resource.create"
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
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 text-xs uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
            <tr>
              <th v-for="column in resource.columns" :key="column" class="px-5 py-3.5 font-semibold">{{ labelFor(column) }}</th>
              <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-if="loading">
              <td :colspan="resource.columns.length + 1" class="px-5 py-12 text-center text-slate-500">
                <RefreshCw class="w-5 h-5 animate-spin mx-auto mb-2 text-blue-500" />
                Loading records...
              </td>
            </tr>
            <tr v-else-if="records.length === 0">
              <td :colspan="resource.columns.length + 1" class="px-5 py-12 text-center text-slate-500">
                No records found.
              </td>
            </tr>
            <tr v-for="item in records" v-else :key="item.id || item.uuid" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
              <td v-for="column in resource.columns" :key="column" class="px-5 py-4 max-w-xs text-slate-700 dark:text-slate-300">
                <StatusBadge v-if="column === 'status'" :value="valueFor(item, column)" />
                <span v-else-if="column === 'is_read'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold" :class="item.is_read ? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300'">
                  {{ valueFor(item, column) }}
                </span>
                <span v-else-if="column === 'is_active'" class="inline-flex items-center gap-1.5 font-medium">
                  <CheckCircle2 class="w-4 h-4" :class="item.is_active ? 'text-emerald-500' : 'text-slate-400'" />
                  {{ valueFor(item, column) }}
                </span>
                <span v-else-if="column === 'skills_count' || column === 'jobs_count'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                  {{ valueFor(item, column) }}
                </span>
                <span v-else class="block truncate" :title="String(valueFor(item, column))">
                  {{ valueFor(item, column) }}
                </span>
              </td>
              <td class="px-5 py-4">
                <div class="flex items-center justify-end gap-1">
                  <button
                    v-if="resource.show"
                    class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/50 rounded-lg transition-colors cursor-pointer"
                    title="View details"
                    @click="openDetails(item)"
                  >
                    <component :is="route.params.resource === 'messages' ? Mail : Users" class="w-4 h-4" />
                  </button>
                  <button
                    v-if="route.params.resource === 'failed-jobs'"
                    class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/50 rounded-lg transition-colors cursor-pointer"
                    title="Retry"
                    @click="retry(item)"
                  >
                    <RefreshCw class="w-4 h-4" />
                  </button>
                  <button
                    v-if="resource.cancel && ['scheduled', 'pending'].includes(String(item.status).toLowerCase())"
                    class="p-2 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition-colors cursor-pointer"
                    title="Cancel interview"
                    @click="cancelInterview(item)"
                  >
                    <XCircle class="w-4 h-4" />
                  </button>
                  <button
                    v-if="resource.toggle"
                    class="p-2 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/50 rounded-lg transition-colors cursor-pointer"
                    :title="item.is_active ? 'Deactivate' : 'Activate'"
                    @click="toggleCategory(item)"
                  >
                    <CheckCircle2 class="w-4 h-4" />
                  </button>
                  <button
                    v-if="resource.update"
                    class="p-2 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/50 rounded-lg transition-colors cursor-pointer"
                    title="Edit"
                    @click="openEdit(item)"
                  >
                    <Pencil class="w-4 h-4" />
                  </button>
                  <button
                    v-if="resource.remove"
                    class="p-2 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition-colors cursor-pointer"
                    title="Delete"
                    @click="remove(item)"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

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

        <!-- Generic JSON viewer for other resources -->
        <pre v-else class="mt-5 overflow-x-auto rounded-xl bg-slate-950 p-4 text-xs text-slate-200">{{ JSON.stringify(detail, null, 2) }}</pre>
      </div>
    </div>

    <!-- Create/Edit Form Modal -->
    <div v-if="formOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="formOpen = false"></div>
      <form class="relative w-full max-w-lg rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-2xl" @submit.prevent="saveResource">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">
          {{ editing ? 'Edit' : 'Add' }} {{ resource.title.replace(/s$/, '').replace(/ies$/, 'y') }}
        </h3>
        <p v-if="formError" class="mt-3 rounded-xl bg-rose-500/10 px-3 py-2 text-sm text-rose-600">{{ formError }}</p>
        
        <div class="mt-5 space-y-4">
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
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Application ID<input v-model="interviewForm.application_id" required type="number" class="setting-input mt-1.5" /></label>
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

        <div class="mt-6 flex justify-end gap-2">
          <button type="button" class="settings-secondary-button cursor-pointer" @click="formOpen = false">Cancel</button>
          <button type="submit" class="settings-primary-button cursor-pointer" :disabled="submitting">
            {{ submitting ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
