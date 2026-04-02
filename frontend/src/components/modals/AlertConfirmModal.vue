<script setup>
import { ref } from 'vue'
import { useAlertStore } from '@/stores/alertStore'

const props = defineProps({
  order: { type: Object, required: false, default: () => ({}) },
  lotNumber: { type: String, required: true },
})

const emit = defineEmits(['close', 'sent', 'error'])
const alertStore = useAlertStore()
const errorMessage = ref('')

async function confirmSend() {
  errorMessage.value = ''
  try {
    const payload = {
      customer_id: props.order?.customer_id,
      order_id: props.order?.id,
      lot_number: props.lotNumber,
    }

    await alertStore.sendAlert(payload)
    emit('sent', { message: 'Alert sent successfully.' })
  } catch (err) {
    const data = err.response?.data

    if (err.response?.status === 422 && data?.errors) {
      errorMessage.value = Object.values(data.errors).flat().join(' ')
    } else {
      errorMessage.value = data?.message || 'Failed to send alert.'
    }
    emit('error', errorMessage.value)
  }
}
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="emit('close')"></div>

      <div class="relative bg-white rounded shadow-lg max-w-sm w-full z-10 border border-gray-300">
        <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-200 bg-gray-50">
          <h2 class="text-sm font-semibold text-gray-800">Send Alert to Customer</h2>
          <button @click="emit('close')" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
        </div>

        <div class="px-4 py-5">
          <p class="text-sm text-gray-700 leading-relaxed">
            <span class="font-semibold">Warning:</span> Important recall notice for medication Lot #{{ lotNumber }}. Please contact the pharmacy immediately.
          </p>
          <p v-if="errorMessage" class="mt-3 text-xs text-red-600 border border-red-200 bg-red-50 px-3 py-2">
            {{ errorMessage }}
          </p>
        </div>

        <div class="flex items-center justify-end gap-2 px-4 py-3 border-t border-gray-200 bg-gray-50">
          <button
            @click="confirmSend"
            :disabled="alertStore.sending"
            class="px-5 py-1.5 text-sm font-medium bg-[#3C826C] text-white rounded hover:bg-[#2f6655] disabled:opacity-50 transition-colors"
          >
            <span v-if="alertStore.sending">Sending...</span>
            <span v-else>Send Email</span>
          </button>
          <button
            @click="emit('close')"
            class="px-5 py-1.5 text-sm font-medium bg-[#24364A] text-white rounded hover:bg-[#1a2838] transition-colors"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
