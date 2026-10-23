<template>
  <transition name="fade">
    <div
      v-if="show"
      class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-0 sm:p-4 lg:p-6 short:p-2"
      @click="handleOverlayClick"
    >
      <div
        class="bg-white dark:bg-gray-800 shadow-xl w-full max-w-7xl h-full sm:h-auto sm:max-h-[92vh] sm:rounded-lg flex flex-col overflow-hidden"
        @click.stop
      >

        <div class="bg-gray-100 dark:bg-gray-700/60 sm:rounded-t-lg flex-shrink-0">
          <div class="flex justify-between items-center px-3 sm:px-6 py-2 sm:py-3 short:py-1.5">
            <h3 class="text-base sm:text-2xl short:text-sm font-bold text-gray-800 dark:text-gray-200 truncate">
              <i class="ti ti-pencil mr-1.5 sm:mr-2"></i>Editar {{ orderCode }}
            </h3>
            <button
              @click="handleClose"
              aria-label="Cerrar"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1.5 sm:p-2 short:p-1 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex-shrink-0"
            >
              <i class="ti ti-x text-lg sm:text-2xl short:text-lg"></i>
            </button>
          </div>
        </div>

        <div
          class="flex-1 overflow-y-auto overflow-x-hidden px-3 sm:px-6 py-3 sm:py-6 short:py-2"
          @input="markDirty"
          @change="markDirty"
          @click="markDirty"
        >
          <OrderDetailsStep
            section-title=""
            :form="form"
            :show-errors="submittingAttempted"
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
            @update:form="form = $event"
          />

          <div class="mt-4 sm:mt-6 short:mt-3">
            <h5 class="text-xs sm:text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2 sm:mb-3">
              Resumen de la orden
            </h5>
            <OrderEditChargeSummary
              :photo-lines="photoLines"
              :photo-types="photoTypes"
              :paper-types="paperTypes"
              :package-lines="packageLines"
              :packages="packages"
              :service-lines="serviceLines"
              :additional-services="additionalServices"
              :estimated-total="estimatedTotal"
              :initial-total="initialTotal"
              :paid-total="paidTotal"
              :discount-source="discount_source"
              :discount-type="discount_type"
              :discount-value="discount_value"
              :discount-reason="discount_reason"
              :discount-includes-packages="discount_applies_to_packages"
            />

            <div class="mt-3">
              <DiscountPanel
                :source="discount_source"
                :type="discount_type"
                :value="discount_value"
                :reason="discount_reason"
                :applies-to-packages="discount_applies_to_packages"
                :suggested-auto="autoDiscountEligibility.eligible"
                :suggested-auto-percent="autoDiscountPercent"
                :show-errors="submittingAttempted"
                :error="fieldErrors.discount_value"
                @set-source="setDiscountSource"
                @update:type="discount_type = $event"
                @update:value="discount_value = $event"
                @update:reason="discount_reason = $event"
                @update:appliesToPackages="discount_applies_to_packages = $event"
              />
            </div>
          </div>
        </div>

        <div class="bg-gray-100 dark:bg-gray-700/60 sm:rounded-b-lg flex gap-2 sm:gap-4 px-3 sm:px-6 py-2 sm:py-4 short:py-1.5 flex-shrink-0 border-t border-gray-200 dark:border-gray-700"
             style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom));">
          <button
            @click="handleClose"
            class="flex-1 btn btn-light py-2 sm:py-3 short:py-1.5 text-sm sm:text-lg short:text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition-colors"
          >
            Cancelar
          </button>
          <button
            @click="submit"
            :disabled="submitting"
            class="flex-1 btn btn-primary py-2 sm:py-3 short:py-1.5 text-sm sm:text-lg short:text-sm rounded-lg bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-1"
          >
            <span v-if="submitting"><i class="ti ti-refresh animate-spin"></i><span class="hidden sm:inline">Guardando...</span></span>
            <span v-else><i class="ti ti-check"></i><span class="hidden sm:inline">Guardar cambios</span><span class="sm:hidden">Guardar</span></span>
          </button>
        </div>

      </div>
    </div>
  </transition>

  <ConfirmModal
    :show="showConfirmModal"
    title="¿Descartar cambios?"
    message="Hay cambios sin guardar. ¿Estás seguro de que quieres cerrar sin guardarlos?"
    confirm-text="Descartar"
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
import orderQuoteFormMixin from '../order-quote-shared/orderQuoteFormMixin.js';
import OrderDetailsStep from '../order-quote-shared/form-steps/OrderDetailsStep.vue';
import OrderEditChargeSummary from './form-steps/OrderEditChargeSummary.vue';
import DiscountPanel from '../order-quote-shared/DiscountPanel.vue';
import ConfirmModal from '../common/ConfirmModal.vue';
import SuccessModal from '../common/SuccessModal.vue';
import AlertModal from '../common/AlertModal.vue';

