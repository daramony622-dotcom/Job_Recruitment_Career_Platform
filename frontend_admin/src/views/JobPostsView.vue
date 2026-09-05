<script setup>
import { onMounted, reactive, ref, watch } from 'vue'
import { Briefcase, Search, Plus, RefreshCw, Eye, Pencil, Trash2 } from 'lucide-vue-next'
import { adminApi } from '../api'
import StatusBadge from '../components/StatusBadge.vue'

const loading = ref(false)
const error = ref('')
const jobPosts = ref([])
const pagination = reactive({ current_page: 1, last_page: 1, total: 0, per_page: 15 })

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
        <h2 class="text-2xl font-bold text-slate-100 flex items-center gap-2">
          <Briefcase class="w-6 h-6 text-blue-500" />
          Job Posts
        </h2>
        <p class="text-sm text-slate-400 mt-1">{{ pagination.total }} job posts in total</p>
      </div>
      <button
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors"
      >
        <Plus class="w-4 h-4" />
        New Job Post
      </button>
    </div>

    <div class="flex flex-col lg:flex-row gap-3 mb-6">
      <div class="flex-1 flex items-center gap-2 bg-slate-900 border border-slate-800 rounded-xl px-3 py-2.5 max-w-xl">
        <Search class="w-4 h-4 text-slate-500" />
        <input
          v-model="filters.search"
          type="text"
          placeholder="Search by title, description, or company..."
          class="bg-transparent outline-none text-sm text-slate-100 placeholder-slate-500 w-full"
        />
      </div>

      <div class="flex gap-3">
        <select
          v-model="filters.status"
          class="bg-slate-900 border border-slate-800 rounded-xl px-3 py-2.5 text-sm text-slate-200 outline-none focus:border-blue-500"
        >
          <option value="">All statuses</option>
          <option value="draft">Draft</option>
          <option value="published">Published</option>
          <option value="closed">Closed</option>
          <option value="suspended">Suspended</option>
        </select>
        <button
          @click="fetchJobPosts"
          class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl border border-slate-700 text-slate-300 text-sm font-semibold hover:border-blue-500 hover:text-blue-400 transition-colors"
        >
          <RefreshCw class="w-4 h-4" />
          <span class="hidden sm:inline">Refresh</span>
        </button>
      </div>
    </div>

    <p v-if="error" class="mb-4 text-sm text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">
      {{ error }}
    </p>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="bg-slate-800/60 text-slate-400 text-xs uppercase border-b border-slate-800">
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
          <tbody class="divide-y divide-slate-800">
            <tr v-if="loading" class="hover:bg-slate-800/30">
              <td colspan="8" class="px-5 py-10 text-center text-slate-400">Loading job posts...</td>
            </tr>
            <tr v-else-if="jobPosts.length === 0" class="hover:bg-slate-800/30">
              <td colspan="8" class="px-5 py-10 text-center text-slate-400">No job posts found.</td>
            </tr>
            <tr
              v-for="job in jobPosts"
              :key="job.id"
              class="hover:bg-slate-800/30 transition-colors"
            >
              <td class="px-5 py-4">
                <p class="font-semibold text-slate-100">{{ job.title }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ job.views_count }} views · {{ job.vacancies }} openings</p>
              </td>
              <td class="px-5 py-4 text-slate-300">{{ job.company?.name || '—' }}</td>
              <td class="px-5 py-4 text-slate-300">{{ job.city || job.location || '—' }}</td>
              <td class="px-5 py-4 text-slate-300 capitalize">
                {{ (job.job_type || '—').replace('_', ' ') }}
                <span class="text-slate-500">·</span>
                {{ (job.work_mode || '—').replace('_', ' ') }}
              </td>
              <td class="px-5 py-4 text-slate-300">{{ job.category?.name || '—' }}</td>
              <td class="px-5 py-4 text-slate-300">
                <span v-if="job.salary_min">
                  {{ job.salary_currency }} {{ job.salary_min }}<span v-if="job.salary_max"> – {{ job.salary_max }}</span>
                </span>
                <span v-else>N/A</span>
              </td>
              <td class="px-5 py-4"><StatusBadge :value="job.status" /></td>
              <td class="px-5 py-4">
                <div class="flex items-center justify-end gap-1">
                  <button
                    class="p-2 text-slate-400 hover:text-blue-400 hover:bg-slate-800 rounded-lg transition-colors"
                    title="View"
                    @click="onView(job)"
                  >
                    <Eye class="w-4 h-4" />
                  </button>
                  <button
                    class="p-2 text-slate-400 hover:text-amber-400 hover:bg-slate-800 rounded-lg transition-colors"
                    title="Edit"
                    @click="onEdit(job)"
                  >
                    <Pencil class="w-4 h-4" />
                  </button>
                  <button
                    class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition-colors"
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
        class="flex items-center justify-between px-5 py-3 border-t border-slate-800 bg-slate-900"
      >
        <p class="text-xs text-slate-500">
          Page {{ pagination.current_page }} of {{ pagination.last_page }}
        </p>
        <div class="flex items-center gap-2">
          <button
            class="px-3 py-1.5 rounded-lg border border-slate-700 text-xs text-slate-300 hover:border-blue-500 hover:text-blue-400 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
            :disabled="pagination.current_page <= 1"
            @click="goToPage(pagination.current_page - 1)"
          >
            Prev
          </button>
          <button
            class="px-3 py-1.5 rounded-lg border border-slate-700 text-xs text-slate-300 hover:border-blue-500 hover:text-blue-400 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
            :disabled="pagination.current_page >= pagination.last_page"
            @click="goToPage(pagination.current_page + 1)"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
