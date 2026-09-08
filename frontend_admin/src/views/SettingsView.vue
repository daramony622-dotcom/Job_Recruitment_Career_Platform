<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  Check,
  CheckCircle2,
  ChevronRight,
  Globe2,
  Mail,
  Phone,
  RefreshCw,
  Save,
  Settings,
  ShieldCheck,
  UserCog,
  Users,
} from 'lucide-vue-next'
import { adminApi } from '../api'
import StatusBadge from '../components/StatusBadge.vue'
import { profileImage } from '../utils/media'

const router = useRouter()
const loading = ref(false)
const saving = ref(false)
const error = ref('')
const notice = ref('')
const users = ref([])
const settings = reactive({
  site_name: '',
  site_email: '',
  site_phone: '',
  site_logo: '',
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
    const [settingsResponse, usersResponse] = await Promise.all([
      adminApi.getSettings(),
      adminApi.getUsers({ per_page: 100 }),
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

async function saveSettings() {
  saving.value = true
  error.value = ''
  notice.value = ''
  try {
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
            <div class="settings-eyebrow">Administration</div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">Settings</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Configure platform identity and review account access at a glance.</p>
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
        <section class="settings-card settings-identity-card">
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

        <section class="settings-card mt-5">
          <div class="settings-card-heading"><div><p class="settings-kicker">Access overview</p><h2>Account roles</h2><p>Review the accounts currently registered in the platform.</p></div><UserCog class="settings-heading-icon" /></div>
          <div class="role-grid">
            <button v-for="role in roleCounts" :key="role.key" class="role-tile" :class="`role-${role.color}`" @click="openRole(role.key)">
              <span class="role-tile-icon"><ShieldCheck class="w-4 h-4" /></span><span class="role-tile-copy"><strong>{{ role.count }}</strong><span>{{ role.label }}</span></span><ChevronRight class="role-arrow w-4 h-4" />
            </button>
          </div>
        </section>

        <section class="settings-card mt-5">
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
