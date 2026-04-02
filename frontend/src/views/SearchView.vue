<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useOrderStore } from '@/stores/orderStore'
import { formatDate } from '@/composables/useFormatDate'
import AlertConfirmModal from '@/components/modals/AlertConfirmModal.vue'
import Pagination from '@/components/ui/Pagination.vue'

const router = useRouter()
const orderStore = useOrderStore()

const form = ref({
  lot: '',
  start_date: '',
  end_date: '',
})

const error = ref('')
const loading = ref(false)

const showAlertModal = ref(false)
const alertTarget = ref(null)
const alertError = ref('')

async function handleSearch() {
  if (!form.value.lot.trim()) {
    error.value = 'Lot number is required'
    return
  }

  error.value = ''
  loading.value = true

  try {
    await orderStore.searchOrders({
      lot: form.value.lot.trim(),
      start_date: form.value.start_date || undefined,
      end_date: form.value.end_date || undefined,
    })
  } catch (err) {
    error.value = err.response?.data?.message || 'Search failed.'
  } finally {
    loading.value = false
  }
}

function openAlertModal(order) {
  alertTarget.value = order
  showAlertModal.value = true
}

function handleAlertSent() {
  showAlertModal.value = false
  alertError.value = ''
}

function handleAlertError(msg) {
  alertError.value = msg
}

async function changePage(page) {
  loading.value = true
  try {
    await orderStore.searchOrders({
      lot: form.value.lot.trim(),
      start_date: form.value.start_date || undefined,
      end_date: form.value.end_date || undefined,
    }, page)
  } catch (err) {
    error.value = err.response?.data?.message || 'Search failed.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="w-full">
    <div class="px-6 py-4">
      <h2 class="text-sm font-bold text-gray-800 mb-3 border-b pb-2">Medication Search</h2>
      
      <div v-if="error" class="mb-4 text-sm text-red-600">{{ error }}</div>

      <div class="flex flex-wrap md:flex-nowrap items-center gap-4">
        <div class="flex items-center flex-1">
          <label for="lot-number" class="text-sm font-semibold text-gray-700 mr-2 whitespace-nowrap">Lot Number:</label>
          <input 
            id="lot-number"
            v-model="form.lot" 
            type="text"
            placeholder="e.g. 951357"
            class="flex-1 min-w-[150px] border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#24364A]"
          />
        </div>
        <div class="flex items-center gap-4">
          <div class="flex items-center">
            <label for="start-date" class="text-sm text-gray-600 mr-2 whitespace-nowrap">Start Date:</label>
            <input 
              id="start-date"
              v-model="form.start_date" 
              type="date" 
              class="w-36 border border-gray-300 px-3 py-1.5 text-sm text-center focus:outline-none focus:ring-1 focus:ring-[#24364A]"
            />
          </div>
          <div class="flex items-center">
            <label for="end-date" class="text-sm text-gray-600 mr-2 whitespace-nowrap">End Date:</label>
            <input 
              id="end-date"
              v-model="form.end_date" 
              type="date" 
              class="w-36 border border-gray-300 px-3 py-1.5 text-sm text-center focus:outline-none focus:ring-1 focus:ring-[#24364A]"
            />
          </div>
        </div>
      </div>
    </div>

    <div class="bg-gray-100 px-6 py-3 border-y border-gray-200 flex justify-between items-center">
      <h2 class="text-sm font-bold text-gray-800">Order Results</h2>
      <button 
        @click="handleSearch"
        :disabled="loading"
        class="bg-[#3C826C] text-white px-8 py-1.5 text-sm font-semibold shadow-sm hover:bg-[#2f6655] transition-colors"
      >
        <span v-if="loading">Searching...</span>
        <span v-else>Search</span>
      </button>
    </div>

    <div class="p-6">
      <table class="w-full text-sm text-left border-collapse">
        <thead>
          <tr class="bg-[#24364A] text-white">
            <th class="px-4 py-2 font-semibold w-1/6">Order ID</th>
            <th class="px-4 py-2 font-semibold w-2/6">Customer</th>
            <th class="px-4 py-2 font-semibold w-1/6">Date</th>
            <th class="px-4 py-2 font-semibold w-2/6 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="orderStore.orders.length === 0 && !loading">
            <td colspan="4" class="px-4 py-6 text-center text-gray-500 bg-gray-50 border-b">
              No orders found. Enter a lot number and search.
            </td>
          </tr>

          <tr v-if="loading">
            <td colspan="4" class="px-4 py-6 text-center text-gray-500 bg-gray-50 border-b">
              Searching...
            </td>
          </tr>
          
          <tr 
            v-for="(order, index) in orderStore.orders" 
            :key="order.id"
            :class="index % 2 === 0 ? 'bg-gray-50' : 'bg-white'"
            class="border-b"
          >
            <td class="px-4 py-3 text-gray-800">{{ String(order.id).padStart(7, '0') }}</td>
            <td class="px-4 py-3 text-gray-800">{{ order.customer?.name }}</td>
            <td class="px-4 py-3 text-gray-800">{{ formatDate(order.purchase_date) }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-center gap-2">
                <button 
                  @click="router.push({ name: 'order-detail', params: { id: order.id } })"
                  class="bg-[#24364A] text-white px-3 py-1.5 text-xs font-semibold flex items-center gap-1 hover:bg-[#1a2838]"
                  aria-label="View order details"
                >
                  <span>👁️</span> View Order
                </button>
                <button 
                  @click="openAlertModal(order)"
                  class="bg-[#24364A] text-white px-3 py-1.5 text-xs font-semibold flex items-center gap-1 hover:bg-[#1a2838]"
                  aria-label="Send alert to buyer"
                >
                  <span>⚠️</span> Alert Buyer
                </button>
                <button 
                  @click="router.push({ name: 'customer-detail', params: { id: order.customer_id } })"
                  class="bg-[#24364A] text-white px-3 py-1.5 text-xs font-semibold flex items-center gap-1 hover:bg-[#1a2838]"
                  aria-label="View buyer profile"
                >
                  <span>👤</span> View Buyer
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <Pagination
        v-if="orderStore.pagination.lastPage > 1"
        :current-page="orderStore.pagination.currentPage"
        :last-page="orderStore.pagination.lastPage"
        :total="orderStore.pagination.total"
        @page-change="changePage"
      />
    </div>

    <AlertConfirmModal
      v-if="showAlertModal && alertTarget"
      :order="alertTarget"
      :lot-number="orderStore.searchFilters.lot"
      @close="showAlertModal = false; alertError = ''"
      @sent="handleAlertSent"
      @error="handleAlertError"
    />
  </div>
</template>
