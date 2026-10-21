<template>
  <section class="space-y-3 sm:space-y-6 short:space-y-2">
    <h4 v-if="sectionTitle" class="text-sm sm:text-base short:text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 flex items-center gap-2">
      <span v-if="sectionBadge !== null" class="w-6 h-6 sm:w-8 sm:h-8 short:w-5 short:h-5 rounded-full bg-blue-500 text-white text-xs sm:text-sm short:text-[10px] flex items-center justify-center font-bold">{{ sectionBadge }}</span>
      {{ sectionTitle }}
    </h4>

    <div
      class="space-y-2 sm:space-y-3 short:space-y-2"
      :data-error-anchor="conceptSectionError ? '' : null"
    >
      <h5 class="text-xs sm:text-sm short:text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
        Conceptos de la orden
      </h5>

      <div>
        <div
          class="relative z-10 flex w-full lg:w-fit rounded-t-lg bg-gray-200 dark:bg-gray-800"
          role="tablist"
        >
          <button
            v-for="(tab, index) in tabs"
            :key="tab.key"
            type="button"
            role="tab"
            :id="`order-tab-${tab.key}`"
            :aria-selected="activeTab === tab.key"
            :aria-controls="`order-panel-${tab.key}`"
            @click="activeTab = tab.key"
            class="relative flex-1 min-w-0 lg:flex-initial flex flex-col sm:flex-row justify-center items-center gap-0.5 sm:gap-2.5
            short:gap-2 px-2 sm:px-5 short:px-4 py-2.5 sm:py-3 short:py-2 text-sm sm:text-xl short:text-base font-medium rounded-t-lg"
            :class="tabClasses(tab, index)"
          >
            <i :class="['ti', tab.icon, 'text-lg sm:text-2xl short:text-xl flex-shrink-0']"></i>
            <span class="min-w-0 text-center">{{ tab.label }}</span>
            <span
              v-if="tab.count > 0"
              class=" absolute top-1 right-1 sm:static flex-shrink-0 min-w-[1.25rem] sm:min-w-[1.9rem] short:min-w-[1.6rem] h-5 sm:h-8 short:h-6 px-1 sm:px-2.5 short:px-1.5 rounded-full text-white text-xs sm:text-base short:text-sm font-bold flex items-center justify-center"
              :class="activeTab === tab.key ? 'bg-blue-500' : 'bg-gray-400 dark:bg-gray-600'"
            >{{ tab.count }}</span>
          </button>
        </div>

        <div class="relative bg-gray-50 dark:bg-gray-700 rounded-b-lg lg:rounded-tr-lg p-2 sm:p-3 short:p-2 transition-none">
          <div
            v-show="activeTab === 'packages'"
            class="absolute top-0 left-0 w-2 h-2 bg-gray-200 dark:bg-gray-800 pointer-events-none"
            aria-hidden="true"
          >
            <div class="w-full h-full rounded-tl-lg bg-gray-50 dark:bg-gray-700"></div>
          </div>

          <div
            v-show="activeTab === 'photos'"
            id="order-panel-photos"
            role="tabpanel"
            aria-labelledby="order-tab-photos"
          >
            <PhotoTypeLinesEditor
              :lines="photoLines"
              :photo-types="photoTypes"
              :paper-types="paperTypes"
              :backgrounds="backgrounds"
              :loading="loadingCatalogs"
              :show-errors="showErrors"
              :error="linesError || contentError"
              title=""
              empty-hint="Sin tipos de fotografía. Puedes dejarlo vacío si la orden lleva paquetes escolares."
            />
          </div>

          <div
            v-show="activeTab === 'packages'"
            id="order-panel-packages"
            role="tabpanel"
            aria-labelledby="order-tab-packages"
          >
            <PackageLinesEditor
              :lines="packageLines"
              :packages="packages"
              :schools="schools"
              :paper-types="paperTypes"
              :loading="loadingCatalogs"
              :show-errors="showErrors"
              :error="contentError || packageLinesError"
              title=""
              empty-hint="Sin paquetes escolares. Puedes dejarlo vacío si la orden lleva tipos de fotografía."
              @view-package-details="openPackageDetails"
            />
          </div>

          <p v-if="showErrors && contentError" class="mt-2 sm:mt-3 short:mt-1.5 text-sm sm:text-lg short:text-sm text-red-500 flex items-center gap-1.5 sm:gap-2">
            <i class="ti ti-alert-circle text-base sm:text-xl short:text-base"></i>
            Agrega al menos un tipo de fotografía o un paquete escolar para continuar.
          </p>
        </div>
      </div>
    </div>

    <div
      v-if="showAdditionalServices"
      :data-error-anchor="serviceSectionError ? '' : null"
    >
      <AdditionalServiceLinesEditor
        :lines="serviceLines"
        :services="additionalServices"
        :loading="loadingCatalogs"
        :show-errors="showErrors"
        :error="serviceLinesError || fieldErrors.service_prices || false"
        title="Servicios adicionales"
        empty-hint="Sin servicios adicionales agregados."
      />
    </div>

    <div
      class="space-y-2 sm:space-y-3 short:space-y-2"
      :data-error-anchor="deliverySectionError ? '' : null"
    >
      <h5 class="text-xs sm:text-sm short:text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
        Entrega
      </h5>

      <div class="grid grid-cols-1 md:grid-cols-7 gap-3 sm:gap-6 short:gap-2">
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

    <div v-if="showEstimatedTotal" class="flex items-center justify-between bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 sm:p-5 short:p-2">
      <span class="text-sm sm:text-lg short:text-sm font-semibold text-blue-800 dark:text-blue-300">Total estimado</span>
      <span class="text-lg sm:text-2xl short:text-base font-bold text-blue-800 dark:text-blue-300">${{ estimatedTotal.toFixed(2) }}</span>
    </div>

    <PackageDetailsModal
      :show="showPackageDetailsModal"
      :package="packageForDetails"
      @close="closePackageDetails"
    />
  </section>
