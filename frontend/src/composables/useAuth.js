// src/composables/useAuth.js

import { computed, ref } from 'vue'
import axios from 'axios'

/* ==========================================================================
 * API CONFIGURATION
 * ========================================================================== */

const rawBaseURL =
  import.meta.env.VITE_API_URL ||
  'http://localhost:8000/api'

export const API_URL = rawBaseURL.replace(/\/+$/, '')

export const API_ORIGIN = API_URL.replace(/\/api$/, '')

/* ==========================================================================
 * STORAGE KEYS
 * ========================================================================== */

const TOKEN_KEY = 'job_platform_token'
const USER_KEY = 'job_platform_user'
const ADMIN_KEY = 'admin_user'

/* ==========================================================================
 * ASSET URL
 * ========================================================================== */

export const resolveAssetUrl = (path) => {
  if (!path) {
    return ''
  }

  const value = String(path).trim()

  // Already a complete URL
  if (/^(https?:|blob:|data:)/i.test(value)) {
    return value
  }

  const cleanPath = value.replace(/^\/+/, '')

  if (cleanPath.startsWith('storage/')) {
    return `${API_ORIGIN}/${cleanPath}`
  }

  return `${API_ORIGIN}/storage/${cleanPath}`
}

/* ==========================================================================
 * ROLE / DASHBOARD HELPER
 * ========================================================================== */

export const dashboardPathFor = (user) => {
  const role = String(user?.role || '').toLowerCase()

  if (
    role === 'admin' ||
    role === 'hr' ||
    role === 'company' ||
    user?.is_admin === true
  ) {
    return '/dashboard'
  }

  return '/profile'
}

/* ==========================================================================
 * STORAGE HELPERS
 * ========================================================================== */

const readToken = () => {
  try {
    return (
      localStorage.getItem('token') ||
      localStorage.getItem(TOKEN_KEY) ||
      null
    )
  } catch {
    return null
  }
}

const readUser = () => {
  try {
    const raw =
      localStorage.getItem('user') ||
      localStorage.getItem(USER_KEY) ||
      localStorage.getItem(ADMIN_KEY) ||
      null

    if (!raw) {
      return null
    }

    return JSON.parse(raw)
  } catch {
    return null
  }
}

/* ==========================================================================
 * AUTH STATE
 * ========================================================================== */

const token = ref(readToken())
const user = ref(readUser())

const profileAvatar = ref('')
const isLoading = ref(false)
const error = ref('')

const isAuthenticated = computed(() => {
  return Boolean(token.value)
})

/* ==========================================================================
 * AXIOS CLIENT
 * ========================================================================== */

const api = axios.create({
  baseURL: API_URL,

  timeout: 20000,

  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
})

/* ==========================================================================
 * CLEAR AUTH STATE
 * ========================================================================== */

const clearAuthState = () => {
  token.value = null
  user.value = null
  profileAvatar.value = ''

  try {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(USER_KEY)
    localStorage.removeItem(ADMIN_KEY)
  } catch {
    // Ignore localStorage errors
  }
}

/* ==========================================================================
 * SAVE AUTH DATA
 * ========================================================================== */

const persistAuthData = (
  authToken,
  userData
) => {
  token.value = authToken || null
  user.value = userData || null

  try {
    if (authToken) {
      const tokenStr = String(authToken)
      localStorage.setItem('token', tokenStr)
      localStorage.setItem(TOKEN_KEY, tokenStr)
    } else {
      localStorage.removeItem('token')
      localStorage.removeItem(TOKEN_KEY)
    }

    if (userData) {
      const userStr = JSON.stringify(userData)
      localStorage.setItem('user', userStr)
      localStorage.setItem(USER_KEY, userStr)

      const role = String(
        userData.role || ''
      ).toLowerCase()

      if (
        role === 'admin' ||
        role === 'hr' ||
        role === 'company' ||
        userData.is_admin === true
      ) {
        localStorage.setItem(
          ADMIN_KEY,
          userStr
        )
      } else {
        localStorage.removeItem(ADMIN_KEY)
      }
    } else {
      localStorage.removeItem('user')
      localStorage.removeItem('token')
      localStorage.removeItem(USER_KEY)
      localStorage.removeItem(ADMIN_KEY)
    }
  } catch (storageError) {
    console.warn(
      'Unable to save authentication data:',
      storageError
    )
  }
}

