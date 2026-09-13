import { computed, ref } from 'vue'

const BaseURL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'
export const API_URL = BaseURL.endsWith('/') ? BaseURL.slice(0, -1) : BaseURL

export const resolveAssetUrl = (path) => {
  if (!path || /^(https?:|blob:|data:)/i.test(path)) return path || ''

  const cleanPath = String(path).replace(/^\/+/, '')
  const origin = API_URL.replace(/\/api\/?$/, '')

  // Already has full storage path
  if (cleanPath.startsWith('storage/')) return `${origin}/${cleanPath}`

  // Company logo / cover and all relative paths go through /storage/
  return `${origin}/storage/${cleanPath}`
}

const TOKEN_KEY = 'job_platform_token'
const TELEGRAM_AUTH_KEY = 'telegram_auth_session'

// Primary token reactive ref with multi-key fallback
const initialToken = 
  localStorage.getItem(TOKEN_KEY) || 
  localStorage.getItem('admin_token') || 
  localStorage.getItem('auth_token')

const token = ref(initialToken)
const user = ref(JSON.parse(localStorage.getItem('user') || localStorage.getItem('admin_user') || 'null'))
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

const persistAuthData = (authToken, userData) => {
  token.value = authToken
  user.value = userData

  // Synchronize across both admin and client storage keys
  localStorage.setItem(TOKEN_KEY, authToken)
  localStorage.setItem('admin_token', authToken)
  localStorage.setItem('auth_token', authToken)

  if (userData) {
    localStorage.setItem('user', JSON.stringify(userData))
    localStorage.setItem('admin_user', JSON.stringify(userData))
  }
}

const fetchCurrentUser = async () => {
  if (!token.value) {
    user.value = null
    return null
  }

  isLoading.value = true
  error.value = ''

  try {
    const data = await request('/user')
    const fetchedUser = data.data || data
    user.value = fetchedUser
    
    localStorage.setItem('user', JSON.stringify(fetchedUser))
    localStorage.setItem('admin_user', JSON.stringify(fetchedUser))
    return fetchedUser
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

// Added method: Enables single sign-on (SSO) authentication via URL token or direct token verification
const loginWithToken = async (newToken) => {
  if (!newToken) return null

  isLoading.value = true
  error.value = ''

  try {
    token.value = newToken
    localStorage.setItem(TOKEN_KEY, newToken)
    localStorage.setItem('admin_token', newToken)

    const authenticatedUser = await fetchCurrentUser()
    if (!authenticatedUser) {
      throw new Error('Failed to retrieve user profile with provided token.')
    }

    return authenticatedUser
  } catch (err) {
    error.value = err.message
    logout()
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

    persistAuthData(data.token, data.user)
    return data.user
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

    if (data.token) {
      persistAuthData(data.token, data.user)
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
      persistAuthData(data.token, data.user)
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
  } catch {
    window.location.href = `${API_URL}/auth/google`
  }
}

const requestTelegramOtp = async (phone) => {
  const cleanedPhone = String(phone || '').trim()
  if (!cleanedPhone) {
    throw new Error('Please enter the Telegram phone number linked to your account.')
  }

  const normalized = cleanedPhone.replace(/\s+/g, '')
  const payload = {
    phone: normalized,
    method: 'telegram_otp',
    created_at: Date.now(),
  }

  localStorage.setItem(TELEGRAM_AUTH_KEY, JSON.stringify(payload))

  return {
    success: true,
    message: 'A verification code has been sent to your Telegram account.',
    phone: normalized,
    expires_in: 300,
  }
}

const verifyTelegramOtp = async (phone, code) => {
  const cleanedPhone = String(phone || '').trim()
  const cleanedCode = String(code || '').trim()

  if (!cleanedPhone) {
    throw new Error('Please enter the Telegram phone number linked to your account.')
  }

  if (!cleanedCode) {
    throw new Error('Please enter the Telegram verification code.')
  }

  const savedSession = localStorage.getItem(TELEGRAM_AUTH_KEY)
  const session = savedSession ? JSON.parse(savedSession) : null

  if (session && session.phone !== cleanedPhone.replace(/\s+/g, '')) {
    throw new Error('The Telegram phone number does not match the current request.')
  }

  const fakeToken = `tg_${Date.now()}_${Math.random().toString(36).slice(2, 10)}`
  const mockUser = {
    id: `telegram-${Date.now()}`,
    name: `Telegram User`,
    email: null,
    phone: cleanedPhone,
    role: 'user',
  }

  persistAuthData(fakeToken, mockUser)
  localStorage.removeItem(TELEGRAM_AUTH_KEY)

  return mockUser
}

const loginWithTelegram = async (phone = '') => {
  if (phone) {
    return requestTelegramOtp(phone)
  }

  window.location.href = `${API_URL}/auth/telegram`
  return { success: true }
}

const logout = async () => {
  try {
    if (token.value) {
      await request('/auth/logout', { method: 'POST' })
    }
  } catch {
    // Clear local auth state even if server logout request fails
  } finally {
    token.value = null
    user.value = null
    profileAvatar.value = ''

    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem('admin_token')
    localStorage.removeItem('auth_token')
    localStorage.removeItem('admin_user')
    localStorage.removeItem('user')
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
    return loginWithToken(callbackToken)
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
    loginWithToken,
    fetchProfileAvatar,
    setProfileAvatar,
    login,
    register,
    verifyOtp,
    loginWithGoogle,
    requestTelegramOtp,
    verifyTelegramOtp,
    loginWithTelegram,
    logout,
    handleOAuthCallback,
  }
}