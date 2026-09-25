<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  Bell,
  Briefcase,
  Building2,
  Check,
  CheckCircle2,
  ChevronRight,
  Globe2,
  Mail,
  Phone,
  Plus,
  RefreshCw,
  Save,
  Settings,
  ShieldCheck,
  Trash2,
  UserCog,
  Users,
} from 'lucide-vue-next'
import { adminApi } from '../api'
import StatusBadge from './StatusBadge.vue'
import { profileImage } from '../utils/media'

const router = useRouter()
const loading = ref(false)
const saving = ref(false)
const error = ref('')
const notice = ref('')
const users = ref([])
const notifications = ref([])
const unreadNotifications = ref(0)
const notificationLoading = ref(false)
const companyExists = ref(false)
const isCompanyScopedUser = computed(() => ['hr', 'company'].includes(JSON.parse(localStorage.getItem('admin_user') || 'null')?.role))
const isHrUser = computed(() => JSON.parse(localStorage.getItem('admin_user') || 'null')?.role === 'hr')
const currentUser = computed(() => JSON.parse(localStorage.getItem('admin_user') || localStorage.getItem('user') || 'null'))
const selectedCompanyId = ref('')
const managedCompanies = ref([])
const switchTargetId = ref('')
const switchingCompany = ref(false)

const settings = reactive({
  site_name: '',
  site_email: '',
  site_phone: '',
  site_logo: '',
})
const company = reactive({
  id: '', name: '', website: '', email: '', phone: '', description: '', industry: '', company_size: '',
  founded_year: '', country: '', city: '', address: '',
})

const roleOptions = [
  { key: 'admin', label: 'Administrators', color: 'blue' },
  { key: 'hr', label: 'HR managers', color: 'violet' },
  { key: 'user', label: 'Candidates', color: 'amber' },
  { key: 'job_seeker', label: 'Job seekers', color: 'teal' },
]

const roleCounts = computed(() => roleOptions.map((role) => ({
  ...role,
  count: users.value.filter((user) => user.role === role.key).length,
})))

const recentUsers = computed(() => users.value.slice(0, 4))
const logoPreview = computed(() => settings.site_logo.trim())
const accountImage = (account) => profileImage(account)

function unwrapUsers(data) {
  const value = data?.data || data || {}
  return value.data || []
}

async function fetchData() {
  loading.value = true
  error.value = ''
  try {
    if (isCompanyScopedUser.value) {
      const [profileResult, companiesResult, notificationResult] = await Promise.allSettled([
        adminApi.getCompanyProfile(),
        adminApi.getManagedCompanies(),
        adminApi.getNotifications(),
      ])

      let values = {}
      if (profileResult.status === 'fulfilled') {
        values = profileResult.value.data?.data || profileResult.value.data || {}
      }

      companyExists.value = Boolean(values.id)
      selectedCompanyId.value = values.id || ''
      switchTargetId.value = values.id || ''
      Object.assign(company, values)

      if (companiesResult.status === 'fulfilled') {
        const payload = companiesResult.value.data?.data || companiesResult.value.data || []
        managedCompanies.value = Array.isArray(payload) ? payload : []
      }

      if (notificationResult.status === 'fulfilled') setNotifications(notificationResult.value.data)
      return
    }

    const [settingsResponse, usersResponse] = await Promise.all([
      adminApi.getSettings(), adminApi.getUsers({ per_page: 100 }),
    ])
    const values = settingsResponse.data?.data || settingsResponse.data || {}
    Object.assign(settings, {
      site_name: values.site_name ?? '',
      site_email: values.site_email ?? '',
      site_phone: values.site_phone ?? '',
      site_logo: values.site_logo ?? '',
    })
    users.value = unwrapUsers(usersResponse.data)
  } catch (requestError) {
    error.value = requestError.response?.data?.message || 'Failed to load settings and account data.'
  } finally {
    loading.value = false
  }
}

