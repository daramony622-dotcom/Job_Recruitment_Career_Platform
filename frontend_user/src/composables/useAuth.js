import { computed, ref } from 'vue'

const BaseURL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'
export const API_URL = BaseURL.endsWith('/') ? BaseURL.slice(0, -1) : BaseURL

export const resolveAssetUrl = (path) => {
  if (!path || /^(https?:|blob:|data:)/i.test(path)) return path || ''

  const origin = API_URL.replace(/\/api\/?$/, '')
  return `${origin}/${path.replace(/^\/+/, '')}`
}

const TOKEN_KEY = 'job_platform_token'

const token = ref(localStorage.getItem(TOKEN_KEY))
const user = ref(null)
const profileAvatar = ref('')
const isLoading = ref(false)
const error = ref('')

const isAuthenticated = computed(() => Boolean(token.value))

const request = async (path, options = {}) => {
  const headers = new Headers(options.headers || {})
  headers.set('Accept', 'application/json')

  if (options.body && !(options.body instanceof FormData)) {
    headers.set('Content-Type', 'application/json')
  }

  if (token.value) {
    headers.set('Authorization', `Bearer ${token.value}`)
  }

  const response = await window.fetch(`${API_URL}${path}`, {
    ...options,
    headers,
  })

  const data = await response.json().catch(() => ({}))

  if (!response.ok) {
    const validationMessage = data.errors
      ? Object.values(data.errors).flat().join(' ')
      : ''
    throw new Error(validationMessage || data.message || 'The request failed.')
  }

  return data
}

const fetchCurrentUser = async () => {
  if (!token.value) {
    user.value = null
    return null
  }

  isLoading.value = true
  error.value = ''

  try {
    user.value = await request('/user')
    return user.value
  } catch (requestError) {
    error.value = requestError.message
    if (requestError.message.includes('Unauthenticated')) {
      logout()
    }
    return null
  } finally {
    isLoading.value = false
  }
}

const fetchProfileAvatar = async () => {
  if (!token.value) {
    profileAvatar.value = ''
    return ''
  }

  try {
    const profile = await request('/user/profile')
    profileAvatar.value = resolveAssetUrl(profile?.avatar)
    return profileAvatar.value
  } catch {
    return profileAvatar.value
  }
}

const setProfileAvatar = (avatar) => {
  profileAvatar.value = resolveAssetUrl(avatar)
}

const login = async (email, password) => {
  isLoading.value = true
  error.value = ''

  try {
    const data = await request('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    })

    token.value = data.token
    localStorage.setItem(TOKEN_KEY, data.token)
    user.value = data.user
    return user.value
  } catch (requestError) {
    error.value = requestError.message
    throw requestError
  } finally {
    isLoading.value = false
  }
}

const register = async (payload) => {
  isLoading.value = true
  error.value = ''

  try {
    const data = await request('/auth/register', {
      method: 'POST',
      body: JSON.stringify(payload),
    })

    // If backend returns token directly upon register
    if (data.token) {
      token.value = data.token
      localStorage.setItem(TOKEN_KEY, data.token)
      user.value = data.user
    }
    return data
  } catch (requestError) {
    error.value = requestError.message
    throw requestError
  } finally {
    isLoading.value = false
  }
}

const verifyOtp = async (email, code) => {
  isLoading.value = true
  error.value = ''

  try {
    const data = await request('/auth/verify-otp', {
      method: 'POST',
      body: JSON.stringify({ email, code }),
    })

    if (data.token) {
      token.value = data.token
      localStorage.setItem(TOKEN_KEY, data.token)
      user.value = data.user
    }
    return data
  } catch (requestError) {
    error.value = requestError.message
    throw requestError
  } finally {
    isLoading.value = false
  }
}

const loginWithGoogle = async () => {
  try {
    const data = await request('/auth/google?json=1')
    if (data.redirect_url) {
      window.location.href = data.redirect_url
    } else {
      window.location.href = `${API_URL}/auth/google`
    }
  } catch (err) {
    window.location.href = `${API_URL}/auth/google`
  }
}

const loginWithTelegram = async () => {
  window.location.href = `${API_URL}/auth/telegram`
}

const logout = async () => {
  try {
    if (token.value) {
      await request('/auth/logout', { method: 'POST' })
    }
  } catch {
    // Clear local authentication even when the server is unavailable.
  } finally {
    token.value = null
    user.value = null
    localStorage.removeItem(TOKEN_KEY)
  }
}

const handleOAuthCallback = () => {
  const urlParams = new URLSearchParams(window.location.search)
  const callbackToken = urlParams.get('token')
  const callbackError = urlParams.get('oauth_error')

  if (callbackError) {
    error.value = callbackError
    window.history.replaceState({}, document.title, window.location.pathname)
    return false
  }

  if (callbackToken) {
    token.value = callbackToken
    localStorage.setItem(TOKEN_KEY, callbackToken)
    // Clean up query string
    window.history.replaceState({}, document.title, window.location.pathname)
    return fetchCurrentUser()
  }

  return null
}

export function useAuth() {
  return {
    API_URL,
    token,
    user,
      profileAvatar,
    isAuthenticated,
    isLoading,
    error,
    request,
    fetchCurrentUser,
    fetchProfileAvatar,
    setProfileAvatar,
    login,
    register,
    verifyOtp,
    loginWithGoogle,
    loginWithTelegram,
    logout,
    handleOAuthCallback,
  }
}

