<template>
  <div>
    <h5
      class="flex items-center mb-2 sm:mb-3 short:mb-2"
      :class="nested
        ? 'font-medium text-gray-700 dark:text-gray-300 text-sm sm:text-base short:text-sm'
        : 'font-semibold text-base sm:text-lg short:text-base text-gray-800 dark:text-gray-200'"
    >
      <i class="ti ti-tool mr-1.5 sm:mr-2 short:mr-1.5" :class="nested ? '' : 'text-base sm:text-xl short:text-base'"></i>Servicios adicionales
      <span class="ml-2 text-xs sm:text-base short:text-xs px-2 sm:px-3 py-0.5 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-300 rounded">
        {{ services.length }}
      </span>
    </h5>

    <div class="space-y-2 short:space-y-1.5">
      <div
        v-for="service in services"
        :key="service.id"
        class="flex items-start justify-between gap-2 sm:gap-3 short:gap-2 p-2 sm:p-3 short:p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600"
      >
        <div class="min-w-0">
          <p class="text-sm sm:text-base short:text-sm font-medium text-gray-800 dark:text-gray-200 truncate">
            {{ service.additional_service_name_snapshot || 'Servicio adicional' }}
            <span v-if="service.quantity > 1" class="text-gray-500 dark:text-gray-400">
              x{{ service.quantity }}
            </span>
          </p>
          <p v-if="service.notes" class="text-xs sm:text-sm short:text-[11px] text-gray-500 dark:text-gray-400 italic break-words">
            {{ service.notes }}
          </p>
        </div>
        <span class="text-sm sm:text-base short:text-sm font-bold text-gray-800 dark:text-gray-200 whitespace-nowrap">
          {{ formatCurrency(service.price * (service.quantity || 1)) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdditionalServicesSummaryList',
  props: {
    services: { type: Array, default: () => [] },
    nested: { type: Boolean, default: false }
  },
  methods: {
    formatCurrency(value) {
      const num = Number(value || 0);
      return '$' + num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
  }
};
</script>