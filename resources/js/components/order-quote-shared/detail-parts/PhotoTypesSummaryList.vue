<template>
  <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-5">
    <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-lg flex items-center mb-3">
      <i class="ti ti-photo mr-2 text-xl"></i>Tipos de fotografía
      <span class="ml-2 text-base px-3 py-0.5 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-300 rounded">{{ lines.length }}</span>
    </h4>
    <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
      <div
        v-for="line in lines"
        :key="line.id"
        class="flex items-center justify-between p-4 rounded bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
      >
        <div class="flex items-center min-w-0 flex-1">
          <div class="w-11 h-11 rounded-md flex items-center justify-center mr-3 flex-shrink-0"
               :class="line.format === 'color' ? 'bg-pink-100 dark:bg-pink-900/40' : 'bg-gray-200 dark:bg-gray-700'">
            <i
              :class="line.format === 'color' ? 'ti ti-palette text-pink-600 dark:text-pink-300' : 'ti ti-contrast text-gray-700 dark:text-gray-300'"
              class="text-xl"
            ></i>
          </div>
          <div class="truncate">
            <p class="font-medium text-gray-800 dark:text-gray-300 text-base truncate">
              {{ line.photo_type?.name || 'Tipo de foto' }}
              <span class="text-gray-400 dark:text-gray-500">x{{ line.quantity }}</span>
            </p>
            <p class="text-base text-gray-500 dark:text-gray-400">
              <span v-if="line.format === 'color'">Color</span>
              <span v-else>Blanco y negro</span>
              <span v-if="line.paper_type"> • {{ line.paper_type.name }}</span>
              <span v-if="line.paper_type"> (+{{ formatCurrency(line.paper_type.extra_price) }})</span>
            </p>
          </div>
        </div>
        <span class="font-semibold text-gray-700 dark:text-gray-300 text-base ml-2 whitespace-nowrap">
          {{ formatCurrency(line.unit_price * line.quantity) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PhotoTypesSummaryList',
  props: {
    lines: { type: Array, default: () => [] }
  },
  methods: {
    formatCurrency(value) {
      const num = Number(value || 0);
      return '$' + num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
  }
};
</script>