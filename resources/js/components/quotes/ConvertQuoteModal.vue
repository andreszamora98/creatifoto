<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-6" @click="handleOverlayClick">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-7xl max-h-[92vh] flex flex-col overflow-hidden" @click.stop>

        <div class="bg-gray-100 dark:bg-gray-700/60 rounded-t-lg flex-shrink-0">
          <div class="flex justify-between items-center px-6 py-3">
            <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Convertir {{ quoteCode }} a orden</h3>
            <button @click="requestCancel" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
              <i class="ti ti-x text-2xl"></i>
            </button>
          </div>
        </div>

        <div class="px-6 pt-6 pb-1 flex-shrink-0 border-b border-gray-200 dark:border-gray-700">
          <StepIndicator :steps="stepLabels" :current-step="currentStep" />
        </div>

        <div class="flex-1 overflow-y-auto overflow-x-hidden px-6 py-6">
          <transition :name="transitionName" mode="out-in">
            <ConvertDeliveryStep
              v-if="currentStep === 1"
              key="step-1"
              :delivery-date="deliveryDate"
              :delivery-time="deliveryTime"
              :show-errors="showErrorsStep1"
              @update:deliveryDate="deliveryDate = $event"
              @update:deliveryTime="deliveryTime = $event"
            />

            <ChargePaymentLayout v-else key="step-2">
              <template #summary>
                <ConvertChargeSummary
                  :quote="quote"
                  :estimated-total="estimatedTotal"
                />
              </template>

              <template #payment>
                <PaymentStep
                  :section-badge="null"
                  section-title=""
                  :payments="payments"
                  :estimated-total="estimatedTotal"
                  :remaining-balance="remainingBalance"
                  :liquidate-all="liquidateAll"
                  :show-errors="showErrorsStep2"
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
              </template>
            </ChargePaymentLayout>
          </transition>
        </div>

        <div class="bg-gray-100 dark:bg-gray-700/60 rounded-b-lg flex gap-3 px-6 py-4 flex-shrink-0 border-t border-gray-200 dark:border-gray-700">
          <button v-if="currentStep === 1" @click="requestCancel" class="flex-1 py-3 text-lg rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Cancelar</button>
          <button v-else @click="goBack" class="flex-1 py-3 text-lg rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">
            <i class="ti ti-chevron-left mr-1"></i>Atrás
          </button>

          <button v-if="currentStep === 1" @click="goNext" class="flex-1 py-3 text-lg rounded-lg bg-blue-500 hover:bg-blue-600 text-white">Continuar</button>
          <button v-else @click="confirmConvert" :disabled="submitting" class="flex-1 py-3 text-lg rounded-lg bg-blue-500 text-white disabled:opacity-50">
            {{ submitting ? 'Creando...' : 'Confirmar y crear orden' }}
          </button>
        </div>
      </div>
    </div>
  </transition>

  <ConfirmModal
    :show="showCancelConfirm"
    title="Cancelar conversión"
    message="¿Estás seguro de cancelar la conversión?"
    confirm-text="Sí, cancelar"
    cancel-text="No, continuar"
    @confirm="confirmCancel"
    @cancel="showCancelConfirm = false"
  />

  <SuccessModal :show="showSuccessModal" :title="successModalTitle" :message="successModalMessage" @closed="onSuccessClosed" />
  <AlertModal :show="showAlertModal" title="Atención" :message="alertMessage" @closed="showAlertModal = false" />
</template>

<script>
import axios from 'axios';
import paymentsMixin from '../order-quote-shared/paymentsMixin.js';
import StepIndicator from '../order-quote-shared/StepIndicator.vue';
import ChargePaymentLayout from '../order-quote-shared/ChargePaymentLayout.vue';
import ConvertDeliveryStep from './form-steps/ConvertDeliveryStep.vue';
import ConvertChargeSummary from './form-steps/ConvertChargeSummary.vue';
import PaymentStep from '../orders/form-steps/PaymentStep.vue';
import ConfirmModal from '../common/ConfirmModal.vue';
import SuccessModal from '../common/SuccessModal.vue';
import AlertModal from '../common/AlertModal.vue';

