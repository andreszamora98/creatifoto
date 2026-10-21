<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-3 sm:p-4 lg:p-6 short:p-0">
      <div class="bg-white dark:bg-gray-800 shadow-xl w-full max-w-7xl h-auto max-h-[92dvh] short:h-full short:max-h-full rounded-lg short:rounded-none flex flex-col overflow-hidden">

        <div class="bg-gray-100 dark:bg-gray-700/60 rounded-t-lg short:rounded-none flex-shrink-0">
          <div class="flex justify-between items-center px-3 sm:px-6 short:px-3 py-2 sm:py-3 short:py-0.5">
            <h3 class="text-base sm:text-2xl short:text-sm font-bold text-gray-800 dark:text-gray-200 truncate">
              <i class="ti ti-file-description mr-1.5 sm:mr-2 short:mr-1"></i>Nueva cotización
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

        <div class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden overscroll-contain px-3 sm:px-6 short:px-3 py-3 sm:py-6 short:py-1.5" @input="markDirty" @change="markDirty" @click="markDirty">
          <transition :name="transitionName" mode="out-in">
            <CustomerStep
              v-if="currentStep === 1"
              key="step-1"
              section-title=""
              :on-phone-blur="onPhoneBlur"
              :clear-phone="clearPhone"
              :clear-email="clearEmail"
              :customer-search="customerSearch"
              :customer-phone="customerPhone"
              :customer-email="customerEmail"
              :selected-customer="selectedCustomer"
              :filtered-customers="filteredCustomers"
              :exact-customer-match="exactCustomerMatch"
              :show-customer-dropdown="showCustomerDropdown"
              :show-errors="showErrorsStep1"
              :field-errors="fieldErrors"
              :editing-phone="editingPhone"
              :editing-email="editingEmail"
              :can-confirm-phone="canConfirmPhone"
              :phone-error="phoneError"
              :phone-exists="phoneExists"
              :email-changed="emailChanged"
              :email-error="emailError"
              :email-exists="emailExists"
              :open-customer-dropdown="openCustomerDropdown"
              :hide-customer-dropdown="hideCustomerDropdown"
              :clear-customer-search="clearCustomerSearch"
              :select-customer="selectCustomer"
              :toggle-edit-phone="toggleEditPhone"
              :confirm-phone-change="confirmPhoneChange"
              :cancel-phone-edit="cancelPhoneEdit"
              :toggle-edit-email="toggleEditEmail"
              :confirm-email-change="confirmEmailChange"
              :cancel-email-edit="cancelEmailEdit"
              @update:customerSearch="customerSearch = $event"
              @update:customerPhone="customerPhone = $event"
              @update:customerEmail="customerEmail = $event"
            />

            <OrderDetailsStep
              v-else-if="currentStep === 2"
              key="step-2"
              section-title=""
              :form="form"
              :show-errors="showErrorsStep2"
              :field-errors="fieldErrors"
              :loading-catalogs="loadingCatalogs"
              :photo-types="photoTypes"
              :photo-lines="photoLines"
              :paper-types="paperTypes"
              :backgrounds="backgrounds"
              :packages="packages"
              :package-lines="packageLines"
              :schools="schools"
              :validation-tick="attemptTick"
              show-additional-services
              :additional-services="additionalServices"
              :service-lines="serviceLines"
              :show-estimated-total="false"
              hide-delivery-date
              @update:form="form = $event"
            />
            <SummaryStep
              v-else
              key="step-3"
              section-title=""
              :active-lines="activeLines"
              :photo-types="photoTypes"
              :paper-types="paperTypes"
              :selected-packages="selectedPackagesData"
              :selected-services="selectedServices"
              :additional-services="additionalServices"
              :estimated-total="estimatedTotal"
              :discount-type="discount_type"
              :discount-value="discount_value"
              :discount-reason="discount_reason"
              :valid-until="validUntil"
              @update:validUntil="validUntil = $event"
            >
              <template #discount>
                <DiscountPanel
                  :type="discount_type"
                  :value="discount_value"
                  :reason="discount_reason"
                  :show-errors="showErrorsStep3"
                  :error="fieldErrors.discount_value"
                  @update:type="discount_type = $event"
                  @update:value="discount_value = $event"
                  @update:reason="discount_reason = $event"
                />
              </template>
            </SummaryStep>
          </transition>
        </div>

        <div class="bg-gray-100 dark:bg-gray-700/60 rounded-b-lg short:rounded-none flex gap-2 sm:gap-4 short:gap-2 px-3 sm:px-6 short:px-3 py-2 sm:py-4 short:py-1 short:pb-[max(0.25rem,env(safe-area-inset-bottom))] flex-shrink-0 border-t border-gray-200 dark:border-gray-700">
          <button @click="handleClose" class="flex-1 btn btn-light py-2 sm:py-4 short:py-1.5 text-sm sm:text-lg short:text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition-colors">
            Cancelar
          </button>
          <button v-if="currentStep > 1" @click="goBack" type="button"
                  class="flex-1 btn btn-light py-2 sm:py-4 short:py-1.5 text-sm sm:text-lg short:text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition-colors font-medium flex items-center justify-center gap-1">
            <i class="ti ti-chevron-left"></i><span>Atrás</span>
          </button>
          <button v-if="currentStep < 3" @click="goNext" type="button"
                  class="flex-1 btn btn-primary py-2 sm:py-4 short:py-1.5 text-sm sm:text-lg short:text-sm rounded-lg bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-medium transition-colors flex items-center justify-center gap-1">
            Siguiente<i class="ti ti-chevron-right"></i>
          </button>
          <button v-else @click="submit" :disabled="submitting" class="flex-1 btn btn-primary py-2 sm:py-4 short:py-1.5 text-sm sm:text-lg short:text-sm rounded-lg bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-1">
            <span v-if="submitting"><i class="ti ti-refresh animate-spin"></i><span class="hidden sm:inline">Guardando...</span></span>
            <span v-else><i class="ti ti-check"></i><span class="hidden sm:inline">Guardar cotización</span><span class="sm:hidden">Guardar</span></span>
          </button>
        </div>

      </div>
    </div>
  </transition>

  <ConfirmModal
    :show="showConfirmModal"
    title="¿Cerrar sin guardar?"
    message="Hay cambios sin guardar. ¿Estás seguro de que quieres cerrar esta ventana?"
    confirm-text="Cerrar de todos modos"
    cancel-text="Seguir editando"
    @confirm="closeConfirmed"
    @cancel="showConfirmModal = false"
  />

  <ConfirmModal
    :show="showMissingContactModal"
    title="¿Estás seguro que quieres continuar?"
    message="No se ha agregado ningún dato de contacto del cliente"
    confirm-text="Continuar"
    cancel-text="Seguir editando"
    @confirm="confirmContinueWithoutContact"
    @cancel="cancelContinueWithoutContact"
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
import CustomerStep from '../order-quote-shared/form-steps/CustomerStep.vue';
import OrderDetailsStep from '../order-quote-shared/form-steps/OrderDetailsStep.vue';
import StepIndicator from '../order-quote-shared/StepIndicator.vue';
import SummaryStep from './form-steps/SummaryStep.vue';
import DiscountPanel from '../order-quote-shared/DiscountPanel.vue';
import orderQuoteFormMixin from '../order-quote-shared/orderQuoteFormMixin.js';

