<template>
  <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5">
    <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center">
      <i class="ti ti-package mr-2 text-xl"></i>{{ title }}
    </h4>
    <div v-if="!loading && packages.length === 0" class="text-lg text-gray-500 dark:text-gray-400 italic">
      No hay paquetes disponibles.
    </div>
    <div v-else class="space-y-3 max-h-[28rem] overflow-y-auto pr-1">
      <label v-for="pkg in packages" :key="pkg.id"
             class="flex items-center justify-between p-4 rounded bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 cursor-pointer text-gray-700 dark:text-gray-300">
        <span class="flex items-center gap-4 text-lg">
          <input type="checkbox" :value="pkg.id" v-model="proxy" class="w-6 h-6 text-blue-600 dark:text-blue-500">
          {{ pkg.name || ('Paquete ' + pkg.package_number) }}
          <span class="text-gray-400 dark:text-gray-500 text-base" v-if="pkg.school">— {{ pkg.school.name }}</span>
        </span>
        <span class="text-lg font-medium text-gray-600 dark:text-gray-400">${{ Number(pkg.total_price).toFixed(2) }}</span>
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