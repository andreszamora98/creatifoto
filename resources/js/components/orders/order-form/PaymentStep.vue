<template>
  <section>
    <h4 class="text-base font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-5 flex items-center gap-2">
      <span class="w-8 h-8 rounded-full bg-blue-500 text-white text-sm flex items-center justify-center font-bold">3</span>
      Pago
    </h4>

    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5">
      <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
        <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300 flex items-center">
          <i class="ti ti-cash-banknote mr-2 text-xl"></i>{{ paymentConcept }} <span class="text-red-500 ml-1">*</span>
        </h4>
        <span v-if="showBalanceBadge" class="text-base font-semibold"
              :class="remainingBalance > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400'">
          <i :class="remainingBalance > 0 ? 'ti ti-progress-alert' : 'ti ti-circle-check'"></i>
          {{ remainingBalance > 0 ? ('Restante: $' + remainingBalance.toFixed(2)) : 'Liquidado' }}
        </span>
      </div>

      <label class="flex items-center gap-2 mb-4 text-base text-gray-600 dark:text-gray-400 cursor-pointer select-none">
        <input type="checkbox" :checked="liquidateAll" @change="toggleLiquidateAll" class="w-5 h-5 text-blue-600 dark:text-blue-500">
        Liquidar todo con un solo pago
      </label>

      <div class="space-y-3">
        <div v-for="(payment, idx) in payments" :key="idx"
             class="flex flex-col gap-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <span class="text-base font-semibold text-gray-500 dark:text-gray-400">Pago {{ idx + 1 }}</span>
            <button v-if="payments.length > 1" @click="removePayment(idx)" type="button"
                    class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors flex-shrink-0">
              <i class="ti ti-trash text-xl"></i>
            </button>
          </div>

          <div class="w-full">
            <input v-model.number="payment.amount" type="number" min="0" step="0.01"
                  @input="onPaymentAmountInput(idx)"
                  placeholder="0.00"
                  class="form-control form-control-sm text-lg p-3 rounded-lg border w-full text-gray-800 dark:text-gray-200"
                  :class="showErrors && (!payment.amount || payment.amount <= 0)
                    ? 'border-red-500 dark:border-red-500'
                    : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700'">

            <p v-if="showErrors && (!payment.amount || payment.amount <= 0)" class="text-sm text-red-600 dark:text-red-400 mt-1 font-medium">
              Campo obligatorio*
            </p>
          </div>

          <div class="relative w-full">
            <button
              type="button"
              @click="togglePaymentMethodDropdown(idx)"
              @blur="closePaymentMethodDropdown(idx)"
              class="w-full text-lg p-3 rounded-lg border text-gray-800 dark:text-gray-200 flex items-center justify-between"
              :class="showErrors && !payment.payment_method
                ? 'border-red-500 dark:border-red-500'
                : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700'"
            >
              <span class="flex items-center gap-2">
                <i v-if="payment.payment_method === 'efectivo'" class="ti ti-cash text-xl text-green-600 dark:text-green-400"></i>
                <i v-else-if="payment.payment_method === 'tarjeta'" class="ti ti-credit-card text-xl text-blue-600 dark:text-blue-400"></i>
                <span v-if="payment.payment_method === 'efectivo'">Efectivo</span>
                <span v-else-if="payment.payment_method === 'tarjeta'">Tarjeta</span>
                <span v-else class="text-gray-400 dark:text-gray-500">Método</span>
              </span>
              <i class="ti ti-chevron-down text-base"></i>
            </button>

            <div v-if="openPaymentMethodDropdownIdx === idx"
                 class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
              <button @mousedown.prevent="selectLinePaymentMethod(idx, 'efectivo')"
                      class="w-full px-3 py-3 text-lg hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-800 dark:text-gray-200">
                <i class="ti ti-cash text-xl text-green-600 dark:text-green-400"></i> Efectivo
              </button>
              <button @mousedown.prevent="selectLinePaymentMethod(idx, 'tarjeta')"
                      class="w-full px-3 py-3 text-lg hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-800 dark:text-gray-200">
                <i class="ti ti-credit-card text-xl text-blue-600 dark:text-blue-400"></i> Tarjeta
              </button>
            </div>
            <p v-if="showErrors && !payment.payment_method" class="text-sm text-red-600 dark:text-red-400 mt-1 font-medium">
              Campo obligatorio*
            </p>
          </div>
        </div>
      </div>

      <button @click="addPayment" type="button" :disabled="remainingBalance <= 0 || liquidateAll"
              class="mt-4 text-base font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1">
        <i class="ti ti-plus text-lg"></i> Agregar otro pago
      </button>

      <p v-if="remainingBalance <= 0 && estimatedTotal > 0" class="text-sm text-gray-400 dark:text-gray-500 mt-2">
        El pago cubre el total de la orden. No se pueden agregar más pagos.
      </p>
    </div>
  </section>
</template>

<script>
export default {
  name: 'PaymentStep',
  props: {
    payments: { type: Array, required: true },
    estimatedTotal: { type: Number, default: 0 },
    remainingBalance: { type: Number, default: 0 },
    liquidateAll: { type: Boolean, default: false },
    showErrors: { type: Boolean, default: false },
    paymentConcept: { type: String, default: '' },
    showBalanceBadge: { type: Boolean, default: false },
    openPaymentMethodDropdownIdx: { type: Number, default: null },
    toggleLiquidateAll: { type: Function, required: true },
    onPaymentAmountInput: { type: Function, required: true },
    togglePaymentMethodDropdown: { type: Function, required: true },
    closePaymentMethodDropdown: { type: Function, required: true },
    selectLinePaymentMethod: { type: Function, required: true },
    addPayment: { type: Function, required: true },
    removePayment: { type: Function, required: true }
  }
};
</script>