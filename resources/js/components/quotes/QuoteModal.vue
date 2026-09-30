<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-6">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-7xl max-h-[85vh] flex flex-col overflow-hidden">

        <div class="bg-gray-100 dark:bg-gray-700/60 rounded-t-lg flex-shrink-0">
          <div class="flex justify-between items-center px-6 py-3">
            <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
              <i class="ti ti-file-description mr-2"></i>Nueva cotización
            </h3>
            <button
              @click="handleClose"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
            >
              <i class="ti ti-x text-2xl"></i>
            </button>
          </div>
        </div>

        <div class="px-6 pt-6 pb-0 flex-shrink-0 border-b border-gray-200 dark:border-gray-700">
          <StepIndicator :steps="stepLabels" :current-step="currentStep" />
        </div>

        <div class="flex-1 overflow-y-auto overflow-x-hidden px-6 py-6" @input="markDirty" @change="markDirty" @click="markDirty">
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
              :packages="packages"
              v-model:selected-package-ids="selectedPackageIds"
              :estimated-total="estimatedTotal"
              @update:form="form = $event"
              hide-delivery-date
            />
            <SummaryStep
              v-else
              key="step-3"
              section-title=""
              :active-lines="activeLines"
              :photo-types="photoTypes"
              :selected-packages="selectedPackagesData"
              :estimated-total="estimatedTotal"
              :valid-until="validUntil"
              @update:validUntil="validUntil = $event"
            />
          </transition>
        </div>

        <div class="bg-gray-100 dark:bg-gray-700/60 rounded-b-lg flex gap-4 px-6 py-4 flex-shrink-0 border-t border-gray-200 dark:border-gray-700">
          <button @click="handleClose" class="flex-1 btn btn-light py-4 text-lg rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition-colors">
            Cancelar
          </button>
          <button v-if="currentStep > 1" @click="goBack" type="button"
                  class="flex-1 btn btn-light py-4 text-lg rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition-colors font-medium">
            <i class="ti ti-chevron-left mr-1"></i>Atrás
          </button>
          <button v-if="currentStep < 3" @click="goNext" type="button"
                  class="flex-1 btn btn-primary py-4 text-lg rounded-lg bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-medium transition-colors">
            Siguiente<i class="ti ti-chevron-right ml-1"></i>
          </button>
          <button v-else @click="submit" :disabled="submitting" class="flex-1 btn btn-primary py-4 text-lg rounded-lg bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <span v-if="submitting"><i class="ti ti-refresh animate-spin mr-2"></i>Guardando...</span>
            <span v-else><i class="ti ti-check mr-2"></i>Guardar cotización</span>
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
import orderQuoteFormMixin from '../order-quote-shared/orderQuoteFormMixin.js';

export default {
  name: 'QuoteModal',
  mixins: [orderQuoteFormMixin],
  components: { ConfirmModal, SuccessModal, AlertModal, CustomerStep, OrderDetailsStep, StepIndicator, SummaryStep },
  props: {
    show: { type: Boolean, default: false }
  },
  emits: ['close', 'created', 'notify'],
  data() {
    return {
      validUntil: '',
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
    fieldErrors() {
      const hasCustomer = !!this.selectedCustomer || this.customerSearch.trim().length > 0;
      const hasEmptyPhotoLine = this.photoLines.some(l => !l.photo_type_id);
      const hasContent = this.activeLines.length > 0 || this.selectedPackageIds.length > 0;
      return {
        customer: !hasCustomer,
        photo_types: hasEmptyPhotoLine || !hasContent,
      };
    },
    currentStepValid() {
      const keysByStep = { 1: ['customer'], 2: ['photo_types'], 3: [] };
      return this.keysValid(keysByStep[this.currentStep] || []);
    }
  },
  methods: {
    getCurrentState() {
      return {
        ...this.getSharedState(),
        validUntil: this.validUntil,
        notes: this.form.notes
      };
    },
    resetForm() {
      this.resetSharedFields();
      this.validUntil = '';
      this.form = { notes: '' };
    },
    async submit() {
      if (!this.runSharedSubmitChecks()) return;
      if (this.submitting) return;
      this.submitting = true;
      try {
        const payload = {
          ...this.resolveCustomerPayload(),
          valid_until: this.validUntil || null,
          notes: this.form.notes,
          photo_types: this.activeLines,
          package_ids: this.selectedPackageIds,
        };
        const res = await axios.post('/quotes', payload);
        this.$emit('created', res.data.quote);
        this.openSuccess('¡Cotización creada!', 'La cotización se registró correctamente.', () => {
          this.isDirty = false;
          this.close();
        });
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: error.response?.data?.message || 'No se pudo crear la cotización', type: 'error' });
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