export default {
  name: 'ConvertQuoteModal',
  mixins: [paymentsMixin],
  components: {
    StepIndicator, ChargePaymentLayout, ConvertDeliveryStep, ConvertChargeSummary,
    PaymentStep, ConfirmModal, SuccessModal, AlertModal
  },
  props: {
    show: { type: Boolean, default: false },
    quote: { type: Object, default: null }
  },
  emits: ['close', 'converted', 'notify'],
  data() {
    return {
      currentStep: 1,
      navDirection: 'forward',
      deliveryDate: '',
      deliveryTime: '16:00',
      payments: [{ amount: null, payment_method: 'tarjeta' }],
      submitting: false,
      stepAttempted: { 1: false, 2: false },
      showCancelConfirm: false,
      showAlertModal: false,
      alertMessage: '',
      showSuccessModal: false,
      successModalTitle: '',
      successModalMessage: '',
      successNext: null
    };
  },
  computed: {
    stepLabels() {
      return [
        { label: 'Fecha y hora' },
        { label: 'Cobro y pago' }
      ];
    },
    transitionName() {
      return this.navDirection === 'forward' ? 'slide-next' : 'slide-prev';
    },
    quoteCode() {
      return 'C-' + String(this.quote?.id || 0).padStart(3, '0');
    },
    estimatedTotal() {
      return Number(this.quote?.total || 0);
    },
    showErrorsStep1() {
      return this.stepAttempted[1];
    },
    showErrorsStep2() {
      return this.stepAttempted[2];
    },
    step1Valid() {
      return !!this.deliveryDate && !!this.deliveryTime;
    },
    step2Valid() {
      return this.payments.length > 0 && this.payments.every(p => p.amount > 0 && p.payment_method);
    }
  },
  watch: {
    show(val) {
      if (val) {
        this.currentStep = 1;
        this.navDirection = 'forward';
        this.deliveryDate = this.getNextBusinessDay();
        this.deliveryTime = '16:00';
        this.payments = [{ amount: null, payment_method: 'tarjeta' }];
        this.liquidateAll = false;
        this.openPaymentMethodDropdownIdx = null;
        this.stepAttempted = { 1: false, 2: false };
        this.submitting = false;
        this.showCancelConfirm = false;
      }
    }
  },
  methods: {
    getNextBusinessDay(from = new Date()) {
      const next = new Date(from.getFullYear(), from.getMonth(), from.getDate() + 1);
      if (next.getDay() === 0) next.setDate(next.getDate() + 1);
      const y = next.getFullYear();
      const m = String(next.getMonth() + 1).padStart(2, '0');
      const d = String(next.getDate()).padStart(2, '0');
      return `${y}-${m}-${d}`;
    },
    goNext() {
      this.stepAttempted[1] = true;
      if (!this.step1Valid) return;
      this.navDirection = 'forward';
      this.currentStep = 2;
    },
    goBack() {
      this.navDirection = 'backward';
      this.currentStep = 1;
    },
    requestCancel() {
      this.showCancelConfirm = true;
    },
    confirmCancel() {
      this.showCancelConfirm = false;
      this.$emit('close');
    },
    handleOverlayClick() {
      if (this.currentStep === 1) this.requestCancel();
    },
    openSuccess(title, message, next = null) {
      this.successModalTitle = title;
      this.successModalMessage = message;
      this.successNext = next;
      this.showSuccessModal = true;
    },
    onSuccessClosed() {
      this.showSuccessModal = false;
      const next = this.successNext;
      this.successNext = null;
      if (typeof next === 'function') next();
    },
    async confirmConvert() {
      this.stepAttempted[2] = true;
      if (!this.step2Valid) {
        this.alertMessage = 'Completa el monto y método de pago antes de continuar.';
        this.showAlertModal = true;
        return;
      }
      this.submitting = true;
      try {
        const payload = {
          estimated_delivery_date: this.deliveryDate,
          estimated_delivery_time: this.deliveryTime,
          payments: this.payments.map(p => ({ amount: p.amount, payment_method: p.payment_method }))
        };
        const res = await axios.post(`/quotes/${this.quote.id}/convert`, payload);
        this.openSuccess('¡Orden creada!', 'La orden se registró correctamente a partir de la cotización.', () => {
          this.$emit('converted', res.data.order);
        });
      } catch (error) {
        this.alertMessage = error.response?.data?.message || 'No se pudo crear la orden';
        this.showAlertModal = true;
      } finally {
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