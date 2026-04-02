import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useOrderStore = defineStore('orders', () => {
  const orders = ref([])
  const pagination = ref({})
  const loading = ref(false)
  const searchFilters = ref({
    lot: '',
    start_date: '',
    end_date: '',
  })

  async function searchOrders(filters, page = 1) {
    loading.value = true
    try {
      searchFilters.value = { ...filters }
      const params = {
        lot: filters.lot,
        start_date: filters.start_date || undefined,
        end_date: filters.end_date || undefined,
        page,
        per_page: 15,
      }

      const { data } = await api.get('/orders', { params })

      orders.value = data.data
      pagination.value = {
        currentPage: data.current_page,
        lastPage: data.last_page,
        total: data.total,
        perPage: data.per_page,
      }
    } finally {
      loading.value = false
    }
  }

  async function getOrder(id) {
    const { data } = await api.get(`/orders/${id}`)
    return data.data
  }

  function resetSearch() {
    orders.value = []
    pagination.value = {}
    searchFilters.value = { lot: '', start_date: '', end_date: '' }
  }

  return {
    orders,
    pagination,
    loading,
    searchFilters,
    searchOrders,
    getOrder,
    resetSearch,
  }
})
