<template>
  <div>
    <h5
      class="flex items-center mb-3"
      :class="nested
        ? 'font-medium text-gray-700 dark:text-gray-300'
        : 'font-semibold text-lg text-gray-800 dark:text-gray-200'"
    >
      <i class="ti ti-tool mr-2" :class="nested ? '' : 'text-xl'"></i>Servicios adicionales
      <span class="ml-2 text-base px-3 py-0.5 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-300 rounded">
        {{ services.length }}
      </span>
    </h5>

    <div class="space-y-2">
      <div
        v-for="service in services"
        :key="service.id"
        class="flex items-start justify-between gap-3 p-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600"
      >
        <div class="min-w-0">
          <p class="text-base font-medium text-gray-800 dark:text-gray-200 truncate">{{ service.name }}</p>
          <p v-if="service.pivot?.notes" class="text-sm text-gray-500 dark:text-gray-400 italic break-words">
            {{ service.pivot.notes }}
          </p>
        </div>
        <span class="text-base font-bold text-gray-800 dark:text-gray-200 whitespace-nowrap">
          {{ formatCurrency(service.pivot?.price ?? service.default_price) }}
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