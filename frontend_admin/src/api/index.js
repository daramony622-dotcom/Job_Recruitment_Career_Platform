import http from './http'

export const adminApi = {
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
    return http.get('/admin/users', {
      params: { role: 'user', ...params },
    })
  },

  showCandidate(id) {
    return http.get(`/admin/users/${id}`)
  },

  updateCandidate(id, data) {
    return http.put(`/admin/users/${id}`, data)
  },

  getApplications(params = {}) {
    return http.get('/admin/applications', { params })
  },

  getCompanies(params = {}) {
    return http.get('/admin/companies', { params })
  },
}
