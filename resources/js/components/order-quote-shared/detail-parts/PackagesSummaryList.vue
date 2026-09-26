<template>
  <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-5">
    <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-lg flex items-center mb-3">
      <i class="ti ti-package mr-2 text-xl"></i>Paquetes escolares
      <span class="ml-2 text-base px-3 py-0.5 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-300 rounded">{{ packages.length }}</span>
    </h4>
    <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
      <div
        v-for="pkg in packages"
        :key="pkg.id"
        class="flex items-center justify-between p-4 rounded bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
      >
        <div class="flex items-center min-w-0 flex-1">
          <div class="w-11 h-11 rounded-md bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center mr-3 flex-shrink-0">
            <i class="ti ti-package text-purple-600 dark:text-purple-300 text-xl"></i>
          </div>
          <div class="truncate">
            <p class="font-medium text-gray-800 dark:text-gray-300 text-base truncate">
              {{ pkg.pivot?.package_name_snapshot || pkg.name || ('Paquete ' + pkg.package_number) }}
            </p>
            <p class="text-base text-gray-500 dark:text-gray-400">Paquete escolar</p>
          </div>
        </div>
        <span class="font-semibold text-gray-700 dark:text-gray-300 text-base ml-2 whitespace-nowrap">
          {{ formatCurrency(pkg.pivot?.price_snapshot || pkg.total_price) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PackagesSummaryList',
  props: {
    packages: { type: Array, default: () => [] }
  },
  methods: {
    formatCurrency(value) {
      const num = Number(value || 0);
      return '$' + num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
  }
};
</script>