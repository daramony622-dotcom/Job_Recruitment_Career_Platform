import { useAuth } from './useAuth'

const unwrap = (response) => response?.data ?? response

export function useJobSeekerApi() {
  const { request } = useAuth()
  const api = (path, options = {}) => request(`/user${path}`, options)

  return {
    getProfile: () => api('/profile').then(unwrap),
    updateAvatar: (file) => {
      const formData = new FormData()
      formData.append('avatar', file)
      return api('/profile/avatar', { method: 'POST', body: formData }).then(unwrap)
    },
    updateProfile: (payload) => api('/profile', {
      method: 'PUT',
      body: payload,
    }).then(unwrap),

    listEducation: () => api('/education'),
    createEducation: (payload) => api('/education', { method: 'POST', body: payload }),
    updateEducation: (id, payload) => api(`/education/${id}`, { method: 'PUT', body: payload }),
    deleteEducation: (id) => api(`/education/${id}`, { method: 'DELETE' }),

    listExperience: () => api('/experience'),
    createExperience: (payload) => api('/experience', { method: 'POST', body: payload }),
    updateExperience: (id, payload) => api(`/experience/${id}`, { method: 'PUT', body: payload }),
    deleteExperience: (id) => api(`/experience/${id}`, { method: 'DELETE' }),

    listSkills: (search = '') => api(`/skills${search ? `?search=${encodeURIComponent(search)}` : ''}`),
    getMySkills: () => api('/skills/mine'),
    updateMySkills: (skillIds) => api('/skills/mine', {
      method: 'PUT',
      body: { skill_ids: skillIds },
    }),
    addCustomSkill: (name) => api('/skills/custom', {
      method: 'POST',
      body: { name },
    }),
    removeCustomSkill: (name) => api(`/skills/custom/${encodeURIComponent(name)}`, { method: 'DELETE' }),

    listCvs: () => api('/cv'),
    getCv: (id) => api(`/cv/${id}`),
    createCv: (formData) => api('/cv', { method: 'POST', body: formData }),
    updateCv: (id, formData) => api(`/cv/${id}`, { method: 'POST', body: formData }),
    deleteCv: (id) => api(`/cv/${id}`, { method: 'DELETE' }),

    searchJobs: (params = {}) => {
      const query = new URLSearchParams(Object.entries(params).filter(([, value]) => value !== '' && value !== null && value !== undefined))
      return api(`/jobs/search${query.toString() ? `?${query}` : ''}`)
    },

    // Job details are public and live outside the authenticated /user route group.
    getJob: (id) => request(`/jobs/${encodeURIComponent(id)}`).then(unwrap),

    listSavedJobs: () => api('/saved-jobs'),
    saveJob: (jobPostId, notes = null) => api('/saved-jobs', {
      method: 'POST',
      body: { job_post_id: jobPostId, notes },
    }),
    removeSavedJob: (id) => api(`/saved-jobs/${id}`, { method: 'DELETE' }),

    listApplications: (status = '') => api(`/applications${status ? `?status=${encodeURIComponent(status)}` : ''}`),
    getApplication: (id) => api(`/applications/${id}`),
    applyToJob: (jobPostId, coverLetter = '', cv = null) => {
      const formData = new FormData()
      formData.append('job_post_id', jobPostId)
      if (coverLetter) formData.append('cover_letter', coverLetter)
      if (cv) formData.append('cv', cv)
      return api('/applications', { method: 'POST', body: formData })
    },
    withdrawApplication: (id) => api(`/applications/${id}/withdraw`, { method: 'PATCH' }),

    listInterviews: (params = {}) => {
      const query = new URLSearchParams(Object.entries(params).filter(([, value]) => value !== '' && value !== null && value !== undefined))
      return api(`/interviews${query.toString() ? `?${query}` : ''}`)
    },
    getInterview: (id) => api(`/interviews/${id}`),

    listNotifications: (page = 1) => api(`/notifications?page=${page}`),
    markNotificationRead: (id) => api(`/notifications/${id}/read`, { method: 'PATCH' }),
    markAllNotificationsRead: () => api('/notifications/read-all', { method: 'POST' }),
    deleteNotification: (id) => api(`/notifications/${id}`, { method: 'DELETE' }),

    unwrap,
  }
}