/* ==========================================================================
 * REQUEST INTERCEPTOR
 * ========================================================================== */

api.interceptors.request.use(
  (config) => {
    if (token.value) {
      config.headers = config.headers || {}

      config.headers.Authorization =
        `Bearer ${token.value}`
    }

    return config
  },

  (requestError) => {
    return Promise.reject(requestError)
  }
)

/* ==========================================================================
 * RESPONSE INTERCEPTOR
 * ========================================================================== */

api.interceptors.response.use(
  (response) => {
    return response
  },

  (err) => {
    const status = err?.response?.status

    const requestUrl = String(
      err?.config?.url || ''
    )

    /*
     * Public authentication endpoints.
     *
     * We should NOT remove an existing session just because
     * one of these endpoints returned 401.
     */

    const isLoginRequest =
      requestUrl.includes('/auth/login')

    const isRegisterRequest =
      requestUrl.includes('/auth/register')

    const isVerifyOtpRequest =
      requestUrl.includes('/auth/verify-otp')

    const isForgotPasswordRequest =
      requestUrl.includes('/auth/forgot-password')

    const isResetPasswordRequest =
      requestUrl.includes('/auth/reset-password')

    const isGoogleRequest =
      requestUrl.includes('/auth/google')

    const isTelegramRequest =
      requestUrl.includes('/auth/telegram')

    const isPublicAuthRequest =
      isLoginRequest ||
      isRegisterRequest ||
      isVerifyOtpRequest ||
      isForgotPasswordRequest ||
      isResetPasswordRequest ||
      isGoogleRequest ||
      isTelegramRequest

    /*
     * A 401 from a protected endpoint means the current
     * saved token is invalid/expired.
     */

    if (
      status === 401 &&
      !isPublicAuthRequest
    ) {
      clearAuthState()
    }

    return Promise.reject(err)
  }
)

/* ==========================================================================
 * ERROR MESSAGE HELPER
 * ========================================================================== */

const extractErrorMessage = (err) => {
  const response = err?.response
  const data = response?.data

  if (!data) {
    return (
      err?.message ||
      'Request failed.'
    )
  }

  /*
   * Laravel validation:
   *
   * {
   *   message: "...",
   *   errors: {
   *     email: ["The email field is required."],
   *     password: ["The password field is required."]
   *   }
   * }
   */

  if (
    data.errors &&
    typeof data.errors === 'object'
  ) {
    const messages = Object.values(data.errors)
      .flat()
      .filter(Boolean)

    if (messages.length > 0) {
      return messages.join(' ')
    }
  }

  if (
    typeof data.message === 'string' &&
    data.message.trim() !== ''
  ) {
    return data.message
  }

  if (
    typeof data.error === 'string' &&
    data.error.trim() !== ''
  ) {
    return data.error
  }

  if (
    typeof data.errors === 'string'
  ) {
    return data.errors
  }

  return (
    err?.message ||
    'Request failed.'
  )
}

/* ==========================================================================
 * CREATE API ERROR
 * ========================================================================== */

const createApiError = (err) => {
  const apiError = new Error(
    extractErrorMessage(err)
  )

  apiError.name = 'ApiError'

  apiError.status =
    err?.response?.status ?? null

  apiError.response =
    err?.response ?? null

  apiError.data =
    err?.response?.data ?? null

  apiError.originalError = err

  return apiError
}

/* ==========================================================================
 * GENERIC REQUEST
 * ========================================================================== */

const request = async (
  path,
  options = {}
) => {
  try {
    let payload = options.body

    /*
     * Support:
     *
     * body: {
     *   email: 'test@gmail.com',
     *   password: '123456'
     * }
     *
     * OR:
     *
     * body: JSON.stringify({...})
     */

    if (typeof payload === 'string') {
      try {
        payload = JSON.parse(payload)
      } catch {
        // Keep string as-is
      }
    }

    const config = {
      url: path,

      method: (
        options.method || 'GET'
      ).toUpperCase(),

      data: payload,

      signal: options.signal,

      headers: {
        ...(options.headers || {}),
      },
    }

    /*
     * Do not manually set Content-Type for FormData.
     * Axios/browser will add the multipart boundary.
     */

    if (
      typeof FormData !== 'undefined' &&
      payload instanceof FormData
    ) {
      delete config.headers['Content-Type']
      delete config.headers['content-type']
      config.headers.Accept = 'application/json'
    }

    const response =
      await api.request(config)

    return response.data
  } catch (err) {
    const apiError =
      createApiError(err)

    error.value = apiError.message

    throw apiError
  }
}

