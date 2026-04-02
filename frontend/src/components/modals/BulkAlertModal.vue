<script setup>
import { ref } from 'vue'
import { useAlertStore } from '@/stores/alertStore'

const props = defineProps({
  customerIds: { type: Array, required: true },
  lotNumber: { type: String, required: true },
  count: { type: Number, required: true },
})

const emit = defineEmits(['close', 'sent', 'error'])

const alertStore = useAlertStore()
const customMessage = ref('')

async function confirmBulkSend() {
  try {
    const result = await alertStore.sendBulkAlerts({
      customer_ids: props.customerIds,
      lot_number: props.lotNumber,
      message: customMessage.value || null,
    })
    emit('sent', result)
  } catch (err) {
    emit('error', err.response?.data?.message || 'Bulk alert failed')
  }
}
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="emit('close')"></div>

      <div class="relative bg-white rounded-xl shadow-2xl max-w-lg w-full p-6 z-10">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Bulk Alert</h2>
            <p class="text-sm text-gray-500">Send alerts to multiple customers at once</p>
          </div>
          <button @click="emit('close')" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
          <p class="text-sm text-amber-800">
            You are about to send pharmacovigilance alerts to
            <strong>{{ customerIds.length }} customer(s)</strong>
            for <strong>{{ count }} selected order(s)</strong>
            regarding lot <strong class="font-mono">{{ lotNumber }}</strong>.
          </p>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Custom message (optional)
          </label>
          <textarea
            v-model="customMessage"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"
            placeholder="Add additional context for all customers..."
          ></textarea>
        </div>

        <div class="flex items-center justify-end gap-3">
          <button
            @click="emit('close')"
            class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
          <button
            @click="confirmBulkSend"
            :disabled="alertStore.sending"
            class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors"
          >
            <span v-if="alertStore.sending">Sending...</span>
            <span v-else>Send {{ customerIds.length }} Alert(s)</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
