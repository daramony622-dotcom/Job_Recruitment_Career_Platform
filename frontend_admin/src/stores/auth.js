import { ref } from 'vue'
import { adminApi } from '../api'

const token = ref(localStorage.getItem('admin_token') || '')
const user = ref(JSON.parse(localStorage.getItem('admin_user') || 'null'))

export function useAuth() {
  async function login(email, password) {
    const { data } = await adminApi.login(email.trim(), password)
    token.value = data.token
    user.value = data.user
    localStorage.setItem('admin_token', data.token)
    localStorage.setItem('admin_user', JSON.stringify(data.user))
    return data.user
  }

  async function loginWithToken(rawToken) {
    token.value = rawToken
    localStorage.setItem('admin_token', rawToken)
    try {
      const { data } = await adminApi.getMe()
      user.value = data
      localStorage.setItem('admin_user', JSON.stringify(data))
      return data
    } catch {
      return null
    }
  }

  function logout() {
    token.value = ''
    user.value = null
    localStorage.removeItem('admin_token')
    localStorage.removeItem('admin_user')
  }

  return {
    token,
    user,
    isAuthenticated: () => Boolean(token.value),
    login,
    loginWithToken,
    logout,
  }
}
