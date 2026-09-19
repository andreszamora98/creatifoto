<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-6" @click="$emit('close')">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-3xl max-h-[85vh] overflow-y-auto p-6" @click.stop>
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Cotización C-{{ String(quote?.id).padStart(3, '0') }}</h3>
          <button @click="$emit('close')"><i class="ti ti-x text-2xl"></i></button>
        </div>

        <div v-if="!converting">
          <p class="text-lg text-gray-700 dark:text-gray-300 mb-1"><strong>Cliente:</strong> {{ quote?.customer?.name }}</p>
          <p class="text-lg text-gray-700 dark:text-gray-300 mb-1"><strong>Total:</strong> ${{ Number(quote?.total).toFixed(2) }}</p>
          <p class="text-lg text-gray-700 dark:text-gray-300 mb-4"><strong>Estado:</strong> {{ statusLabel(quote?.status) }}</p>

          <div v-if="quote?.order" class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg text-green-700 dark:text-green-400 mb-4">
            Convertida en orden #{{ quote.order.id }}
          </div>

          <div class="flex gap-3">
            <button
              v-if="quote && ['pending', 'accepted'].includes(quote.status)"
              @click="converting = true"
              class="px-4 py-2 bg-blue-500 text-white rounded-lg"
            >
              Crear orden
            </button>
            <button
              v-if="quote?.status === 'pending'"
              @click="updateStatus('rejected')"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300"
            >
              Rechazar
            </button>
          </div>
        </div>

        <div v-else>
          <p class="text-lg text-gray-700 dark:text-gray-300 mb-4">
            Se creará una orden a partir de esta cotización. Define la fecha de entrega y registra el pago inicial.
          </p>

          <div class="mb-4">
            <label class="text-lg font-medium block mb-2 text-gray-700 dark:text-gray-300">Fecha de entrega estimada</label>
            <input v-model="deliveryDate" type="date" class="form-control text-lg p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
          </div>

          <div class="mb-4">
            <label class="text-lg font-medium block mb-2 text-gray-700 dark:text-gray-300">Pago inicial</label>
            <input v-model.number="paymentAmount" type="number" step="0.01" placeholder="0.00" class="form-control text-lg p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 w-full mb-2">
            <select v-model="paymentMethod" class="form-control text-lg p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 w-full">
              <option value="">Seleccionar método</option>
              <option value="Efectivo">Efectivo</option>
              <option value="Tarjeta">Tarjeta</option>
              <option value="Transferencia">Transferencia</option>
            </select>
          </div>

          <div class="flex gap-3">
            <button @click="converting = false" class="flex-1 py-3 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Cancelar</button>
            <button @click="confirmConvert" :disabled="submitting" class="flex-1 py-3 rounded-lg bg-blue-500 text-white disabled:opacity-50">
              {{ submitting ? 'Creando...' : 'Confirmar y crear orden' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import axios from 'axios';

export default {
  name: 'QuoteDetailModal',
  props: {
    show: { type: Boolean, default: false },
    quote: { type: Object, default: null }
  },
  emits: ['close', 'converted', 'notify'],
  data() {
    return {
      converting: false,
      deliveryDate: '',
      paymentAmount: null,
      paymentMethod: '',
      submitting: false
    };
  },
  watch: {
    show(val) {
      if (!val) {
        this.converting = false;
        this.deliveryDate = '';
        this.paymentAmount = null;
        this.paymentMethod = '';
      }
    }
  },
  methods: {
    statusLabel(status) {
      const labels = { pending: 'Pendiente', accepted: 'Aceptada', rejected: 'Rechazada', expired: 'Vencida', converted: 'Convertida' };
      return labels[status] || status;
    },
    async updateStatus(status) {
      try {
        await axios.patch(`/quotes/${this.quote.id}/status`, { status });
        this.$emit('converted');
        this.$emit('close');
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: error.response?.data?.message || 'No se pudo actualizar', type: 'error' });
      }
    },
    async confirmConvert() {
      if (!this.deliveryDate || !this.paymentAmount || !this.paymentMethod) {
        this.$emit('notify', { title: 'Atención', message: 'Completa fecha de entrega y pago inicial', type: 'error' });
        return;
      }
      this.submitting = true;
      try {
        await axios.post(`/quotes/${this.quote.id}/convert`, {
          estimated_delivery_date: this.deliveryDate,
          payments: [{ amount: this.paymentAmount, payment_method: this.paymentMethod }]
        });
        this.$emit('converted');
        this.$emit('close');
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: error.response?.data?.message || 'No se pudo crear la orden', type: 'error' });
      } finally {
        this.submitting = false;
      }
    }
  }
};
</script>