<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import { formatDate } from '@/composables/useFormatDate'
import Pagination from '@/components/ui/Pagination.vue'

const props = defineProps({
  id: [String, Number],
})

const router = useRouter()
const customer = ref(null)
const loading = ref(true)
const error = ref('')

const ordersPage = ref(1)
const ordersPerPage = 5
const alertsPage = ref(1)
const alertsPerPage = 5

const paginatedOrders = computed(() => {
  const orders = customer.value?.orders || []
  const start = (ordersPage.value - 1) * ordersPerPage
  return orders.slice(start, start + ordersPerPage)
})

const ordersLastPage = computed(() => {
  const total = customer.value?.orders?.length || 0
  return Math.max(1, Math.ceil(total / ordersPerPage))
})

const paginatedAlerts = computed(() => {
  const alerts = customer.value?.alerts || []
  const start = (alertsPage.value - 1) * alertsPerPage
  return alerts.slice(start, start + alertsPerPage)
})

const alertsLastPage = computed(() => {
  const total = customer.value?.alerts?.length || 0
  return Math.max(1, Math.ceil(total / alertsPerPage))
})

onMounted(async () => {
  try {
    const { data } = await api.get(`/customers/${props.id}`)
    customer.value = data.data
  } catch (err) {
    error.value = 'Failed to load customer details'
  } finally {
    loading.value = false
  }
})

function safePrice(value) {
  const n = parseFloat(value)
  return isNaN(n) ? '0.00' : n.toFixed(2)
}

function goBack() {
  router.back()
}
</script>

<template>
  <div class="max-w-4xl mx-auto">
    <button
      @click="goBack"
      class="mb-6 text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1"
    >
      &larr; Back
    </button>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="animate-spin h-8 w-8 border-4 border-red-600 border-t-transparent rounded-full"></div>
    </div>

    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
      <p class="text-red-700">{{ error }}</p>
    </div>

    <div v-else-if="customer" class="space-y-6">
      <!-- Customer info card -->
      <div class="bg-white shadow-md border border-gray-300">
        <div class="bg-[#24364A] px-6 py-4 flex items-center justify-between">
          <h1 class="text-lg font-bold text-white tracking-wide">{{ customer.name }}</h1>
        </div>

        <div class="p-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="border border-gray-200 bg-gray-50 p-4">
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Email</p>
              <p class="text-sm font-medium text-gray-900">{{ customer.email || 'Not provided' }}</p>
            </div>
            <div class="border border-gray-200 bg-gray-50 p-4">
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Phone</p>
              <p class="text-sm font-medium text-gray-900">{{ customer.phone || 'Not provided' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Order history -->
      <div class="bg-white shadow-md border border-gray-300">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
          <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wider">
            Order History ({{ customer.orders?.length || 0 }})
          </h2>
        </div>

        <div class="p-0">
          <div v-if="customer.orders?.length > 0" class="divide-y divide-gray-200">
            <div
              v-for="order in paginatedOrders"
              :key="order.id"
              class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors cursor-pointer"
              @click="router.push({ name: 'order-detail', params: { id: order.id } })"
            >
              <div>
                <p class="text-sm font-semibold text-[#24364A]">Order #{{ order.id }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ formatDate(order.purchase_date) }}</p>
                <div class="flex flex-wrap gap-1 mt-2">
                  <span
                    v-for="item in order.items"
                    :key="item.id"
                    class="text-[11px] px-2 py-0.5 bg-white border border-gray-300 text-gray-600"
                  >
                    {{ item.medication?.name }}
                  </span>
                </div>
              </div>
              <div class="text-right">
                <p class="font-bold text-gray-900">${{ safePrice(order.total) }}</p>
                <span class="text-xs text-gray-400">&rarr;</span>
              </div>
            </div>

            <div class="p-4 bg-gray-50 border-t border-gray-200" v-if="ordersLastPage > 1">
              <Pagination
                :current-page="ordersPage"
                :last-page="ordersLastPage"
                :total="customer.orders.length"
                :per-page="ordersPerPage"
                @page-change="ordersPage = $event"
              />
            </div>
          </div>
          <div v-else class="p-6 text-center text-gray-500 text-sm">
            No orders found for this customer.
          </div>
        </div>
      </div>

      <!-- Alerts sent to this customer -->
      <div v-if="customer.alerts?.length > 0" class="bg-white shadow-md border border-gray-300">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
          <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wider">
            Alerts Sent ({{ customer.alerts.length }})
          </h2>
        </div>
        <div class="p-0">
          <div class="divide-y divide-gray-200">
            <div
              v-for="alert in paginatedAlerts"
              :key="alert.id"
              class="flex items-center justify-between p-4 hover:bg-gray-50"
            >
              <div>
                <p class="text-sm text-gray-900">
                  Lot <span class="font-mono font-semibold text-red-600">{{ alert.lot_number }}</span>
                  — Order #{{ alert.order_id }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5">
                  By {{ alert.user?.name || 'Unknown' }} on {{ formatDate(alert.sent_at || alert.created_at) }}
                </p>
              </div>
              <span
                class="px-2.5 py-1 text-xs font-medium border"
                :class="{
                  'bg-green-50 text-green-700 border-green-200': alert.status === 'sent',
                  'bg-red-50 text-red-700 border-red-200': alert.status === 'failed',
                  'bg-yellow-50 text-yellow-700 border-yellow-200': alert.status === 'pending',
                }"
              >
                {{ alert.status }}
              </span>
            </div>

            <div class="p-4 bg-gray-50 border-t border-gray-200" v-if="alertsLastPage > 1">
              <Pagination
                :current-page="alertsPage"
                :last-page="alertsLastPage"
                :total="customer.alerts.length"
                :per-page="alertsPerPage"
                @page-change="alertsPage = $event"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
