<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <div v-if="loading" class="text-center py-10">
      <i class="ti ti-refresh animate-spin text-xl text-blue-500"></i>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Cargando...</p>
    </div>

    <div v-else-if="items.length === 0" class="text-center py-10 text-gray-500 dark:text-gray-400">
      <i class="ti ti-inbox text-2xl mb-2 block"></i>
      {{ emptyMessage }}
    </div>

    <table v-else class="w-full text-left text-sm">
      <thead>
        <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400">
          <th class="py-3 px-4">Nombre</th>
          <th class="px-4">{{ priceLabel }}</th>
          <th class="px-4 w-16"></th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="item in items"
          :key="item.id"
          @click="$emit('edit', item)"
          class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer"
        >
          <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200">{{ item.name }}</td>
          <td class="px-4 text-gray-600 dark:text-gray-400">{{ formatCurrency(priceOf(item)) }}</td>
          <td class="px-4" @click.stop>
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
</template>

<script>
export default {
  name: 'CatalogTable',
  props: {
    items: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    priceKey: { type: String, default: 'base_price' },
    priceLabel: { type: String, default: 'Precio' },
    emptyMessage: { type: String, default: 'No hay registros.' }
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