<script setup>
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

async function handleLogout() {
  await authStore.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <header class="bg-[#24364A] text-white px-4 py-3 flex items-center justify-between">
    <div class="flex items-center gap-6">
      <h1 class="text-lg font-semibold tracking-wide">Pharmacovigilance</h1>

      <nav class="flex items-center gap-1">
        <router-link
          :to="{ name: 'search' }"
          class="px-3 py-1.5 text-sm text-gray-300 hover:text-white hover:bg-white/10 transition-colors"
          active-class="text-white bg-white/20 font-medium"
        >
          Search
        </router-link>
        <router-link
          :to="{ name: 'alerts' }"
          class="px-3 py-1.5 text-sm text-gray-300 hover:text-white hover:bg-white/10 transition-colors"
          active-class="text-white bg-white/20 font-medium"
        >
          Alerts
        </router-link>
        <router-link
          v-if="authStore.isAdmin"
          :to="{ name: 'audit-logs' }"
          class="px-3 py-1.5 text-sm text-gray-300 hover:text-white hover:bg-white/10 transition-colors"
          active-class="text-white bg-white/20 font-medium"
        >
          Audit Log
        </router-link>
      </nav>
    </div>

    <div class="flex items-center gap-3">
      <span v-if="authStore.userName" class="text-sm text-gray-300">{{ authStore.userName }}</span>
      <button
        @click="handleLogout"
        class="hover:text-gray-300 transition-colors"
        aria-label="Logout"
        title="Logout"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
      </button>
    </div>
  </header>
</template>