export default {
  name: 'OrderEditModal',
  mixins: [orderQuoteFormMixin],
  components: {
    OrderDetailsStep,
    OrderEditChargeSummary,
    DiscountPanel,
    ConfirmModal,
    SuccessModal,
    AlertModal
  },
  props: {
    show: { type: Boolean, default: false },
    order: { type: Object, default: null }
  },
  emits: ['close', 'updated', 'notify'],
  data() {
    return {
      form: {
        estimated_delivery_date: '',
        estimated_delivery_time: '',
        notes: ''
      }
    };
  },
  computed: {
    orderCode() {
      return 'ORD-' + String(this.order?.id || 0).padStart(3, '0');
    },
    initialTotal() {
      return Number(this.order?.initial_total ?? 0);
    },
    paidTotal() {
      return (this.order?.payments || []).reduce(
        (sum, p) => sum + Number(p.amount || 0),
        0
      );
    },
    fieldErrors() {
      const hasEmptyPhotoLine = this.photoLines.some(l => !l.photo_type_id);
      const hasEmptyPackageLine = this.packageLines.some(l => !l.package_id);
      const hasEmptyServiceLine = this.serviceLines.some(l => !l.service_id);
      const hasContent = this.activeLines.length > 0 || this.selectedPackageIds.length > 0;
      return {
        delivery_date: !this.form.estimated_delivery_date,
        delivery_time: !this.form.estimated_delivery_time,
        order_content: !hasContent,
        photo_lines: hasEmptyPhotoLine,
        package_lines: hasEmptyPackageLine,
        service_lines: hasEmptyServiceLine,
        service_prices: this.selectedServices.some(s =>
          s.price === '' || s.price === null || Number.isNaN(Number(s.price)) || Number(s.price) < 0
        ),
        discount_value: this.discountTypeHasError()
      };
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
    async afterCatalogsLoaded() {
      if (!this.order) return;
      this.hydratePhotoLinesFromRows(this.order.order_photo_types || []);
      this.hydratePackageLinesFromRows(this.order.order_packages || []);
      this.hydrateServiceLinesFromRows(this.order.services || []);

      this.discount_source = this.order.discount_source || 'none';
      this.discount_type = this.order.discount_type || null;
      this.discount_value = (this.order.discount_value !== null && this.order.discount_value !== undefined)
        ? Number(this.order.discount_value)
        : null;
      this.discount_reason = this.order.discount_reason || '';
      this.discount_applies_to_packages = !!this.order.discount_applies_to_packages;
      this.auto_discount_reason = this.order.auto_discount_reason || null;

      this.form = {
        estimated_delivery_date: this.order.estimated_delivery_date
          ? String(this.order.estimated_delivery_date).substring(0, 10)
          : '',
        estimated_delivery_time: this.order.estimated_delivery_time || '',
        notes: this.order.notes || ''
      };
    },
    getCurrentState() {
      return {
        ...this.getSharedState(),
        form: {
          estimated_delivery_date: this.form.estimated_delivery_date,
          estimated_delivery_time: this.form.estimated_delivery_time,
          notes: this.form.notes
        }
      };
    },
    resetForm() {
      this.resetSharedFields();
      this.form = { estimated_delivery_date: '', estimated_delivery_time: '', notes: '' };
    },
    handleOverlayClick() {
      this.handleClose();
    },
    async submit() {
      this.submittingAttempted = true;
      this.attemptTick++;

      const errs = this.fieldErrors;
      const hasBlockingError = errs.delivery_date || errs.delivery_time || errs.order_content
        || errs.photo_lines || errs.package_lines || errs.service_lines || errs.service_prices
        || errs.discount_value;

      if (hasBlockingError) {
        this.alertMessage = 'Por favor, completa todos los campos marcados con * antes de guardar.';
        this.showAlertModal = true;
        return;
      }

      if (this.submitting) return;
      this.submitting = true;

      try {
        const payload = {
          ...this.resolveDiscountPayload(),
          estimated_delivery_date: this.form.estimated_delivery_date,
          estimated_delivery_time: this.form.estimated_delivery_time,
          notes: this.form.notes || null,
          photo_types: this.activeLines,
          package_ids: this.selectedPackageIds,
          additional_services: this.activeServices
        };

        const res = await axios.put(`/orders/${this.order.id}`, payload);
        this.$emit('updated', res.data.order);
        this.openSuccess('¡Orden actualizada!', 'Los cambios se guardaron correctamente.', () => {
          this.isDirty = false;
          this.close();
        });
      } catch (error) {
        const data = error.response?.data;
        const firstValidationError = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
        this.alertMessage = firstValidationError || data?.message || 'No se pudo actualizar la orden';
        this.showAlertModal = true;
        this.submitting = false;
      }
    }
  }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>