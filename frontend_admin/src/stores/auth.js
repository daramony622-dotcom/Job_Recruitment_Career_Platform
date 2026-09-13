import { ref } from 'vue'
import { adminApi } from '../api'

const token = ref(localStorage.getItem('admin_token') || localStorage.getItem('job_platform_token') || '')
const user = ref(JSON.parse(localStorage.getItem('admin_user') || 'null'))

export function useAuth() {
  async function loginWithToken(rawToken) {
    token.value = rawToken
    localStorage.setItem('admin_token', rawToken)
    localStorage.setItem('job_platform_token', rawToken)
    try {
      const { data } = await adminApi.getMe()
      user.value = data
      localStorage.setItem('admin_user', JSON.stringify(data))
      return data
    } catch {
      logout()
      return null
    }
  }

  function logout() {
    token.value = ''
    user.value = null
    localStorage.removeItem('admin_token')
    localStorage.removeItem('job_platform_token')
    localStorage.removeItem('admin_user')
  }

  return {
    token,
    user,
    isAuthenticated: () => Boolean(token.value),
    loginWithToken,
    logout,
  }
}
