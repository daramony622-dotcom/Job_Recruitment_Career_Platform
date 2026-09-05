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
    logout,
  }
}
