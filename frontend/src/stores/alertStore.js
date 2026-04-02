import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useAlertStore = defineStore('alerts', () => {
  const sending = ref(false)
  const alertHistory = ref([])
  const historyPagination = ref({})

  async function sendAlert(payload) {
    sending.value = true
    try {
      const { data } = await api.post('/alerts/send', payload)
      return data
    } finally {
      sending.value = false
    }
  }

  async function sendBulkAlerts(payload) {
    sending.value = true
    try {
      const { data } = await api.post('/alerts/send-bulk', payload)
      return data
    } finally {
      sending.value = false
    }
  }

  async function fetchHistory(page = 1, lotNumber = null) {
    const params = { page, per_page: 20 }
    if (lotNumber) params.lot_number = lotNumber

    const { data } = await api.get('/alerts', { params })
    alertHistory.value = data.data
    historyPagination.value = {
      currentPage: data.current_page,
      lastPage: data.last_page,
      total: data.total,
    }
  }

  return {
    sending,
    alertHistory,
    historyPagination,
    sendAlert,
    sendBulkAlerts,
    fetchHistory,
  }
})
