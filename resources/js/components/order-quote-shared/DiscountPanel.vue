<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg p-3 sm:p-4 short:p-2 border border-gray-200 dark:border-gray-600 space-y-2 sm:space-y-3">
    <div class="flex items-center justify-between gap-2">
      <h5 class="text-sm sm:text-base short:text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center">
        <i class="ti ti-discount mr-2 text-base sm:text-lg"></i>Descuento
      </h5>
      <span
        v-if="source === 'auto' && type !== null"
        class="text-[10px] sm:text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 font-medium whitespace-nowrap"
      >Automático</span>
      <span
        v-else-if="source === 'manual'"
        class="text-[10px] sm:text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 font-medium whitespace-nowrap"
      >Manual</span>
    </div>

    <template v-if="source === 'auto' && type !== null">
      <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-200 dark:border-blue-800 space-y-1.5">
        <div class="text-sm sm:text-base font-semibold text-blue-800 dark:text-blue-300">
          {{ type === 'percent' ? `Descuento automático ${Number(value)}%` : 'Descuento automático' }}
        </div>
        <p v-if="reason" class="text-xs sm:text-sm text-blue-700 dark:text-blue-400">
          Motivo: {{ reason }}
        </p>
      </div>
      <div class="flex flex-wrap gap-2">
        <button
          type="button"
          @click="$emit('set-source', 'none')"
          class="text-sm px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
        >Quitar</button>
        <button
          type="button"
          @click="$emit('set-source', 'manual')"
          class="text-sm px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
        >Convertir a manual</button>
      </div>
    </template>

    <template v-else-if="source === 'auto'">
      <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-2.5 border border-amber-200 dark:border-amber-800">
        <p class="text-xs sm:text-sm text-amber-800 dark:text-amber-300">
          Aún no se cumplen las condiciones para el descuento automático. Agrega más fotos para activarlo.
        </p>
      </div>
      <button
        type="button"
        @click="$emit('set-source', 'none')"
        class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 hover:underline"
      >Desactivar descuento automático</button>
    </template>

    <template v-else-if="source === 'none'">
      <p class="text-sm text-gray-500 dark:text-gray-400 italic">Sin descuento aplicado.</p>
      <div class="flex flex-wrap gap-2">
        <button
          v-if="suggestedAuto"
          type="button"
          @click="$emit('set-source', 'auto')"
          class="text-xs sm:text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline"
        >Aplicar descuento automático {{ suggestedAutoPercent }}%</button>
        <button
          type="button"
          @click="$emit('set-source', 'manual')"
          class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:underline"
        >Agregar descuento manual</button>
      </div>
    </template>

    <template v-else-if="source === 'manual'">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
        <div>
          <label class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-1 block">Tipo</label>
          <select
            :value="type || ''"
            @change="$emit('update:type', $event.target.value || null)"
            class="w-full text-sm p-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
          >
            <option value="">Seleccionar</option>
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
              class="w-full text-sm p-2 rounded-lg border text-gray-800 dark:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
              :class="showErrors && error ? 'border-red-500' : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700'"
            >
            <span v-if="type === 'percent'" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 text-sm pointer-events-none">%</span>
          </div>
          <p v-if="showErrors && error" class="text-xs text-red-600 dark:text-red-400 mt-1 font-medium">
            {{ type === 'percent' ? 'Valor entre 0 y 100' : 'Valor requerido' }}
          </p>
        </div>
        <div>
          <label class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-1 block">
            Motivo <span class="text-[10px] text-gray-400">(opcional)</span>
          </label>
          <input
            :value="reason"
            @input="$emit('update:reason', $event.target.value)"
            type="text"
            maxlength="255"
            :disabled="!type"
            placeholder="Ej. cliente frecuente"
            class="w-full text-sm p-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
          >
        </div>
      </div>
      <div class="flex flex-wrap gap-2">
        <button
          type="button"
          @click="$emit('set-source', 'none')"
          class="text-xs sm:text-sm text-red-500 dark:text-red-400 hover:underline"
        >Quitar</button>
        <button
          v-if="suggestedAuto"
          type="button"
          @click="$emit('set-source', 'auto')"
          class="text-xs sm:text-sm text-blue-600 dark:text-blue-400 hover:underline"
        >Restablecer a automático</button>
      </div>
    </template>

    <div v-if="source !== 'none'" class="flex items-center gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
      <input
        type="checkbox"
        :checked="appliesToPackages"
        @change="$emit('update:appliesToPackages', $event.target.checked)"
        id="discount-includes-packages"
        class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
      >
      <label for="discount-includes-packages" class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 cursor-pointer select-none">
        Aplicar descuento también a los paquetes
      </label>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DiscountPanel',
  props: {
    source: { type: String, default: 'none' },
    type: { type: String, default: null },
    value: { type: [Number, String], default: null },
    reason: { type: String, default: null },
    appliesToPackages: { type: Boolean, default: false },
    suggestedAuto: { type: Boolean, default: false },
    suggestedAutoPercent: { type: Number, default: 10 },
    showErrors: { type: Boolean, default: false },
    error: { type: Boolean, default: false }
  },
  emits: ['update:type', 'update:value', 'update:reason', 'update:appliesToPackages', 'set-source'],
  methods: {
    normalizeInput(raw) {
      if (raw === '' || raw === null || raw === undefined) return null;
      const n = Number(raw);
      return Number.isFinite(n) ? n : null;
    }
  }
};
</script>