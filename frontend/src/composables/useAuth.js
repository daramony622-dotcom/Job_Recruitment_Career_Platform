// src/composables/useAuth.js

import { computed, ref } from 'vue'
import axios from 'axios'

/* ==========================================================================
 * API CONFIGURATION
 * ========================================================================== */

const rawBaseURL =
  import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

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
  if (!path) return ''

  const value = String(path).trim()

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
 * ROLE / REDIRECT HELPERS
 *
 * Single source of truth for "which dashboard does this user land on".
 * Login.vue and Register.vue both call this instead of keeping their own
 * (previously divergent) copies of this logic.
 * ========================================================================== */

export const dashboardPathFor = (user) => {
  const role = String(user?.role || '').toLowerCase()

  if (role === 'admin' || user?.is_admin) {
    return '/admin/dashboard'
  }

  switch (role) {
    case 'hr':
    case 'company':
      return '/company/dashboard'

    case 'user':
    case 'job_seeker':
    default:
      return '/user/dashboard'
  }
}

/* ==========================================================================
 * STORAGE HELPERS
 * ========================================================================== */

const readToken = () => {
  try {
    return localStorage.getItem(TOKEN_KEY)
  } catch {
    return null
  }
}

const readUser = () => {
  try {
    const value = localStorage.getItem(USER_KEY)

    if (!value) {
      return null
    }

    return JSON.parse(value)
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

  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },

  timeout: 20000,
})

/* ==========================================================================
 * AUTH STATE CLEANUP
 * ========================================================================== */

const clearAuthState = () => {
  token.value = null
  user.value = null
  profileAvatar.value = ''

  try {
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(USER_KEY)
    localStorage.removeItem(ADMIN_KEY)
  } catch {
    // Ignore localStorage errors.
  }
}

/* ==========================================================================
 * REQUEST INTERCEPTOR
 * ========================================================================== */

api.interceptors.request.use(
  (config) => {
    if (token.value) {
      config.headers = config.headers || {}

      config.headers.Authorization = `Bearer ${token.value}`
    }

    return config
  },
  (error) => Promise.reject(error)
)

/* ==========================================================================
 * RESPONSE INTERCEPTOR
 *
 * IMPORTANT:
 * Do NOT clear authentication for a failed login.
 *
 * Login can legitimately return 401 when credentials are incorrect.
 * Only clear the stored session for protected API requests.
 * ========================================================================== */

api.interceptors.response.use(
  (response) => response,

  (err) => {
    const status = err?.response?.status

    const requestUrl = String(
      err?.config?.url || ''
    )

    const isLoginRequest =
      requestUrl.includes('/auth/login')

    const isRegisterRequest =
      requestUrl.includes('/auth/register')

    const isGoogleRequest =
      requestUrl.includes('/auth/google')

    const isTelegramRequest =
      requestUrl.includes('/auth/telegram')

    const isPublicAuthRequest =
      isLoginRequest ||
      isRegisterRequest ||
      isGoogleRequest ||
      isTelegramRequest

    /*
     * 401 from protected endpoints means the saved token
     * is no longer valid.
     *
     * Do NOT clear state for login itself.
     */
    if (status === 401 && !isPublicAuthRequest) {
      clearAuthState()
    }

    return Promise.reject(err)
  }
)

/* ==========================================================================
 * ERROR HELPERS
 * ========================================================================== */

