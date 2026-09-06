import { computed, ref } from 'vue'

const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'
const TOKEN_KEY = 'job_platform_token'

const token = ref(localStorage.getItem(TOKEN_KEY))
const user = ref(null)
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

  const response = await fetch(`${API_URL}${path}`, {
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

export function useAuth() {
  return {
    API_URL,
    token,
    user,
    isAuthenticated,
    isLoading,
    error,
    request,
    fetchCurrentUser,
    login,
    logout,
  }
}
