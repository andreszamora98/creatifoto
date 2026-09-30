<template>
  <section class="space-y-7">
    <h4 v-if="sectionTitle" class="text-base font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 flex items-center gap-2">
      <span v-if="sectionBadge !== null" class="w-8 h-8 rounded-full bg-blue-500 text-white text-sm flex items-center justify-center font-bold">{{ sectionBadge }}</span>
      {{ sectionTitle }}
    </h4>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
      <PhotoTypeLinesEditor
        :lines="photoLines"
        :photo-types="photoTypes"
        :paper-types="paperTypes"
        :loading="loadingCatalogs"
        :show-errors="showErrors"
        :error="fieldErrors.photo_types"
        title="Tipos de fotografía"
        empty-hint="Sin tipos de fotografía. Se puede dejar vacío si la orden lleva paquetes escolares."
      />

      <PackagesSelector
        :packages="packages"
        v-model:selected-ids="selectedPackageIdsProxy"
        :loading="loadingCatalogs"
      />
    </div>

    <div class="flex items-center justify-between bg-blue-50 dark:bg-blue-900/20 rounded-lg p-5">
      <span class="text-lg font-semibold text-blue-800 dark:text-blue-300">Total estimado</span>
      <span class="text-2xl font-bold text-blue-800 dark:text-blue-300">${{ estimatedTotal.toFixed(2) }}</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-7 gap-6">
      <div v-if="!hideDeliveryDate" class="md:col-span-2">
        <DeliveryDateTimeFields
          stacked
          :delivery-date="form.estimated_delivery_date"
          :delivery-time="form.estimated_delivery_time"
          :show-errors="showErrors && (fieldErrors.delivery_date || fieldErrors.delivery_time)"
          @update:deliveryDate="emitFormUpdate('estimated_delivery_date', $event)"
          @update:deliveryTime="emitFormUpdate('estimated_delivery_time', $event)"
        />
      </div>

      <div :class="hideDeliveryDate ? 'md:col-span-7' : 'md:col-span-5'">
        <NotesField
          :model-value="form.notes"
          @update:modelValue="emitFormUpdate('notes', $event)"
        />
      </div>
    </div>
  </section>
</template>

<script>
import DeliveryDateTimeFields from '../DeliveryDateTimeFields.vue';
import PhotoTypeLinesEditor from '../photo-lines/PhotoTypeLinesEditor.vue';
import PackagesSelector from '../PackagesSelector.vue';
import NotesField from '../NotesField.vue';

export default {
  name: 'OrderDetailsStep',
  components: { DeliveryDateTimeFields, PhotoTypeLinesEditor, PackagesSelector, NotesField },
  props: {
    sectionBadge: { type: [Number, String], default: 2 },
    sectionTitle: { type: String, default: 'Datos de la orden' },
    hideDeliveryDate: { type: Boolean, default: false },
    form: { type: Object, required: true },
    showErrors: { type: Boolean, default: false },
    fieldErrors: { type: Object, required: true },
    loadingCatalogs: { type: Boolean, default: false },
    photoTypes: { type: Array, default: () => [] },
    photoLines: { type: Array, required: true },
    paperTypes: { type: Array, default: () => [] },
    packages: { type: Array, default: () => [] },
    selectedPackageIds: { type: Array, required: true },
    estimatedTotal: { type: Number, default: 0 }
  },
  emits: ['update:selectedPackageIds', 'update:form'],
  computed: {
    selectedPackageIdsProxy: {
      get() { return this.selectedPackageIds; },
      set(value) { this.$emit('update:selectedPackageIds', value); }
    }
  },
  methods: {
    emitFormUpdate(key, value) {
      this.$emit('update:form', { ...this.form, [key]: value });
    }
  }
};
</script>