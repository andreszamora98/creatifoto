<template>
  <section>
    <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300 flex items-center">
      <i class="ti ti-receipt mr-2 text-xl"></i>Cálculo del precio
    </h4>

    <div class="mt-4 space-y-5 text-lg">
      <!-- Subtotal -->
      <div>
        <label class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-1">Subtotal</label>
        <div class="relative">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">$</span>
          <input
            type="number" min="0" step="0.01"
            :value="subtotalInputValue"
            @input="onSubtotalInput($event.target.value)"
            v-blur-on-enter
            enterkeyhint="next"
            :class="inputClass(showErrors && subtotalError, 'pl-8')"
          />
        </div>
        <p v-if="showErrors && subtotalError" class="text-sm text-red-600 dark:text-red-400 mt-1 font-medium">
          El subtotal debe ser 0 o mayor.
        </p>
        <p v-else-if="overridden" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          Calculado: ${{ calculatedSubtotal.toFixed(2) }}.
          <button type="button" @click="resetSubtotal" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
            Restablecer
          </button>
        </p>
        <p v-else class="text-sm text-gray-400 dark:text-gray-500 mt-1">
          Calculado automáticamente. Puedes editarlo manualmente.
        </p>
      </div>

      <!-- Descuento -->
      <div>
        <label class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-1">Descuento</label>
        <div class="flex gap-2">
          <div class="flex rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden flex-shrink-0">
            <button
              type="button"
              @click="setDiscountType('percent')"
              class="px-4 text-lg font-medium transition-colors"
              :class="pricing.discount_type === 'percent'
                ? 'bg-blue-500 text-white'
                : 'bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'"
              title="Descuento en porcentaje"
            >%</button>
            <button
              type="button"
              @click="setDiscountType('amount')"
              class="px-4 text-lg font-medium border-l border-gray-300 dark:border-gray-600 transition-colors"
              :class="pricing.discount_type === 'amount'
                ? 'bg-blue-500 text-white'
                : 'bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'"
              title="Descuento en monto"
            >$</button>
          </div>

          <div class="relative flex-1 min-w-0">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
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
              :class="inputClass(showErrors && discountError, 'pl-8')"
            />
          </div>
        </div>
        <p v-if="showErrors && discountError" class="text-sm text-red-600 dark:text-red-400 mt-1 font-medium">
          {{ discountErrorMessage }}
        </p>
      </div>

      <div v-if="discountAmount > 0" class="flex items-center justify-between text-green-600 dark:text-green-400">
        <span><i class="ti ti-discount-2 mr-1"></i>{{ discountLabel }}</span>
        <span class="font-medium">-${{ discountAmount.toFixed(2) }}</span>
      </div>
      <p v-else class="text-sm text-gray-400 dark:text-gray-500 italic">Sin descuento aplicado.</p>
    </div>

    <div class="mt-5 flex items-center justify-between bg-blue-50 dark:bg-blue-900/20 rounded-lg p-5">
      <span class="text-lg font-semibold text-blue-800 dark:text-blue-300">Precio total del paquete</span>
      <span class="text-2xl font-bold text-blue-800 dark:text-blue-300">${{ total.toFixed(2) }}</span>
    </div>
  </section>
</template>

<script>
const INPUT_BASE =
  'form-control form-control-sm w-full text-lg p-3 rounded-lg border bg-gray-50 dark:bg-gray-700 ' +
  'text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent';

export default {
  name: 'PackagePriceBreakdown',
  props: {
    pricing: { type: Object, required: true }, // { subtotal_override, discount_type, discount_value }
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