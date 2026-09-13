import axios from 'axios'

const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    Accept: 'application/json',
    // Do NOT set default 'Content-Type': 'application/json' here so Axios 
    // can automatically handle FormData headers when uploading files.
  },
})

// Request Interceptor: Attach bearer token dynamically
http.interceptors.request.use(
  (config) => {
    const token =
      localStorage.getItem('admin_token') ||
      localStorage.getItem('job_platform_token') ||
      localStorage.getItem('auth_token') ||
      localStorage.getItem('token')

    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Response Interceptor: Global 401 Unauthorized handling
http.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      const requestUrl = error.config?.url || ''
      const isAuthEndpoint = /\/auth\/(login|register|verify-otp|resend-otp|forgot-password|reset-password)(?:$|\?)/.test(requestUrl)

      // Only purge storage and redirect if the 401 wasn't triggered by a login/auth attempt
      if (!isAuthEndpoint) {
        localStorage.removeItem('admin_token')
        localStorage.removeItem('job_platform_token')
        localStorage.removeItem('auth_token')
        localStorage.removeItem('admin_user')
        localStorage.removeItem('user')

        const userLoginUrl = import.meta.env.VITE_CLIENT_URL || 'http://localhost:5173'
        window.location.href = `${userLoginUrl.replace(/\/$/, '')}/login?redirect=admin`
      }
    }
    return Promise.reject(error)
  }
)

export default http