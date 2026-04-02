import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref((() => {
    try { return JSON.parse(localStorage.getItem('pharma_user') || 'null') }
    catch { return null }
  })())
  const token = ref(localStorage.getItem('pharma_token') || null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const userName = computed(() => user.value?.name || '')

  async function login(username, password) {
    const { data } = await api.post('/login', { username, password })

    token.value = data.data.token
    user.value = data.data.user

    localStorage.setItem('pharma_token', data.data.token)
    localStorage.setItem('pharma_user', JSON.stringify(data.data.user))

    return data
  }

  async function logout() {
    try {
      await api.post('/logout')
    } catch {
      // If the server is down we still want to clear the local session
    }
    clearSession()
  }

  async function fetchUser() {
    try {
      const { data } = await api.get('/user')
      user.value = data.data
      localStorage.setItem('pharma_user', JSON.stringify(data.data))
    } catch {
      clearSession()
    }
  }

  function clearSession() {
    token.value = null
    user.value = null
    localStorage.removeItem('pharma_token')
    localStorage.removeItem('pharma_user')
  }

  return {
    user,
    token,
    isAuthenticated,
    isAdmin,
    userName,
    login,
    logout,
    fetchUser,
    clearSession,
  }
})
