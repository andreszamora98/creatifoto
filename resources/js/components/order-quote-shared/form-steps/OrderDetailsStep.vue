<template>
  <section class="space-y-6">
    <h4 v-if="sectionTitle" class="text-base font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 flex items-center gap-2">
      <span v-if="sectionBadge !== null" class="w-8 h-8 rounded-full bg-blue-500 text-white text-sm flex items-center justify-center font-bold">{{ sectionBadge }}</span>
      {{ sectionTitle }}
    </h4>

    <div class="space-y-3">
      <h5 class="text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
        Conceptos de la orden
      </h5>

      <div>
        <div
          class="relative z-10 flex w-fit rounded-t-lg bg-gray-200 dark:bg-gray-800"
          role="tablist"
        >
          <button
            v-for="(tab, index) in tabs"
            :key="tab.key"
            type="button"
            role="tab"
            :aria-selected="activeTab === tab.key"
            @click="activeTab = tab.key"
            class="relative flex items-center gap-2 px-4 py-2.5 text-lg font-medium rounded-t-lg transition-colors"
            :class="[
              index > 0
                ? 'before:absolute before:left-0 before:top-3 before:bottom-3 before:w-px before:bg-gray-300 dark:before:bg-gray-600'
                : '',
              activeTab === tab.key
                ? 'bg-gray-50 dark:bg-gray-700 text-blue-600 dark:text-blue-400 after:absolute after:inset-x-0 after:-bottom-px after:h-px after:bg-gray-50 dark:after:bg-gray-700'
                : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'
            ]"
          >
            <i :class="['ti', tab.icon, 'text-xl']"></i>
            <span>{{ tab.label }}</span>
            <span
              v-if="tab.count > 0"
              class="min-w-[1.75rem] h-7 px-2 rounded-full text-white text-sm font-bold flex items-center justify-center transition-colors"
              :class="activeTab === tab.key ? 'bg-blue-500' : 'bg-gray-400 dark:bg-gray-600'"
            >{{ tab.count }}</span>
          </button>
        </div>

        <div class="relative bg-gray-50 dark:bg-gray-700 rounded-b-lg rounded-tr-lg p-3 space-y-3">
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
            <PackageLinesEditor
              :lines="packageLines"
              :packages="packages"
              :schools="schools"
              :loading="loadingCatalogs"
              :show-errors="showErrors"
              :error="contentError"
              title=""
              empty-hint="Sin paquetes escolares. Puedes dejarlo vacío si la orden lleva tipos de fotografía."
            />
          </div>

          <p v-if="showErrors && contentError" class="text-sm text-red-500 flex items-center gap-1">
            <i class="ti ti-alert-circle"></i>
            Agrega al menos un tipo de fotografía o un paquete escolar para continuar.
          </p>
        </div>
      </div>
    </div>

    <AdditionalServiceLinesEditor
      v-if="showAdditionalServices"
      :lines="serviceLines"
      :services="additionalServices"
      :loading="loadingCatalogs"
      :show-errors="showErrors"
      :error="fieldErrors.service_prices || false"
      title="Servicios adicionales"
      empty-hint="Sin servicios adicionales agregados."
    />

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
import PackageLinesEditor from '../PackageLinesEditor.vue';
import AdditionalServiceLinesEditor from '../AdditionalServiceLinesEditor.vue';
import NotesField from '../NotesField.vue';

export default {
  name: 'OrderDetailsStep',
  components: {
    DeliveryDateTimeFields, PhotoTypeLinesEditor, PackageLinesEditor,
    AdditionalServiceLinesEditor, NotesField
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
    packageLines: { type: Array, required: true },
    schools: { type: Array, default: () => [] },
    showAdditionalServices: { type: Boolean, default: false },
    additionalServices: { type: Array, default: () => [] },
    serviceLines: { type: Array, required: true },
    estimatedTotal: { type: Number, default: 0 },
    showEstimatedTotal: { type: Boolean, default: true }
  },
  data() {
    return { activeTab: 'photos' };
  },
  computed: {
    photoCount() {
      return this.photoLines.filter(l => l.photo_type_id).length;
    },
    packageCount() {
      return this.packageLines.filter(l => l.package_id).length;
    },
    tabs() {
      return [
        { key: 'photos', label: 'Tipos de fotografía', icon: 'ti-camera', count: this.photoCount },
        { key: 'packages', label: 'Paquetes escolares', icon: 'ti-school', count: this.packageCount }
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