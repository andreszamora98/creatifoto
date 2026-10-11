<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-start justify-center z-50 p-2 sm:p-4 lg:p-6 short:p-0 overflow-y-auto short:items-center short:overflow-hidden">
      <div class="bg-white dark:bg-gray-800 shadow-xl w-full max-w-7xl h-auto rounded-lg short:rounded-none flex flex-col my-auto short:my-0 short:h-full short:overflow-hidden">

        <div class="bg-gray-100 dark:bg-gray-700/60 rounded-t-lg short:rounded-none flex-shrink-0">
          <div class="flex justify-between items-center px-3 sm:px-6 short:px-3 py-2 sm:py-3 short:py-0.5">
            <h3 class="text-base sm:text-2xl short:text-sm font-bold text-gray-800 dark:text-gray-200 truncate">
              <i class="ti ti-package mr-1.5 sm:mr-2 short:mr-1"></i>Nuevo paquete
            </h3>
            <button
              @click="handleClose"
              aria-label="Cerrar"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1.5 sm:p-2 short:p-1 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex-shrink-0"
            >
              <i class="ti ti-x text-lg sm:text-2xl short:text-base"></i>
            </button>
          </div>
        </div>

        <div class="px-3 sm:px-6 short:px-3 pt-3 sm:pt-6 short:pt-1 pb-0 flex-shrink-0 border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
          <StepIndicator :steps="stepLabels" :current-step="currentStep" />
        </div>

        <div class="px-3 sm:px-6 short:px-3 py-3 sm:py-6 short:py-1.5 short:flex-1 short:min-h-0 short:overflow-y-auto short:overflow-x-hidden short:overscroll-contain">
          <transition :name="transitionName" mode="out-in">
            <PackageInfoStep
              v-if="currentStep === 1"
              key="step-1"
              section-title=""
              :form="form"
              :schools="schools"
              :loading-catalogs="loadingCatalogs"
              :show-errors="showErrorsStep1"
              :field-errors="fieldErrors"
              :next-package-number="nextPackageNumber"
              @update:form="form = $event"
              @new-school="showSchoolModal = true"
            />

            <section v-else-if="currentStep === 2" key="step-2" class="space-y-3 sm:space-y-6 short:space-y-2">
              <div>
                <PhotoTypeLinesEditor
                  :lines="photoLines"
                  :photo-types="photoTypes"
                  :paper-types="paperTypes"
                  :loading="loadingCatalogs"
                  :show-errors="showErrorsStep2"
                  :error="photoLinesHasError"
                  :error-message="photoLinesErrorMessage"
                  title="Tipos de fotografía incluidos"
                  icon="ti ti-camera"
                  empty-hint="Agrega al menos un tipo de fotografía."
                />
              </div>

              <div>
                <AdditionalServiceLinesEditor
                  :lines="serviceLines"
                  :services="additionalServices"
                  :loading="loadingCatalogs"
                  :show-errors="showErrorsStep2"
                  :error="fieldErrors.service_prices"
                  title="Servicios adicionales"
                  empty-hint="Sin servicios adicionales agregados."
                />
              </div>

              <div class="space-y-2 sm:space-y-3 short:space-y-2">
                <h5 class="text-xs sm:text-sm short:text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                  Características
                </h5>

                <div>
                  <div
                    class="relative z-10 flex w-full lg:w-fit rounded-t-lg bg-gray-200 dark:bg-gray-800"
                    role="tablist"
                  >
                    <button
                      v-for="(tab, index) in featureTabs"
                      :key="tab.key"
                      type="button"
                      role="tab"
                      :aria-selected="featureTab === tab.key"
                      @click="featureTab = tab.key"
                      class="relative flex-1 min-w-0 lg:flex-initial flex justify-center items-center gap-2 sm:gap-2.5 short:gap-2 px-3.5 sm:px-5 short:px-4 py-2.5 sm:py-3 short:py-2 text-base sm:text-xl short:text-base font-medium rounded-t-lg"
                      :class="featureTabClasses(tab, index)"
                    >
                      <i :class="['ti', tab.icon, 'text-xl sm:text-2xl short:text-xl flex-shrink-0']"></i>
                      <span class="min-w-0 text-center">{{ tab.label }}</span>
                    </button>
                  </div>

                  <div class="relative bg-gray-50 dark:bg-gray-700 rounded-b-lg lg:rounded-tr-lg p-2 sm:p-3 short:p-2 transition-none">
                    <div
                      v-show="featureTab === 'men'"
                      class="absolute top-0 left-0 w-2 h-2 bg-gray-200 dark:bg-gray-800 pointer-events-none"
                      aria-hidden="true"
                    >
                      <div class="w-full h-full rounded-tl-lg bg-gray-50 dark:bg-gray-700"></div>
                    </div>

                    <div v-show="featureTab === 'women'" role="tabpanel">
                      <textarea
                        :value="form.women_features_text"
                        @input="updateFeature('women_features_text', $event.target.value)"
                        rows="4"
                        placeholder="Describe las características incluidas para este grupo..."
                        class="w-full text-sm sm:text-base short:text-sm p-2.5 sm:p-3 short:p-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 resize-y min-h-[5rem] sm:min-h-[7rem] short:min-h-[4rem]"
                      ></textarea>
                    </div>

                    <div v-show="featureTab === 'men'" role="tabpanel">
                      <textarea
                        :value="form.men_features_text"
                        @input="updateFeature('men_features_text', $event.target.value)"
                        rows="4"
                        placeholder="Describe las características incluidas para este grupo..."
                        class="w-full text-sm sm:text-base short:text-sm p-2.5 sm:p-3 short:p-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 resize-y min-h-[5rem] sm:min-h-[7rem] short:min-h-[4rem]"
                      ></textarea>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <ChargePaymentLayout v-else key="step-3">
              <template #summary>
                <PackageSummary
                  :school="selectedSchool"
                  :package-number="nextPackageNumber"
                  :name="form.name"
                  :lines="priceLines"
                  :services="summaryServices"
                  :women-text="form.women_features_text"
                  :men-text="form.men_features_text"
                />
              </template>

              <template #payment>
                <PackagePriceBreakdown
                  :pricing="pricing"
                  :calculated-subtotal="calculatedSubtotal"
                  :subtotal="subtotal"
                  :discount-amount="discountAmount"
                  :total="total"
                  :show-errors="showErrorsStep3"
                  :subtotal-error="fieldErrors.price_subtotal"
                  :discount-error="fieldErrors.price_discount"
                  :discount-error-message="discountErrorMessage"
                  @update:pricing="pricing = $event"
                />
              </template>
            </ChargePaymentLayout>
          </transition>
        </div>

        <div class="bg-gray-100 dark:bg-gray-700/60 rounded-b-lg short:rounded-none flex gap-2 sm:gap-4 short:gap-2 px-3 sm:px-6 short:px-3 py-2 sm:py-4 short:py-1 short:pb-[max(0.25rem,env(safe-area-inset-bottom))] flex-shrink-0 border-t border-gray-200 dark:border-gray-700">
          <button @click="handleClose" class="flex-1 btn btn-light py-2 sm:py-4 short:py-1.5 text-sm sm:text-lg short:text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition-colors">
            Cancelar
          </button>
          <button v-if="currentStep > 1" @click="goBack" type="button"
                  class="flex-1 btn btn-light py-2 sm:py-4 short:py-1.5 text-sm sm:text-lg short:text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition-colors font-medium flex items-center justify-center gap-1">
            <i class="ti ti-chevron-left"></i>Atrás
          </button>
          <button v-if="currentStep < totalSteps" @click="goNext" type="button"
                  class="flex-1 btn btn-primary py-2 sm:py-4 short:py-1.5 text-sm sm:text-lg short:text-sm rounded-lg bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-medium transition-colors flex items-center justify-center gap-1">
            Siguiente<i class="ti ti-chevron-right"></i>
          </button>
          <button v-else @click="submit" :disabled="submitting" class="flex-1 btn btn-primary py-2 sm:py-4 short:py-1.5 text-sm sm:text-lg short:text-sm rounded-lg bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-1">
            <span v-if="submitting"><i class="ti ti-refresh animate-spin"></i><span class="hidden sm:inline">Creando...</span></span>
            <span v-else><i class="ti ti-check"></i><span class="hidden sm:inline">Crear paquete</span><span class="sm:hidden">Crear</span></span>
          </button>
        </div>

      </div>
    </div>
  </transition>

  <SchoolModal
    :show="showSchoolModal"
    @close="showSchoolModal = false"
    @created="handleSchoolCreated"
    @notify="$emit('notify', $event)"
  />

  <ConfirmModal
    :show="showConfirmModal"
    title="¿Cerrar sin guardar?"
    message="Hay cambios sin guardar. ¿Estás seguro de que quieres cerrar esta ventana?"
    confirm-text="Cerrar de todos modos"
    cancel-text="Seguir editando"
    @confirm="closeConfirmed"
    @cancel="showConfirmModal = false"
  />

  <SuccessModal
    :show="showSuccessModal"
    :title="successModalTitle"
    :message="successModalMessage"
    @closed="onSuccessClosed"
  />
  <AlertModal
    :show="showAlertModal"
    title="Atención"
    :message="alertMessage"
    @closed="showAlertModal = false"
  />