/* ==========================================================================
 * LOGIN
 * ========================================================================== */

const login = async ({
  email,
  password,
}) => {
  isLoading.value = true
  error.value = ''

  /*
   * Remove old token before a new login.
   */
  clearAuthState()

  try {
    const cleanEmail = String(
      email || ''
    )
      .trim()
      .toLowerCase()

    const cleanPassword = String(
      password ?? ''
    )

    const data = await request(
      '/auth/login',
      {
        method: 'POST',

        body: {
          email: cleanEmail,
          password: cleanPassword,
        },
      }
    )

    /*
     * Expected backend response:
     *
     * {
     *   message: "Login successful.",
     *   user: {...},
     *   token: "xxxxx"
     * }
     */

    if (
      !data?.token ||
      !data?.user
    ) {
      throw new Error(
        'Login succeeded, but the server did not return a token and user.'
      )
    }

    persistAuthData(
      data.token,
      data.user
    )

    return data
  } catch (err) {
    error.value =
      err?.message ||
      'Login failed.'

    throw err
  } finally {
    isLoading.value = false
  }
}

/* ==========================================================================
 * REGISTER
 * ========================================================================== */

const register = async (
  payload
) => {
  isLoading.value = true
  error.value = ''

  try {
    const data = await request(
      '/auth/register',
      {
        method: 'POST',
        body: payload,
      }
    )

    /*
     * Some backends automatically log the user in
     * after registration.
     */

    if (
      data?.token &&
      data?.user
    ) {
      persistAuthData(
        data.token,
        data.user
      )
    }

    return data
  } catch (err) {
    error.value =
      err?.message ||
      'Registration failed.'

    throw err
  } finally {
    isLoading.value = false
  }
}

/* ==========================================================================
 * VERIFY OTP
 * ========================================================================== */

const verifyOtp = async (
  email,
  code
) => {
  isLoading.value = true
  error.value = ''

  try {
    const cleanEmail = String(
      email || ''
    )
      .trim()
      .toLowerCase()

    const cleanCode = String(
      code || ''
    ).trim()

    const data = await request(
      '/auth/verify-otp',
      {
        method: 'POST',

        body: {
          email: cleanEmail,
          code: cleanCode,
        },
      }
    )

    /*
     * If OTP verification logs the user in,
     * save the returned token.
     */

    if (
      data?.token &&
      data?.user
    ) {
      persistAuthData(
        data.token,
        data.user
      )
    }

    return data
  } catch (err) {
    error.value =
      err?.message ||
      'OTP verification failed.'

    throw err
  } finally {
    isLoading.value = false
  }
}

/* ==========================================================================
 * CURRENT USER
 * ========================================================================== */

const fetchCurrentUser = async () => {
  if (!token.value) {
    user.value = null
    return null
  }

  isLoading.value = true
  error.value = ''

  try {
    const data = await request('/user')

    /*
     * Support both:
     *
     * {
     *   id: 1,
     *   name: "John"
     * }
     *
     * and:
     *
     * {
     *   data: {
     *      id: 1,
     *      name: "John"
     *   }
     * }
     */

    const fetchedUser =
      data?.data ?? data

    if (!fetchedUser) {
      throw new Error(
        'The server did not return your user information.'
      )
    }

    user.value = fetchedUser

    try {
      localStorage.setItem(
        USER_KEY,
        JSON.stringify(fetchedUser)
      )

      const role = String(
        fetchedUser.role || ''
      ).toLowerCase()

      if (
        role === 'admin' ||
        fetchedUser.is_admin === true
      ) {
        localStorage.setItem(
          ADMIN_KEY,
          JSON.stringify(fetchedUser)
        )
      } else {
        localStorage.removeItem(ADMIN_KEY)
      }
    } catch {
      // Ignore localStorage errors
    }

    return fetchedUser
  } catch (err) {
    error.value =
      err?.message ||
      'Unable to retrieve your account.'

    return null
  } finally {
    isLoading.value = false
  }
}

/* ==========================================================================
 * SESSION INITIALIZATION
 * ========================================================================== */

let sessionCheckPromise = null