</template>

<script>
import DeliveryDateTimeFields from '../DeliveryDateTimeFields.vue';
import PhotoTypeLinesEditor from '../photo-lines/PhotoTypeLinesEditor.vue';
import PackageLinesEditor from '../PackageLinesEditor.vue';
import AdditionalServiceLinesEditor from '../AdditionalServiceLinesEditor.vue';
import NotesField from '../NotesField.vue';
import PackageDetailsModal from '../../packages/PackageDetailsModal.vue';

export default {
  name: 'OrderDetailsStep',
  components: {
    DeliveryDateTimeFields, PhotoTypeLinesEditor, PackageLinesEditor,
    AdditionalServiceLinesEditor, NotesField, PackageDetailsModal
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
    backgrounds: { type: Array, default: () => [] },
    packages: { type: Array, default: () => [] },
    packageLines: { type: Array, required: true },
    schools: { type: Array, default: () => [] },
    showAdditionalServices: { type: Boolean, default: false },
    additionalServices: { type: Array, default: () => [] },
    serviceLines: { type: Array, required: true },
    estimatedTotal: { type: Number, default: 0 },
    showEstimatedTotal: { type: Boolean, default: true },
    validationTick: { type: Number, default: 0 }
  },
  data() {
    return {
      activeTab: 'photos',
      showPackageDetailsModal: false,
      packageForDetails: null
    };
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
    },
    packageLinesError() {
      return this.fieldErrors.package_lines ?? false;
    },
    serviceLinesError() {
      return this.fieldErrors.service_lines ?? false;
    },
    conceptSectionError() {
      return this.linesError || this.packageLinesError || this.contentError;
    },
    serviceSectionError() {
      return this.serviceLinesError || this.fieldErrors.service_prices;
    },
    deliverySectionError() {
      return this.fieldErrors.delivery_date || this.fieldErrors.delivery_time;
    }
  },
  watch: {
    showErrors(val) {
      if (val) this.handleValidationError();
    },
    validationTick() {
      if (this.showErrors) this.handleValidationError();
    }
  },
  methods: {
    emitFormUpdate(key, value) {
      this.$emit('update:form', { ...this.form, [key]: value });
    },
    showDivider(index) {
      if (index === 0) return false;
      const current = this.tabs[index].key;
      const previous = this.tabs[index - 1].key;
      return this.activeTab !== current && this.activeTab !== previous;
    },
    tabHasError(tab) {
      if (!this.showErrors) return false;
      if (tab.key === 'photos') return this.linesError || this.contentError;
      if (tab.key === 'packages') return this.packageLinesError || this.contentError;
      return false;
    },
    tabClasses(tab, index) {
      const isActive = this.activeTab === tab.key;
      const hasError = this.tabHasError(tab);

      const divider = this.showDivider(index)
        ? 'before:absolute before:left-0 before:top-3 before:bottom-3 before:w-px before:bg-gray-300 dark:before:bg-gray-600'
        : '';

      let stateClasses;
      if (isActive && hasError) {
        stateClasses = 'bg-gray-50 dark:bg-gray-700 text-red-600 dark:text-red-400 after:absolute after:inset-x-0 after:-bottom-px after:h-px after:bg-red-500 dark:after:bg-red-400';
      } else if (isActive) {
        stateClasses = 'bg-gray-50 dark:bg-gray-700 text-blue-600 dark:text-blue-400 after:absolute after:inset-x-0 after:-bottom-px after:h-px after:bg-gray-50 dark:after:bg-gray-700';
      } else if (hasError) {
        stateClasses = 'text-red-500 dark:text-red-400 hover:text-red-600 dark:hover:text-red-300';
      } else {
        stateClasses = 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300';
      }

      return [divider, stateClasses];
    },
    jumpToErrorTab() {
      if (this.linesError && this.activeTab !== 'photos') {
        this.activeTab = 'photos';
        return;
      }
      if (this.packageLinesError && this.activeTab !== 'packages') {
        this.activeTab = 'packages';
        return;
      }
      if (this.contentError && this.activeTab !== 'photos') {
        this.activeTab = 'photos';
      }
    },
    async handleValidationError() {
      this.jumpToErrorTab();
      await this.$nextTick();
      this.scrollToFirstError();
    },
    scrollToFirstError() {
      const root = this.$el;
      if (!root) return;
      const el = root.querySelector('[data-error-anchor]');
      if (!el) return;
      el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    },
    openPackageDetails(pkg) {
      this.packageForDetails = pkg;
      this.showPackageDetailsModal = true;
    },
    closePackageDetails() {
      this.showPackageDetailsModal = false;
      this.packageForDetails = null;
    }
  }
};
</script>