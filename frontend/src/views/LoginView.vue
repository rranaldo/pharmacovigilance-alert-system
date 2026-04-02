<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  username: '',
  password: '',
})
const error = ref('')
const loading = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true

  try {
    await authStore.login(form.value.username, form.value.password)
    router.push({ name: 'search' })
  } catch (err) {
    error.value = err.response?.data?.message || 'Login failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <div class="w-full max-w-sm bg-gray-50 shadow-md">
      <!-- Header -->
      <div class="bg-[#24364A] px-4 py-4 text-center">
        <h1 class="text-xl font-bold text-white tracking-wide">Pharmacovigilance</h1>
      </div>

      <!-- Form -->
      <div class="p-6 pb-8 bg-[#F3F4F6]">
        <div v-if="error" class="mb-4 text-sm text-red-600 font-medium text-center">
          {{ error }}
        </div>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <input
              id="username"
              v-model="form.username"
              type="text"
              required
              autofocus
              class="w-full px-3 py-2 border border-gray-300 shadow-sm focus:outline-none focus:ring-1 focus:ring-[#24364A] text-gray-800"
              placeholder="Username"
            />
          </div>

          <div>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="w-full px-3 py-2 border border-gray-300 shadow-sm focus:outline-none focus:ring-1 focus:ring-[#24364A] text-gray-800"
              placeholder="Password"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full py-2.5 bg-[#24364A] text-white font-semibold hover:bg-[#1a2838] transition-colors disabled:opacity-50 mt-2"
          >
            <span v-if="loading">Logging in...</span>
            <span v-else>Login</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
