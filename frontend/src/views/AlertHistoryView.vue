<script setup>
import { ref, onMounted } from 'vue'
import { useAlertStore } from '@/stores/alertStore'
import { formatDateTime } from '@/composables/useFormatDate'
import Pagination from '@/components/ui/Pagination.vue'

const alertStore = useAlertStore()

const filters = ref({
  lot_number: ''
})

onMounted(() => {
  alertStore.fetchHistory()
})

function applyFilters() {
  alertStore.fetchHistory(1, filters.value.lot_number)
}

function clearFilters() {
  filters.value.lot_number = ''
  alertStore.fetchHistory(1)
}

function changePage(page) {
  alertStore.fetchHistory(page, filters.value.lot_number)
}
</script>

<template>
  <div class="w-full">
    <!-- Filter bar -->
    <div class="px-6 py-4">
      <h2 class="text-sm font-bold text-gray-800 mb-3 border-b pb-2">Alert History Filters</h2>
      <div class="flex flex-wrap md:flex-nowrap items-center gap-4">
        <div class="flex items-center flex-1 max-w-sm">
          <label class="text-sm font-semibold text-gray-700 mr-2 whitespace-nowrap">Lot Number:</label>
          <input
            v-model="filters.lot_number"
            type="text"
            placeholder="e.g. LOT-1234"
            class="flex-1 min-w-[150px] border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#24364A]"
            @keyup.enter="applyFilters"
          />
        </div>
      </div>
    </div>

    <!-- Section header -->
    <div class="bg-gray-100 px-6 py-3 border-y border-gray-200 flex justify-between items-center">
      <h2 class="text-sm font-bold text-gray-800">
        Results
        <span v-if="alertStore.historyPagination.total > 0" class="font-normal text-gray-500 ml-1">({{ alertStore.historyPagination.total }})</span>
      </h2>
      <div class="flex items-center gap-2">
        <button
          @click="clearFilters"
          class="border border-gray-300 text-gray-600 px-4 py-1.5 text-sm font-semibold hover:bg-gray-50 transition-colors"
        >
          Clear
        </button>
        <button
          @click="applyFilters"
          class="bg-[#3C826C] text-white px-8 py-1.5 text-sm font-semibold shadow-sm hover:bg-[#2f6655] transition-colors"
        >
          Filter
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="p-6">
      <div v-if="alertStore.alertHistory.length === 0" class="py-10 text-center text-sm text-gray-500 bg-gray-50 border-b border-gray-200">
        No alerts found. Alerts will appear here after they are triggered or try adjusting the filters.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm text-left border-collapse">
          <thead>
            <tr class="bg-[#24364A] text-white">
              <th class="px-4 py-2 font-semibold">ID</th>
              <th class="px-4 py-2 font-semibold">Customer</th>
              <th class="px-4 py-2 font-semibold">Lot #</th>
              <th class="px-4 py-2 font-semibold">Order</th>
              <th class="px-4 py-2 font-semibold">Method</th>
              <th class="px-4 py-2 font-semibold">Status</th>
              <th class="px-4 py-2 font-semibold">Triggered By</th>
              <th class="px-4 py-2 font-semibold">Sent At</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(alert, index) in alertStore.alertHistory"
              :key="alert.id"
              :class="index % 2 === 0 ? 'bg-gray-50' : 'bg-white'"
              class="border-b hover:bg-blue-50 transition-colors"
            >
              <td class="px-4 py-3 font-mono text-gray-500">{{ String(alert.id).padStart(5, '0') }}</td>
              <td class="px-4 py-3 font-medium text-gray-800">{{ alert.customer?.name || '—' }}</td>
              <td class="px-4 py-3">
                <span class="font-mono text-xs px-2 py-0.5 bg-red-50 text-red-700 rounded">
                  {{ alert.lot_number }}
                </span>
              </td>
              <td class="px-4 py-3 text-gray-600 font-mono text-xs">#{{ alert.order_id }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-0.5 text-xs font-medium font-mono bg-gray-100 text-gray-700 uppercase">
                  {{ alert.method }}
                </span>
              </td>
              <td class="px-4 py-3">
                <span
                  class="px-2 py-0.5 text-xs font-medium font-mono"
                  :class="{
                    'bg-green-100 text-green-700': alert.status === 'sent',
                    'bg-red-100 text-red-700': alert.status === 'failed',
                    'bg-yellow-100 text-yellow-700': alert.status === 'pending',
                  }"
                >
                  {{ alert.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-gray-800">
                {{ alert.user?.name || '—' }}
                <span v-if="alert.user?.role" class="ml-1 text-xs text-gray-400">({{ alert.user.role }})</span>
              </td>
              <td class="px-4 py-3 text-gray-600 text-xs">{{ formatDateTime(alert.sent_at) }}</td>
            </tr>
          </tbody>
        </table>

        <div class="mt-4">
          <Pagination
            v-if="alertStore.historyPagination.lastPage > 1"
            :current-page="alertStore.historyPagination.currentPage"
            :last-page="alertStore.historyPagination.lastPage"
            :total="alertStore.historyPagination.total"
            :per-page="20"
            @page-change="changePage"
          />
        </div>
      </div>
    </div>
  </div>
</template>
