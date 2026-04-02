<script setup>
import { useAuthStore } from '@/stores/authStore'
import { onMounted } from 'vue'

const authStore = useAuthStore()

onMounted(() => {
  if (authStore.isAuthenticated) {
    authStore.fetchUser()
  }
})
</script>

<template>
  <router-view />
</template>
