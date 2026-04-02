<script setup>
import AppHeader from './AppHeader.vue'
</script>

<template>
  <div class="min-h-screen bg-gray-200 p-4 sm:p-8 flex justify-center items-start">
    <div class="w-full max-w-6xl bg-white shadow-xl flex flex-col">
      <AppHeader />
      <main class="flex-1 bg-white">
        <router-view />
      </main>
    </div>
  </div>
</template>