</template>

<script>
import axios from 'axios';
import ConfirmModal from '../common/ConfirmModal.vue';
import SuccessModal from '../common/SuccessModal.vue';
import AlertModal from '../common/AlertModal.vue';
import StepIndicator from '../order-quote-shared/StepIndicator.vue';
import ChargePaymentLayout from '../order-quote-shared/ChargePaymentLayout.vue';
import PhotoTypeLinesEditor from '../order-quote-shared/photo-lines/PhotoTypeLinesEditor.vue';
import AdditionalServiceLinesEditor from '../order-quote-shared/AdditionalServiceLinesEditor.vue';
import SchoolModal from './SchoolModal.vue';
import PackageInfoStep from './form-steps/PackageInfoStep.vue';
import PackageSummary from './form-steps/PackageSummary.vue';
import PackagePriceBreakdown from './form-steps/PackagePriceBreakdown.vue';
import packageWizardMixin from './packageWizardMixin.js';

const round2 = (n) => Math.round((Number(n) + Number.EPSILON) * 100) / 100;
const pad2 = (n) => String(n ?? '').padStart(2, '0');
const isBlank = (v) => v === '' || v === null || v === undefined;

const emptyForm = () => ({
  school_id: null,
  name: '',
  women_features_text: '',
  men_features_text: ''
});

