<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-6">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-5xl max-h-[85vh] overflow-y-auto">
        <div class="p-8">
          <div class="flex justify-between items-start mb-3">
            <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-200">
              <i class="ti ti-plus mr-2"></i>Nueva orden
            </h3>
            <button
              @click="handleClose"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            >
              <i class="ti ti-x text-3xl"></i>
            </button>
          </div>

          <div class="flex items-center justify-center gap-3 mb-6">
            <template v-for="n in 3" :key="n">
              <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-base font-bold transition-colors"
                     :class="n === currentStep
                       ? 'bg-blue-500 text-white'
                       : n < currentStep
                         ? 'bg-green-500 text-white'
                         : 'bg-gray-200 dark:bg-gray-600 text-gray-500 dark:text-gray-300'">
                  <i v-if="n < currentStep" class="ti ti-check"></i>
                  <span v-else>{{ n }}</span>
                </div>
              </div>
              <div v-if="n < 3" class="w-10 h-1 rounded"
                   :class="n < currentStep ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"></div>
            </template>
          </div>

          <div @input="markDirty" @change="markDirty" @click="markDirty">
            <transition :name="transitionName" mode="out-in">
              <CustomerStep
                v-if="currentStep === 1"
                key="step-1"
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
                :form="form"
                :show-errors="showErrorsStep2"
                :field-errors="fieldErrors"
                :loading-catalogs="loadingCatalogs"
                :photo-types="photoTypes"
                :lines="lines"
                :paper-types="paperTypes"
                :packages="packages"
                v-model:selected-package-ids="selectedPackageIds"
                :estimated-total="estimatedTotal"
                :open-format-dropdown-id="openFormatDropdownId"
                :toggle-date-picker="toggleDatePicker"
                :close-date-picker="closeDatePicker"
                :toggle-photo-type="togglePhotoType"
                :on-quantity-blur="onQuantityBlur"
                :toggle-format-dropdown="toggleFormatDropdown"
                :close-format-dropdown="closeFormatDropdown"
                :select-format="selectFormat"
                @update:form="form = $event"

              />

              <PaymentStep
                v-else
                key="step-3"
                :payments="payments"
                :estimated-total="estimatedTotal"
                :remaining-balance="remainingBalance"
                :liquidate-all="liquidateAll"
                :show-errors="showErrorsStep3"
                :payment-concept="paymentConcept"
                :show-balance-badge="showBalanceBadge"
                :open-payment-method-dropdown-idx="openPaymentMethodDropdownIdx"
                :toggle-liquidate-all="toggleLiquidateAll"
                :on-payment-amount-input="onPaymentAmountInput"
                :toggle-payment-method-dropdown="togglePaymentMethodDropdown"
                :close-payment-method-dropdown="closePaymentMethodDropdown"
                :select-line-payment-method="selectLinePaymentMethod"
                :add-payment="addPayment"
                :remove-payment="removePayment"
              />
            </transition>
          </div>

          <div class="flex gap-4 mt-6">
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
              <span v-if="submitting"><i class="ti ti-refresh animate-spin mr-2"></i>Creando...</span>
              <span v-else><i class="ti ti-check mr-2"></i>Crear orden</span>
            </button>
          </div>

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
import PaymentStep from './form-steps/PaymentStep.vue';
import orderQuoteFormMixin from '../order-quote-shared/orderQuoteFormMixin.js';

