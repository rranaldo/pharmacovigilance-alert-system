<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentPage: { type: Number, required: true },
  lastPage: { type: Number, required: true },
  total: { type: Number, default: 0 },
  perPage: { type: Number, default: 15 },
})

const emit = defineEmits(['page-change'])

const from = computed(() => {
  if (props.total === 0) return 0
  return (props.currentPage - 1) * props.perPage + 1
})

const to = computed(() => {
  return Math.min(props.currentPage * props.perPage, props.total)
})

function goToPage(page) {
  if (page >= 1 && page <= props.lastPage && page !== props.currentPage) {
    emit('page-change', page)
  }
}
</script>

<template>
  <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200">
    <p class="text-sm text-gray-500">
      Showing <span class="font-medium text-gray-700">{{ from }}</span>
      to <span class="font-medium text-gray-700">{{ to }}</span>
      of <span class="font-medium text-gray-700">{{ total }}</span> results
    </p>
    <div class="flex items-center gap-1">
      <button
        @click="goToPage(currentPage - 1)"
        :disabled="currentPage <= 1"
        class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
      >
        Prev
      </button>

      <template v-for="page in lastPage" :key="page">
        <button
          v-if="page === 1 || page === lastPage || Math.abs(page - currentPage) <= 1"
          @click="goToPage(page)"
          class="px-3 py-1 text-sm rounded-md transition-colors"
          :class="page === currentPage
            ? 'bg-red-600 text-white'
            : 'border border-gray-300 hover:bg-gray-50'"
        >
          {{ page }}
        </button>
        <span
          v-else-if="page === 2 && currentPage > 3 || page === lastPage - 1 && currentPage < lastPage - 2"
          class="px-1 text-gray-400"
        >
          ...
        </span>
      </template>

      <button
        @click="goToPage(currentPage + 1)"
        :disabled="currentPage >= lastPage"
        class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
      >
        Next
      </button>
    </div>
  </div>
</template>
