import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useAuditLogStore = defineStore('auditLogs', () => {
  const logs = ref([])
  const pagination = ref({ currentPage: 1, lastPage: 1, total: 0 })
  const loading = ref(false)

  async function fetchLogs(page = 1, filters = {}) {
    loading.value = true
    try {
      const params = { page, per_page: 25, ...filters }

      Object.keys(params).forEach((k) => {
        if (params[k] === '' || params[k] === null || params[k] === undefined) {
          delete params[k]
        }
      })

      const { data } = await api.get('/audit-logs', { params })

      logs.value = data.data
      pagination.value = {
        currentPage: data.current_page,
        lastPage: data.last_page,
        total: data.total,
      }
    } finally {
      loading.value = false
    }
  }

  function reset() {
    logs.value = []
    pagination.value = { currentPage: 1, lastPage: 1, total: 0 }
  }

  return { logs, pagination, loading, fetchLogs, reset }
})
