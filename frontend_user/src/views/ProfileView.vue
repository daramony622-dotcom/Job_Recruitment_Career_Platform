<script setup>
import { ref, computed, onMounted } from 'vue'
import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
import { useJobSeekerApi } from '../composables/useJobSeekerApi'
import { resolveAssetUrl, useAuth } from '../composables/useAuth'
import { 
  User, Mail, Phone, MapPin, Briefcase, Calendar, 
  Camera, Edit3, ExternalLink, Award, FileText, Download, 
  Eye, EyeOff, Globe, Github, Linkedin, CheckCircle2, 
  DollarSign, Sparkles, Clock, ShieldCheck, X, Upload, Check,
  GraduationCap, Building2, Wrench, Bookmark, ClipboardList, Video,
  Trash2, MapPinned, MoreHorizontal
} from 'lucide-vue-next'

const profile = ref({
  user_name: '',
  email: '',
  avatar: '',
  headline: '',
  bio: '',
  phone: '',
  date_of_birth: '',
  gender: '',
  nationality: '',
  country: '',
  city: '',
  address: '',
  linkedin_url: '',
  github_url: '',
  portfolio_url: '',
  cv_path: '',
  cv_original_name: '',
  cv_uploaded_at: '',
  availability: '',
  expected_salary_min: null,
  expected_salary_max: null,
  salary_currency: '',
  is_open_to_work: false,
  is_profile_visible: false,
  profile_views: 0
})

// File Input Ref
const avatarInput = ref(null)

// UI State
const isEditing = ref(false)
const showUploadModal = ref(false)
const isSaving = ref(false)
const saveSuccess = ref(false)
const uploadMessage = ref('')
const saveError = ref('')
const isLoading = ref(true)
const profileError = ref('')
const api = useJobSeekerApi()
const { setProfileAvatar } = useAuth()

const education = ref([])
const experience = ref([])
const skills = ref([])
const customSkills = ref([])
const cvDocuments = ref([])
const savedJobs = ref([])
const applications = ref([])
const interviews = ref([])
const dashboardLoading = ref(false)
const dashboardError = ref('')
const cvUploadInput = ref(null)
const isCvUploading = ref(false)
const activeSection = ref('overview')
const showCareerModal = ref(false)
const careerType = ref('education')
const editingCareerId = ref(null)
const isCareerSaving = ref(false)
const careerFormError = ref('')
const careerForm = ref({})
const skillCatalog = ref([])
const skillSearch = ref('')
const showSkillsModal = ref(false)
const isSkillsSaving = ref(false)

const unwrapCollection = (response) => {
  const payload = response?.data ?? response
  return Array.isArray(payload) ? payload : (payload?.data || [])
}

const loadCandidateWorkspace = async () => {
  dashboardLoading.value = true
  dashboardError.value = ''

  const results = await Promise.allSettled([
    api.listEducation(),
    api.listExperience(),
    api.getMySkills(),
    api.listCvs(),
    api.listSavedJobs(),
    api.listApplications(),
    api.listInterviews(),
  ])

  const [educationResult, experienceResult, skillsResult, cvsResult, savedResult, applicationsResult, interviewsResult] = results
  education.value = educationResult.status === 'fulfilled' ? unwrapCollection(educationResult.value) : []
  experience.value = experienceResult.status === 'fulfilled' ? unwrapCollection(experienceResult.value) : []
  skills.value = skillsResult.status === 'fulfilled' ? unwrapCollection(skillsResult.value) : []
  cvDocuments.value = cvsResult.status === 'fulfilled' ? unwrapCollection(cvsResult.value) : []
  savedJobs.value = savedResult.status === 'fulfilled' ? unwrapCollection(savedResult.value) : []
  applications.value = applicationsResult.status === 'fulfilled' ? unwrapCollection(applicationsResult.value) : []
  interviews.value = interviewsResult.status === 'fulfilled' ? unwrapCollection(interviewsResult.value) : []

  if (results.some((result) => result.status === 'rejected')) {
    dashboardError.value = 'Some workspace data could not be loaded. You can retry without losing your profile.'
  }
  dashboardLoading.value = false
}

const loadSkillCatalog = async () => {
  try {
    const response = await api.listSkills(skillSearch.value)
    const payload = response?.data ?? response
    skillCatalog.value = Array.isArray(payload) ? payload : (payload?.data || [])
  } catch (error) {
    careerFormError.value = error.message || 'Unable to load available skills.'
  }
}

const openCareerModal = (type, item = null) => {
  careerType.value = type
  editingCareerId.value = item?.id || null
  careerFormError.value = ''
  careerForm.value = item ? { ...item, is_current: Boolean(item.is_current) } : type === 'education'
    ? { institution_name: '', degree: '', field_of_study: '', start_date: '', end_date: '', is_current: false, grade: '', country: '', description: '' }
    : { job_title: '', company_name: '', location: '', start_date: '', end_date: '', is_current: false, description: '' }
  showCareerModal.value = true
}

const closeCareerModal = () => {
  showCareerModal.value = false
  editingCareerId.value = null
}

const saveCareerItem = async () => {
  isCareerSaving.value = true
  careerFormError.value = ''
  try {
    const payload = { ...careerForm.value }
    if (payload.is_current) payload.end_date = null
    const collection = careerType.value === 'education' ? education : experience
    const saved = editingCareerId.value
      ? await (careerType.value === 'education' ? api.updateEducation(editingCareerId.value, payload) : api.updateExperience(editingCareerId.value, payload))
      : await (careerType.value === 'education' ? api.createEducation(payload) : api.createExperience(payload))
    const item = api.unwrap(saved)
    const index = collection.value.findIndex((entry) => entry.id === item.id)
    if (index === -1) collection.value.unshift(item)
    else collection.value[index] = item
    closeCareerModal()
  } catch (error) {
    careerFormError.value = error.message || 'Unable to save this record.'
  } finally {
    isCareerSaving.value = false
  }
}

const deleteCareerItem = async (type, item) => {
  if (!window.confirm(`Delete this ${type} record?`)) return
  try {
    if (type === 'education') await api.deleteEducation(item.id)
    else await api.deleteExperience(item.id)
    const collection = type === 'education' ? education : experience
    collection.value = collection.value.filter((entry) => entry.id !== item.id)
  } catch (error) {
    saveError.value = error.message || 'Unable to delete this record.'
  }
}

const openSkillsModal = async () => {
  careerFormError.value = ''
  showSkillsModal.value = true
  await loadSkillCatalog()
}