const extractErrorMessage = (err) => {
  const response = err?.response
  const data = response?.data

  if (!data) {
    return err?.message || 'Request failed.'
  }

  /*
   * Laravel validation response:
   *
   * {
   *   message: "...",
   *   errors: {
   *     email: ["..."],
   *     password: ["..."]
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

  if (data.message) {
    return data.message
  }

  return err?.message || 'Request failed.'
}

/*
 * Convert an Axios error into a useful object while preserving
 * response/status/data.
 *
 * This is important because:
 *
 * error.response.status
 * error.response.data
 *
 * must remain available to the Vue component.
 */
const createApiError = (err) => {
  const apiError = new Error(
    extractErrorMessage(err)
  )

  apiError.name = 'ApiError'

  apiError.status =
    err?.response?.status ?? null

  apiError.response = err?.response ?? null

  apiError.data =
    err?.response?.data ?? null

  apiError.originalError = err

  return apiError
}

/* ==========================================================================
 * GENERIC REQUEST HELPER
 * ========================================================================== */

const request = async (
  path,
  options = {}
) => {
  try {
    let payload = options.body

    /*
     * Support both:
     *
     * body: { email, password }
     *
     * and:
     *
     * body: JSON.stringify(...)
     */
    if (typeof payload === 'string') {
      try {
        payload = JSON.parse(payload)
      } catch {
        // Keep the original string.
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

    // Let Axios add the multipart boundary for file uploads.
    if (typeof FormData !== 'undefined' && payload instanceof FormData) {
      delete config.headers['Content-Type']
    }

    const response = await api.request(config)

    return response.data
  } catch (err) {
    const apiError = createApiError(err)

    error.value = apiError.message

    /*
     * IMPORTANT:
     *
     * Throw our enhanced error instead of:
     *
     * throw new Error(message)
     *
     * because we want to preserve status/data.
     */
    throw apiError
  }
}

/* ==========================================================================
 * PERSIST AUTH
 * ========================================================================== */

const persistAuthData = (
  authToken,
  userData
) => {
  token.value = authToken || null
  user.value = userData || null

  try {
    if (authToken) {
      localStorage.setItem(
        TOKEN_KEY,
        authToken
      )
    } else {
      localStorage.removeItem(TOKEN_KEY)
    }

    if (userData) {
      localStorage.setItem(
        USER_KEY,
        JSON.stringify(userData)
      )

      const role = String(
        userData.role || ''
      ).toLowerCase()

      if (
        role === 'admin' ||
        userData.is_admin
      ) {
        localStorage.setItem(
          ADMIN_KEY,
          JSON.stringify(userData)
        )
      } else {
        localStorage.removeItem(
          ADMIN_KEY
        )
      }
    } else {
      localStorage.removeItem(USER_KEY)
      localStorage.removeItem(ADMIN_KEY)
    }
  } catch (storageError) {
    console.warn(
      'Unable to persist authentication data:',
      storageError
    )
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
   * Remove stale token before a new login.
   */
  clearAuthState()

  try {
    const cleanEmail = String(
      email || ''
    ).trim().toLowerCase()

    const data = await request(
      '/auth/login',
      {
        method: 'POST',

        body: {
          email: cleanEmail,
          password: String(
            password ?? ''
          ),
        },
      }
    )

    /*
     * Laravel should return:
     *
     * {
     *   message,
     *   user,
     *   token,
     *   redirect_url
     * }
     */

    if (
      !data?.token ||
      !data?.user
    ) {
      throw new Error(
        'Login succeeded but the server did not return valid authentication data.'
      )
    }

    persistAuthData(
      data.token,
      data.user
    )

    return data
  } catch (err) {
    /*
     * request() already converted the error
     * into ApiError.
     */
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

const register = async (payload) => {
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
     * Only persist a token if the backend
     * actually returned one.
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
    const data = await request(
      '/auth/verify-otp',
      {
        method: 'POST',

        body: {
          email: String(
            email || ''
          ).trim().toLowerCase(),

          code: String(
            code || ''
          ).trim(),
        },
      }
    )

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
     * Supports both:
     *
     * { id, name, ... }
     *
     * and:
     *
     * { data: { id, name, ... } }
     */
    const fetchedUser =
      data?.data ?? data

    if (!fetchedUser) {
      throw new Error(
        'The server did not return a user.'
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
        fetchedUser.is_admin
      ) {
        localStorage.setItem(
          ADMIN_KEY,
          JSON.stringify(fetchedUser)
        )
      } else {
        localStorage.removeItem(
          ADMIN_KEY
        )
      }
    } catch {
      // Ignore storage errors.
    }

    return fetchedUser
  } catch (err) {
    /*
     * If the session is invalid, the response interceptor
     * has already cleared the authentication state.
     */
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
    token.value = newToken

    localStorage.setItem(
      TOKEN_KEY,
      newToken
    )

    const fetchedUser =
      await fetchCurrentUser()

    if (!fetchedUser) {
      throw new Error(
        'Failed to retrieve your account using the provided token.'
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
  } catch {
    /*
     * Even if the server logout fails,
     * remove the local session.
     */
  } finally {
    clearAuthState()
  }
}

/* ==========================================================================
 * PROFILE AVATAR
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
  window.location.href =
    `${API_ORIGIN}/api/auth/google`
}

/* ==========================================================================
 * TELEGRAM LOGIN
 *
 * Flow:
 *   1. Open a blank popup synchronously (must happen inside the click
 *      handler or browsers block it).
 *   2. POST /auth/telegram/init to get a { token, url }.
 *   3. Navigate the popup to that deep link.
 *   4. Self-scheduling poll of /auth/telegram/status/{token} (NOT
 *      setInterval — see note below) until approved/declined/expired,
 *      or the popup is closed, or a 5 minute timeout elapses.
 * ========================================================================== */

let telegramPollTimer = null
let telegramTimeoutTimer = null
let telegramReject = null
let telegramPopup = null

const stopTelegramTimers = () => {
  // telegramPollTimer is a setTimeout id (self-scheduling loop), not
  // a setInterval id — see loginWithTelegram() below.
  if (telegramPollTimer) {
    clearTimeout(
      telegramPollTimer
    )

    telegramPollTimer = null
  }

  if (telegramTimeoutTimer) {
    clearTimeout(
      telegramTimeoutTimer
    )

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
      // Ignore popup errors.
    }
  }

  telegramPopup = null

  if (telegramReject) {
    const reject = telegramReject

    telegramReject = null

    reject(
      new Error('cancelled')
    )
  }
}

const loginWithTelegram = () => {
  error.value = ''

  /*
   * Open popup synchronously from the click event, BEFORE any await,
   * or browsers will treat it as an unsolicited popup and block it.
   */
  const popup = window.open(
    '',
    '_blank'
  )

  if (!popup) {
    return Promise.reject(
      new Error(
        'Telegram could not open. Please allow pop-ups for this site and try again.'
      )
    )
  }

  try {
    popup.opener = null
  } catch {
    // Ignore.
  }

  isLoading.value = true
  telegramPopup = popup

  return new Promise(
    (resolve, reject) => {
      telegramReject = reject

      const cleanup = () => {
        stopTelegramTimers()

        telegramReject = null
        telegramPopup = null

        isLoading.value = false
      }

      const fail = (message) => {
        cleanup()

        error.value = message

        try {
          popup?.close()
        } catch {
          // Ignore.
        }

        reject(
          new Error(message)
        )
      }

      let loginToken = null

      /*
       * Self-scheduling poll: each tick only starts once the previous
       * request has finished, so slow/overlapping responses can never
       * race each other into resolving/rejecting the same promise
       * twice or leaving a stray "used" status unhandled.
       */
      const scheduleNextPoll = () => {
        telegramPollTimer = setTimeout(pollOnce, 2000)
      }

      const pollOnce = async () => {
        if (popup?.closed) {
          fail('Telegram window was closed. Please try again.')
          return
        }

        try {
          const statusData = await request(
            `/auth/telegram/status/${encodeURIComponent(loginToken)}`
          )

          switch (statusData?.status) {
            case 'approved': {
              cleanup()

              if (statusData.token && statusData.user) {
                persistAuthData(
                  statusData.token,
                  statusData.user
                )
              }

              try {
                popup.close()
              } catch {
                // Ignore.
              }

              resolve(statusData.user)
              return
            }

            case 'declined':
              fail('Telegram login was declined.')
              return

            case 'expired':
              fail('Telegram login link expired. Please try again.')
              return

            case 'used':
              // Another in-flight poll already resolved this token —
              // nothing to do, just keep the loop alive in case this
              // was actually a stale/duplicate response.
              scheduleNextPoll()
              return

            default:
              // 'pending' or anything unrecognized — keep polling.
              scheduleNextPoll()
              return
          }
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
        .then((data) => {
          loginToken = data?.token
          const url = data?.url

          if (!loginToken || !url) {
            fail(
              'The server did not return a valid Telegram login link.'
            )
            return
          }

          popup.location.href = url

          scheduleNextPoll()

          telegramTimeoutTimer = setTimeout(
            () => {
              fail(
                'Telegram login timed out. Please try again.'
              )
            },
            5 * 60 * 1000
          )
        })
        .catch((err) => {
          fail(
            err?.message ||
            'Failed to start Telegram login.'
          )
        })
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
    return await request(
      '/auth/forgot-password',
      {
        method: 'POST',

        body: {
          email: String(
            email || ''
          ).trim().toLowerCase(),
        },
      }
    )
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
    return await request(
      '/auth/reset-password',
      {
        method: 'POST',

        body: {
          email: String(
            email || ''
          ).trim().toLowerCase(),

          code: String(
            code || ''
          ).trim(),

          password,

          password_confirmation:
            password_confirmation ||
            password,
        },
      }
    )
  } finally {
    isLoading.value = false
  }
}

/* ==========================================================================
 * OAUTH CALLBACK
 * ========================================================================== */

const handleOAuthCallback = () => {
  const urlParams =
    new URLSearchParams(
      window.location.search
    )

  const callbackToken =
    urlParams.get('token')

  const callbackError =
    urlParams.get('oauth_error')

  if (callbackError) {
    error.value =
      callbackError

    window.history.replaceState(
      {},
      document.title,
      window.location.pathname
    )

    return false
  }

  if (callbackToken) {
    /*
     * Remove token from browser URL after
     * reading it.
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
    API_URL,
    API_ORIGIN,

    token,
    user,
    profileAvatar,

    isAuthenticated,
    isLoading,
    error,

    api,

    request,

    fetchCurrentUser,
    initSession,
    loginWithToken,

    fetchProfileAvatar,
    setProfileAvatar,

    login,
    register,
    verifyOtp,

    forgotPassword,
    resetPassword,

    loginWithGoogle,

    loginWithTelegram,
    cancelTelegramLogin,

    logout,

    handleOAuthCallback,

    clearAuthState,

    dashboardPathFor,
  }
}