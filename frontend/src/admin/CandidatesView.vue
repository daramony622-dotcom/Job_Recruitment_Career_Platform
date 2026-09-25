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
  Plus,
  UserPlus,
  Mail,
  Lock,
  User as UserIcon,
} from 'lucide-vue-next'
import { adminApi } from '../api'
import StatusBadge from './StatusBadge.vue'
import { profileImage } from '../utils/media'

const router = useRouter()

const route = useRoute()
const loggedInUser = JSON.parse(localStorage.getItem('admin_user') || localStorage.getItem('job_platform_user') || 'null')
const canManageRoles = computed(() => loggedInUser?.role === 'admin')
const canManageEmployees = computed(() => canManageRoles.value || loggedInUser?.role === 'hr')

const loading = ref(false)
const error = ref('')
const notice = ref('')
const candidates = ref([])
const companies = ref([])
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
      current_page: pager.current_page || 1,
      last_page: pager.last_page || 1,
      total: pager.total || 0,
      per_page: pager.per_page || 20,
    })
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to load accounts.'
  } finally {
    loading.value = false
  }
}

async function fetchCompanies() {
  if (!canManageRoles.value) return
  try {
    const { data } = await adminApi.getCompanies({ per_page: 100 })
    const payload = data?.data || data || {}
    companies.value = Array.isArray(payload) ? payload : (payload.data || [])
  } catch {
    companies.value = []
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
  return user.is_active ?? true ? 'active' : 'inactive'
}

function onView(c) {
  router.push(`/candidates/${c.id}`)
}

// ---- Create User Modal ----
const showCreateModal = ref(false)
const createSubmitting = ref(false)
const createError = ref('')
const createSuccess = ref('')

const createForm = reactive({
  name: '',
  email: '',
  password: '',
  role: 'user',
})

function openCreateModal() {
  createForm.name = ''
  createForm.email = ''
  createForm.password = ''
  createForm.role = 'user'
  createError.value = ''
  createSuccess.value = ''
  showCreateModal.value = true
}

function closeCreateModal() {
  if (createSubmitting.value) return
  showCreateModal.value = false
}

async function submitCreate() {
  createSubmitting.value = true
  createError.value = ''
  createSuccess.value = ''
  try {
    await adminApi.storeUser({
      name: createForm.name,
      email: createForm.email,
      password: createForm.password,
      role: createForm.role,
    })
    createSuccess.value = 'User created successfully.'
    await fetchCandidates()
    setTimeout(closeCreateModal, 1200)
  } catch (e) {
    const err = e.response?.data
    if (err && err.errors) {
      createError.value = Object.values(err.errors).flat().join(' ')
    } else {
      createError.value = err?.message || 'User creation failed.'
    }
  } finally {
    createSubmitting.value = false
  }
}

// ---- Edit User Modal ----
const showEditModal = ref(false)
const editingCandidate = ref(null)
const editSubmitting = ref(false)
const editError = ref('')
const editSuccess = ref('')

const editForm = reactive({
  name: '',
  email: '',
  password: '',
  role: 'user',
  company_id: '',
  is_active: true,
})

function openEdit(c) {
  editingCandidate.value = c
  editForm.name = c.name || ''
  editForm.email = c.email || ''
  editForm.password = ''
  editForm.role = c.role || 'user'
  editForm.company_id = c.company_id || c.company?.id || ''
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
    const payload = canManageRoles.value
      ? { role: editForm.role, company_id: editForm.role === 'hr' ? (editForm.company_id || null) : null }
      : { name: editForm.name, email: editForm.email, is_active: editForm.is_active }
    await adminApi.updateCandidate(editingCandidate.value.id, payload)
    editSuccess.value = canManageRoles.value
      ? 'User access and company assignment updated successfully.'
      : 'Employee account updated successfully.'
    await fetchCandidates()
    setTimeout(closeEditModal, 1200)
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

// ---- Delete User ----
async function onDelete(c) {
  if (Number(c.id) === Number(loggedInUser?.id)) {
    error.value = 'You cannot delete the account currently being used.'
    return
  }
  if (!confirm(`Delete user "${c.name}" (${c.email})? This action cannot be undone.`)) return
  try {
    await adminApi.deleteUser(c.id)
    error.value = ''
    notice.value = `${c.name} was deleted successfully.`
    await fetchCandidates()
  } catch (e) {
    error.value = e.response?.data?.message || 'Delete failed.'
  }
}

onMounted(() => {
  fetchCandidates()
  fetchCompanies()
})
</script>

<template>
  <div class="p-4 sm:p-6 lg:p-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <Users class="w-6 h-6 text-blue-600 dark:text-blue-500" />
          Users & HR Management
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ pagination.total }} platform accounts registered</p>
      </div>

      <button
        v-if="canManageRoles"
        @click="openCreateModal"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-md shadow-blue-600/20 transition-all cursor-pointer self-start md:self-auto"
      >
        <Plus class="w-4 h-4" /> Add User / HR
      </button>
    </div>

    <!-- Filter Bar -->
    <div class="flex flex-col lg:flex-row gap-3 mb-6">
      <div class="flex-1 flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2.5">
        <Search class="w-4 h-4 text-slate-500" />
        <label for="account-search" class="sr-only">Search accounts by name or email</label>
        <input
          id="account-search"
          v-model="filters.search"
          type="text"
          placeholder="Search candidates, HR, or admins by name or email..."
          class="bg-transparent outline-none text-sm text-slate-900 dark:text-slate-100 placeholder-slate-500 w-full"
        />
      </div>
      <select
        v-model="filters.role"
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 dark:text-slate-200 outline-none focus:border-blue-500"
        aria-label="Filter accounts by role"
      >
        <option value="">All Roles</option>
        <option value="user">User / Job Seeker</option>
        <option value="hr">HR Manager</option>
        <option value="admin">Administrator</option>
      </select>
      <div class="flex gap-3">
        <button
          @click="fetchCandidates"
          class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
        >
          <RefreshCw class="w-4 h-4" />
          <span class="hidden sm:inline">Refresh</span>
        </button>
      </div>
    </div>

    <!-- Error / Notice Banners -->
    <p v-if="error" class="mb-4 text-sm text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">
      {{ error }}
    </p>
    <p v-if="notice" class="mb-4 flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3">
      <CheckCircle2 class="w-4 h-4" /> {{ notice }}
    </p>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
          <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 text-[10px] font-extrabold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
            <tr>
              <th class="px-4 py-2.5 font-bold">Account</th>
              <th class="px-4 py-2.5 font-bold">Email</th>
              <th class="px-4 py-2.5 font-bold">Access Role</th>
              <th class="px-4 py-2.5 font-bold">Location</th>
              <th class="px-4 py-2.5 font-bold">Joined</th>
              <th class="px-4 py-2.5 font-bold">Status</th>
              <th class="px-4 py-2.5 font-bold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-if="loading">
              <td colspan="7" class="px-4 py-8 text-center text-slate-500">Loading accounts...</td>
            </tr>
            <tr v-else-if="candidates.length === 0">
              <td colspan="7" class="px-4 py-8 text-center text-slate-500">No accounts found.</td>
            </tr>
            <tr
              v-for="c in candidates"
              :key="c.id"
              class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="px-4 py-2.5">
                <div class="flex items-center gap-2.5">
                  <img
                    v-if="profileImage(c)"
                    :src="profileImage(c)"
                    :alt="c.name"
                    class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700 shrink-0"
                  />
                  <div
                    v-else
                    class="w-8 h-8 rounded-full bg-blue-100 dark:bg-slate-800 text-blue-600 dark:text-cyan-400 flex items-center justify-center text-xs font-bold shrink-0 border border-blue-200 dark:border-slate-700"
                  >
                    {{ (c.name || '?').charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <p class="font-bold text-slate-900 dark:text-slate-100 text-xs leading-tight">{{ c.name }}</p>
                  </div>
                </div>

              </td>
              <td class="px-4 py-2.5 text-slate-700 dark:text-slate-300 font-medium">{{ c.email }}</td>
              <td class="px-4 py-2.5"><StatusBadge :value="c.role" /></td>
              <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400">
                <span v-if="c.profile">{{ c.profile.city || c.profile.country || '—' }}</span>
                <span v-else>—</span>
              </td>
              <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400 text-[11px] font-semibold">{{ new Date(c.created_at).toLocaleDateString() }}</td>
              <td class="px-4 py-2.5"><StatusBadge :value="statusFor(c)" /></td>
              <td class="px-4 py-2.5">
                <div class="flex items-center justify-end gap-1">
                  <button
                    class="p-1.5 text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-cyan-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer"
                    title="View Profile"
                    @click="onView(c)"
                  >
                    <Eye class="w-3.5 h-3.5" />
                  </button>
                  <button
                    v-if="canManageEmployees && (canManageRoles || c.role === 'user' || c.role === 'job_seeker')"
                    class="p-1.5 text-slate-500 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer"
                    title="Edit User"
                    @click="openEdit(c)"
                  >
                    <Pencil class="w-3.5 h-3.5" />
                  </button>
                  <button
                    v-if="Number(c.id) !== Number(loggedInUser?.id)"
                    class="p-1.5 text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer"
                    title="Delete User"
                    @click="onDelete(c)"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="pagination.last_page > 1"
        class="flex items-center justify-between px-5 py-3 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900"
      >
        <p class="text-xs text-slate-500">
          Page {{ pagination.current_page }} of {{ pagination.last_page }} ({{ pagination.total }} total)
        </p>
        <div class="flex items-center gap-2">
          <button
            class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer"
            :disabled="pagination.current_page <= 1"
            @click="goToPage(pagination.current_page - 1)"
          >
            Prev
          </button>
          <button
            class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer"
            :disabled="pagination.current_page >= pagination.last_page"
            @click="goToPage(pagination.current_page + 1)"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- ===== Create User Modal ===== -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeCreateModal"></div>
      <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200 dark:border-slate-800">
          <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
              <UserPlus class="w-5 h-5 text-blue-600 dark:text-blue-400" />
              Add New User / HR
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Create a new account on the platform.</p>
          </div>
          <button @click="closeCreateModal" class="p-2 text-slate-500 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitCreate" class="p-6 space-y-4">
          <p v-if="createError" class="flex items-start gap-2 text-sm text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">
            <AlertCircle class="w-4 h-4 mt-0.5 shrink-0" />
            <span>{{ createError }}</span>
          </p>
          <p v-if="createSuccess" class="flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3">
            <CheckCircle2 class="w-4 h-4 shrink-0" />
            <span>{{ createSuccess }}</span>
          </p>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Full Name</label>
            <div class="relative">
              <UserIcon class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input v-model="createForm.name" type="text" placeholder="e.g. Sokha Chea" required class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl pl-10 pr-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none focus:border-blue-500 transition-colors" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Email Address</label>
            <div class="relative">
              <Mail class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input v-model="createForm.email" type="email" placeholder="user@example.com" required class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl pl-10 pr-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none focus:border-blue-500 transition-colors" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
            <div class="relative">
              <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input v-model="createForm.password" type="password" placeholder="••••••••" required minlength="6" class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl pl-10 pr-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none focus:border-blue-500 transition-colors" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Access Role</label>
            <select v-model="createForm.role" class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none focus:border-blue-500 transition-colors">
              <option value="user">User / Job Seeker</option>
              <option value="hr">HR Manager</option>
              <option value="admin">Administrator</option>
            </select>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <button type="button" @click="closeCreateModal" :disabled="createSubmitting" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
              Cancel
            </button>
            <button type="submit" :disabled="createSubmitting" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-md shadow-blue-600/20 disabled:opacity-60 transition-all cursor-pointer">
              <Loader2 v-if="createSubmitting" class="w-4 h-4 animate-spin" />
              <CheckCircle2 v-else class="w-4 h-4" />
              {{ createSubmitting ? 'Creating...' : 'Create Account' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ===== Edit User Modal ===== -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeEditModal"></div>
      <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200 dark:border-slate-800">
          <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
              <Pencil class="w-5 h-5 text-amber-600 dark:text-amber-400" />
              Edit Access Role
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Change platform role for {{ editingCandidate?.name }}.</p>
          </div>
          <button @click="closeEditModal" class="p-2 text-slate-500 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitEdit" class="p-6 space-y-4">
          <p v-if="editError" class="flex items-start gap-2 text-sm text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/30 rounded-xl px-4 py-3">
            <AlertCircle class="w-4 h-4 mt-0.5 shrink-0" />
            <span>{{ editError }}</span>
          </p>
          <p v-if="editSuccess" class="flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3">
            <CheckCircle2 class="w-4 h-4 shrink-0" />
            <span>{{ editSuccess }}</span>
          </p>

          <!-- Read-only Account Summary Card -->
          <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 p-4 space-y-3">
            <div class="flex items-center gap-3">
              <img
                v-if="profileImage(editingCandidate)"
                :src="profileImage(editingCandidate)"
                :alt="editingCandidate?.name"
                class="w-10 h-10 rounded-full object-cover border border-slate-200 dark:border-slate-700 shrink-0"
              />
              <div
                v-else
                class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm font-bold shrink-0 border border-blue-200 dark:border-blue-900/50"
              >
                {{ (editingCandidate?.name || '?').charAt(0).toUpperCase() }}
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-slate-900 dark:text-slate-100 truncate">{{ editingCandidate?.name }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ editingCandidate?.email }}</p>
              </div>
            </div>

          </div>

          <!-- Role Edit Selection -->
          <div v-if="canManageRoles">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Access Role</label>
            <select v-model="editForm.role" class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-3 text-sm font-semibold text-slate-900 dark:text-slate-100 outline-none focus:border-blue-500 transition-colors">
              <option value="user">User / Job Seeker</option>
              <option value="hr">HR Manager</option>
              <option value="admin">Administrator</option>
            </select>
          </div>

          <div v-if="canManageRoles && editForm.role === 'hr'">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2" for="edit-company">Assigned Company</label>
            <select id="edit-company" v-model="editForm.company_id" class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-3 text-sm font-semibold text-slate-900 dark:text-slate-100 outline-none focus:border-blue-500 transition-colors">
              <option value="">No company assigned</option>
              <option v-for="item in companies" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
            <p class="mt-1.5 text-xs text-slate-500">HR can manage only this company from Company settings.</p>
          </div>

          <div v-if="!canManageRoles" class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/50">
            <div class="grid gap-3 sm:grid-cols-2">
              <label class="text-xs font-semibold text-slate-600 dark:text-slate-300" for="employee-name">Name<input id="employee-name" v-model="editForm.name" type="text" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" /></label>
              <label class="text-xs font-semibold text-slate-600 dark:text-slate-300" for="employee-email">Email<input id="employee-email" v-model="editForm.email" type="email" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" /></label>
            </div>
            <label class="flex items-center gap-3 text-sm font-semibold text-slate-700 dark:text-slate-200" for="employee-active">
              <input id="employee-active" v-model="editForm.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600" />
              Employee account is active
            </label>
            <p class="mt-1.5 text-xs text-slate-500">HR can update employee contact details and account availability.</p>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <button type="button" @click="closeEditModal" :disabled="editSubmitting" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
              Cancel
            </button>
            <button type="submit" :disabled="editSubmitting" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-md shadow-blue-600/20 disabled:opacity-60 transition-all cursor-pointer">
              <Loader2 v-if="editSubmitting" class="w-4 h-4 animate-spin" />
              <CheckCircle2 v-else class="w-4 h-4" />
              {{ editSubmitting ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
