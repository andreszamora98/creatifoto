<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg p-3 sm:p-4 short:p-2 border border-gray-200 dark:border-gray-600 space-y-2 sm:space-y-3">
    <h5 class="text-sm sm:text-base short:text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center">
      <i class="ti ti-discount mr-2 text-base sm:text-lg"></i>Descuento
    </h5>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
      <div>
        <label class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-1 block">Tipo</label>
        <select
          :value="type"
          @change="$emit('update:type', $event.target.value || null)"
          class="w-full text-sm sm:text-base p-2 sm:p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
        >
          <option :value="''">Sin descuento</option>
          <option value="fixed">Monto fijo</option>
          <option value="percent">Porcentaje</option>
        </select>
      </div>

      <div>
        <label class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-1 block">Valor</label>
        <div class="relative">
          <input
            :value="value"
            @input="$emit('update:value', normalizeInput($event.target.value))"
            type="number"
            min="0"
            :max="type === 'percent' ? 100 : undefined"
            step="0.01"
            :disabled="!type"
            :placeholder="type === 'percent' ? '0' : '0.00'"
            class="w-full text-sm sm:text-base p-2 sm:p-2.5 rounded-lg border text-gray-800 dark:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
            :class="showErrors && error ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700'"
          >
          <span v-if="type === 'percent'" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 text-sm">%</span>
          <span v-else-if="type === 'fixed'" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 text-sm">$</span>
        </div>
        <p v-if="showErrors && error" class="text-xs text-red-600 dark:text-red-400 mt-1 font-medium">
          {{ type === 'percent' ? 'Valor entre 0 y 100' : 'Valor requerido' }}
        </p>
      </div>

      <div>
        <label class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-1 block">Motivo <span class="text-[10px] text-gray-400">(opcional)</span></label>
        <input
          :value="reason"
          @input="$emit('update:reason', $event.target.value)"
          type="text"
          maxlength="255"
          :disabled="!type"
          placeholder="Ej. cliente frecuente"
          class="w-full text-sm sm:text-base p-2 sm:p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DiscountPanel',
  props: {
    type: { type: String, default: null },
    value: { type: [Number, String], default: null },
    reason: { type: String, default: null },
    showErrors: { type: Boolean, default: false },
    error: { type: Boolean, default: false }
  },
  emits: ['update:type', 'update:value', 'update:reason'],
  methods: {
    normalizeInput(raw) {
      if (raw === '' || raw === null || raw === undefined) return null;
      const n = Number(raw);
      return Number.isFinite(n) ? n : null;
    }
  }
};
</script>