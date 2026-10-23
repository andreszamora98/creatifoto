<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-3 sm:p-4 lg:p-6 short:p-0" @click="handleOverlayClick">
      <div class="bg-white dark:bg-gray-800 shadow-xl w-full max-w-7xl h-auto max-h-[92dvh] short:h-full short:max-h-full rounded-lg short:rounded-none flex flex-col overflow-hidden" @click.stop>

        <div class="bg-gray-100 dark:bg-gray-700/60 rounded-t-lg short:rounded-none flex-shrink-0">
          <div class="flex justify-between items-center px-3 sm:px-6 short:px-3 py-2 sm:py-3 short:py-0.5">
            <h3 class="text-base sm:text-2xl short:text-sm font-bold text-gray-800 dark:text-gray-200 truncate">
              Convertir {{ quoteCode }} a orden
            </h3>
            <button @click="requestCancel" aria-label="Cerrar" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1.5 sm:p-2 short:p-1 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex-shrink-0">
              <i class="ti ti-x text-lg sm:text-2xl short:text-base"></i>
            </button>
          </div>
        </div>

        <div class="px-3 sm:px-6 short:px-3 pt-3 sm:pt-6 short:pt-1 pb-0 flex-shrink-0 border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
          <StepIndicator :steps="stepLabels" :current-step="currentStep" />
        </div>

        <div class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden overscroll-contain px-3 sm:px-6 short:px-3 py-3 sm:py-6 short:py-1.5">
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
                <div class="space-y-3">
                  <ConvertChargeSummary
                    :quote="quote"
                    :estimated-total="estimatedTotal"
                    :override-discount="overrideDiscount"
                    :discount-source="discount_source"
                    :discount-includes-packages="discount_applies_to_packages"
                  />

                  <DiscountPanel
                    :source="discount_source"
                    :type="discount_type"
                    :value="discount_value"
                    :reason="discount_reason"
                    :applies-to-packages="discount_applies_to_packages"
                    :suggested-auto="false"
                    :suggested-auto-percent="10"
                    :show-errors="showErrorsStep2"
                    :error="discountValueError"
                    @set-source="setDiscountSource"
                    @update:type="discount_type = $event"
                    @update:value="discount_value = $event"
                    @update:reason="discount_reason = $event"
                    @update:appliesToPackages="discount_applies_to_packages = $event"
                  />
                </div>
              </template>

              <template #payment>
                <PaymentStep
                  :section-badge="null"
                  section-title=""
                  :payments="payments"
                  :net-total="netTotal"
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

        <div class="bg-gray-100 dark:bg-gray-700/60 rounded-b-lg short:rounded-none flex gap-2 sm:gap-4 short:gap-2 px-3 sm:px-6 short:px-3 py-2 sm:py-4 short:py-1 short:pb-[max(0.25rem,env(safe-area-inset-bottom))] flex-shrink-0 border-t border-gray-200 dark:border-gray-700">
          <button v-if="currentStep === 1" @click="requestCancel" class="flex-1 py-2 sm:py-4 short:py-1.5 text-sm sm:text-lg short:text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Cancelar</button>
          <button v-else @click="goBack" class="flex-1 py-2 sm:py-4 short:py-1.5 text-sm sm:text-lg short:text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 flex items-center justify-center gap-1">
            <i class="ti ti-chevron-left"></i>Atrás
          </button>

          <button v-if="currentStep === 1" @click="goNext" class="flex-1 py-2 sm:py-4 short:py-1.5 text-sm sm:text-lg short:text-sm rounded-lg bg-blue-500 hover:bg-blue-600 text-white">Continuar</button>
          <button v-else @click="confirmConvert" :disabled="submitting" class="flex-1 py-2 sm:py-4 short:py-1.5 text-sm sm:text-lg short:text-sm rounded-lg bg-blue-500 text-white disabled:opacity-50 flex items-center justify-center gap-1">
            <span v-if="submitting"><i class="ti ti-refresh animate-spin"></i><span class="hidden sm:inline">Creando...</span></span>
            <span v-else><span class="hidden sm:inline">Confirmar y crear orden</span><span class="sm:hidden">Confirmar</span></span>
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
import DiscountPanel from '../order-quote-shared/DiscountPanel.vue';
import PaymentStep from '../orders/form-steps/PaymentStep.vue';
import ConfirmModal from '../common/ConfirmModal.vue';
import SuccessModal from '../common/SuccessModal.vue';
import AlertModal from '../common/AlertModal.vue';

