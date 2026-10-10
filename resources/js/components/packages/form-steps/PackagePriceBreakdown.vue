<template>
  <section>
    <h4 class="text-base sm:text-lg short:text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center">
      <i class="ti ti-receipt mr-1.5 sm:mr-2 text-base sm:text-xl short:text-base"></i>Cálculo del precio
    </h4>

    <div class="mt-3 sm:mt-4 short:mt-2 space-y-3 sm:space-y-5 short:space-y-2.5 text-sm sm:text-lg short:text-sm">
      <div>
        <label class="block text-sm sm:text-base short:text-xs font-medium text-gray-700 dark:text-gray-300 mb-1 short:mb-0.5">Subtotal</label>
        <div class="relative">
          <span class="absolute left-2.5 sm:left-3 short:left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm short:text-xs pointer-events-none">$</span>
          <input
            type="number" min="0" step="0.01"
            :value="subtotalInputValue"
            @input="onSubtotalInput($event.target.value)"
            v-blur-on-enter
            enterkeyhint="next"
            :class="inputClass(showErrors && subtotalError, 'pl-7 sm:pl-8 short:pl-6')"
          />
        </div>
        <p v-if="showErrors && subtotalError" class="text-xs sm:text-sm short:text-xs text-red-600 dark:text-red-400 mt-1 font-medium">
          El subtotal debe ser 0 o mayor.
        </p>
        <p v-else-if="overridden" class="text-xs sm:text-sm short:text-xs text-gray-500 dark:text-gray-400 mt-1">
          Calculado: ${{ calculatedSubtotal.toFixed(2) }}.
          <button type="button" @click="resetSubtotal" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
            Restablecer
          </button>
        </p>
        <p v-else class="text-xs sm:text-sm short:text-xs text-gray-400 dark:text-gray-500 mt-1">
          Calculado automáticamente. Puedes editarlo manualmente.
        </p>
      </div>

      <div>
        <label class="block text-sm sm:text-base short:text-xs font-medium text-gray-700 dark:text-gray-300 mb-1 short:mb-0.5">Descuento</label>
        <div class="flex gap-2 short:gap-1.5">
          <div class="flex rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden flex-shrink-0">
            <button
              type="button"
              @click="setDiscountType('percent')"
              class="px-3 sm:px-4 short:px-3 text-sm sm:text-lg short:text-sm font-medium transition-colors"
              :class="pricing.discount_type === 'percent'
                ? 'bg-blue-500 text-white'
                : 'bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'"
              title="Descuento en porcentaje"
            >%</button>
            <button
              type="button"
              @click="setDiscountType('amount')"
              class="px-3 sm:px-4 short:px-3 text-sm sm:text-lg short:text-sm font-medium border-l border-gray-300 dark:border-gray-600 transition-colors"
              :class="pricing.discount_type === 'amount'
                ? 'bg-blue-500 text-white'
                : 'bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'"
              title="Descuento en monto"
            >$</button>
          </div>

          <div class="relative flex-1 min-w-0">
            <span class="absolute left-2.5 sm:left-3 short:left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm short:text-xs pointer-events-none">
              {{ pricing.discount_type === 'percent' ? '%' : '$' }}
            </span>
            <input
              type="number" min="0" step="0.01"
              :max="pricing.discount_type === 'percent' ? 100 : null"
              :value="pricing.discount_value"
              @input="update({ discount_value: $event.target.value })"
              v-blur-on-enter
              enterkeyhint="done"
              placeholder="0"
              :class="inputClass(showErrors && discountError, 'pl-7 sm:pl-8 short:pl-6')"
            />
          </div>
        </div>
        <p v-if="showErrors && discountError" class="text-xs sm:text-sm short:text-xs text-red-600 dark:text-red-400 mt-1 font-medium">
          {{ discountErrorMessage }}
        </p>
      </div>

      <div v-if="discountAmount > 0" class="flex items-center justify-between gap-2 text-green-600 dark:text-green-400">
        <span class="truncate"><i class="ti ti-discount-2 mr-1"></i>{{ discountLabel }}</span>
        <span class="font-medium whitespace-nowrap">-${{ discountAmount.toFixed(2) }}</span>
      </div>
      <p v-else class="text-xs sm:text-sm short:text-xs text-gray-400 dark:text-gray-500 italic">Sin descuento aplicado.</p>
    </div>

    <div class="mt-3 sm:mt-5 short:mt-2 flex items-center justify-between gap-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 sm:p-5 short:p-2">
      <span class="text-sm sm:text-lg short:text-sm font-semibold text-blue-800 dark:text-blue-300">Precio total del paquete</span>
      <span class="text-lg sm:text-2xl short:text-base font-bold text-blue-800 dark:text-blue-300 whitespace-nowrap">${{ total.toFixed(2) }}</span>
    </div>
  </section>
</template>

<script>
const INPUT_BASE =
  'form-control form-control-sm w-full text-sm sm:text-lg short:text-sm p-2.5 sm:p-3 short:p-2 rounded-lg border bg-gray-50 dark:bg-gray-700 ' +
  'text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent';

export default {
  name: 'PackagePriceBreakdown',
  props: {
    pricing: { type: Object, required: true },
    calculatedSubtotal: { type: Number, default: 0 },
    subtotal: { type: Number, default: 0 },
    discountAmount: { type: Number, default: 0 },
    total: { type: Number, default: 0 },
    showErrors: { type: Boolean, default: false },
    subtotalError: { type: Boolean, default: false },
    discountError: { type: Boolean, default: false },
    discountErrorMessage: { type: String, default: 'Descuento inválido.' }
  },
  emits: ['update:pricing'],
  computed: {
    overridden() {
      const v = this.pricing.subtotal_override;
      return v !== '' && v !== null && v !== undefined;
    },
    subtotalInputValue() {
      return this.overridden ? this.pricing.subtotal_override : this.calculatedSubtotal.toFixed(2);
    },
    discountLabel() {
      if (this.pricing.discount_type === 'percent') {
        const v = Number(this.pricing.discount_value);
        return `Descuento (${Number.isFinite(v) ? v : 0}%)`;
      }
      return 'Descuento';
    }
  },
  methods: {
    inputClass(invalid, extra = '') {
      return [
        INPUT_BASE,
        extra,
        invalid ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'
      ];
    },
    update(patch) {
      this.$emit('update:pricing', { ...this.pricing, ...patch });
    },
    onSubtotalInput(raw) {
      this.update({ subtotal_override: raw });
    },
    resetSubtotal() {
      this.update({ subtotal_override: '' });
    },
    setDiscountType(type) {
      if (this.pricing.discount_type !== type) this.update({ discount_type: type });
    }
  }
};
</script>