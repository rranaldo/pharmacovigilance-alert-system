<script setup>
import { ref, onMounted } from 'vue'
import { useAuditLogStore } from '@/stores/auditLogStore'
import { formatDateTime } from '@/composables/useFormatDate'
import Pagination from '@/components/ui/Pagination.vue'

const store = useAuditLogStore()

const filters = ref({
  action: '',
  date_from: '',
  date_to: '',
})

const expandedRow = ref(null)

onMounted(() => store.fetchLogs())

function applyFilters() {
  store.fetchLogs(1, filters.value)
}

function clearFilters() {
  filters.value = { action: '', date_from: '', date_to: '' }
  store.fetchLogs(1)
}

function changePage(page) {
  store.fetchLogs(page, filters.value)
}

function toggleRow(id) {
  expandedRow.value = expandedRow.value === id ? null : id
}

function actionBadgeClass(action) {
  if (action.startsWith('auth.')) return 'bg-blue-100 text-blue-700'
  if (action.startsWith('alert.')) return 'bg-orange-100 text-orange-700'
  if (action.includes('delete') || action.includes('remove')) return 'bg-red-100 text-red-700'
  if (action.includes('create') || action.includes('send')) return 'bg-green-100 text-green-700'
  return 'bg-gray-100 text-gray-700'
}

function formatDetails(details) {
  if (!details) return 'No details'
  return JSON.stringify(details, null, 2)
}
</script>

<template>
  <div class="w-full">

    <!-- Filter bar -->
    <div class="px-6 py-4">
      <h2 class="text-sm font-bold text-gray-800 mb-3 border-b pb-2">Audit Log Filters</h2>
      <div class="flex flex-wrap md:flex-nowrap items-center gap-4">
        <div class="flex items-center flex-1">
          <label class="text-sm font-semibold text-gray-700 mr-2 whitespace-nowrap">Action:</label>
          <input
            v-model="filters.action"
            type="text"
            placeholder="e.g. auth.login, alert.send"
            class="flex-1 min-w-[150px] border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#24364A]"
            @keyup.enter="applyFilters"
          />
        </div>
        <div class="flex items-center gap-4">
          <div class="flex items-center">
            <label class="text-sm text-gray-600 mr-2 whitespace-nowrap">From:</label>
            <input
              v-model="filters.date_from"
              type="date"
              class="w-36 border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#24364A]"
            />
          </div>
          <div class="flex items-center">
            <label class="text-sm text-gray-600 mr-2 whitespace-nowrap">To:</label>
            <input
              v-model="filters.date_to"
              type="date"
              class="w-36 border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#24364A]"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Section header -->
    <div class="bg-gray-100 px-6 py-3 border-y border-gray-200 flex justify-between items-center">
      <h2 class="text-sm font-bold text-gray-800">
        Results
        <span v-if="store.pagination.total > 0" class="font-normal text-gray-500 ml-1">({{ store.pagination.total }})</span>
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
      <div v-if="store.loading" class="py-10 text-center text-sm text-gray-500">
        Searching...
      </div>

      <div v-else-if="store.logs.length === 0" class="py-10 text-center text-sm text-gray-500 bg-gray-50 border-b">
        No audit logs found. Try adjusting the filters.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm text-left border-collapse">
          <thead>
            <tr class="bg-[#24364A] text-white">
              <th class="px-4 py-2 font-semibold w-8"></th>
              <th class="px-4 py-2 font-semibold">ID</th>
              <th class="px-4 py-2 font-semibold">Action</th>
              <th class="px-4 py-2 font-semibold">User</th>
              <th class="px-4 py-2 font-semibold">Entity</th>
              <th class="px-4 py-2 font-semibold">IP Address</th>
              <th class="px-4 py-2 font-semibold">Date</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(log, index) in store.logs" :key="log.id">
              <tr
                :class="index % 2 === 0 ? 'bg-gray-50' : 'bg-white'"
                class="border-b cursor-pointer hover:bg-blue-50 transition-colors"
                @click="toggleRow(log.id)"
              >
                <td class="px-4 py-3 text-gray-400">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-3.5 w-3.5 transition-transform"
                    :class="expandedRow === log.id ? 'rotate-90' : ''"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </td>
                <td class="px-4 py-3 font-mono text-gray-500">{{ String(log.id).padStart(5, '0') }}</td>
                <td class="px-4 py-3">
                  <span
                    class="px-2 py-0.5 text-xs font-medium font-mono"
                    :class="actionBadgeClass(log.action)"
                  >
                    {{ log.action }}
                  </span>
                </td>
                <td class="px-4 py-3 text-gray-800">
                  {{ log.user?.name || '—' }}
                  <span v-if="log.user?.role" class="ml-1 text-xs text-gray-400">({{ log.user.role }})</span>
                </td>
                <td class="px-4 py-3 text-gray-500 font-mono text-xs">
                  <span v-if="log.entity_type">
                    {{ log.entity_type.split('\\').pop() }} #{{ log.entity_id }}
                  </span>
                  <span v-else>—</span>
                </td>
                <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ log.ip_address || '—' }}</td>
                <td class="px-4 py-3 text-gray-600 text-xs">{{ formatDateTime(log.created_at) }}</td>
              </tr>

              <!-- Expanded details -->
              <tr v-if="expandedRow === log.id" :class="index % 2 === 0 ? 'bg-gray-50' : 'bg-white'">
                <td colspan="7" class="px-8 py-3 border-b">
                  <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Details</p>
                  <pre class="text-xs bg-white border border-gray-200 p-3 overflow-x-auto text-gray-700 max-h-40 leading-relaxed">{{ formatDetails(log.details) }}</pre>
                </td>
              </tr>
            </template>
          </tbody>
        </table>

        <Pagination
          v-if="store.pagination.lastPage > 1"
          :current-page="store.pagination.currentPage"
          :last-page="store.pagination.lastPage"
          :total="store.pagination.total"
          :per-page="25"
          @page-change="changePage"
        />
      </div>
    </div>

  </div>
</template>
