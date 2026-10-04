<template>
  <section class="space-y-6">
    <h4 v-if="sectionTitle" class="text-base font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 flex items-center gap-2">
      <span v-if="sectionBadge !== null" class="w-8 h-8 rounded-full bg-blue-500 text-white text-sm flex items-center justify-center font-bold">{{ sectionBadge }}</span>
      {{ sectionTitle }}
    </h4>

    <!-- 1. Conceptos de la orden -->
    <div class="space-y-3">
      <h5 class="text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
        Conceptos de la orden
      </h5>

      <div class="flex gap-2 border-b border-gray-200 dark:border-gray-700" role="tablist">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          type="button"
          role="tab"
          :aria-selected="activeTab === tab.key"
          @click="activeTab = tab.key"
          class="flex items-center gap-2 px-4 py-2.5 -mb-px border-b-2 text-lg font-medium transition-colors"
          :class="activeTab === tab.key
            ? 'border-blue-500 text-blue-600 dark:text-blue-400 dark:border-blue-400'
            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
        >
          <i :class="['ti', tab.icon, 'text-xl']"></i>
          <span>{{ tab.label }}</span>
          <span
            v-if="tab.count > 0"
            class="min-w-[1.75rem] h-7 px-2 rounded-full bg-blue-500 text-white text-sm font-bold flex items-center justify-center"
          >{{ tab.count }}</span>
        </button>
      </div>

      <div v-show="activeTab === 'photos'" role="tabpanel">
        <PhotoTypeLinesEditor
          :lines="photoLines"
          :photo-types="photoTypes"
          :paper-types="paperTypes"
          :loading="loadingCatalogs"
          :show-errors="showErrors"
          :error="linesError"
          title=""
          empty-hint="Sin tipos de fotografía. Puedes dejarlo vacío si la orden lleva paquetes escolares."
        />
      </div>

      <div v-show="activeTab === 'packages'" role="tabpanel">
        <PackagesSelector
          :packages="packages"
          v-model:selected-ids="selectedPackageIdsProxy"
          :loading="loadingCatalogs"
        />
      </div>

      <p v-if="showErrors && contentError" class="text-sm text-red-500 flex items-center gap-1">
        <i class="ti ti-alert-circle"></i>
        Agrega al menos un tipo de fotografía o un paquete escolar para continuar.
      </p>
    </div>

    <!-- 2. Servicios adicionales -->
    <AdditionalServicesSelector
      v-if="showAdditionalServices"
      :services="additionalServices"
      v-model:selected="selectedServicesProxy"
      :loading="loadingCatalogs"
      :show-errors="showErrors"
      :error="fieldErrors.service_prices || false"
    />

    <!-- 3. Entrega -->
    <div class="space-y-3">
      <h5 class="text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
        Entrega
      </h5>

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
    </div>

    <div v-if="showEstimatedTotal" class="flex items-center justify-between bg-blue-50 dark:bg-blue-900/20 rounded-lg p-5">
      <span class="text-lg font-semibold text-blue-800 dark:text-blue-300">Total estimado</span>
      <span class="text-2xl font-bold text-blue-800 dark:text-blue-300">${{ estimatedTotal.toFixed(2) }}</span>
    </div>
  </section>
</template>

<script>
import DeliveryDateTimeFields from '../DeliveryDateTimeFields.vue';
import PhotoTypeLinesEditor from '../photo-lines/PhotoTypeLinesEditor.vue';
import PackagesSelector from '../PackagesSelector.vue';
import AdditionalServicesSelector from '../AdditionalServicesSelector.vue';
import NotesField from '../NotesField.vue';

export default {
  name: 'OrderDetailsStep',
  components: {
    DeliveryDateTimeFields, PhotoTypeLinesEditor, PackagesSelector,
    AdditionalServicesSelector, NotesField
  },
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
    showAdditionalServices: { type: Boolean, default: false },
    additionalServices: { type: Array, default: () => [] },
    selectedServices: { type: Array, default: () => [] },
    estimatedTotal: { type: Number, default: 0 },
    showEstimatedTotal: { type: Boolean, default: true }
  },
  emits: ['update:selectedPackageIds', 'update:selectedServices', 'update:form'],
  data() {
    return { activeTab: 'photos' };
  },
  computed: {
    selectedPackageIdsProxy: {
      get() { return this.selectedPackageIds; },
      set(value) { this.$emit('update:selectedPackageIds', value); }
    },
    selectedServicesProxy: {
      get() { return this.selectedServices; },
      set(value) { this.$emit('update:selectedServices', value); }
    },
    photoCount() {
      return this.photoLines.filter(l => l.photo_type_id).length;
    },
    tabs() {
      return [
        { key: 'photos', label: 'Tipos de fotografía', icon: 'ti-camera', count: this.photoCount },
        { key: 'packages', label: 'Paquetes escolares', icon: 'ti ti-school', count: this.selectedPackageIds.length }
      ];
    },
    contentError() {
      return this.fieldErrors.order_content ?? this.fieldErrors.photo_types ?? false;
    },
    linesError() {
      return this.fieldErrors.photo_lines ?? false;
    }
  },
  watch: {
    showErrors(val) {
      if (val && this.linesError) this.activeTab = 'photos';
    }
  },
  methods: {
    emitFormUpdate(key, value) {
      this.$emit('update:form', { ...this.form, [key]: value });
    }
  }
};
</script>