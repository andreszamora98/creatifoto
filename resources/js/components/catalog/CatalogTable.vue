<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <!-- Leyenda con contador de registros -->
    <div v-if="!loading && items.length > 0" class="p-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3 flex-wrap">
      <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
        <span>{{ items.length }} {{ items.length === 1 ? itemLabelSingular : itemLabelPlural }}</span>
      </div>
    </div>

    <div v-if="loading" class="text-center py-10">
      <i class="ti ti-refresh animate-spin text-xl text-blue-500"></i>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Cargando...</p>
    </div>

    <div v-else-if="items.length === 0" class="text-center py-10 text-gray-500 dark:text-gray-400">
      <i class="ti ti-inbox text-2xl mb-2 block"></i>
      {{ emptyMessage }}
    </div>

    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
        <thead class="bg-gray-50 dark:bg-gray-800">
          <tr>
            <th class="px-3 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
              Nombre
            </th>
            <th class="px-3 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
              {{ priceLabel }}
            </th>
            <th 
              v-if="showBaseQuantity" 
              class="px-3 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap"
            >
              {{ baseQuantityLabel }}
            </th>
            <th class="px-3 py-2 w-16"></th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr
            v-for="item in items"
            :key="item.id"
            @click="$emit('edit', item)"
            class="cursor-pointer transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
          >
            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">
              {{ item.name }}
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              {{ formatCurrency(priceOf(item)) }}
            </td>
            <td 
              v-if="showBaseQuantity" 
              class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
            >
              {{ item[baseQuantityKey] ?? item.base_quantity ?? item.baseQuantity ?? '—' }}
            </td>
            <td class="px-3 py-2 whitespace-nowrap" @click.stop>
              <button
                type="button"
                @click="$emit('delete', item)"
                class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 p-1.5 rounded-lg transition-colors"
              >
                <i class="ti ti-trash text-xl"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CatalogTable',
  props: {
    items: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    priceKey: { type: String, default: 'base_price' },
    priceLabel: { type: String, default: 'Precio' },
    emptyMessage: { type: String, default: 'No hay registros.' },
    showBaseQuantity: { type: Boolean, default: false },
    baseQuantityKey: { type: String, default: 'base_quantity' },
    baseQuantityLabel: { type: String, default: 'Cantidad base' },
    itemLabelSingular: { type: String, default: 'registro' },
    itemLabelPlural: { type: String, default: 'registros' }
  },
  emits: ['edit', 'delete'],
  methods: {
    priceOf(item) {
      return item[this.priceKey];
    },
    formatCurrency(value) {
      const num = Number(value || 0);
      return '$' + num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
  }
};
</script>