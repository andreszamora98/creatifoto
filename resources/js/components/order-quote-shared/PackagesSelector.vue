<template>
  <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-5 short:p-2">
    <h4 class="text-base sm:text-lg short:text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2.5 sm:mb-4 short:mb-2 flex items-center">
      <i class="ti ti-package mr-1.5 sm:mr-2 text-base sm:text-xl short:text-base"></i>{{ title }}
    </h4>
    <div v-if="!loading && packages.length === 0" class="text-sm sm:text-lg short:text-sm text-gray-500 dark:text-gray-400 italic">
      No hay paquetes disponibles.
    </div>
    <div v-else class="space-y-2 sm:space-y-3 short:space-y-1.5 max-h-[20rem] sm:max-h-[28rem] short:max-h-[14rem] overflow-y-auto pr-1">
      <label v-for="pkg in packages" :key="pkg.id"
             class="flex items-center justify-between gap-2 p-2.5 sm:p-4 short:p-2 rounded bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 cursor-pointer text-gray-700 dark:text-gray-300">
        <span class="flex items-center gap-2 sm:gap-4 short:gap-2 text-sm sm:text-lg short:text-sm min-w-0">
          <input type="checkbox" :value="pkg.id" v-model="proxy" class="w-5 h-5 sm:w-6 sm:h-6 short:w-5 short:h-5 text-blue-600 dark:text-blue-500 flex-shrink-0">
          <span class="truncate">{{ pkg.name || ('Paquete ' + pkg.package_number) }}</span>
          <span class="hidden sm:inline text-gray-400 dark:text-gray-500 text-xs sm:text-base short:text-xs truncate" v-if="pkg.school">— {{ pkg.school.name }}</span>
        </span>
        <span class="text-sm sm:text-lg short:text-sm font-medium text-gray-600 dark:text-gray-400 whitespace-nowrap flex-shrink-0">${{ Number(pkg.total_price).toFixed(2) }}</span>
      </label>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PackagesSelector',
  props: {
    packages: { type: Array, default: () => [] },
    selectedIds: { type: Array, required: true },
    loading: { type: Boolean, default: false },
    title: { type: String, default: 'Paquetes escolares (opcional)' }
  },
  emits: ['update:selectedIds'],
  computed: {
    proxy: {
      get() { return this.selectedIds; },
      set(value) { this.$emit('update:selectedIds', value); }
    }
  }
};
</script>