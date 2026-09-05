<script setup>
import { onMounted, reactive, ref, watch } from 'vue'
import { Users, Search, RefreshCw, Eye, Pencil, Trash2 } from 'lucide-vue-next'
import { adminApi } from '../api'
import StatusBadge from '../components/StatusBadge.vue'

const loading = ref(false)
const error = ref('')
const candidates = ref([])
const pagination = reactive({ current_page: 1, last_page: 1, total: 0, per_page: 20 })

const filters = reactive({
  search: '',
  per_page: 20,
  page: 1,
})

async function fetchCandidates() {
  loading.value = true
  error.value = ''
  try {
    const params = { ...filters }
    if (!params.search) delete params.search

    const { data } = await adminApi.getCandidates(params)
    const pager = data.data || data
    candidates.value = pager.data || []
    Object.assign(pagination, {
      current_page: pager.current_page,
      last_page: pager.last_page,
      total: pager.total,
      per_page: pager.per_page,
    })
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to load candidates.'
  } finally {
    loading.value = false
  }
}

watch(
  () => filters.search,
  () => {
    filters.page = 1
    fetchCandidates()
  },
)

function goToPage(page) {
  if (page < 1 || page > pagination.last_page) return
  filters.page = page
  fetchCandidates()
}

function statusFor(user) {
  return user.is_active ? 'active' : 'inactive'
}

function onView(c) {
  alert(`View user: ${c.name}`)
}

function onEdit(c) {
  alert(`Edit user: ${c.name}`)
}

async function onDelete(c) {
  if (!confirm(`Delete user "${c.name}"?`)) return
  try {
    await adminApi.updateCandidate(c.id, { is_active: false })
    fetchCandidates()
  } catch (e) {
    error.value = e.response?.data?.message || 'Delete failed.'
  }
}

onMounted(fetchCandidates)
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-100 flex items-center gap-2">
          <Users class="w-6 h-6 text-blue-500" />
          Candidates
        </h2>
        <p class="text-sm text-slate-400 mt-1">{{ pagination.total }} candidates registered</p>
      </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-3 mb-6">
      <div class="flex-1 flex items-center gap-2 bg-slate-900 border border-slate-800 rounded-xl px-3 py-2.5 max-w-xl">
        <Search class="w-4 h-4 text-slate-500" />
        <input
          v-model="filters.search"
          type="text"
          placeholder="Search candidates by name or email..."
          class="bg-transparent outline-none text-sm text-slate-100 placeholder-slate-500 w-full"
        />
      </div>
      <div class="flex gap-3">
        <button
          @click="fetchCandidates"
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
              <th class="px-5 py-3.5 font-semibold">Candidate</th>
              <th class="px-5 py-3.5 font-semibold">Email</th>
              <th class="px-5 py-3.5 font-semibold">Role</th>
              <th class="px-5 py-3.5 font-semibold">Location</th>
              <th class="px-5 py-3.5 font-semibold">Joined</th>
              <th class="px-5 py-3.5 font-semibold">Status</th>
              <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800">
            <tr v-if="loading" class="hover:bg-slate-800/30">
              <td colspan="7" class="px-5 py-10 text-center text-slate-400">Loading candidates...</td>
            </tr>
            <tr v-else-if="candidates.length === 0" class="hover:bg-slate-800/30">
              <td colspan="7" class="px-5 py-10 text-center text-slate-400">No candidates found.</td>
            </tr>
            <tr
              v-for="c in candidates"
              :key="c.id"
              class="hover:bg-slate-800/30 transition-colors"
            >
              <td class="px-5 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center text-sm font-bold text-slate-200 shrink-0">
                    {{ (c.name || '?').charAt(0).toUpperCase() }}
                  </div>
                  <p class="font-semibold text-slate-100">{{ c.name }}</p>
                </div>
              </td>
              <td class="px-5 py-4 text-slate-300">{{ c.email }}</td>
              <td class="px-5 py-4"><StatusBadge :value="c.role" /></td>
              <td class="px-5 py-4 text-slate-300">
                <span v-if="c.profile">{{ c.profile.city || c.profile.country || '—' }}</span>
                <span v-else>—</span>
              </td>
              <td class="px-5 py-4 text-slate-300">{{ new Date(c.created_at).toLocaleDateString() }}</td>
              <td class="px-5 py-4"><StatusBadge :value="statusFor(c)" /></td>
              <td class="px-5 py-4">
                <div class="flex items-center justify-end gap-1">
                  <button
                    class="p-2 text-slate-400 hover:text-blue-400 hover:bg-slate-800 rounded-lg transition-colors"
                    title="View"
                    @click="onView(c)"
                  >
                    <Eye class="w-4 h-4" />
                  </button>
                  <button
                    class="p-2 text-slate-400 hover:text-amber-400 hover:bg-slate-800 rounded-lg transition-colors"
                    title="Edit"
                    @click="onEdit(c)"
                  >
                    <Pencil class="w-4 h-4" />
                  </button>
                  <button
                    class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition-colors"
                    title="Deactivate"
                    @click="onDelete(c)"
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
