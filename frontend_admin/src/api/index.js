import http from './http'

// Admin API methods are grouped by backend resource.

export const adminApi = {
  getMe() {
    return http.get('/user')
  },

  login(email, password) {
    return http.post('/auth/login', { email, password })
  },

  getJobPosts(params = {}) {
    return http.get('/admin/job-posts', { params })
  },

  showJobPost(id) {
    return http.get(`/admin/job-posts/${id}`)
  },

  updateJobPost(id, data) {
    return http.put(`/admin/job-posts/${id}`, data)
  },

  deleteJobPost(id) {
    return http.delete(`/admin/job-posts/${id}`)
  },

  toggleFeaturedJobPost(id) {
    return http.post(`/admin/job-posts/${id}/toggle-featured`)
  },

  restoreJobPost(id) {
    return http.post(`/admin/job-posts/${id}/restore`)
  },

  getCandidates(params = {}) {
    return this.getUsers({ role: 'user', ...params })
  },

  getUsers(params = {}) {
    return http.get('/admin/users', { params })
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

  getApplications(params = {}) {
    return http.get('/admin/applications', { params })
  },

  getCompanies(params = {}) {
    return http.get('/admin/companies', { params })
  },

  storeCompany(data) {
    return http.post('/admin/companies', data)
  },

  getJobCategories(params = {}) {
    return http.get('/admin/job-categories', { params })
  },

  getSkills(params = {}) {
    return http.get('/admin/skills', { params })
  },

  storeJobPost(data) {
    return http.post('/admin/job-posts', data)
  },

  getReports() { return http.get('/admin/reports') },
  getSettings() { return http.get('/admin/settings') },
  updateSettings(data) { return http.put('/admin/settings', data) },

  storeUser(data) { return http.post('/admin/users', data) },
  showCompany(id) { return http.get(`/admin/companies/${id}`) },
  updateCompany(id, data) { return http.put(`/admin/companies/${id}`, data) },
  updateCompanyStatus(id, status) { return http.patch(`/admin/companies/${id}/status`, { status }) },
  deleteCompany(id) { return http.delete(`/admin/companies/${id}`) },

  storeJobCategory(data) { return http.post('/admin/job-categories', data) },
  showJobCategory(id) { return http.get(`/admin/job-categories/${id}`) },
  updateJobCategory(id, data) { return http.put(`/admin/job-categories/${id}`, data) },
  deleteJobCategory(id) { return http.delete(`/admin/job-categories/${id}`) },
  toggleJobCategory(id) { return http.patch(`/admin/job-categories/${id}/toggle-active`) },
  getJobCategoryTree() { return http.get('/admin/job-categories/tree') },
  reorderJobCategories(items) { return http.post('/admin/job-categories/reorder', { items }) },

  forceDeleteJobPost(id) { return http.delete(`/admin/job-posts/${id}/force-delete`) },

  storeSkill(data) { return http.post('/admin/skills', data) },
  showSkill(id) { return http.get(`/admin/skills/${id}`) },
  updateSkill(id, data) { return http.put(`/admin/skills/${id}`, data) },
  deleteSkill(id) { return http.delete(`/admin/skills/${id}`) },

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

  showApplication(id) { return http.get(`/admin/applications/${id}`) },
  getInterviews(params = {}) { return http.get('/admin/interviews', { params }) },
  storeInterview(data) { return http.post('/admin/interviews', data) },
  showInterview(id) { return http.get(`/admin/interviews/${id}`) },
  updateInterview(id, data) { return http.put(`/admin/interviews/${id}`, data) },
  deleteInterview(id) { return http.delete(`/admin/interviews/${id}`) },
  cancelInterview(id) { return http.patch(`/admin/interviews/${id}/cancel`) },

  // Notifications
  getNotifications() { return http.get('/admin/notifications') },
  markNotificationAsRead(id) { return http.post(`/admin/notifications/${id}/read`) },
  markAllNotificationsAsRead() { return http.post('/admin/notifications/read-all') },
  deleteNotification(id) { return http.delete(`/admin/notifications/${id}`) },

  // Security & Audit
  getSecurityOverview() { return http.get('/admin/security/overview') },
  getSecurityLogs() { return http.get('/admin/security/logs') },
  revokeOtherTokens() { return http.post('/admin/security/revoke-other-tokens') },
}
