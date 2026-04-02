<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useOrderStore } from '@/stores/orderStore'
import { formatDate } from '@/composables/useFormatDate'
import AlertConfirmModal from '@/components/modals/AlertConfirmModal.vue'
import BulkAlertModal from '@/components/modals/BulkAlertModal.vue'
import Pagination from '@/components/ui/Pagination.vue'

const router = useRouter()
const orderStore = useOrderStore()

const selectedOrders = ref([])
const showAlertModal = ref(false)
const showBulkModal = ref(false)
const alertTarget = ref(null)
const notification = ref({ show: false, message: '', type: 'success' })

const allSelected = computed(() => {
  return orderStore.orders.length > 0 &&
    selectedOrders.value.length === orderStore.orders.length
})

const hasFilters = computed(() => !!orderStore.searchFilters.lot)

const selectedCustomerIds = computed(() => {
  const selected = new Set(selectedOrders.value)
  const ids = new Set()
  orderStore.orders.forEach(o => {
    if (selected.has(o.id)) ids.add(o.customer_id)
  })
  return Array.from(ids)
})

onMounted(() => {
  if (!hasFilters.value) {
    router.push({ name: 'search' })
  }
})

function toggleAll() {
  if (allSelected.value) {
    selectedOrders.value = []
  } else {
    selectedOrders.value = orderStore.orders.map(o => o.id)
  }
}

function toggleOrder(orderId) {
  const idx = selectedOrders.value.indexOf(orderId)
  if (idx > -1) {
    selectedOrders.value.splice(idx, 1)
  } else {
    selectedOrders.value.push(orderId)
  }
}

function isSelected(orderId) {
  return selectedOrders.value.includes(orderId)
}

function openAlertModal(order) {
  alertTarget.value = order
  showAlertModal.value = true
}

function openBulkModal() {
  showBulkModal.value = true
}

async function handleAlertSent(result) {
  showAlertModal.value = false
  showBulkModal.value = false
  showNotification(result.message || 'Alert sent successfully', 'success')
  await orderStore.searchOrders(orderStore.searchFilters, orderStore.pagination.currentPage)
  selectedOrders.value = []
}

function handleAlertError(errorMsg) {
  showNotification(errorMsg, 'error')
}

function showNotification(message, type) {
  notification.value = { show: true, message, type }
  setTimeout(() => { notification.value.show = false }, 4000)
}

async function changePage(page) {
  await orderStore.searchOrders(orderStore.searchFilters, page)
  selectedOrders.value = []
}

function goToSearch() {
  orderStore.resetSearch()
  router.push({ name: 'search' })
}

function exportCsv() {
  const params = new URLSearchParams({
    lot: orderStore.searchFilters.lot,
  })
  if (orderStore.searchFilters.start_date) params.set('start_date', orderStore.searchFilters.start_date)
  if (orderStore.searchFilters.end_date) params.set('end_date', orderStore.searchFilters.end_date)

  window.open(`/api/orders/export?${params.toString()}`, '_blank')
}

function safePrice(value) {
  const n = parseFloat(value)
  return isNaN(n) ? '0.00' : n.toFixed(2)
}
</script>

<template>
  <div>
    <Transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="translate-y-2 opacity-0"
      enter-to-class="translate-y-0 opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="notification.show"
        class="fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium"
        :class="notification.type === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'"
      >
        {{ notification.message }}
      </div>
    </Transition>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Orders</h1>
        <p class="text-gray-500 text-sm mt-1">
          Lot <span class="font-mono font-semibold text-red-600">{{ orderStore.searchFilters.lot }}</span>
          &middot; {{ orderStore.pagination.total || 0 }} orders found
        </p>
      </div>
      <div class="flex items-center gap-2">
        <button
          @click="goToSearch"
          class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
        >
          New Search
        </button>
        <button
          @click="exportCsv"
          class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
        >
          Export CSV
        </button>
        <button
          v-if="selectedOrders.length > 0"
          @click="openBulkModal"
          class="px-3 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
        >
          Alert Selected ({{ selectedOrders.length }})
        </button>
      </div>
    </div>

    <div v-if="orderStore.loading" class="flex items-center justify-center py-20">
      <div class="animate-spin h-8 w-8 border-4 border-red-600 border-t-transparent rounded-full"></div>
    </div>

    <div v-else-if="orderStore.orders.length === 0" class="text-center py-20 bg-white rounded-xl border border-gray-200">
      <p class="text-gray-500 text-lg">No orders found</p>
      <p class="text-gray-400 text-sm mt-1">Try adjusting your search criteria</p>
      <button @click="goToSearch" class="mt-4 text-red-600 hover:text-red-700 text-sm font-medium">
        Back to Search
      </button>
    </div>

    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-4 py-3 text-left">
                <input
                  type="checkbox"
                  :checked="allSelected"
                  @change="toggleAll"
                  class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                  aria-label="Select all orders"
                />
              </th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Order ID</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Customer</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Contact</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Purchase Date</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Total</th>
              <th class="px-4 py-3 text-left font-medium text-gray-600">Alerts</th>
              <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr
              v-for="order in orderStore.orders"
              :key="order.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-4 py-3">
                <input
                  type="checkbox"
                  :checked="isSelected(order.id)"
                  @change="toggleOrder(order.id)"
                  class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                  :aria-label="`Select order ${order.id}`"
                />
              </td>
              <td class="px-4 py-3 font-mono text-gray-900">#{{ order.id }}</td>
              <td class="px-4 py-3 text-gray-900 font-medium">{{ order.customer?.name }}</td>
              <td class="px-4 py-3 text-gray-500">
                {{ order.customer?.email || order.customer?.phone || '—' }}
              </td>
              <td class="px-4 py-3 text-gray-600">{{ formatDate(order.purchase_date) }}</td>
              <td class="px-4 py-3 text-gray-900">${{ safePrice(order.total) }}</td>
              <td class="px-4 py-3">
                <span
                  v-if="order.alerts_count > 0"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700"
                >
                  Sent ({{ order.alerts_count }})
                </span>
                <span
                  v-else
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500"
                >
                  Pending
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-1">
                  <button
                    @click="router.push({ name: 'order-detail', params: { id: order.id } })"
                    class="p-1.5 hover:bg-blue-50 rounded-md transition-colors text-base"
                    aria-label="View order details"
                    title="View Order"
                  >
                    👁️
                  </button>
                  <button
                    @click="openAlertModal(order)"
                    class="p-1.5 hover:bg-red-50 rounded-md transition-colors text-base"
                    aria-label="Send alert"
                    title="Send Alert"
                  >
                    ⚠️
                  </button>
                  <button
                    @click="router.push({ name: 'customer-detail', params: { id: order.customer_id } })"
                    class="p-1.5 hover:bg-green-50 rounded-md transition-colors text-base"
                    aria-label="View customer"
                    title="View Customer"
                  >
                    👤
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

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
      @close="showAlertModal = false"
      @sent="handleAlertSent"
      @error="handleAlertError"
    />

    <BulkAlertModal
      v-if="showBulkModal"
      :customer-ids="selectedCustomerIds"
      :lot-number="orderStore.searchFilters.lot"
      :count="selectedOrders.length"
      @close="showBulkModal = false"
      @sent="handleAlertSent"
      @error="handleAlertError"
    />
  </div>
</template>
