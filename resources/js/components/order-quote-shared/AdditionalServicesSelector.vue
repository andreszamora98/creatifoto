<template>
  <div class="space-y-3">
    <h5 class="text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
      Servicios adicionales
      <span class="normal-case font-normal text-gray-400 dark:text-gray-500">(opcional)</span>
    </h5>

    <p v-if="loading" class="text-sm text-gray-400">Cargando servicios...</p>
    <p v-else-if="!services.length" class="text-sm text-gray-400">
      No hay servicios adicionales en el catálogo.
    </p>

    <template v-else>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="s in services"
          :key="s.id"
          type="button"
          @click="toggle(s)"
          class="flex items-center gap-2 px-3 py-2 rounded-lg border text-sm font-medium transition-colors"
          :class="isSelected(s.id)
            ? 'bg-blue-50 dark:bg-blue-900/30 border-blue-500 text-blue-700 dark:text-blue-300'
            : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'"
        >
          <i :class="['ti', isSelected(s.id) ? 'ti-check' : 'ti-plus']"></i>
          {{ s.name }}
          <span class="text-xs opacity-70">${{ Number(s.default_price).toFixed(2) }}</span>
        </button>
      </div>

      <div v-if="selected.length" class="space-y-2">
        <div
          v-for="line in selected"
          :key="line.additional_service_id"
          class="grid grid-cols-12 gap-3 items-center bg-gray-50 dark:bg-gray-700/40 rounded-lg px-4 py-3"
        >
          <span class="col-span-12 md:col-span-4 font-medium text-gray-800 dark:text-gray-200">
            {{ line.additional_service_name_snapshot || nameOf(line.additional_service_id) }}
          </span>

          <div class="col-span-5 md:col-span-2 relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">$</span>
            <input
              type="number" min="0" step="0.01"
              :value="line.price"
              @input="update(line.additional_service_id, 'price', $event.target.value)"
              class="w-full pl-7 pr-2 py-2 rounded-lg border bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200"
              :class="showErrors && priceInvalid(line) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
            />
          </div>

          <input
            type="text" maxlength="255" placeholder="Nota (opcional)"
            :value="line.notes"
            @input="update(line.additional_service_id, 'notes', $event.target.value)"
            class="col-span-6 md:col-span-5 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200"
          />

          <button
            type="button"
            @click="remove(line.additional_service_id)"
            class="col-span-1 text-gray-400 hover:text-red-500 transition-colors"
            title="Quitar"
          >
            <i class="ti ti-trash text-lg"></i>
          </button>
        </div>

        <p v-if="showErrors && error" class="text-sm text-red-500 flex items-center gap-1">
          <i class="ti ti-alert-circle"></i>El precio de cada servicio debe ser 0 o mayor.
        </p>
      </div>
    </template>
  </div>
</template>

<script>
export default {
  name: 'AdditionalServicesSelector',
  props: {
    services: { type: Array, default: () => [] },
    selected: { type: Array, required: true },   // [{ additional_service_id, price, notes }]
    loading: { type: Boolean, default: false },
    showErrors: { type: Boolean, default: false },
    error: { type: Boolean, default: false }
  },
  emits: ['update:selected'],
  methods: {
    isSelected(id) {
      return this.selected.some(l => l.additional_service_id === id);
    },
    nameOf(id) {
      return this.services.find(s => s.id === id)?.name || '';
    },
    priceInvalid(line) {
      return line.price === '' || line.price === null || Number(line.price) < 0 || Number.isNaN(Number(line.price));
    },
    toggle(service) {
      if (this.isSelected(service.id)) return this.remove(service.id);
      this.$emit('update:selected', [
        ...this.selected,
        { additional_service_id: service.id, price: Number(service.default_price), notes: '' }
      ]);
    },
    remove(id) {
      this.$emit('update:selected', this.selected.filter(l => l.additional_service_id !== id));
    },
    update(id, key, value) {
      this.$emit('update:selected', this.selected.map(l =>
        l.additional_service_id === id ? { ...l, [key]: value } : l
      ));
    }
  }
};
</script>