export default {
  name: 'OrderModal',
  mixins: [orderQuoteFormMixin],
  components: { ConfirmModal, SuccessModal, AlertModal, CustomerStep, OrderDetailsStep, PaymentStep },
  props: {
    show: { type: Boolean, default: false }
  },
  emits: ['close', 'created', 'notify'],
  data() {
    return {
      liquidateAll: false,
      openPaymentMethodDropdownIdx: null,
      payments: [{ amount: null, payment_method: '' }],
      form: {
        estimated_delivery_date: '',
        estimated_delivery_time: '',
        notes: ''
      }
    };
  },
  computed: {
    totalPaid() {
      return this.payments.reduce((sum, p) => sum + (Number(p.amount) || 0), 0);
    },
    remainingBalance() {
      return Math.max(this.estimatedTotal - this.totalPaid, 0);
    },
    paymentConcept() {
      return this.remainingBalance > 0 ? 'Anticipo' : 'De contado';
    },
    showBalanceBadge() {
      return this.estimatedTotal > 0 && this.payments.length > 0;
    },
    showErrorsStep3() {
      return this.submittingAttempted || this.stepAttempted[3];
    },
    fieldErrors() {
      const hasCustomer = !!this.selectedCustomer || this.customerSearch.trim().length > 0;
      return {
        customer: !hasCustomer,
        delivery_date: !this.form.estimated_delivery_date,
        delivery_time: !this.form.estimated_delivery_time,
        photo_types: this.activeLines.length === 0,
        initial_payment_amount: this.payments.length === 0 || this.payments.some(p => !p.amount || p.amount <= 0),
        initial_payment_method: this.payments.some(p => !p.payment_method),
      };
    },
    currentStepValid() {
      const keysByStep = {
        1: ['customer'],
        2: ['delivery_date', 'delivery_time', 'photo_types'],
        3: ['initial_payment_amount', 'initial_payment_method']
      };
      return this.keysValid(keysByStep[this.currentStep] || []);
    }
  },
  watch: {
    estimatedTotal() {
      if (this.liquidateAll && this.payments.length) {
        this.payments[0].amount = this.estimatedTotal;
      }
    }
  },
  methods: {
    afterCatalogsLoaded() {
      this.setDefaultDeliveryDate();
    },
    formatDateLocal(date) {
      const y = date.getFullYear();
      const m = String(date.getMonth() + 1).padStart(2, '0');
      const d = String(date.getDate()).padStart(2, '0');
      return `${y}-${m}-${d}`;
    },
    setDefaultDeliveryDate() {
      const today = new Date();
      let nextDay = new Date(today);
      nextDay.setDate(nextDay.getDate() + 1);
      if (nextDay.getDay() === 0) {
        nextDay.setDate(nextDay.getDate() + 1);
      }
      this.form.estimated_delivery_date = this.formatDateLocal(nextDay);
      this.form.estimated_delivery_time = '16:00';
    },
    maxAmountFor(idx) {
      const othersSum = this.payments.reduce(
        (sum, p, i) => (i === idx ? sum : sum + (Number(p.amount) || 0)),
        0
      );
      return Math.max(this.estimatedTotal - othersSum, 0);
    },
    toggleLiquidateAll() {
      this.liquidateAll = !this.liquidateAll;
      if (this.liquidateAll) {
        const method = this.payments[0]?.payment_method || '';
        this.payments = [{ amount: this.estimatedTotal, payment_method: method }];
      } else {
        this.payments[0].amount = 0;
      }
      this.markDirty();
    },
    onPaymentAmountInput(idx) {
      const payment = this.payments[idx];
      const amount = Number(payment.amount) || 0;

      if (this.estimatedTotal > 0 && amount >= this.estimatedTotal) {
        payment.amount = this.estimatedTotal;
        this.payments = [payment];
        this.liquidateAll = true;
        this.markDirty();
        return;
      }

      const max = this.maxAmountFor(idx);
      if (amount > max) payment.amount = max;
      if (this.liquidateAll) this.liquidateAll = false;
      this.markDirty();
    },
    addPayment() {
      if (this.remainingBalance <= 0 || this.liquidateAll) return;
      this.payments.push({ amount: null, payment_method: '' });
      this.markDirty();
    },
    removePayment(idx) {
      this.payments.splice(idx, 1);
      this.markDirty();
    },
    togglePaymentMethodDropdown(idx) {
      this.openPaymentMethodDropdownIdx = this.openPaymentMethodDropdownIdx === idx ? null : idx;
    },
    closePaymentMethodDropdown(idx) {
      setTimeout(() => {
        if (this.openPaymentMethodDropdownIdx === idx) this.openPaymentMethodDropdownIdx = null;
      }, 200);
    },
    selectLinePaymentMethod(idx, method) {
      this.payments[idx].payment_method = method;
      this.openPaymentMethodDropdownIdx = null;
      this.markDirty();
    },
    getCurrentState() {
      return {
        ...this.getSharedState(),
        payments: JSON.parse(JSON.stringify(this.payments)),
        form: {
          estimated_delivery_date: this.form.estimated_delivery_date,
          estimated_delivery_time: this.form.estimated_delivery_time,
          notes: this.form.notes,
        }
      };
    },
    resetForm() {
      this.resetSharedFields();
      this.liquidateAll = false;
      this.payments = [{ amount: null, payment_method: '' }];
      this.openPaymentMethodDropdownIdx = null;
      this.form = { estimated_delivery_date: '', estimated_delivery_time: '', notes: '' };
    },
    async submit() {
      if (!this.runSharedSubmitChecks()) return;
      if (this.submitting) return;
      this.submitting = true;
      try {
        const payload = {
          ...this.resolveCustomerPayload(),
          estimated_delivery_date: this.form.estimated_delivery_date,
          estimated_delivery_time: this.form.estimated_delivery_time || null,
          notes: this.form.notes,
          photo_types: this.activeLines,
          package_ids: this.selectedPackageIds,
          payments: this.payments.map(p => ({
            amount: p.amount,
            payment_method: p.payment_method,
            type: 'advance'
          }))
        };
        const res = await axios.post('/orders', payload);
        this.$emit('created', res.data.order);
        this.openSuccess('¡Orden creada!', 'La orden se registró correctamente.', () => {
          this.isDirty = false;
          this.close();
        });
      } catch (error) {
        this.$emit('notify', {
          title: 'Error',
          message: error.response?.data?.message || 'No se pudo crear la orden',
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