export default {
  name: 'QuoteModal',
  mixins: [orderQuoteFormMixin],
  components: { ConfirmModal, SuccessModal, AlertModal, CustomerStep, OrderDetailsStep, StepIndicator, SummaryStep, DiscountPanel },
  props: {
    show: { type: Boolean, default: false }
  },
  emits: ['close', 'created', 'notify'],
  data() {
    return {
      validUntil: this.defaultValidUntil(),
      form: { notes: '' }
    };
  },
  computed: {
    stepLabels() {
      return [
        { label: 'Cliente' },
        { label: 'Detalles' },
        { label: 'Resumen' }
      ];
    },
    showErrorsStep3() {
      return this.submittingAttempted || this.stepAttempted[3];
    },
    fieldErrors() {
      const hasCustomer = !!this.selectedCustomer || this.customerSearch.trim().length > 0;
      const hasEmptyPhotoLine = this.photoLines.some(l => !l.photo_type_id);
      const hasEmptyPackageLine = this.packageLines.some(l => !l.package_id);
      const hasEmptyServiceLine = this.serviceLines.some(l => !l.service_id);
      const hasContent = this.activeLines.length > 0 || this.selectedPackageIds.length > 0;
      return {
        customer: !hasCustomer,
        order_content: !hasContent,
        photo_lines: hasEmptyPhotoLine,
        package_lines: hasEmptyPackageLine,
        service_lines: hasEmptyServiceLine,
        service_prices: this.selectedServices.some(s =>
          s.price === '' || s.price === null || Number.isNaN(Number(s.price)) || Number(s.price) < 0
        ),
        discount_value: this.discountTypeHasError()
      };
    },
    currentStepValid() {
      const keysByStep = {
        1: ['customer'],
        2: ['order_content', 'photo_lines', 'package_lines', 'service_lines', 'service_prices'],
        3: ['discount_value']
      };
      return this.keysValid(keysByStep[this.currentStep] || []);
    }
  },
  watch: {
    show(val) {
      if (val && !this.validUntil) {
        this.validUntil = this.defaultValidUntil();
      }
    }
  },
  methods: {
    discountTypeHasError() {
      if (!this.discount_type) return false;
      if (this.discount_value === null || this.discount_value === undefined || this.discount_value === '') return true;
      const v = Number(this.discount_value);
      if (!Number.isFinite(v) || v < 0) return true;
      if (this.discount_type === 'percent' && v > 100) return true;
      return false;
    },
    defaultValidUntil() {
      const d = new Date();
      d.setDate(d.getDate() + 7);
      const y = d.getFullYear();
      const m = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      return `${y}-${m}-${day}`;
    },
    getCurrentState() {
      return {
        ...this.getSharedState(),
        validUntil: this.validUntil,
        notes: this.form.notes
      };
    },
    resetForm() {
      this.resetSharedFields();
      this.validUntil = this.defaultValidUntil();
      this.form = { notes: '' };
    },
    async submit() {
      if (!this.runSharedSubmitChecks()) return;
      if (this.submitting) return;
      this.submitting = true;
      try {
        const payload = {
          ...this.resolveCustomerPayload(),
          ...this.resolveDiscountPayload(),
          valid_until: this.validUntil || null,
          notes: this.form.notes,
          photo_types: this.activeLines,
          package_ids: this.selectedPackageIds,
          additional_services: this.activeServices,
        };
        const res = await axios.post('/quotes', payload);
        this.$emit('created', res.data.quote);
        this.openSuccess('¡Cotización creada!', 'La cotización se registró correctamente.', () => {
          this.isDirty = false;
          this.close();
        });
      } catch (error) {
        const data = error.response?.data;
        const firstValidationError = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
        this.$emit('notify', {
          title: 'Error',
          message: firstValidationError || data?.message || 'No se pudo crear la cotización',
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