<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useOrderStore } from '@/stores/orderStore'
import { formatDate } from '@/composables/useFormatDate'

const props = defineProps({
  id: [String, Number],
})

const router = useRouter()
const orderStore = useOrderStore()

const order = ref(null)
const loading = ref(true)
const error = ref('')

onMounted(async () => {
  try {
    order.value = await orderStore.getOrder(props.id)
  } catch (err) {
    error.value = 'Failed to load order details'
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
      &larr; Back to orders
    </button>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="animate-spin h-8 w-8 border-4 border-red-600 border-t-transparent rounded-full"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
      <p class="text-red-700">{{ error }}</p>
    </div>

    <div v-else-if="order" class="space-y-6">
      <div class="bg-white shadow-md border border-gray-300">
        <!-- Header -->
        <div class="bg-[#24364A] px-6 py-4 flex items-center justify-between">
          <div>
            <h1 class="text-lg font-bold text-white tracking-wide">Order #{{ order.id }}</h1>
            <p class="text-gray-300 text-sm mt-1">{{ formatDate(order.purchase_date) }}</p>
          </div>
          <span class="text-xl font-semibold text-white">${{ safePrice(order.total) }}</span>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">
          <!-- Customer info -->
          <div class="border border-gray-200 bg-gray-50 p-4 flex justify-between items-start">
            <div>
              <h3 class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wider">Customer</h3>
              <p class="font-medium text-gray-900">{{ order.customer?.name }}</p>
              <p class="text-sm text-gray-600">{{ order.customer?.email || 'No email' }}</p>
              <p class="text-sm text-gray-600">{{ order.customer?.phone || 'No phone' }}</p>
            </div>
            <router-link
              :to="{ name: 'customer-detail', params: { id: order.customer_id } }"
              class="text-sm font-medium text-[#3C826C] hover:text-[#2f6655]"
            >
              View full profile &rarr;
            </router-link>
          </div>

          <!-- Order items -->
          <div class="border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
              <h3 class="text-xs font-semibold text-gray-800 uppercase tracking-wider">Items</h3>
            </div>
            <table class="w-full text-sm">
              <thead class="bg-white border-b border-gray-200">
                <tr>
                  <th class="px-4 py-2.5 text-left font-medium text-gray-600">Medication</th>
                  <th class="px-4 py-2.5 text-left font-medium text-gray-600">Lot</th>
                  <th class="px-4 py-2.5 text-center font-medium text-gray-600">Qty</th>
                  <th class="px-4 py-2.5 text-right font-medium text-gray-600">Unit Price</th>
                  <th class="px-4 py-2.5 text-right font-medium text-gray-600">Subtotal</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="item in order.items" :key="item.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-gray-900">{{ item.medication?.name }}</td>
                  <td class="px-4 py-3">
                    <span
                      class="font-mono text-xs px-2 py-0.5 rounded"
                      :class="item.medication?.lot_number === orderStore.searchFilters.lot
                        ? 'bg-red-100 text-red-700 font-semibold'
                        : 'bg-gray-100 text-gray-600'"
                    >
                      {{ item.medication?.lot_number }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center text-gray-600">{{ item.quantity }}</td>
                  <td class="px-4 py-3 text-right text-gray-600">${{ safePrice(item.unit_price) }}</td>
                  <td class="px-4 py-3 text-right font-medium text-gray-900">
                    ${{ safePrice(item.quantity * parseFloat(item.unit_price)) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Alerts history for this order -->
      <div v-if="order.alerts && order.alerts.length > 0" class="bg-white shadow-md border border-gray-300">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
          <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wider">Alert History</h3>
        </div>
        <div class="divide-y divide-gray-100">
          <div
            v-for="alert in order.alerts"
            :key="alert.id"
            class="flex items-center justify-between p-4 hover:bg-gray-50"
          >
            <div>
              <p class="text-sm font-medium text-gray-900">
                {{ alert.method.toUpperCase() }} alert — {{ alert.status }}
              </p>
              <p class="text-xs text-gray-500 mt-0.5">
                Sent {{ alert.sent_at ? formatDate(alert.sent_at) : 'pending' }}
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
        </div>
      </div>
    </div>
  </div>
</template>
