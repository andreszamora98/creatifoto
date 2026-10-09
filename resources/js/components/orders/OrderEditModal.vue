<template>
  <transition name="fade">
    <div
      v-if="show"
      class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-6"
      @click="handleOverlayClick"
    >
      <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-7xl max-h-[92vh] flex flex-col overflow-hidden"
        @click.stop
      >

        <div class="bg-gray-100 dark:bg-gray-700/60 rounded-t-lg flex-shrink-0">
          <div class="flex justify-between items-center px-6 py-3">
            <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
              <i class="ti ti-pencil mr-2"></i>Editar {{ orderCode }}
            </h3>
            <button
              @click="handleClose"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
            >
              <i class="ti ti-x text-2xl"></i>
            </button>
          </div>
        </div>

        <div
          class="flex-1 overflow-y-auto overflow-x-hidden px-6 py-6"
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

          <div class="mt-6">
            <h5 class="text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">
              Resumen de la orden
            </h5>
            <OrderEditChargeSummary
              :photo-lines="photoLines"
              :photo-types="photoTypes"
              :package-lines="packageLines"
              :packages="packages"
              :service-lines="serviceLines"
              :additional-services="additionalServices"
              :estimated-total="estimatedTotal"
              :initial-total="initialTotal"
              :paid-total="paidTotal"
            />
          </div>
        </div>

        <div class="bg-gray-100 dark:bg-gray-700/60 rounded-b-lg flex gap-4 px-6 py-4 flex-shrink-0 border-t border-gray-200 dark:border-gray-700">
          <button
            @click="handleClose"
            class="flex-1 btn btn-light py-3 text-lg rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition-colors"
          >
            Cancelar
          </button>
          <button
            @click="submit"
            :disabled="submitting"
            class="flex-1 btn btn-primary py-3 text-lg rounded-lg bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="submitting"><i class="ti ti-refresh animate-spin mr-2"></i>Guardando...</span>
            <span v-else><i class="ti ti-check mr-2"></i>Guardar cambios</span>
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
import ConfirmModal from '../common/ConfirmModal.vue';
import SuccessModal from '../common/SuccessModal.vue';
import AlertModal from '../common/AlertModal.vue';

export default {
  name: 'OrderEditModal',
  mixins: [orderQuoteFormMixin],
  components: {
    OrderDetailsStep,
    OrderEditChargeSummary,
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
        )
      };
    }
  },
  methods: {
    async afterCatalogsLoaded() {
      if (!this.order) return;
      this.hydratePhotoLinesFromRows(this.order.order_photo_types || []);
      this.hydratePackageLinesFromRows(this.order.order_packages || []);
      this.hydrateServiceLinesFromRows(this.order.services || []);

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
        || errs.photo_lines || errs.package_lines || errs.service_lines || errs.service_prices;

      if (hasBlockingError) {
        this.alertMessage = 'Por favor, completa todos los campos marcados con * antes de guardar.';
        this.showAlertModal = true;
        return;
      }

      if (this.submitting) return;
      this.submitting = true;

      try {
        const payload = {
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