const emptyPricing = () => ({
  subtotal_override: '',
  discount_type: 'percent',
  discount_value: ''
});

export default {
  name: 'PackageWizardModal',
  mixins: [packageWizardMixin],
  components: {
    ConfirmModal, SuccessModal, AlertModal, StepIndicator, ChargePaymentLayout,
    PhotoTypeLinesEditor, AdditionalServiceLinesEditor, SchoolModal,
    PackageInfoStep, PackageSummary, PackagePriceBreakdown
  },
  props: {
    show: { type: Boolean, default: false }
  },
  emits: ['close', 'created', 'notify', 'school-created'],
  data() {
    return {
      showSchoolModal: false,
      featureTab: 'women',
      form: emptyForm(),
      photoLines: [],
      serviceLines: [],
      pricing: emptyPricing()
    };
  },
  computed: {
    stepLabels() {
      return [
        { label: 'Información del paquete' },
        { label: 'Armado del paquete' },
        { label: 'Precio y resumen' }
      ];
    },

    featureTabs() {
      return [
        { key: 'women', label: 'Mujeres', icon: 'ti-woman' },
        { key: 'men', label: 'Hombres', icon: 'ti-man' }
      ];
    },

    selectedSchool() {
      return this.schools.find(s => Number(s.id) === Number(this.form.school_id)) || null;
    },

    nextPackageNumber() {
      if (!this.form.school_id) return null;
      const numbers = this.existingPackages
        .filter(p => Number(p.school_id) === Number(this.form.school_id))
        .map(p => Number(p.package_number) || 0);
      return (numbers.length ? Math.max(...numbers) : 0) + 1;
    },

    nameMissing() {
      return !String(this.form.name || '').trim();
    },

    nameDuplicated() {
      if (this.nameMissing || !this.form.school_id) return false;
      const target = String(this.form.name || '').trim().toLowerCase();
      return this.existingPackages.some(p =>
        Number(p.school_id) === Number(this.form.school_id)
        && String(p.name || '').trim().toLowerCase() === target
      );
    },

    activeLines() {
      return this.photoLines
        .filter(l => l.photo_type_id)
        .map(l => ({
          photo_type_id: l.photo_type_id,
          quantity: Number.isFinite(Number(l.quantity)) && Number(l.quantity) >= 1
            ? Math.floor(Number(l.quantity))
            : 1,
          format: l.format === 'color' ? 'color' : 'bn',
          paper_type_id: l.paper_type_id || null
        }));
    },
    hasDuplicatePhotoTypes() {
      const keys = this.activeLines.map(l => `${l.photo_type_id}|${l.format}|${l.paper_type_id ?? ''}`);
      return new Set(keys).size !== keys.length;
    },
    activeServices() {
      return this.serviceLines
        .filter(l => l.service_id)
        .map(l => {
          const svc = this.additionalServices.find(s => s.id === l.service_id);
          const unitPrice = Number(svc?.default_price || 0);
          const quantity = Number.isFinite(Number(l.quantity)) && Number(l.quantity) >= 1
            ? Math.floor(Number(l.quantity))
            : 1;
          return {
            additional_service_id: l.service_id,
            price: round2(unitPrice),
            quantity,
            subtotal: round2(unitPrice * quantity),
            notes: ''
          };
        });
    },
    servicePricesInvalid() {
      return this.serviceLines.some(l =>
        l.service_id && !this.additionalServices.find(s => s.id === l.service_id)
      );
    },

    priceLines() {
      return this.activeLines.map(l => {
        const pt = this.photoTypes.find(p => p.id === l.photo_type_id);
        const paper = l.paper_type_id ? this.paperTypes.find(p => p.id === l.paper_type_id) : null;
        const unitPrice = Number(pt?.base_price || 0);
        return {
          photo_type_id: l.photo_type_id,
          name: pt?.name || '',
          quantity: l.quantity,
          format: l.format,
          paper_name: paper?.name || '',
          unit_price: unitPrice,
          subtotal: round2(unitPrice * l.quantity)
        };
      });
    },
    summaryServices() {
      return this.activeServices.map(s => ({
        additional_service_id: s.additional_service_id,
        name: this.additionalServices.find(x => x.id === s.additional_service_id)?.name || '',
        price: s.price,
        quantity: s.quantity,
        subtotal: s.subtotal,
        notes: s.notes
      }));
    },
    calculatedSubtotal() {
      const photos = this.priceLines.reduce((sum, l) => sum + l.subtotal, 0);
      const services = this.activeServices.reduce((sum, s) => sum + s.subtotal, 0);
      return round2(photos + services);
    },
    subtotalOverridden() {
      return !isBlank(this.pricing.subtotal_override);
    },
    subtotal() {
      if (!this.subtotalOverridden) return this.calculatedSubtotal;
      const v = Number(this.pricing.subtotal_override);
      return Number.isFinite(v) ? round2(Math.max(0, v)) : 0;
    },
    discountValueNumber() {
      const v = Number(this.pricing.discount_value);
      return Number.isFinite(v) ? v : 0;
    },
    discountAmount() {
      const v = this.discountValueNumber;
      const raw = this.pricing.discount_type === 'percent' ? (this.subtotal * v) / 100 : v;
      return round2(Math.min(Math.max(raw, 0), this.subtotal));
    },
    total() {
      return Math.max(0, round2(this.subtotal - this.discountAmount));
    },

    discountErrorMessage() {
      if (this.pricing.discount_type === 'percent') return 'El porcentaje debe estar entre 0 y 100.';
      return 'El descuento debe ser 0 o mayor y no puede superar el subtotal.';
    },
    discountInvalid() {
      if (isBlank(this.pricing.discount_value)) return false;
      const v = Number(this.pricing.discount_value);
      if (!Number.isFinite(v) || v < 0) return true;
      if (this.pricing.discount_type === 'percent') return v > 100;
      return v > this.subtotal;
    },
    subtotalInvalid() {
      if (!this.subtotalOverridden) return false;
      const v = Number(this.pricing.subtotal_override);
      return !Number.isFinite(v) || v < 0;
    },

    fieldErrors() {
      return {
        school: !this.form.school_id,
        name: this.nameMissing,
        name_duplicated: this.nameDuplicated,
        photo_content: this.activeLines.length === 0,
        photo_lines: this.photoLines.some(l => !l.photo_type_id),
        photo_duplicates: this.hasDuplicatePhotoTypes,
        service_prices: this.servicePricesInvalid,
        price_subtotal: this.subtotalInvalid,
        price_discount: this.discountInvalid
      };
    },
    stepKeys() {
      return {
        1: ['school', 'name', 'name_duplicated'],
        2: ['photo_content', 'photo_lines', 'photo_duplicates', 'service_prices'],
        3: ['price_subtotal', 'price_discount']
      };
    },
    currentStepValid() {
      return this.keysValid(this.stepKeys[this.currentStep] || []);
    },

    photoLinesHasError() {
      return this.fieldErrors.photo_content || this.fieldErrors.photo_lines || this.fieldErrors.photo_duplicates;
    },
    photoLinesErrorMessage() {
      if (this.fieldErrors.photo_content) return 'Agrega al menos un tipo de fotografía.';
      if (this.fieldErrors.photo_duplicates) return 'Hay líneas repetidas (mismo tipo, formato y papel). Déjalas en una sola línea.';
      return 'Selecciona un tipo en cada línea o elimina las vacías.';
    }
  },
  methods: {
    featureTabClasses(tab, index) {
      const isActive = this.featureTab === tab.key;
      const divider = index > 0
        ? 'before:absolute before:left-0 before:top-3 before:bottom-3 before:w-px before:bg-gray-300 dark:before:bg-gray-600'
        : '';
      let stateClasses;
      if (isActive) {
        stateClasses = 'bg-gray-50 dark:bg-gray-700 text-blue-600 dark:text-blue-400 after:absolute after:inset-x-0 after:-bottom-px after:h-px after:bg-gray-50 dark:after:bg-gray-700';
      } else {
        stateClasses = 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300';
      }
      return [divider, stateClasses];
    },
    updateFeature(key, value) {
      this.form = { ...this.form, [key]: value };
    },

    getValidationMessage() {
      /*
      if (this.fieldErrors.name) {
        return 'El nombre del paquete es obligatorio.';
      }
      */
      if (this.fieldErrors.name_duplicated) {
        return 'Ya existe un paquete con ese nombre en esta escuela.';
      }
      if (this.fieldErrors.photo_duplicates) {
        return 'Hay líneas repetidas (mismo tipo, formato y papel). Déjalas en una sola línea.';
      }
      if (this.fieldErrors.service_prices) {
        return 'El precio de cada servicio adicional debe ser 0 o mayor.';
      }
      if (this.fieldErrors.price_subtotal) {
        return 'El subtotal debe ser 0 o mayor.';
      }
      if (this.fieldErrors.price_discount) {
        return this.discountErrorMessage;
      }
      return 'Por favor, completa todos los campos marcados con * antes de continuar.';
    },

    getCurrentState() {
      return {
        form: { ...this.form },
        photoLines: JSON.parse(JSON.stringify(this.photoLines)),
        serviceLines: JSON.parse(JSON.stringify(this.serviceLines)),
        pricing: { ...this.pricing }
      };
    },

    resetForm() {
      this.resetWizardState();
      this.featureTab = 'women';
      this.form = emptyForm();
      this.photoLines = [];
      this.serviceLines = [];
      this.pricing = emptyPricing();
      this.showSchoolModal = false;
    },

    handleSchoolCreated(school) {
      this.schools.push(school);
      this.form = { ...this.form, school_id: school.id };
      this.showSchoolModal = false;
      this.$emit('school-created', school);
    },

    async submit() {
      if (!this.runSubmitChecks()) return;
      if (this.submitting) return;
      this.submitting = true;
      try {
        const payload = {
          school_id: this.form.school_id,
          name: String(this.form.name || '').trim(),
          women_features_text: this.form.women_features_text,
          men_features_text: this.form.men_features_text,
          photo_types: this.activeLines,
          additional_services: this.activeServices,
          subtotal: this.subtotal,
          discount_type: this.pricing.discount_type,
          discount_value: this.discountValueNumber,
          discount_amount: this.discountAmount,
          total_price: this.total
        };
        const res = await axios.post('/packages', payload);
        this.$emit('created', res.data.package);
        this.openSuccess('¡Paquete creado!', 'El paquete se registró correctamente.', () => {
          this.close();
        });
      } catch (error) {
        const data = error.response?.data;
        const firstValidationError = data?.errors
          ? Object.values(data.errors)[0]?.[0]
          : null;
        this.$emit('notify', {
          title: 'Error',
          message: firstValidationError || data?.message || 'No se pudo crear el paquete',
          type: 'error'
        });
        this.submitting = false;
      }
    }
  }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-next-enter-active, .slide-next-leave-active,
.slide-prev-enter-active, .slide-prev-leave-active {
  transition: opacity 0.28s ease, transform 0.28s ease;
}
.slide-next-enter-from { opacity: 0; transform: translateX(32px); }
.slide-next-leave-to { opacity: 0; transform: translateX(-32px); }
.slide-prev-enter-from { opacity: 0; transform: translateX(-32px); }
.slide-prev-leave-to { opacity: 0; transform: translateX(32px); }
</style>