const saveSkills = async () => {
  isSkillsSaving.value = true
  careerFormError.value = ''
  try {
    const response = await api.updateMySkills(skills.value.map((skill) => skill.id))
    skills.value = unwrapCollection(response)
    showSkillsModal.value = false
  } catch (error) {
    careerFormError.value = error.message || 'Unable to update your skills.'
  } finally {
    isSkillsSaving.value = false
  }
}

const toggleSkill = (skill) => {
  const exists = skills.value.some((item) => item.id === skill.id)
  skills.value = exists ? skills.value.filter((item) => item.id !== skill.id) : [...skills.value, skill]
}

const customSkillName = ref('')
const isCustomSkillSaving = ref(false)

const addCustomSkill = async () => {
  const name = customSkillName.value.trim()
  if (!name) return
  isCustomSkillSaving.value = true
  careerFormError.value = ''
  try {
    const response = await api.addCustomSkill(name)
    customSkills.value = response?.data?.data ?? response?.data ?? response ?? customSkills.value
    customSkillName.value = ''
  } catch (error) {
    careerFormError.value = error.message || 'Unable to add your custom skill.'
  } finally {
    isCustomSkillSaving.value = false
  }
}

const removeCustomSkill = async (name) => {
  try {
    const response = await api.removeCustomSkill(name)
    customSkills.value = response?.data?.data ?? response?.data ?? response ?? customSkills.value.filter((skill) => skill !== name)
  } catch (error) {
    careerFormError.value = error.message || 'Unable to remove this custom skill.'
  }
}

// Edit Form Draft State
const editForm = ref({ ...profile.value })

const openEditModal = () => {
  editForm.value = JSON.parse(JSON.stringify(profile.value))
  isEditing.value = true
}

const closeEditModal = () => {
  isEditing.value = false
}

const saveProfile = async () => {
  isSaving.value = true
  saveError.value = ''
  try {
    const payload = {
      name: editForm.value.user_name,
      email: editForm.value.email,
      headline: editForm.value.headline,
      bio: editForm.value.bio,
      phone: editForm.value.phone,
      date_of_birth: editForm.value.date_of_birth,
      gender: editForm.value.gender,
      nationality: editForm.value.nationality,
      country: editForm.value.country,
      city: editForm.value.city,
      address: editForm.value.address,
      linkedin_url: editForm.value.linkedin_url,
      github_url: editForm.value.github_url,
      portfolio_url: editForm.value.portfolio_url,
      availability: editForm.value.availability,
      expected_salary_min: editForm.value.expected_salary_min,
      expected_salary_max: editForm.value.expected_salary_max,
      salary_currency: editForm.value.salary_currency,
      is_open_to_work: editForm.value.is_open_to_work,
      is_profile_visible: editForm.value.is_profile_visible,
    }
    const response = await api.updateProfile(payload)
    const savedProfile = response?.data ?? response
    profile.value = {
      ...profile.value,
      ...savedProfile,
      user_name: savedProfile.user?.name || profile.value.user_name,
      email: savedProfile.user?.email || profile.value.email,
      avatar: resolveAssetUrl(savedProfile.avatar),
      cv_path: resolveAssetUrl(savedProfile.cv_path),
    }
    setProfileAvatar(profile.value.avatar)
    editForm.value = JSON.parse(JSON.stringify(profile.value))
    isSaving.value = false
    isEditing.value = false
    saveSuccess.value = true
    uploadMessage.value = 'Profile details updated successfully!'
    setTimeout(() => { saveSuccess.value = false }, 3500)
  } catch (error) {
    isSaving.value = false
    saveError.value = error.message || 'Unable to update your profile.'
    saveSuccess.value = false
  }
}

const loadProfile = async () => {
  isLoading.value = true
  profileError.value = ''

  try {
    const remoteProfile = await api.getProfile()
    profile.value = {
      ...profile.value,
      ...remoteProfile,
      user_name: remoteProfile.user?.name || profile.value.user_name,
      email: remoteProfile.user?.email || profile.value.email,
      avatar: resolveAssetUrl(remoteProfile.avatar),
      cv_path: resolveAssetUrl(remoteProfile.cv_path),
    }
    customSkills.value = remoteProfile.custom_skills || []
    setProfileAvatar(profile.value.avatar)
    editForm.value = JSON.parse(JSON.stringify(profile.value))
  } catch (error) {
    profileError.value = error.message || 'Unable to load your profile.'
  } finally {
    isLoading.value = false
    await loadCandidateWorkspace()
  }
}

onMounted(loadProfile)

// Image Upload Handler
const triggerAvatarUpload = () => {
  if (avatarInput.value) avatarInput.value.click()
}

const handleAvatarUpload = (e) => {
  const file = e.target.files[0]
  e.target.value = ''
  if (!file) return

  uploadAvatar(file)
}

const uploadAvatar = async (file) => {
  isSaving.value = true
  saveError.value = ''

  try {
    const response = await api.updateAvatar(file)
    const savedProfile = response?.data ?? response
    profile.value = {
      ...profile.value,
      ...savedProfile,
      avatar: resolveAssetUrl(savedProfile.avatar),
    }
    setProfileAvatar(profile.value.avatar)
    editForm.value.avatar = profile.value.avatar
    uploadMessage.value = 'Profile photo updated successfully!'
    saveSuccess.value = true
    setTimeout(() => { saveSuccess.value = false }, 3500)
  } catch (error) {
    saveError.value = error.message || 'Unable to upload your profile photo.'
  } finally {
    isSaving.value = false
  }
}

const handleFileUpload = async (e) => {
  const file = e.target.files[0]
  e.target.value = ''
  if (!file) return

  isCvUploading.value = true
  saveError.value = ''
  try {
    const formData = new FormData()
    formData.append('file_path', file)
    formData.append('title', file.name.replace(/\.[^/.]+$/, ''))
    formData.append('is_primary', String(cvDocuments.value.length === 0))
    const savedCv = api.unwrap(await api.createCv(formData))
    cvDocuments.value.unshift(savedCv)
    uploadMessage.value = 'CV uploaded successfully.'
    saveSuccess.value = true
    setTimeout(() => { saveSuccess.value = false }, 3500)
  } catch (error) {
    saveError.value = error.message || 'Unable to upload your CV.'
  } finally {
    isCvUploading.value = false
  }
}

const removeSavedJob = async (savedJob) => {
  try {
    await api.removeSavedJob(savedJob.id)
    savedJobs.value = savedJobs.value.filter((item) => item.id !== savedJob.id)
  } catch (error) {
    saveError.value = error.message || 'Unable to remove this saved job.'
  }
}

