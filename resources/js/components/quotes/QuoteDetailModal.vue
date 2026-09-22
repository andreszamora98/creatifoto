<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-6" @click="$emit('close')">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-3xl max-h-[85vh] overflow-y-auto p-6" @click.stop>
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Cotización C-{{ String(quote?.id).padStart(3, '0') }}</h3>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200 transition-colors"
          >
            <i class="ti ti-x text-2xl"></i>
          </button>
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
            Se creará una orden a partir de esta cotización. Define la fecha y hora de entrega, y registra el pago inicial.
          </p>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label class="text-lg font-medium block mb-2 text-gray-700 dark:text-gray-300">Fecha de entrega estimada</label>
              <input v-model="deliveryDate" type="date" class="form-control text-lg p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 w-full">
            </div>

            <div>
              <label class="text-lg font-medium block mb-2 text-gray-700 dark:text-gray-300">Hora de entrega estimada</label>
              <div class="relative">
                <button
                  type="button"
                  @click="toggleTimeDropdown"
                  @blur="closeTimeDropdown"
                  class="form-control text-lg p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 flex items-center justify-between text-left w-full"
                >
                  <span class="flex items-center gap-2 truncate">
                    <i class="ti ti-clock text-xl text-gray-400 dark:text-gray-500 flex-shrink-0"></i>
                    <span v-if="deliveryTime" class="truncate">
                      {{ deliveryTime }} <span class="text-gray-500 dark:text-gray-400">hrs</span>
                    </span>
                    <span v-else class="text-gray-400 dark:text-gray-500 truncate">Seleccionar hora</span>
                  </span>
                  <i class="ti ti-chevron-down text-sm flex-shrink-0"></i>
                </button>

                <div
                  v-if="openTimeDropdown"
                  class="absolute z-30 mt-1 left-0 right-0 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden"
                >
                  <div class="flex divide-x divide-gray-200 dark:divide-gray-700">
                    <!-- Horas -->
                    <div class="flex-1 max-h-56 overflow-y-auto">
                      <div class="sticky top-0 px-3 py-1.5 text-xs font-bold uppercase tracking-wide bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                        Hora
                      </div>
                      <button
                        v-for="h in hourOptions"
                        :key="h"
                        type="button"
                        @mousedown.prevent="selectHour(h)"
                        class="w-full px-3 py-2 text-base text-center hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200"
                        :class="{ 'bg-blue-50 dark:bg-blue-900/30 font-semibold text-blue-700 dark:text-blue-300': selectedHour === h }"
                      >
                        {{ h }}
                      </button>
                    </div>

                    <!-- Minutos -->
                    <div class="flex-1 max-h-56 overflow-y-auto">
                      <div class="sticky top-0 px-3 py-1.5 text-xs font-bold uppercase tracking-wide bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                        Min
                      </div>
                      <button
                        v-for="m in minuteOptions"
                        :key="m"
                        type="button"
                        @mousedown.prevent="selectMinute(m)"
                        class="w-full px-3 py-2 text-base text-center hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200"
                        :class="{ 'bg-blue-50 dark:bg-blue-900/30 font-semibold text-blue-700 dark:text-blue-300': selectedMinute === m }"
                      >
                        {{ m }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
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
      deliveryTime: '16:00',
      paymentAmount: null,
      paymentMethod: '',
      submitting: false,
      openTimeDropdown: false
    };
  },
  computed: {
    hourOptions() {
      const arr = [];
      for (let h = 0; h <= 23; h++) arr.push(String(h).padStart(2, '0'));
      return arr;
    },
    minuteOptions() {
      const arr = [];
      for (let m = 0; m <= 59; m++) arr.push(String(m).padStart(2, '0'));
      return arr;
    },
    selectedHour() {
      return this.deliveryTime ? this.deliveryTime.split(':')[0] : null;
    },
    selectedMinute() {
      return this.deliveryTime ? this.deliveryTime.split(':')[1] : null;
    }
  },
  watch: {
    show(val) {
      if (val) {
        // Default 16:00 al abrir
        this.deliveryTime = '16:00';
      } else {
        this.converting = false;
        this.deliveryDate = '';
        this.deliveryTime = '16:00';
        this.paymentAmount = null;
        this.paymentMethod = '';
        this.openTimeDropdown = false;
      }
    }
  },
  methods: {
    statusLabel(status) {
      const labels = { pending: 'Pendiente', accepted: 'Aceptada', rejected: 'Rechazada', expired: 'Vencida', converted: 'Convertida' };
      return labels[status] || status;
    },
    toggleTimeDropdown() {
      this.openTimeDropdown = !this.openTimeDropdown;
    },
    closeTimeDropdown() {
      setTimeout(() => { this.openTimeDropdown = false; }, 200);
    },
    selectHour(h) {
      const minute = this.selectedMinute || '00';
      this.deliveryTime = `${h}:${minute}`;
    },
    selectMinute(m) {
      const hour = this.selectedHour || '16';
      this.deliveryTime = `${hour}:${m}`;
      this.openTimeDropdown = false;
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
      if (!this.deliveryDate || !this.deliveryTime || !this.paymentAmount || !this.paymentMethod) {
        this.$emit('notify', { title: 'Atención', message: 'Completa fecha, hora y pago inicial', type: 'error' });
        return;
      }
      this.submitting = true;
      try {
        await axios.post(`/quotes/${this.quote.id}/convert`, {
          estimated_delivery_date: this.deliveryDate,
          estimated_delivery_time: this.deliveryTime,
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

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>