async function switchCompany() {
  if (!switchTargetId.value || switchTargetId.value === String(selectedCompanyId.value)) return

  switchingCompany.value = true
  error.value = ''
  notice.value = ''
  try {
    await adminApi.switchCompany(switchTargetId.value)
    localStorage.setItem('active_company_id', String(switchTargetId.value))
    const savedUser = JSON.parse(localStorage.getItem('admin_user') || localStorage.getItem('user') || 'null')
    if (savedUser) {
      savedUser.company_id = Number(switchTargetId.value)
      localStorage.setItem('admin_user', JSON.stringify(savedUser))
      localStorage.setItem('user', JSON.stringify(savedUser))
    }
    notice.value = 'Company switched successfully.'
    await fetchData()
  } catch (requestError) {
    error.value = requestError.response?.data?.message || 'Unable to switch company.'
  } finally {
    switchingCompany.value = false
  }
}

function setNotifications(data) {
  notifications.value = data?.data || []
  unreadNotifications.value = Number(data?.unread_count || 0)
}

async function markAllNotificationsRead() {
  notificationLoading.value = true
  try {
    await adminApi.markAllNotificationsAsRead()
    notifications.value = notifications.value.map((notification) => ({ ...notification, read_at: notification.read_at || new Date().toISOString() }))
    unreadNotifications.value = 0
  } finally {
    notificationLoading.value = false
  }
}

async function deleteNotification(id) {
  await adminApi.deleteNotification(id)
  notifications.value = notifications.value.filter((notification) => notification.id !== id)
}

async function deleteCompanyProfile() {
  if (isHrUser.value) return
  if (!companyExists.value || !window.confirm('Delete this company profile? This will remove access to its job posts.')) return
  saving.value = true
  error.value = ''
  try {
    if (selectedCompanyId.value && selectedCompanyId.value !== 'new') {
      await adminApi.deleteCompany(selectedCompanyId.value)
    } else {
      await adminApi.deleteCompanyProfile()
    }
    companyExists.value = false
    Object.keys(company).forEach((key) => { company[key] = '' })
    notice.value = 'Company profile deleted successfully.'
    fetchData()
  } catch (requestError) {
    error.value = requestError.response?.data?.message || 'Failed to delete company profile.'
  } finally {
    saving.value = false
  }
}

async function saveSettings() {
  saving.value = true
  error.value = ''
  notice.value = ''
  try {
    if (isCompanyScopedUser.value) {
      if (isHrUser.value && !companyExists.value) {
        throw new Error('No company has been assigned to this HR account. Ask an administrator to assign one.')
      }
      const payload = {
        name: company.name,
        website: company.website,
        email: company.email,
        phone: company.phone,
        description: company.description,
        industry: company.industry,
        company_size: company.company_size,
        founded_year: company.founded_year,
        country: company.country,
        city: company.city,
        address: company.address,
      }
      let res = null
      if (isHrUser.value) {
        res = await adminApi.updateCompanyProfile(payload)
      } else if (companyExists.value && selectedCompanyId.value) {
        res = await adminApi.updateCompany(selectedCompanyId.value, payload)
      } else {
        res = await adminApi.storeCompany(payload)
      }
      const updated = res?.data?.data || res?.data || payload
      companyExists.value = true
      selectedCompanyId.value = updated.id || selectedCompanyId.value
      Object.assign(company, updated)
      notice.value = 'Company profile saved successfully.'
      fetchData()
      return
    }

    await adminApi.updateSettings({
      site_name: settings.site_name || null,
      site_email: settings.site_email || null,
      site_phone: settings.site_phone || null,
      site_logo: settings.site_logo || null,
    })
    notice.value = 'Settings updated successfully.'
  } catch (requestError) {
    const response = requestError.response?.data
    error.value = response?.errors ? Object.values(response.errors).flat().join(' ') : response?.message || 'Failed to save settings.'
  } finally {
    saving.value = false
  }
}

function openRole(role) {
  router.push({ path: '/candidates', query: { role } })
}

onMounted(fetchData)
</script>