const deleteCv = async (cv) => {
  try {
    await api.deleteCv(cv.id)
    cvDocuments.value = cvDocuments.value.filter((item) => item.id !== cv.id)
  } catch (error) {
    saveError.value = error.message || 'Unable to delete this CV.'
  }
}

const withdrawApplication = async (application) => {
  try {
    await api.withdrawApplication(application.id)
    application.status = 'withdrawn'
  } catch (error) {
    saveError.value = error.message || 'Unable to withdraw this application.'
  }
}

const formatStatus = (value) => (value || 'pending').replace(/_/g, ' ')
const jobTitle = (item) => item?.job?.title || item?.job_post?.title || 'Untitled position'
const companyName = (item) => item?.job?.company?.name || item?.job_post?.company?.name || item?.job_post?.company_name || 'Company'
const formatPeriod = (start, end, current = false) => `${formatDate(start) || 'Present'} - ${current ? 'Present' : (formatDate(end) || 'Present')}`

// Helpers
const formatAvailability = (val) => {
  switch (val) {
    case 'immediately': return 'Immediately Available'
    case 'within_1_month': return 'Available in 1 Month'
    case 'within_3_months': return 'Available in 3 Months'
    case 'not_available': return 'Not Currently Available'
    default: return val
  }
}

const availabilityBadgeStyle = (val) => {
  switch (val) {
    case 'immediately': return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/50'
    case 'within_1_month': return 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-900/50'
    case 'within_3_months': return 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-900/50'
    default: return 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700'
  }
}