const initSession = () => {
  if (!token.value) {
    return Promise.resolve(null)
  }

  if (!sessionCheckPromise) {
    sessionCheckPromise =
      fetchCurrentUser().finally(() => {
        sessionCheckPromise = null
      })
  }

  return sessionCheckPromise
}

/* ==========================================================================
 * LOGIN WITH TOKEN
 * ========================================================================== */

const loginWithToken = async (
  newToken
) => {
  if (!newToken) {
    return null
  }

  isLoading.value = true
  error.value = ''

  try {
    token.value = String(newToken)

    localStorage.setItem(
      TOKEN_KEY,
      String(newToken)
    )

    const fetchedUser =
      await fetchCurrentUser()

    if (!fetchedUser) {
      throw new Error(
        'Unable to retrieve the user associated with this token.'
      )
    }

    return fetchedUser
  } catch (err) {
    error.value =
      err?.message ||
      'Unable to authenticate.'

    clearAuthState()

    return null
  } finally {
    isLoading.value = false
  }
}

/* ==========================================================================
 * LOGOUT
 * ========================================================================== */

const logout = async () => {
  try {
    if (token.value) {
      await request(
        '/auth/logout',
        {
          method: 'POST',
        }
      )
    }
  } catch (err) {
    /*
     * Even when the server returns an error,
     * remove the local authentication.
     */
  } finally {
    clearAuthState()
  }
}

/* ==========================================================================
 * PROFILE
 * ========================================================================== */

const fetchProfileAvatar = async () => {
  if (!token.value) {
    profileAvatar.value = ''
    return ''
  }

  try {
    const profile =
      await request('/user/profile')

    profileAvatar.value =
      resolveAssetUrl(
        profile?.avatar
      )

    return profileAvatar.value
  } catch {
    return profileAvatar.value
  }
}

const setProfileAvatar = (
  avatar
) => {
  profileAvatar.value =
    resolveAssetUrl(avatar)
}

/* ==========================================================================
 * GOOGLE LOGIN
 * ========================================================================== */

const loginWithGoogle = () => {
  error.value = ''

  window.location.href =
    `${API_ORIGIN}/api/auth/google`
}

/* ==========================================================================
 * TELEGRAM LOGIN
 * ========================================================================== */

let telegramPollTimer = null
let telegramTimeoutTimer = null
let telegramReject = null
let telegramPopup = null

const stopTelegramTimers = () => {
  if (telegramPollTimer) {
    clearTimeout(telegramPollTimer)
    telegramPollTimer = null
  }

  if (telegramTimeoutTimer) {
    clearTimeout(telegramTimeoutTimer)
    telegramTimeoutTimer = null
  }
}

const cancelTelegramLogin = () => {
  stopTelegramTimers()

  isLoading.value = false

  if (telegramPopup) {
    try {
      telegramPopup.close()
    } catch {
      // Ignore
    }
  }

  telegramPopup = null

  if (telegramReject) {
    const reject =
      telegramReject

    telegramReject = null

    reject(
      new Error('cancelled')
    )
  }
}

