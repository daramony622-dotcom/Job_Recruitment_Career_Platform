<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  Users,
  Search,
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
const route = useRoute()
const loggedInUser = JSON.parse(localStorage.getItem('admin_user') || 'null')
const canManageRoles = computed(() => loggedInUser?.role === 'admin')

const loading = ref(false)
const error = ref('')
const notice = ref('')
const candidates = ref([])
const pagination = reactive({ current_page: 1, last_page: 1, total: 0, per_page: 20 })

const filters = reactive({
  search: '',
  role: route.query.role || '',
  per_page: 20,
  page: 1,
})

async function fetchCandidates() {
  loading.value = true
  error.value = ''
  try {
    const params = { ...filters }
    if (!params.search) delete params.search

    const { data } = await adminApi.getUsers(params)
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
  () => [filters.search, filters.role],
  () => {
    filters.page = 1
    fetchCandidates()
  },
)

watch(
  () => route.query.role,
  (role) => {
    filters.role = role || ''
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

// ---- View ----
function onView(c) {
  router.push(`/candidates/${c.id}`)
}

// ---- Edit modal ----
const showEditModal = ref(false)
const editingCandidate = ref(null)
const editSubmitting = ref(false)
const editError = ref('')
const editSuccess = ref('')

const editForm = reactive({
  name: '',
  email: '',
  role: 'user',
  is_active: true,
})

function openEdit(c) {
  editingCandidate.value = c
  editForm.name = c.name || ''
  editForm.email = c.email || ''
  editForm.role = c.role || 'user'
  editForm.is_active = c.is_active !== false
  editError.value = ''
  editSuccess.value = ''
  showEditModal.value = true
}

function closeEditModal() {
  if (editSubmitting.value) return
  showEditModal.value = false
  editingCandidate.value = null
}

async function submitEdit() {
  editSubmitting.value = true
  editError.value = ''
  editSuccess.value = ''
  try {
    const payload = {}
    if (canManageRoles.value) payload.role = editForm.role
    await adminApi.updateCandidate(editingCandidate.value.id, payload)
    editSuccess.value = 'User role updated successfully.'
    await fetchCandidates()
    setTimeout(closeEditModal, 1000)
  } catch (e) {
    const err = e.response?.data
    if (err && err.errors) {
      editError.value = Object.values(err.errors).flat().join(' ')
    } else {
      editError.value = err?.message || 'Update failed.'
    }
  } finally {
    editSubmitting.value = false
  }
}

// ---- Delete ----
async function onDelete(c) {
  if (Number(c.id) === Number(loggedInUser?.id)) {
    error.value = 'You cannot delete the account currently being used.'
    return
  }
  if (!confirm(`Delete candidate "${c.name}"? This action cannot be undone.`)) return
  try {
    await adminApi.deleteUser(c.id)
    error.value = ''
    notice.value = `${c.name} was deleted successfully.`
    await fetchCandidates()
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
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <Users class="w-6 h-6 text-blue-600 dark:text-blue-500" />
          Users & HR
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ pagination.total }} platform accounts</p>
      </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-3 mb-6">
      <div class="flex-1 flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2.5 max-w-xl">
        <Search class="w-4 h-4 text-slate-500" />
        <label for="account-search" class="sr-only">Search accounts by name or email</label>
        <input
          id="account-search"
          v-model="filters.search"
          type="text"
          placeholder="Search candidates by name or email..."
          class="bg-transparent outline-none text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 w-full"
        />
      </div>
      <select
        v-model="filters.role"
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2.5 text-sm text-slate-800 dark:text-slate-200 outline-none focus:border-blue-500"
        aria-label="Filter accounts by role"
      >
        <option value="">All roles</option>
        <option value="user">User / job seeker</option>
        <option value="hr">HR</option>
        <option value="admin">Admin</option>
      </select>
      <div class="flex gap-3">
        <button
          @click="fetchCandidates"
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
    <p v-if="notice" class="mb-4 flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3">
      <CheckCircle2 class="w-4 h-4" /> {{ notice }}
    </p>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 text-xs uppercase border-b border-slate-200 dark:border-slate-800">
            <tr>
              <th class="px-5 py-3.5 font-semibold">Account</th>
              <th class="px-5 py-3.5 font-semibold">Email</th>
              <th class="px-5 py-3.5 font-semibold">Access role</th>
              <th class="px-5 py-3.5 font-semibold">Location</th>
              <th class="px-5 py-3.5 font-semibold">Joined</th>
              <th class="px-5 py-3.5 font-semibold">Status</th>
              <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-if="loading" class="hover:bg-slate-100 dark:hover:bg-slate-800/30">
              <td colspan="7" class="px-5 py-10 text-center text-slate-600 dark:text-slate-400">Loading accounts...</td>
            </tr>
            <tr v-else-if="candidates.length === 0" class="hover:bg-slate-100 dark:hover:bg-slate-800/30">
              <td colspan="7" class="px-5 py-10 text-center text-slate-600 dark:text-slate-400">No accounts found.</td>
            </tr>
            <tr
              v-for="c in candidates"
              :key="c.id"
              class="hover:bg-slate-100 dark:hover:bg-slate-800/30 transition-colors"
            >
              <td class="px-5 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full bg-slate-300 dark:bg-slate-700 flex items-center justify-center text-sm font-bold text-slate-800 dark:text-slate-200 shrink-0">
                    {{ (c.name || '?').charAt(0).toUpperCase() }}
                  </div>
                  <p class="font-semibold text-slate-900 dark:text-slate-100">{{ c.name }}</p>
                </div>
              </td>
              <td class="px-5 py-4 text-slate-700 dark:text-slate-300">{{ c.email }}</td>
              <td class="px-5 py-4"><StatusBadge :value="c.role" /></td>
              <td class="px-5 py-4 text-slate-700 dark:text-slate-300">
                <span v-if="c.profile">{{ c.profile.city || c.profile.country || '—' }}</span>
                <span v-else>—</span>
              </td>
              <td class="px-5 py-4 text-slate-700 dark:text-slate-300">{{ new Date(c.created_at).toLocaleDateString() }}</td>
              <td class="px-5 py-4"><StatusBadge :value="statusFor(c)" /></td>
              <td class="px-5 py-4">
                <div class="flex items-center justify-end gap-1">
                  <button
                    class="p-2 text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                    title="View"
                    @click="onView(c)"
                  >
                    <Eye class="w-4 h-4" />
                  </button>
                  <button
                    class="p-2 text-slate-600 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                    title="Edit"
                    @click="onEdit(c)"
                  >
                    <Pencil class="w-4 h-4" />
                  </button>
                  <button
                    class="p-2 text-slate-600 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                    title="Delete"
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

    <!-- ===== Edit Candidate Modal ===== -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
      <div
        class="absolute inset-0 bg-black/60 backdrop-blur-sm"
        @click="closeEditModal"
      ></div>

      <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200 dark:border-slate-800">
          <div v-if="canManageRoles">
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
              <Pencil class="w-5 h-5 text-amber-600 dark:text-amber-400" />
              Change user role
            </h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">Only the access role can be changed here.</p>
          </div>
          <button
            @click="closeEditModal"
            class="p-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="px-6 py-5 space-y-4">
          <p v-if="editError" class="flex items-start gap-2 text-sm text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">
            <AlertCircle class="w-4 h-4 mt-0.5 shrink-0" />
            <span>{{ editError }}</span>
          </p>
          <p v-if="editSuccess" class="flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3">
            <CheckCircle2 class="w-4 h-4 shrink-0" />
            <span>{{ editSuccess }}</span>
          </p>

          <div v-if="canManageRoles">
            <label for="edit-account-role" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Access role</label>
            <select
              id="edit-account-role"
              v-model="editForm.role"
              class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none focus:border-blue-500"
            >
              <option value="user">User / job seeker</option>
              <option value="hr">HR</option>
              <option value="admin">Admin</option>
            </select>
          </div>

        </div>

        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-200 dark:border-slate-800">
          <button
            @click="closeEditModal"
            class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:border-slate-400 dark:hover:border-slate-500 hover:text-slate-900 dark:hover:text-slate-100 transition-colors"
            :disabled="editSubmitting"
          >
            Cancel
          </button>
          <button
            @click="submitEdit"
            :disabled="editSubmitting"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors"
          >
            <Loader2 v-if="editSubmitting" class="w-4 h-4 animate-spin" />
            <CheckCircle2 v-else class="w-4 h-4" />
            {{ editSubmitting ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