const formatDate = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors duration-200">
    <Navbar />

    <!-- Hidden Native File Input for Avatar -->
    <input 
      ref="avatarInput" 
      id="avatar-upload"
      type="file" 
      accept="image/*" 
      class="hidden" 
      @change="handleAvatarUpload" 
    />
    <label for="avatar-upload" class="sr-only">Upload profile photo</label>

    <div v-if="isLoading" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
      <div class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-300">
        Loading your profile...
      </div>
    </div>

    <div v-else-if="profileError" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
      <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300">
        <span>{{ profileError }}</span>
        <button type="button" class="font-bold underline" @click="loadProfile">Try again</button>
      </div>
    </div>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

      <!-- Success Notification Alert -->
      <div v-if="saveSuccess" class="p-4 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-900/50 rounded-2xl text-xs font-bold text-emerald-700 dark:text-emerald-300 flex items-center justify-between shadow-xs">
        <span class="flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 text-emerald-500" />
          <span>{{ uploadMessage || 'Profile updated successfully!' }}</span>
        </span>
        <button @click="saveSuccess = false" class="text-emerald-600 hover:text-emerald-800"><X class="w-4 h-4" /></button>
      </div>

      <!-- ─── 1. Header Profile Header Card (Without Cover Photo) ────────── -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
          
          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
            
            <!-- Avatar Image with Camera Upload Button Overlay -->
            <div class="relative group shrink-0">
              <img
                v-if="profile.avatar"
                :src="profile.avatar" 
                :alt="profile.user_name" 
                class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl object-cover border-2 border-slate-200/80 dark:border-slate-700/80 shadow-md bg-white" 
              />
              <div
                v-else
                class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl border-2 border-slate-200/80 dark:border-slate-700/80 shadow-md bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-3xl font-extrabold text-blue-600 dark:text-blue-400"
                aria-label="No profile photo"
              >
                {{ (profile.user_name || '?').charAt(0).toUpperCase() }}
              </div>
              
              <!-- Avatar Upload Camera Button -->
              <button 
                @click="triggerAvatarUpload" 
                type="button"
                class="absolute -bottom-1 -right-1 p-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl shadow-lg transition cursor-pointer hover:scale-105 active:scale-95 border-2 border-white dark:border-slate-900" 
                title="Upload Profile Photo"
              >
                <Camera class="w-4 h-4" />
              </button>
            </div>

            <!-- Profile Main Information -->
            <div class="space-y-2">
              <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                  {{ profile.user_name }}
                </h1>
                
                <!-- Open To Work Badge -->
                <span 
                  v-if="profile.is_open_to_work" 
                  class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-xs font-extrabold px-3 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-900/50 flex items-center gap-1.5"
                >
                  <Sparkles class="w-3.5 h-3.5 text-emerald-500" /> Open to Work
                </span>

                <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold border" :class="availabilityBadgeStyle(profile.availability)">
                  {{ formatAvailability(profile.availability) }}
                </span>
              </div>

              <p class="text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 flex items-center gap-1.5">
                <Briefcase class="w-4 h-4" /> {{ profile.headline }}
              </p>

              <div class="flex items-center gap-4 flex-wrap text-xs text-slate-500 dark:text-slate-400 font-medium pt-1">
                <span class="flex items-center gap-1.5">
                  <MapPin class="w-4 h-4 text-slate-400" />
                  {{ profile.address ? `${profile.address}, ` : '' }}{{ profile.city }}, {{ profile.country }}
                </span>
                <span class="flex items-center gap-1.5">
                  <Mail class="w-4 h-4 text-slate-400" />
                  {{ profile.email }}
                </span>
              </div>
            </div>

          </div>

          <!-- Action Buttons -->
          <div class="flex items-center gap-2.5 w-full md:w-auto shrink-0 flex-wrap pt-2 md:pt-0">
            <button 
              @click="triggerAvatarUpload"
              type="button"
              class="flex-1 md:flex-initial inline-flex items-center justify-center gap-1.5 px-4 py-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-2xl border border-slate-200 dark:border-slate-700 transition active:scale-95 cursor-pointer"
            >
              <Camera class="w-4 h-4 text-blue-600" />
              <span>Change Photo</span>
            </button>

            <button 
              @click="openEditModal"
              type="button"
              :disabled="isLoading || Boolean(profileError)"
              class="flex-1 md:flex-initial inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-2xl shadow-md shadow-blue-500/20 transition active:scale-95 cursor-pointer"
            >
              <Edit3 class="w-4 h-4" />
              <span>Edit Profile</span>
            </button>
          </div>

        </div>

      </div>

      <!-- ─── 2. Quick Specs & Stats Grid ─────────────────────────────── -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl space-y-1">
          <div class="flex items-center gap-2 text-xs font-semibold text-slate-400"><DollarSign class="w-4 h-4 text-emerald-500" /> Expected Salary</div>
          <p class="text-sm font-extrabold text-slate-900 dark:text-white">
            ${{ Number(profile.expected_salary_min).toLocaleString() }} - ${{ Number(profile.expected_salary_max).toLocaleString() }}
          </p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ profile.salary_currency || 'USD' }} / month</p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl space-y-1">
          <div class="flex items-center gap-2 text-xs font-semibold text-slate-400"><Clock class="w-4 h-4 text-blue-500" /> Availability</div>
          <p class="text-sm font-extrabold text-slate-900 dark:text-white truncate capitalize">{{ formatAvailability(profile.availability) }}</p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">Notice period</p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl space-y-1">
          <div class="flex items-center gap-2 text-xs font-semibold text-slate-400"><Eye class="w-4 h-4 text-indigo-500" /> Recruiter Views</div>
          <p class="text-sm font-extrabold text-slate-900 dark:text-white">{{ profile.profile_views }} Views</p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">HR searches</p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl space-y-1">
          <div class="flex items-center gap-2 text-xs font-semibold text-slate-400"><Globe class="w-4 h-4 text-purple-500" /> Nationality</div>
          <p class="text-sm font-extrabold text-slate-900 dark:text-white">{{ profile.nationality || 'Cambodian' }}</p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ profile.city }}</p>
        </div>

      </div>

      <!-- ─── 3. Main Grid Layout ─────────────────────────────────────── -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-8">
          
          <!-- About Me & Bio Card -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 md:p-8 rounded-3xl space-y-4">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">About Candidate</h2>
            <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
              {{ profile.bio || 'No bio specified yet.' }}
            </p>
          </div>

        </div>

        <!-- ─── Sidebar: Personal Details & Professional Links ──────────── -->
        <div class="space-y-6">
          
          <!-- Personal Information Card -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 rounded-3xl space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3">
              Personal Information
            </h3>

            <div class="space-y-3 text-xs text-slate-600 dark:text-slate-300">
              <div class="flex items-center gap-3">
                <Mail class="w-4 h-4 text-blue-600 shrink-0" />
                <span class="truncate">{{ profile.email }}</span>
              </div>

              <div class="flex items-center gap-3">
                <Phone class="w-4 h-4 text-blue-600 shrink-0" />
                <span>{{ profile.phone || 'Not specified' }}</span>
              </div>

              <div class="flex items-center gap-3">
                <Calendar class="w-4 h-4 text-blue-600 shrink-0" />
                <span>Born {{ formatDate(profile.date_of_birth) }}</span>
              </div>

              <div class="flex items-center gap-3 capitalize">
                <User class="w-4 h-4 text-blue-600 shrink-0" />
                <span>Gender: {{ profile.gender || 'Not specified' }}</span>
              </div>

              <div class="flex items-center gap-3">
                <MapPin class="w-4 h-4 text-blue-600 shrink-0" />
                <span>{{ profile.address }}, {{ profile.city }}, {{ profile.country }}</span>
              </div>
            </div>
          </div>

          <!-- Professional Links Card -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 rounded-3xl space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3">
              Social & Portfolio Links
            </h3>

            <div class="space-y-2.5">
              <a 
                v-if="profile.linkedin_url" 
                :href="profile.linkedin_url" 
                target="_blank" 
                rel="noopener noreferrer"
                class="flex items-center justify-between p-3 rounded-2xl bg-blue-50/60 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 hover:bg-blue-100 transition text-xs font-semibold"
              >
                <span class="flex items-center gap-2">
                  <Linkedin class="w-4 h-4" />
                  <span>LinkedIn Profile</span>
                </span>
                <ExternalLink class="w-3.5 h-3.5" />
              </a>

              <a 
                v-if="profile.github_url" 
                :href="profile.github_url" 
                target="_blank" 
                rel="noopener noreferrer"
                class="flex items-center justify-between p-3 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition text-xs font-semibold"
              >
                <span class="flex items-center gap-2">
                  <Github class="w-4 h-4" />
                  <span>GitHub Repositories</span>
                </span>
                <ExternalLink class="w-3.5 h-3.5" />
              </a>

              <a 
                v-if="profile.portfolio_url" 
                :href="profile.portfolio_url" 
                target="_blank" 
                rel="noopener noreferrer"
                class="flex items-center justify-between p-3 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 transition text-xs font-semibold"
              >
                <span class="flex items-center gap-2">
                  <Globe class="w-4 h-4" />
                  <span>Personal Portfolio</span>
                </span>
                <ExternalLink class="w-3.5 h-3.5" />
              </a>
            </div>
          </div>

        </div>

      </div>

      <!-- Candidate workspace navigation -->
      <section class="space-y-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-400">Candidate workspace</p>
            <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Your career dashboard</h2>
          </div>
          <button type="button" @click="loadCandidateWorkspace" :disabled="dashboardLoading" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 transition hover:border-blue-200 hover:text-blue-600 disabled:opacity-60 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200">
            <MoreHorizontal class="h-4 w-4" />
            {{ dashboardLoading ? 'Refreshing...' : 'Refresh workspace' }}
          </button>
        </div>

        <div class="flex gap-2 overflow-x-auto border-b border-slate-200/80 pb-px dark:border-slate-800">
          <button v-for="tab in [
            { id: 'overview', label: 'Overview', icon: ClipboardList },
            { id: 'career', label: 'Education & experience', icon: GraduationCap },
            { id: 'skills', label: 'Skills', icon: Wrench },
            { id: 'documents', label: 'CV management', icon: FileText },
            { id: 'activity', label: 'Saved jobs & activity', icon: Bookmark },
          ]" :key="tab.id" type="button" @click="activeSection = tab.id" :class="activeSection === tab.id ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'" class="inline-flex shrink-0 items-center gap-2 border-b-2 px-1 pb-3 text-xs font-extrabold transition">
            <component :is="tab.icon" class="h-4 w-4" /> {{ tab.label }}
          </button>
        </div>

        <p v-if="dashboardError" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300">{{ dashboardError }}</p>

        <div v-if="activeSection === 'overview'" class="grid grid-cols-2 gap-4 lg:grid-cols-4">
          <button type="button" @click="activeSection = 'career'" class="group rounded-2xl border border-slate-200/80 bg-white p-5 text-left transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
            <GraduationCap class="mb-4 h-5 w-5 text-amber-500" /><p class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ education.length }}</p><p class="mt-1 text-xs font-semibold text-slate-500">Education records</p>
          </button>
          <button type="button" @click="activeSection = 'career'" class="group rounded-2xl border border-slate-200/80 bg-white p-5 text-left transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
            <Briefcase class="mb-4 h-5 w-5 text-blue-500" /><p class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ experience.length }}</p><p class="mt-1 text-xs font-semibold text-slate-500">Work experiences</p>
          </button>
          <button type="button" @click="activeSection = 'documents'" class="group rounded-2xl border border-slate-200/80 bg-white p-5 text-left transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
            <FileText class="mb-4 h-5 w-5 text-red-500" /><p class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ cvDocuments.length }}</p><p class="mt-1 text-xs font-semibold text-slate-500">CV documents</p>
          </button>
          <button type="button" @click="activeSection = 'activity'" class="group rounded-2xl border border-slate-200/80 bg-white p-5 text-left transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
            <ClipboardList class="mb-4 h-5 w-5 text-emerald-500" /><p class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ applications.length }}</p><p class="mt-1 text-xs font-semibold text-slate-500">Applications sent</p>
          </button>
        </div>

        <div v-if="activeSection === 'career'" class="grid grid-cols-1 gap-5 lg:grid-cols-2">
          <div class="rounded-3xl border border-slate-200/80 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-5 flex items-center justify-between"><h3 class="flex items-center gap-2 text-base font-extrabold text-slate-900 dark:text-white"><GraduationCap class="h-5 w-5 text-amber-500" /> Education</h3><div class="flex items-center gap-2"><span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">{{ education.length }}</span><button type="button" @click="openCareerModal('education')" class="rounded-lg p-1.5 text-blue-600 transition hover:bg-blue-50 dark:hover:bg-blue-950/30" title="Add education"><span class="text-lg leading-none">+</span></button></div></div>
            <div v-if="education.length" class="space-y-4">
              <article v-for="item in education" :key="item.id" class="border-l-2 border-amber-200 pl-4 dark:border-amber-900/60"><div class="flex items-start justify-between gap-3"><div><p class="text-sm font-extrabold text-slate-900 dark:text-white">{{ item.degree || 'Education' }}<span v-if="item.field_of_study">, {{ item.field_of_study }}</span></p><p class="mt-1 text-xs font-semibold text-blue-600 dark:text-blue-400">{{ item.institution_name }}</p><p class="mt-1 text-[11px] text-slate-500">{{ formatPeriod(item.start_date, item.end_date, item.is_current) }}<span v-if="item.grade"> · Grade {{ item.grade }}</span></p></div><div class="flex shrink-0"><button type="button" @click="openCareerModal('education', item)" class="rounded-lg p-1.5 text-slate-400 hover:bg-blue-50 hover:text-blue-600" title="Edit education"><Edit3 class="h-3.5 w-3.5" /></button><button type="button" @click="deleteCareerItem('education', item)" class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600" title="Delete education"><Trash2 class="h-3.5 w-3.5" /></button></div></div></article>
            </div>
            <p v-else class="py-6 text-center text-xs text-slate-500">No education history added yet.</p>
          </div>
          <div class="rounded-3xl border border-slate-200/80 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-5 flex items-center justify-between"><h3 class="flex items-center gap-2 text-base font-extrabold text-slate-900 dark:text-white"><Briefcase class="h-5 w-5 text-blue-500" /> Experience</h3><div class="flex items-center gap-2"><span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">{{ experience.length }}</span><button type="button" @click="openCareerModal('experience')" class="rounded-lg p-1.5 text-blue-600 transition hover:bg-blue-50 dark:hover:bg-blue-950/30" title="Add experience"><span class="text-lg leading-none">+</span></button></div></div>
            <div v-if="experience.length" class="space-y-4">
              <article v-for="item in experience" :key="item.id" class="border-l-2 border-blue-200 pl-4 dark:border-blue-900/60"><div class="flex items-start justify-between gap-3"><div><p class="text-sm font-extrabold text-slate-900 dark:text-white">{{ item.job_title }}</p><p class="mt-1 text-xs font-semibold text-blue-600 dark:text-blue-400">{{ item.company_name }}<span v-if="item.location"> · {{ item.location }}</span></p><p class="mt-1 text-[11px] text-slate-500">{{ formatPeriod(item.start_date, item.end_date, item.is_current) }}</p><p v-if="item.description" class="mt-2 line-clamp-2 text-xs leading-relaxed text-slate-600 dark:text-slate-300">{{ item.description }}</p></div><div class="flex shrink-0"><button type="button" @click="openCareerModal('experience', item)" class="rounded-lg p-1.5 text-slate-400 hover:bg-blue-50 hover:text-blue-600" title="Edit experience"><Edit3 class="h-3.5 w-3.5" /></button><button type="button" @click="deleteCareerItem('experience', item)" class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600" title="Delete experience"><Trash2 class="h-3.5 w-3.5" /></button></div></div></article>
            </div>
            <p v-else class="py-6 text-center text-xs text-slate-500">No work experience added yet.</p>
          </div>
        </div>

        <div v-if="activeSection === 'skills'" class="rounded-3xl border border-slate-200/80 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
          <div class="mb-5 flex items-center justify-between"><div><h3 class="flex items-center gap-2 text-base font-extrabold text-slate-900 dark:text-white"><Wrench class="h-5 w-5 text-emerald-500" /> Professional skills</h3><p class="mt-1 text-xs text-slate-500">Skills recruiters can use to discover your profile.</p></div><div class="flex items-center gap-2"><span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">{{ skills.length }} skills</span><button type="button" @click="openSkillsModal" class="rounded-lg p-1.5 text-blue-600 transition hover:bg-blue-50 dark:hover:bg-blue-950/30" title="Edit skills"><Edit3 class="h-3.5 w-3.5" /></button></div></div>
          <div v-if="skills.length || customSkills.length" class="flex flex-wrap gap-2"><span v-for="skill in skills" :key="skill.id" class="inline-flex items-center gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300">{{ skill.name }}<span v-if="skill.category" class="text-[10px] font-semibold text-emerald-600/70 dark:text-emerald-400/70">{{ skill.category }}</span></span><span v-for="skill in customSkills" :key="`custom-${skill}`" class="inline-flex items-center gap-2 rounded-xl border border-amber-100 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300">{{ skill }}<span class="text-[10px] font-semibold opacity-70">Personal</span></span></div>
          <p v-else class="py-8 text-center text-xs text-slate-500">No skills added yet. Add skills from your profile settings.</p>
        </div>

        <div v-if="activeSection === 'documents'" class="rounded-3xl border border-slate-200/80 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
          <div class="mb-5 flex items-center justify-between gap-4"><div><h3 class="flex items-center gap-2 text-base font-extrabold text-slate-900 dark:text-white"><FileText class="h-5 w-5 text-red-500" /> CV management</h3><p class="mt-1 text-xs text-slate-500">Keep multiple tailored resumes ready for different roles.</p></div><label class="inline-flex shrink-0 cursor-pointer items-center gap-2 rounded-xl bg-blue-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-blue-700"><Upload class="h-4 w-4" />{{ isCvUploading ? 'Uploading...' : 'Upload CV' }}<input ref="cvUploadInput" type="file" accept=".pdf,.doc,.docx" class="hidden" :disabled="isCvUploading" @change="handleFileUpload" /></label></div>
          <div v-if="cvDocuments.length" class="grid gap-3 sm:grid-cols-2"><article v-for="cv in cvDocuments" :key="cv.id" class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/50"><div class="flex min-w-0 items-center gap-3"><div class="rounded-xl bg-red-50 p-2.5 text-red-600 dark:bg-red-950/40"><FileText class="h-5 w-5" /></div><div class="min-w-0"><p class="truncate text-xs font-extrabold text-slate-900 dark:text-white">{{ cv.title || 'Untitled CV' }}</p><p class="mt-1 text-[11px] text-slate-500">{{ cv.is_primary ? 'Primary CV · ' : '' }}{{ formatDate(cv.updated_at || cv.created_at) }}</p></div></div><div class="flex items-center gap-1"><a v-if="cv.file_path" :href="cv.file_path" target="_blank" rel="noopener noreferrer" class="rounded-lg p-2 text-slate-500 transition hover:bg-white hover:text-blue-600 dark:hover:bg-slate-900" title="Open CV"><Download class="h-4 w-4" /></a><button type="button" @click="deleteCv(cv)" class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/30" title="Delete CV"><Trash2 class="h-4 w-4" /></button></div></article></div>
          <p v-else class="rounded-2xl border-2 border-dashed border-slate-200 py-8 text-center text-xs text-slate-500 dark:border-slate-800">No CV documents yet. Upload a PDF or Word resume to apply faster.</p>
        </div>

        <div v-if="activeSection === 'activity'" class="grid grid-cols-1 gap-5 lg:grid-cols-3">
          <div class="rounded-3xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><h3 class="mb-4 flex items-center gap-2 text-sm font-extrabold text-slate-900 dark:text-white"><Bookmark class="h-4 w-4 text-amber-500" /> Saved jobs <span class="ml-auto text-[11px] text-slate-500">{{ savedJobs.length }}</span></h3><div v-if="savedJobs.length" class="space-y-3"><article v-for="item in savedJobs.slice(0, 5)" :key="item.id" class="flex items-start justify-between gap-2 border-b border-slate-100 pb-3 last:border-0 dark:border-slate-800"><div class="min-w-0"><p class="truncate text-xs font-bold text-slate-900 dark:text-white">{{ jobTitle(item) }}</p><p class="mt-1 truncate text-[11px] text-slate-500">{{ companyName(item) }}</p></div><button type="button" @click="removeSavedJob(item)" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/30" title="Remove saved job"><Trash2 class="h-3.5 w-3.5" /></button></article></div><p v-else class="py-5 text-center text-xs text-slate-500">No saved jobs.</p></div>
          <div class="rounded-3xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><h3 class="mb-4 flex items-center gap-2 text-sm font-extrabold text-slate-900 dark:text-white"><ClipboardList class="h-4 w-4 text-blue-500" /> Applications <span class="ml-auto text-[11px] text-slate-500">{{ applications.length }}</span></h3><div v-if="applications.length" class="space-y-3"><article v-for="item in applications.slice(0, 5)" :key="item.id" class="border-b border-slate-100 pb-3 last:border-0 dark:border-slate-800"><div class="flex items-start justify-between gap-2"><div class="min-w-0"><p class="truncate text-xs font-bold text-slate-900 dark:text-white">{{ jobTitle(item) }}</p><p class="mt-1 truncate text-[11px] text-slate-500">{{ companyName(item) }}</p></div><span class="shrink-0 rounded-full bg-blue-50 px-2 py-1 text-[10px] font-bold capitalize text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">{{ formatStatus(item.status) }}</span></div><button v-if="!['withdrawn', 'rejected', 'hired'].includes(item.status)" type="button" @click="withdrawApplication(item)" class="mt-2 text-[10px] font-bold text-slate-500 hover:text-red-600">Withdraw application</button></article></div><p v-else class="py-5 text-center text-xs text-slate-500">No applications yet.</p></div>
          <div class="rounded-3xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><h3 class="mb-4 flex items-center gap-2 text-sm font-extrabold text-slate-900 dark:text-white"><Video class="h-4 w-4 text-violet-500" /> Interviews <span class="ml-auto text-[11px] text-slate-500">{{ interviews.length }}</span></h3><div v-if="interviews.length" class="space-y-3"><article v-for="item in interviews.slice(0, 5)" :key="item.id" class="border-b border-slate-100 pb-3 last:border-0 dark:border-slate-800"><p class="text-xs font-bold text-slate-900 dark:text-white">{{ item.title || jobTitle(item) }}</p><p class="mt-1 text-[11px] font-semibold text-violet-600 dark:text-violet-300">{{ formatDate(item.scheduled_at) }} · {{ item.duration_minutes || 30 }} min</p><p v-if="item.location" class="mt-1 flex items-center gap-1 text-[11px] text-slate-500"><MapPinned class="h-3 w-3" />{{ item.location }}</p><a v-if="item.meeting_link" :href="item.meeting_link" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 hover:underline">Join meeting <ExternalLink class="h-3 w-3" /></a></article></div><p v-else class="py-5 text-center text-xs text-slate-500">No interviews scheduled.</p></div>
        </div>
      </section>

    </main>

    <!-- Education and experience editor -->
    <div v-if="showCareerModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-xs">
      <div class="w-full max-w-2xl space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900 sm:p-8">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800"><div><p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-blue-600">Candidate workspace</p><h3 class="mt-1 text-lg font-extrabold text-slate-900 dark:text-white">{{ editingCareerId ? 'Edit' : 'Add' }} {{ careerType === 'education' ? 'education' : 'experience' }}</h3></div><button type="button" @click="closeCareerModal" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800"><X class="h-5 w-5" /></button></div>
        <form @submit.prevent="saveCareerItem" class="grid grid-cols-1 gap-4 text-xs sm:grid-cols-2">
          <div v-if="careerFormError" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 font-semibold text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300 sm:col-span-2">{{ careerFormError }}</div>
          <template v-if="careerType === 'education'">
            <label class="space-y-1"><span class="font-bold text-slate-700 dark:text-slate-300">Institution name</span><input v-model="careerForm.institution_name" required type="text" class="form-input" /></label>
            <label class="space-y-1"><span class="font-bold text-slate-700 dark:text-slate-300">Degree</span><input v-model="careerForm.degree" required type="text" class="form-input" /></label>
            <label class="space-y-1"><span class="font-bold text-slate-700 dark:text-slate-300">Field of study</span><input v-model="careerForm.field_of_study" type="text" class="form-input" /></label>
            <label class="space-y-1"><span class="font-bold text-slate-700 dark:text-slate-300">Grade</span><input v-model="careerForm.grade" type="text" class="form-input" /></label>
            <label class="space-y-1"><span class="font-bold text-slate-700 dark:text-slate-300">Start date</span><input v-model="careerForm.start_date" required type="date" class="form-input" /></label>
            <label class="space-y-1"><span class="font-bold text-slate-700 dark:text-slate-300">End date</span><input v-model="careerForm.end_date" :disabled="careerForm.is_current" type="date" class="form-input disabled:opacity-50" /></label>
            <label class="flex items-center gap-2 font-bold text-slate-700 dark:text-slate-300 sm:col-span-2"><input v-model="careerForm.is_current" type="checkbox" class="h-4 w-4 rounded text-blue-600" /> I am currently studying here</label>
            <label class="space-y-1 sm:col-span-2"><span class="font-bold text-slate-700 dark:text-slate-300">Description</span><textarea v-model="careerForm.description" rows="3" class="form-input resize-none"></textarea></label>
          </template>
          <template v-else>
            <label class="space-y-1"><span class="font-bold text-slate-700 dark:text-slate-300">Job title</span><input v-model="careerForm.job_title" required type="text" class="form-input" /></label>
            <label class="space-y-1"><span class="font-bold text-slate-700 dark:text-slate-300">Company name</span><input v-model="careerForm.company_name" required type="text" class="form-input" /></label>
            <label class="space-y-1 sm:col-span-2"><span class="font-bold text-slate-700 dark:text-slate-300">Location</span><input v-model="careerForm.location" type="text" class="form-input" /></label>
            <label class="space-y-1"><span class="font-bold text-slate-700 dark:text-slate-300">Start date</span><input v-model="careerForm.start_date" required type="date" class="form-input" /></label>
            <label class="space-y-1"><span class="font-bold text-slate-700 dark:text-slate-300">End date</span><input v-model="careerForm.end_date" :disabled="careerForm.is_current" type="date" class="form-input disabled:opacity-50" /></label>
            <label class="flex items-center gap-2 font-bold text-slate-700 dark:text-slate-300 sm:col-span-2"><input v-model="careerForm.is_current" type="checkbox" class="h-4 w-4 rounded text-blue-600" /> I currently work here</label>
            <label class="space-y-1 sm:col-span-2"><span class="font-bold text-slate-700 dark:text-slate-300">Description</span><textarea v-model="careerForm.description" rows="3" class="form-input resize-none"></textarea></label>
          </template>
          <div class="flex justify-end gap-3 border-t border-slate-100 pt-4 dark:border-slate-800 sm:col-span-2"><button type="button" @click="closeCareerModal" class="rounded-xl bg-slate-100 px-4 py-2.5 font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-200">Cancel</button><button type="submit" :disabled="isCareerSaving" class="rounded-xl bg-blue-600 px-5 py-2.5 font-bold text-white shadow-md shadow-blue-500/20 disabled:opacity-60">{{ isCareerSaving ? 'Saving...' : 'Save record' }}</button></div>
        </form>
      </div>
    </div>

    <!-- Skills editor -->
    <div v-if="showSkillsModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-xs">
      <div class="w-full max-w-2xl space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900 sm:p-8">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800"><div><p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-emerald-600">Profile skills</p><h3 class="mt-1 text-lg font-extrabold text-slate-900 dark:text-white">Choose your skills</h3></div><button type="button" @click="showSkillsModal = false" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800"><X class="h-5 w-5" /></button></div>
        <input v-model="skillSearch" @input="loadSkillCatalog" type="search" placeholder="Search skills or categories" class="form-input" />
        <div v-if="careerFormError" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300">{{ careerFormError }}</div>
        <div class="space-y-3"><p class="text-xs font-extrabold text-slate-700 dark:text-slate-300">Platform skills</p><div class="grid max-h-64 grid-cols-1 gap-2 overflow-y-auto sm:grid-cols-2"><button v-for="skill in skillCatalog" :key="skill.id" type="button" @click="toggleSkill(skill)" :class="skills.some((item) => item.id === skill.id) ? 'border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300' : 'border-slate-200 text-slate-700 dark:border-slate-700 dark:text-slate-200'" class="flex items-center justify-between rounded-xl border px-3 py-2.5 text-left text-xs font-bold transition"><span>{{ skill.name }}<small v-if="skill.category" class="ml-1 font-medium opacity-60">{{ skill.category }}</small></span><Check v-if="skills.some((item) => item.id === skill.id)" class="h-4 w-4" /></button></div></div>
        <div class="space-y-3 rounded-2xl border border-amber-100 bg-amber-50/60 p-4 dark:border-amber-900/50 dark:bg-amber-950/20"><p class="text-xs font-extrabold text-slate-800 dark:text-slate-200">Add your own skill</p><div class="flex gap-2"><input v-model="customSkillName" @keyup.enter="addCustomSkill" type="text" maxlength="100" placeholder="Example: Khmer copywriting" class="form-input bg-white dark:bg-slate-900" /><button type="button" @click="addCustomSkill" :disabled="isCustomSkillSaving || !customSkillName.trim()" class="shrink-0 rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-white disabled:opacity-50">{{ isCustomSkillSaving ? 'Adding...' : 'Add' }}</button></div><div v-if="customSkills.length" class="flex flex-wrap gap-2"><button v-for="skill in customSkills" :key="`remove-${skill}`" type="button" @click="removeCustomSkill(skill)" class="inline-flex items-center gap-1 rounded-lg bg-white px-2.5 py-1.5 text-[11px] font-bold text-amber-800 shadow-sm dark:bg-slate-900 dark:text-amber-300" title="Remove custom skill">{{ skill }} <X class="h-3 w-3" /></button></div></div>
        <div class="flex justify-end gap-3 border-t border-slate-100 pt-4 dark:border-slate-800"><button type="button" @click="showSkillsModal = false" class="rounded-xl bg-slate-100 px-4 py-2.5 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-200">Cancel</button><button type="button" @click="saveSkills" :disabled="isSkillsSaving" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-500/20 disabled:opacity-60">{{ isSkillsSaving ? 'Saving...' : `Save ${skills.length + customSkills.length} skills` }}</button></div>
      </div>
    </div>

    <!-- ─── Edit Profile Modal Drawer ─────────────────────────────────── -->
    <div v-if="isEditing" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 space-y-6 shadow-2xl">
        
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
          <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Edit Candidate Profile</h3>
          <button @click="closeEditModal" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><X class="w-5 h-5" /></button>
        </div>

        <form @submit.prevent="saveProfile" class="space-y-4 text-xs">

          <div v-if="saveError" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 font-semibold text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300">
            {{ saveError }}
          </div>
          
          <!-- Image Upload Action in Modal -->
          <div class="p-4 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-2xl space-y-3">
            <h4 class="font-bold text-slate-900 dark:text-white">Profile Photo</h4>
            <div class="flex items-center gap-3">
              <button 
                type="button" 
                @click="triggerAvatarUpload" 
                class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl font-bold hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center gap-2"
              >
                <Camera class="w-4 h-4 text-blue-600" />
                <span>Upload New Profile Photo</span>
              </button>
            </div>
          </div>

          <!-- Headline -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1">
              <label for="profile-name" class="font-bold text-slate-700 dark:text-slate-300">Full Name</label>
              <input id="profile-name" v-model="editForm.user_name" type="text" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
            <div class="space-y-1">
              <label for="profile-email" class="font-bold text-slate-700 dark:text-slate-300">Email</label>
              <input id="profile-email" v-model="editForm.email" type="email" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
          </div>

          <!-- Headline -->
          <div class="space-y-1">
            <label for="profile-headline" class="font-bold text-slate-700 dark:text-slate-300">Professional Headline</label>
            <input 
              id="profile-headline"
              v-model="editForm.headline" 
              type="text" 
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" 
            />
          </div>

          <!-- Bio -->
          <div class="space-y-1">
            <label for="profile-bio" class="font-bold text-slate-700 dark:text-slate-300">Bio (About Me)</label>
            <textarea 
              id="profile-bio"
              v-model="editForm.bio" 
              rows="4" 
              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 resize-none" 
            ></textarea>
          </div>

          <!-- Phone & DOB -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1">
              <label for="profile-phone" class="font-bold text-slate-700 dark:text-slate-300">Phone Number</label>
              <input id="profile-phone" v-model="editForm.phone" type="text" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
            <div class="space-y-1">
              <label for="profile-date-of-birth" class="font-bold text-slate-700 dark:text-slate-300">Date of Birth</label>
              <input id="profile-date-of-birth" v-model="editForm.date_of_birth" type="date" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
          </div>

          <!-- Gender & Nationality -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1">
              <label for="profile-gender" class="font-bold text-slate-700 dark:text-slate-300">Gender</label>
              <select id="profile-gender" v-model="editForm.gender" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100">
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
                <option value="prefer_not_to_say">Prefer not to say</option>
              </select>
            </div>
            <div class="space-y-1">
              <label for="profile-nationality" class="font-bold text-slate-700 dark:text-slate-300">Nationality</label>
              <input id="profile-nationality" v-model="editForm.nationality" type="text" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
          </div>

          <!-- Location (Address, City, Country) -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="space-y-1">
              <label for="profile-address" class="font-bold text-slate-700 dark:text-slate-300">Address</label>
              <input id="profile-address" v-model="editForm.address" type="text" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
            <div class="space-y-1">
              <label for="profile-city" class="font-bold text-slate-700 dark:text-slate-300">City</label>
              <input id="profile-city" v-model="editForm.city" type="text" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
            <div class="space-y-1">
              <label for="profile-country" class="font-bold text-slate-700 dark:text-slate-300">Country</label>
              <input id="profile-country" v-model="editForm.country" type="text" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
          </div>

          <!-- Availability & Salary -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="space-y-1">
              <label for="profile-availability" class="font-bold text-slate-700 dark:text-slate-300">Availability</label>
              <select id="profile-availability" v-model="editForm.availability" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100">
                <option value="immediately">Immediately</option>
                <option value="within_1_month">Within 1 Month</option>
                <option value="within_3_months">Within 3 Months</option>
                <option value="not_available">Not Available</option>
              </select>
            </div>
            <div class="space-y-1">
              <label for="profile-min-salary" class="font-bold text-slate-700 dark:text-slate-300">Min Salary (USD)</label>
              <input id="profile-min-salary" v-model="editForm.expected_salary_min" type="number" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
            <div class="space-y-1">
              <label for="profile-max-salary" class="font-bold text-slate-700 dark:text-slate-300">Max Salary (USD)</label>
              <input id="profile-max-salary" v-model="editForm.expected_salary_max" type="number" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
          </div>

          <!-- Social Links -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="space-y-1">
              <label for="profile-linkedin" class="font-bold text-slate-700 dark:text-slate-300">LinkedIn URL</label>
              <input id="profile-linkedin" v-model="editForm.linkedin_url" type="url" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
            <div class="space-y-1">
              <label for="profile-github" class="font-bold text-slate-700 dark:text-slate-300">GitHub URL</label>
              <input id="profile-github" v-model="editForm.github_url" type="url" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
            <div class="space-y-1">
              <label for="profile-portfolio" class="font-bold text-slate-700 dark:text-slate-300">Portfolio URL</label>
              <input id="profile-portfolio" v-model="editForm.portfolio_url" type="url" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100" />
            </div>
          </div>

          <!-- Toggles: Open to Work & Profile Visibility -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input v-model="editForm.is_open_to_work" type="checkbox" class="w-4 h-4 text-blue-600 rounded" />
              <span class="font-bold text-slate-700 dark:text-slate-300">Open to Work (Show badge to recruiters)</span>
            </label>

            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input v-model="editForm.is_profile_visible" type="checkbox" class="w-4 h-4 text-blue-600 rounded" />
              <span class="font-bold text-slate-700 dark:text-slate-300">Visible to HR & Employer Search</span>
            </label>
          </div>

          <!-- Modal Actions -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <button type="button" @click="closeEditModal" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 rounded-xl text-slate-700 dark:text-slate-300 font-bold">
              Cancel
            </button>
            <button type="submit" :disabled="isSaving" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md shadow-blue-500/20">
              {{ isSaving ? 'Saving...' : 'Save Profile Changes' }}
            </button>
          </div>

        </form>

      </div>
    </div>

    <Footer />
  </div>
</template>