const loginWithTelegram = () => {
  error.value = ''

  /*
   * Open popup synchronously from the click event.
   */
  const popup = window.open(
    '',
    '_blank'
  )

  if (!popup) {
    const popupError =
      new Error(
        'Telegram could not open. Please allow pop-ups for this site and try again.'
      )

    error.value =
      popupError.message

    return Promise.reject(
      popupError
    )
  }

  try {
    popup.opener = null
  } catch {
    // Ignore
  }

  telegramPopup = popup
  isLoading.value = true

  return new Promise(
    (resolve, reject) => {
      telegramReject = reject

      const cleanup = () => {
        stopTelegramTimers()

        telegramReject = null
        telegramPopup = null

        isLoading.value = false
      }

      const fail = (
        message
      ) => {
        cleanup()

        error.value = message

        try {
          popup.close()
        } catch {
          // Ignore
        }

        reject(
          new Error(message)
        )
      }

      let loginToken = null

      const scheduleNextPoll = () => {
        telegramPollTimer =
          setTimeout(
            pollOnce,
            2000
          )
      }

      const pollOnce = async () => {
        if (!loginToken) {
          fail(
            'Telegram login token is missing.'
          )
          return
        }

        if (popup.closed) {
          fail(
            'Telegram window was closed. Please try again.'
          )
          return
        }

        try {
          const statusData =
            await request(
              `/auth/telegram/status/${encodeURIComponent(loginToken)}`
            )

          const status =
            statusData?.status

          if (
            status === 'approved'
          ) {
            cleanup()

            if (
              statusData?.token &&
              statusData?.user
            ) {
              persistAuthData(
                statusData.token,
                statusData.user
              )
            }

            try {
              popup.close()
            } catch {
              // Ignore
            }

            resolve(
              statusData.user
            )

            return
          }

          if (
            status === 'declined'
          ) {
            fail(
              'Telegram login was declined.'
            )
            return
          }

          if (
            status === 'expired'
          ) {
            fail(
              'Telegram login link expired. Please try again.'
            )
            return
          }

          /*
           * pending / used / unknown status
           */
          scheduleNextPoll()
        } catch (err) {
          fail(
            err?.message ||
              'Telegram login failed.'
          )
        }
      }

      request(
        '/auth/telegram/init',
        {
          method: 'POST',
        }
      )
        .then(
          (data) => {
            loginToken =
              data?.token

            const url =
              data?.url

            if (
              !loginToken ||
              !url
            ) {
              fail(
                'The server did not return a valid Telegram login link.'
              )

              return
            }

            popup.location.href =
              url

            scheduleNextPoll()

            telegramTimeoutTimer =
              setTimeout(
                () => {
                  fail(
                    'Telegram login timed out. Please try again.'
                  )
                },
                5 * 60 * 1000
              )
          }
        )
        .catch(
          (err) => {
            fail(
              err?.message ||
                'Failed to start Telegram login.'
            )
          }
        )
    }
  )
}

/* ==========================================================================
 * FORGOT PASSWORD
 * ========================================================================== */

const forgotPassword = async (
  email
) => {
  isLoading.value = true
  error.value = ''

  try {
    const cleanEmail =
      String(email || '')
        .trim()
        .toLowerCase()

    return await request(
      '/auth/forgot-password',
      {
        method: 'POST',

        body: {
          email: cleanEmail,
        },
      }
    )
  } catch (err) {
    error.value =
      err?.message ||
      'Unable to send password reset request.'

    throw err
  } finally {
    isLoading.value = false
  }
}

/* ==========================================================================
 * RESET PASSWORD
 * ========================================================================== */

const resetPassword = async ({
  email,
  code,
  password,
  password_confirmation,
}) => {
  isLoading.value = true
  error.value = ''

  try {
    const cleanEmail =
      String(email || '')
        .trim()
        .toLowerCase()

    const cleanCode =
      String(code || '').trim()

    return await request(
      '/auth/reset-password',
      {
        method: 'POST',

        body: {
          email: cleanEmail,

          code: cleanCode,

          password,

          password_confirmation:
            password_confirmation ||
            password,
        },
      }
    )
  } catch (err) {
    error.value =
      err?.message ||
      'Unable to reset password.'

    throw err
  } finally {
    isLoading.value = false
  }
}

/* ==========================================================================
 * OAUTH CALLBACK
 * ========================================================================== */

const handleOAuthCallback = () => {
  const params =
    new URLSearchParams(
      window.location.search
    )

  const callbackToken =
    params.get('token')

  const callbackError =
    params.get('oauth_error')

  if (callbackError) {
    error.value = callbackError

    window.history.replaceState(
      {},
      document.title,
      window.location.pathname
    )

    return false
  }

  if (callbackToken) {
    /*
     * Remove token from address bar.
     */
    window.history.replaceState(
      {},
      document.title,
      window.location.pathname
    )

    return loginWithToken(
      callbackToken
    )
  }

  return null
}

/* ==========================================================================
 * COMPOSABLE
 * ========================================================================== */

export function useAuth() {
  return {
    // API
    API_URL,
    API_ORIGIN,
    api,
    request,

    // Auth state
    token,
    user,
    profileAvatar,
    isAuthenticated,
    isLoading,
    error,

    // Session
    fetchCurrentUser,
    initSession,
    loginWithToken,

    // Authentication
    login,
    register,
    verifyOtp,
    logout,

    // Password
    forgotPassword,
    resetPassword,

    // OAuth
    loginWithGoogle,
    loginWithTelegram,
    cancelTelegramLogin,
    handleOAuthCallback,

    // Profile
    fetchProfileAvatar,
    setProfileAvatar,

    // Helpers
    clearAuthState,
    dashboardPathFor,
  }
}