import http from './http'

/**
 * Dynamically check if the logged-in user has HR or Company roles.
 * Reads fallback keys to prevent null pointer routing errors.
 */
const isCompanyScopedRole = () => {
  try {
    const rawUser = localStorage.getItem('admin_user') || localStorage.getItem('user')
    const user = JSON.parse(rawUser || 'null')
    return ['hr', 'company'].includes(user?.role)
  } catch {
    return false
  }
}

const jobPostPrefix = () => (isCompanyScopedRole() ? '/company/job-posts' : '/admin/job-posts')
const lookupPrefix = () => (isCompanyScopedRole() ? '/company' : '/admin')

export const adminApi = {
  getMe() {
    return http.get('/user')
  },

  // Job Posts
  getJobPosts(params = {}) {
    return http.get(jobPostPrefix(), { params })
  },

  showJobPost(id) {
    return http.get(`${jobPostPrefix()}/${id}`)
  },

  storeJobPost(data) {
    return http.post(jobPostPrefix(), data)
  },

  updateJobPost(id, data) {
    return http.put(`${jobPostPrefix()}/${id}`, data)
  },

  deleteJobPost(id) {
    return http.delete(`${jobPostPrefix()}/${id}`)
  },

  toggleFeaturedJobPost(id) {
    return http.post(`${jobPostPrefix()}/${id}/toggle-featured`)
  },

  restoreJobPost(id) {
    return http.post(`${jobPostPrefix()}/${id}/restore`)
  },

  // Candidates & Users
  getCandidates(params = {}) {
    return this.getUsers({ role: 'user', ...params })
  },

  getUsers(params = {}) {
    return http.get('/admin/users', { params })
  },

  storeUser(data) {
    return http.post('/admin/users', data)
  },

  showCandidate(id) {
    return http.get(`/admin/users/${id}`)
  },

  updateCandidate(id, data) {
    return http.put(`/admin/users/${id}`, data)
  },

  deleteUser(id) {
    return http.delete(`/admin/users/${id}`)
  },

  // Applications
  getApplications(params = {}) {
    return isCompanyScopedRole()
      ? http.get('/company/applicants', { params })
      : http.get('/admin/applications', { params })
  },

  showApplication(id) {
    return isCompanyScopedRole()
      ? http.get(`/company/applicants/${id}`)
      : http.get(`/admin/applications/${id}`)
  },

  // Companies
  getCompanyProfile() {
    return http.get('/company/profile')
  },

  storeCompanyProfile(data) {
    return http.post('/company/profile', data)
  },

  updateCompanyProfile(data) {
    return http.put('/company/profile', data)
  },

  deleteCompanyProfile() {
    return http.delete('/company/profile')
  },

  getCompanies(params = {}) {
    return isCompanyScopedRole()
      ? http.get('/company/profile')
      : http.get(`${lookupPrefix()}/companies`, { params })
  },

  showCompany(id) {
    return http.get(`/admin/companies/${id}`)
  },

  storeCompany(data) {
    if (data instanceof FormData) {
      return http.post('/admin/companies', data, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    }
    return http.post('/admin/companies', data)
  },

  updateCompany(id, data) {
    if (data instanceof FormData) {
      if (!data.has('_method')) {
        data.append('_method', 'PUT')
      }
      return http.post(`/admin/companies/${id}`, data, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    }
    return http.put(`/admin/companies/${id}`, data)
  },

  updateCompanyStatus(id, status) {
    return http.patch(`/admin/companies/${id}/status`, { status })
  },

  deleteCompany(id) {
    return http.delete(`/admin/companies/${id}`)
  },

  // Categories & Skills
  getJobCategories(params = {}) {
    return http.get(`${lookupPrefix()}/job-categories`, { params })
  },

  getSkills(params = {}) {
    return http.get(`${lookupPrefix()}/skills`, { params })
  },

  // Interviews
  getInterviews(params = {}) {
    return isCompanyScopedRole()
      ? http.get('/company/interviews', { params })
      : http.get('/admin/interviews', { params })
  },

  storeInterview(data) {
    return isCompanyScopedRole()
      ? http.post('/company/interviews', data)
      : http.post('/admin/interviews', data)
  },

  showInterview(id) {
    return isCompanyScopedRole()
      ? http.get(`/company/interviews/${id}`)
      : http.get(`/admin/interviews/${id}`)
  },

  updateInterview(id, data) {
    return isCompanyScopedRole()
      ? http.put(`/company/interviews/${id}`, data)
      : http.put(`/admin/interviews/${id}`, data)
  },

  deleteInterview(id) {
    return isCompanyScopedRole()
      ? http.delete(`/company/interviews/${id}`)
      : http.delete(`/admin/interviews/${id}`)
  },

  cancelInterview(id) {
    return isCompanyScopedRole()
      ? http.patch(`/company/interviews/${id}/cancel`)
      : http.patch(`/admin/interviews/${id}/cancel`)
  },

  // Reports & Settings
  getReports() {
    return isCompanyScopedRole()
      ? http.get('/company/reports')
      : http.get('/admin/reports')
  },

  getSettings() { return http.get('/admin/settings') },
  updateSettings(data) { return http.put('/admin/settings', data) },

  // Admin Job Categories Management
  storeJobCategory(data) {
    return isCompanyScopedRole()
      ? http.post('/company/job-categories', data)
      : http.post('/admin/job-categories', data)
  },
  showJobCategory(id) {
    return isCompanyScopedRole()
      ? http.get(`/company/job-categories/${id}`)
      : http.get(`/admin/job-categories/${id}`)
  },
  updateJobCategory(id, data) {
    return isCompanyScopedRole()
      ? http.put(`/company/job-categories/${id}`, data)
      : http.put(`/admin/job-categories/${id}`, data)
  },
  deleteJobCategory(id) {
    return isCompanyScopedRole()
      ? http.delete(`/company/job-categories/${id}`)
      : http.delete(`/admin/job-categories/${id}`)
  },
  toggleJobCategory(id) {
    return isCompanyScopedRole()
      ? http.patch(`/company/job-categories/${id}/toggle-active`)
      : http.patch(`/admin/job-categories/${id}/toggle-active`)
  },
  getJobCategoryTree() {
    return isCompanyScopedRole()
      ? http.get('/company/job-categories/tree')
      : http.get('/admin/job-categories/tree')
  },
  reorderJobCategories(items) {
    return isCompanyScopedRole()
      ? http.post('/company/job-categories/reorder', { items })
      : http.post('/admin/job-categories/reorder', { items })
  },

  // Admin Skills & Skill Categories Management
  storeSkill(data) {
    return isCompanyScopedRole()
      ? http.post('/company/skills', data)
      : http.post('/admin/skills', data)
  },
  showSkill(id) {
    return isCompanyScopedRole()
      ? http.get(`/company/skills/${id}`)
      : http.get(`/admin/skills/${id}`)
  },
  updateSkill(id, data) {
    return isCompanyScopedRole()
      ? http.put(`/company/skills/${id}`, data)
      : http.put(`/admin/skills/${id}`, data)
  },
  deleteSkill(id) {
    return isCompanyScopedRole()
      ? http.delete(`/company/skills/${id}`)
      : http.delete(`/admin/skills/${id}`)
  },
  getSkillCategories(params = {}) {
    return isCompanyScopedRole()
      ? http.get('/company/skill-categories', { params })
      : http.get('/admin/skill-categories', { params })
  },
  showSkillCategory(id) {
    return isCompanyScopedRole()
      ? http.get(`/company/skill-categories/${id}`)
      : http.get(`/admin/skill-categories/${id}`)
  },
  storeSkillCategory(data) {
    return isCompanyScopedRole()
      ? http.post('/company/skill-categories', data)
      : http.post('/admin/skill-categories', data)
  },
  updateSkillCategory(id, data) {
    return isCompanyScopedRole()
      ? http.put(`/company/skill-categories/${id}`, data)
      : http.put(`/admin/skill-categories/${id}`, data)
  },
  deleteSkillCategory(id) {
    return isCompanyScopedRole()
      ? http.delete(`/company/skill-categories/${id}`)
      : http.delete(`/admin/skill-categories/${id}`)
  },
  toggleSkillCategory(id) {
    return isCompanyScopedRole()
      ? http.patch(`/company/skill-categories/${id}/toggle-active`)
      : http.patch(`/admin/skill-categories/${id}/toggle-active`)
  },

  // Queue Jobs Management
  getJobs(params = {}) { return http.get('/admin/jobs', { params }) },
  storeJob(data) { return http.post('/admin/jobs', data) },
  showJob(id) { return http.get(`/admin/jobs/${id}`) },
  updateJob(id, data) { return http.put(`/admin/jobs/${id}`, data) },
  deleteJob(id) { return http.delete(`/admin/jobs/${id}`) },

  getJobBatches(params = {}) { return http.get('/admin/job-batches', { params }) },
  storeJobBatch(data) { return http.post('/admin/job-batches', data) },
  showJobBatch(id) { return http.get(`/admin/job-batches/${id}`) },
  updateJobBatch(id, data) { return http.put(`/admin/job-batches/${id}`, data) },
  deleteJobBatch(id) { return http.delete(`/admin/job-batches/${id}`) },

  getFailedJobs(params = {}) { return http.get('/admin/failed-jobs', { params }) },
  storeFailedJob(data) { return http.post('/admin/failed-jobs', data) },
  showFailedJob(id) { return http.get(`/admin/failed-jobs/${id}`) },
  updateFailedJob(id, data) { return http.put(`/admin/failed-jobs/${id}`, data) },
  deleteFailedJob(id) { return http.delete(`/admin/failed-jobs/${id}`) },
  retryFailedJob(id) { return http.post(`/admin/failed-jobs/${id}/retry`) },
  flushFailedJobs() { return http.delete('/admin/failed-jobs/flush') },
  forceDeleteJobPost(id) { return http.delete(`/admin/job-posts/${id}/force-delete`) },

  // Contact Messages & Notifications
  getContactMessages(params = {}) { return http.get('/admin/contact-messages', { params }) },
  showContactMessage(id) { return http.get(`/admin/contact-messages/${id}`) },
  deleteContactMessage(id) { return http.delete(`/admin/contact-messages/${id}`) },
  getNotifications() {
    return isCompanyScopedRole()
      ? http.get('/company/notifications')
      : http.get('/admin/notifications')
  },
  markNotificationAsRead(id) {
    return isCompanyScopedRole()
      ? http.post(`/company/notifications/${id}/read`)
      : http.post(`/admin/notifications/${id}/read`)
  },
  markAllNotificationsAsRead() {
    return isCompanyScopedRole()
      ? http.post('/company/notifications/read-all')
      : http.post('/admin/notifications/read-all')
  },
  deleteNotification(id) {
    return isCompanyScopedRole()
      ? http.delete(`/company/notifications/${id}`)
      : http.delete(`/admin/notifications/${id}`)
  },

  // Security & Audit
  getSecurityOverview() { return http.get('/admin/security/overview') },
  getSecurityLogs() { return http.get('/admin/security/logs') },
  revokeOtherTokens() { return http.post('/admin/security/revoke-other-tokens') },
}