<template>
  <div class="settings-page p-4 sm:p-6 lg:p-8">
    <div class="max-w-6xl mx-auto">
      <header class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5 mb-7">
        <div class="flex items-start gap-3">
          <div class="settings-title-icon"><Settings class="w-5 h-5" /></div>
          <div>
            <div class="settings-eyebrow">{{ isCompanyScopedUser ? 'Company workspace' : 'Administration' }}</div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">{{ isCompanyScopedUser ? 'Company settings' : 'Settings' }}</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ isCompanyScopedUser ? 'Manage your company profile used across jobs and applications.' : 'Configure platform identity and review account access at a glance.' }}</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button class="settings-secondary-button" :disabled="loading" @click="fetchData"><RefreshCw class="w-4 h-4" :class="loading ? 'animate-spin' : ''" /> Refresh</button>
          <button class="settings-primary-button" :disabled="saving || loading" @click="saveSettings"><Save class="w-4 h-4" /> {{ saving ? 'Saving...' : 'Save settings' }}</button>
        </div>
      </header>

      <p v-if="error" class="settings-alert settings-alert-error">{{ error }}</p>
      <p v-if="notice" class="settings-alert settings-alert-success"><CheckCircle2 class="w-4 h-4" /> {{ notice }}</p>

      <div v-if="loading" class="settings-loading"><RefreshCw class="w-5 h-5 animate-spin" /> Loading configuration...</div>

      <template v-else>
        <section v-if="isHrUser && managedCompanies.length" class="settings-card mb-5">
          <div class="settings-card-heading">
            <div><p class="settings-kicker">Company access</p><h2>Switch Company</h2><p>Choose one of the companies assigned to your HR account. Ask an administrator to assign more companies when needed.</p></div>
            <Building2 class="settings-heading-icon" />
          </div>
          <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <label class="settings-field flex-1"><span>Active company</span><div class="settings-input-wrap"><Building2 class="w-4 h-4" /><select v-model="switchTargetId"><option v-for="item in managedCompanies" :key="item.id" :value="String(item.id)">{{ item.name }}</option></select></div></label>
            <button class="settings-primary-button" :disabled="switchingCompany || switchTargetId === String(selectedCompanyId)" @click="switchCompany"><RefreshCw class="w-4 h-4" :class="switchingCompany ? 'animate-spin' : ''" /> {{ switchingCompany ? 'Switching...' : 'Switch Company' }}</button>
          </div>
        </section>
        <section v-if="isCompanyScopedUser" class="settings-card settings-identity-card">
          <div class="settings-card-heading">
            <div><p class="settings-kicker">Company profile</p><h2>Organization details</h2><p>These details appear on your job posts and company profile.</p></div>
            <Globe2 class="settings-heading-icon" />
          </div>
          <div class="settings-form-grid">
            <label class="settings-field"><span>Company name</span><div class="settings-input-wrap"><Building2 class="w-4 h-4" /><input v-model="company.name" type="text" placeholder="Company name" /></div></label>
            <label class="settings-field"><span>Industry</span><div class="settings-input-wrap"><Briefcase class="w-4 h-4" /><input v-model="company.industry" type="text" placeholder="Technology" /></div></label>
            <label class="settings-field"><span>Website</span><div class="settings-input-wrap"><Globe2 class="w-4 h-4" /><input v-model="company.website" type="url" placeholder="https://example.com" /></div></label>
            <label class="settings-field"><span>Contact email</span><div class="settings-input-wrap"><Mail class="w-4 h-4" /><input v-model="company.email" type="email" placeholder="company@example.com" /></div></label>
            <label class="settings-field"><span>Phone number</span><div class="settings-input-wrap"><Phone class="w-4 h-4" /><input v-model="company.phone" type="text" placeholder="+855 12 345 678" /></div></label>
            <label class="settings-field"><span>Company size</span><div class="settings-input-wrap"><Users class="w-4 h-4" /><select v-model="company.company_size"><option value="">Select size</option><option v-for="size in ['1-10', '11-50', '51-200', '201-500', '501-1000', '1000+']" :key="size" :value="size">{{ size }}</option></select></div></label>
            <label class="settings-field"><span>Country</span><div class="settings-input-wrap"><Globe2 class="w-4 h-4" /><input v-model="company.country" type="text" placeholder="Cambodia" /></div></label>
            <label class="settings-field"><span>City</span><div class="settings-input-wrap"><Globe2 class="w-4 h-4" /><input v-model="company.city" type="text" placeholder="Phnom Penh" /></div></label>
            <label class="settings-field"><span>Address</span><div class="settings-input-wrap"><Globe2 class="w-4 h-4" /><input v-model="company.address" type="text" placeholder="Office address" /></div></label>
            <label class="settings-field"><span>Founded year</span><div class="settings-input-wrap"><Settings class="w-4 h-4" /><input v-model="company.founded_year" type="number" min="1800" max="2100" placeholder="2020" /></div></label>
          </div>
          <label class="settings-field mt-4"><span>Description</span><textarea v-model="company.description" rows="4" class="setting-input" placeholder="Tell candidates about your company..."></textarea></label>
          <div class="settings-preview-row">
            <div class="settings-brand-preview"><div class="settings-logo-fallback"><Building2 class="w-5 h-5" /></div><div><strong>{{ company.name || 'Company profile' }}</strong><span>{{ companyExists ? 'Profile is active' : 'Create your company profile' }}</span></div></div>
            <button v-if="companyExists && !isHrUser" class="settings-secondary-button text-rose-600" :disabled="saving" @click="deleteCompanyProfile"><Trash2 class="w-4 h-4" /> Delete profile</button>
          </div>
        </section>

        <section v-else class="settings-card settings-identity-card">
          <div class="settings-card-heading">
            <div><p class="settings-kicker">Platform identity</p><h2>Site information</h2><p>These values are stored by the Laravel settings API and used across the platform.</p></div>
            <Globe2 class="settings-heading-icon" />
          </div>
          <div class="settings-form-grid">
            <label class="settings-field"><span>Site name</span><div class="settings-input-wrap"><Settings class="w-4 h-4" /><input v-model="settings.site_name" type="text" placeholder="Your recruitment platform" /></div></label>
            <label class="settings-field"><span>Contact email</span><div class="settings-input-wrap"><Mail class="w-4 h-4" /><input v-model="settings.site_email" type="email" placeholder="admin@example.com" /></div></label>
            <label class="settings-field"><span>Phone number</span><div class="settings-input-wrap"><Phone class="w-4 h-4" /><input v-model="settings.site_phone" type="text" placeholder="+855 12 345 678" /></div></label>
            <label class="settings-field"><span>Logo URL</span><div class="settings-input-wrap"><Globe2 class="w-4 h-4" /><input v-model="settings.site_logo" type="url" placeholder="https://example.com/logo.png" /></div></label>
          </div>
          <div class="settings-preview-row">
            <div class="settings-brand-preview">
              <div v-if="logoPreview" class="settings-logo-preview"><img :src="logoPreview" alt="Site logo preview" /></div>
              <div v-else class="settings-logo-fallback"><Settings class="w-5 h-5" /></div>
              <div><strong>{{ settings.site_name || 'Your platform name' }}</strong><span>{{ settings.site_email || 'No contact email configured' }}</span></div>
            </div>
            <span class="settings-saved-state"><Check class="w-3.5 h-3.5" /> Backend fields only</span>
          </div>
        </section>

        <template v-if="isCompanyScopedUser">
          <section class="settings-card mt-5">
            <div class="settings-card-heading"><div><p class="settings-kicker">User access</p><h2>Your workspace access</h2><p>Review the account currently controlling this company workspace.</p></div><UserCog class="settings-heading-icon" /></div>
            <div class="settings-account-list">
              <div class="settings-account-row">
                <div class="settings-account-avatar">{{ (currentUser?.name || 'H').charAt(0).toUpperCase() }}</div>
                <div class="settings-account-copy"><strong>{{ currentUser?.name || 'HR manager' }}</strong><span>{{ currentUser?.email || 'No email available' }}</span></div>
                <StatusBadge :value="currentUser?.role || 'hr'" /><span class="hidden sm:inline text-xs text-emerald-600">{{ currentUser?.is_active === false ? 'Inactive' : 'Active' }}</span>
              </div>
            </div>
          </section>

          <section class="settings-card mt-5">
            <div class="settings-card-heading"><div><p class="settings-kicker">Notifications</p><h2>Workspace notifications <span v-if="unreadNotifications" class="text-blue-600">({{ unreadNotifications }} unread)</span></h2><p>Review and clear notifications delivered to your HR account.</p></div><div class="flex items-center gap-2"><Bell class="settings-heading-icon" /><button v-if="unreadNotifications" class="settings-inline-link" :disabled="notificationLoading" @click="markAllNotificationsRead">Mark all read</button></div></div>
            <div v-if="notifications.length" class="settings-account-list">
              <div v-for="notification in notifications" :key="notification.id" class="settings-account-row">
                <div class="settings-account-avatar"><Bell class="w-4 h-4" /></div>
                <div class="settings-account-copy"><strong>{{ notification.data?.title || notification.data?.message || notification.type || 'Notification' }}</strong><span>{{ notification.created_at ? new Date(notification.created_at).toLocaleString() : 'Recent activity' }}</span></div>
                <span v-if="!notification.read_at" class="text-xs font-bold text-blue-600">Unread</span>
                <button class="p-2 text-slate-400 transition hover:text-rose-600" title="Delete notification" @click="deleteNotification(notification.id)"><Trash2 class="w-4 h-4" /></button>
              </div>
            </div>
            <div v-else class="settings-empty"><Bell class="w-5 h-5" /> No notifications.</div>
          </section>
        </template>

        <section v-if="!isCompanyScopedUser" class="settings-card mt-5">
          <div class="settings-card-heading"><div><p class="settings-kicker">Access overview</p><h2>Account roles</h2><p>Review the accounts currently registered in the platform.</p></div><UserCog class="settings-heading-icon" /></div>
          <div class="role-grid">
            <button v-for="role in roleCounts" :key="role.key" class="role-tile" :class="`role-${role.color}`" @click="openRole(role.key)">
              <span class="role-tile-icon"><ShieldCheck class="w-4 h-4" /></span><span class="role-tile-copy"><strong>{{ role.count }}</strong><span>{{ role.label }}</span></span><ChevronRight class="role-arrow w-4 h-4" />
            </button>
          </div>
        </section>

        <section v-if="!isCompanyScopedUser" class="settings-card mt-5">
          <div class="settings-card-heading"><div><p class="settings-kicker">Account directory</p><h2>Recent accounts</h2><p>Open Users & HR to update names, roles, and active status.</p></div><router-link to="/candidates" class="settings-inline-link">Manage accounts <ChevronRight class="w-4 h-4" /></router-link></div>
          <div v-if="recentUsers.length" class="settings-account-list">
            <div v-for="account in recentUsers" :key="account.id" class="settings-account-row">
              <div class="settings-account-avatar">
                <img v-if="accountImage(account)" :src="accountImage(account)" :alt="account.name || 'Account'" />
                <template v-else>{{ (account.name || 'A').charAt(0).toUpperCase() }}</template>
              </div>
              <div class="settings-account-copy"><strong>{{ account.name }}</strong><span>{{ account.email }}</span></div>
              <StatusBadge :value="account.role" /><span class="hidden sm:inline text-xs text-slate-500">{{ account.is_active ? 'Active' : 'Inactive' }}</span>
            </div>
          </div>
          <div v-else class="settings-empty"><Users class="w-5 h-5" /> No accounts found.</div>
        </section>
      </template>
    </div>
  </div>
</template>