export default {
  name: 'ConvertQuoteModal',
  mixins: [paymentsMixin],
  components: {
    StepIndicator, ChargePaymentLayout, ConvertDeliveryStep, ConvertChargeSummary,
    DiscountPanel, PaymentStep, ConfirmModal, SuccessModal, AlertModal
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
      successNext: null,
      discount_source: 'none',
      discount_type: null,
      discount_value: null,
      discount_reason: '',
      discount_applies_to_packages: false,
      auto_discount_reason: null
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
    baseDirect() {
      if (!this.quote) return 0;

      const photos = (this.quote.quote_photo_types || []).filter(l => !l.quote_package_id);
      const photoSum = photos.reduce((sum, l) => sum + Number(l.unit_price || 0) * Number(l.quantity || 0), 0);

      const services = (this.quote.services || []).filter(s => !s.quote_package_id);
      const serviceSum = services.reduce((sum, s) => sum + Number(s.price || 0) * Number(s.quantity || 0), 0);

      let base = photoSum + serviceSum;

      if (this.discount_applies_to_packages) {
        const packagesTotal = (this.quote.quote_packages || []).reduce(
          (sum, p) => sum + Number(p.price_snapshot || 0), 0
        );
        base += packagesTotal;
      }

      return base;
    },
    discountAmount() {
      if (!this.discount_type) return 0;
      const v = Number(this.discount_value || 0);
      if (!Number.isFinite(v) || v <= 0) return 0;

      if (this.discount_type === 'percent') {
        const clamped = Math.max(0, Math.min(100, v));
        return Number((this.baseDirect * clamped / 100).toFixed(2));
      }
      if (this.discount_type === 'fixed') {
        return Number(Math.min(v, this.baseDirect).toFixed(2));
      }
      return 0;
    },
    netTotal() {
      return Math.max(0, Number(this.estimatedTotal) - Number(this.discountAmount));
    },
    overrideDiscount() {
      return {
        type: this.discount_type || null,
        value: this.discount_value !== null && this.discount_value !== undefined && this.discount_value !== ''
          ? Number(this.discount_value)
          : null,
        reason: (this.discount_reason || '').trim() || null
      };
    },
    discountValueError() {
      if (!this.discount_type) return false;
      if (this.discount_value === null || this.discount_value === undefined || this.discount_value === '') return true;
      const v = Number(this.discount_value);
      if (!Number.isFinite(v) || v < 0) return true;
      if (this.discount_type === 'percent' && v > 100) return true;
      return false;
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
      if (this.discountValueError) return false;
      return this.payments.length > 0 && this.payments.every(p => Number(p.amount) > 0 && p.payment_method);
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

        this.discount_source = this.quote?.discount_source || 'none';
        this.discount_type = this.quote?.discount_type || null;
        this.discount_value = (this.quote?.discount_value !== null && this.quote?.discount_value !== undefined)
          ? Number(this.quote.discount_value)
          : null;
        this.discount_reason = this.quote?.discount_reason || '';
        this.discount_applies_to_packages = !!this.quote?.discount_applies_to_packages;
        this.auto_discount_reason = this.quote?.auto_discount_reason || null;
      }
    }
  },
  methods: {
    setDiscountSource(source) {
      this.discount_source = source;
      if (source === 'auto') {
        if (this.discount_type === null) {
          this.discount_reason = null;
        }
      } else if (source === 'manual') {
        if (!this.discount_type) {
          this.discount_type = 'percent';
          this.discount_value = 10;
        }
      } else {
        this.discount_type = null;
        this.discount_value = null;
        this.discount_reason = null;
        this.auto_discount_reason = null;
      }
    },
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
          discount_source: this.discount_source || 'none',
          discount_type: this.overrideDiscount.type,
          discount_value: this.overrideDiscount.value,
          discount_reason: this.overrideDiscount.reason,
          discount_applies_to_packages: !!this.discount_applies_to_packages,
          payments: this.payments.map(p => ({ amount: p.amount, payment_method: p.payment_method }))
        };
        const res = await axios.post(`/quotes/${this.quote.id}/convert`, payload);
        this.openSuccess('¡Orden creada!', 'La orden se registró correctamente a partir de la cotización.', () => {
          this.$emit('converted', res.data.order);
        });
      } catch (error) {
        const data = error.response?.data;
        const firstValidationError = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
        this.alertMessage = firstValidationError || data?.message || 'No se pudo crear la orden';
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