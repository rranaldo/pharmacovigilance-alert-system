import axios from 'axios'
import { useAuthStore } from '@/stores/authStore'
import router from '@/router'

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Attach the Sanctum token to every outgoing request
api.interceptors.request.use((config) => {
  const authStore = useAuthStore()
  if (authStore.token) {
    config.headers.Authorization = `Bearer ${authStore.token}`
  }
  return config
})

// Handle 401s globally so we don't repeat the same logic everywhere
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      authStore.clearSession()
      router.push({ name: 'login' })
    }
    return Promise.reject(error)
  }
)

export default api
