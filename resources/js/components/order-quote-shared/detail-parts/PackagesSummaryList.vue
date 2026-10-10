<template>
  <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-3 sm:p-5 short:p-2">
    <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-base sm:text-lg short:text-base flex items-center mb-2 sm:mb-3 short:mb-2">
      <i class="ti ti-package mr-1.5 sm:mr-2 text-base sm:text-xl short:text-base"></i>Paquetes escolares
      <span class="ml-2 text-xs sm:text-base short:text-xs px-2 sm:px-3 py-0.5 bg-gray-200 dark:bg-[#293341] text-gray-800 dark:text-gray-300 rounded">
        {{ packages.length }}
      </span>
    </h4>

    <div class="space-y-2 short:space-y-1.5 max-h-60 sm:max-h-72 short:max-h-40 overflow-y-auto pr-1">
      <div
        v-for="pkg in packages"
        :key="pkg.id"
        class="flex items-center justify-between p-2.5 sm:p-4 short:p-2 rounded bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 gap-2"
      >
        <div class="flex items-center min-w-0 flex-1">
          <div class="w-9 h-9 sm:w-11 sm:h-11 short:w-8 short:h-8 rounded-md bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center mr-2 sm:mr-3 short:mr-2 flex-shrink-0">
            <i class="ti ti-package text-purple-600 dark:text-purple-300 text-base sm:text-xl short:text-base"></i>
          </div>

          <div class="truncate">
            <p class="font-medium text-gray-800 dark:text-gray-300 text-sm sm:text-base short:text-sm truncate">
              {{ pkg.package_name_snapshot || ('Paquete #' + pkg.package_id) }}
            </p>
            <p v-if="pkg.school_name_snapshot" class="text-xs sm:text-base short:text-xs text-gray-500 dark:text-gray-400 truncate">
              <i class="ti ti-school mr-1"></i>{{ pkg.school_name_snapshot }}
            </p>
          </div>
        </div>

        <span class="font-semibold text-gray-700 dark:text-gray-300 text-sm sm:text-base short:text-sm ml-2 whitespace-nowrap">
          {{ formatCurrency(pkg.price_snapshot) }}
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
      return '$' + num.toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }
  }
};
</script>