<template>
  <Teleport to="body">
    <transition name="fade">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-0 sm:p-4 bg-black/40 backdrop-blur-[1px] modal-overlay"
      >
        <div class="modal-zoom w-full h-full sm:h-auto sm:max-w-4xl">
          <div class="bg-white dark:bg-gray-800 sm:rounded-lg shadow-xl w-full h-full sm:h-auto sm:max-h-[85vh] overflow-y-auto isolate">
            <div class="relative sm:flex sm:items-center sm:justify-between sm:gap-3 p-3 sm:p-5 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800 z-30">
              <div class="min-w-0 flex-1 pr-10 sm:pr-0">
                <h3 class="text-lg sm:text-2xl font-semibold text-gray-800 dark:text-gray-200 truncate">
                  <i class="ti ti-shopping-cart mr-2"></i>Orden: {{ orderCode }}
                </h3>
                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-0.5 sm:mt-1">
                  Creada el {{ formatDate(localOrder?.created_at) }}
                </p>
              </div>

              <div class="mt-3 sm:mt-0 flex items-center justify-center sm:justify-end gap-2 flex-shrink-0">
                <button
                  v-if="canEdit"
                  @click="requestEdit"
                  class="px-3 sm:px-5 py-2 sm:py-3 text-sm sm:text-base font-medium text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 rounded-lg flex items-center gap-1.5 sm:gap-2 transition-colors"
                  title="Editar orden"
                >
                  <i class="ti ti-pencil text-base sm:text-lg"></i>
                  Editar
                </button>

                <button
                  v-if="canDeliver"
                  @click="requestDeliver"
                  class="px-3 sm:px-5 py-2 sm:py-3 text-sm sm:text-base font-medium text-white bg-green-600 hover:bg-green-700 active:bg-green-800 rounded-lg flex items-center gap-1.5 sm:gap-2 transition-colors"
                  title="Marcar como entregada"
                >
                  <i class="ti ti-check text-base sm:text-lg"></i>
                  <span class="hidden sm:inline">Marcar entregada</span>
                  <span class="sm:hidden">Entregar</span>
                </button>
              </div>

              <button
                @click="handleClose"
                aria-label="Cerrar"
                class="absolute top-3 right-3 sm:static sm:flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2 sm:p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200 transition-colors"
              >
                <i class="ti ti-x text-xl sm:text-2xl"></i>
              </button>
            </div>

            <div v-if="localOrder" class="p-3 sm:p-5 space-y-4 sm:space-y-5">
              <div
                v-if="localOrder.status === 'delivered'"
                class="flex items-start gap-3 p-3 sm:p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700"
              >
                <div class="flex-shrink-0 w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-green-100 dark:bg-green-800 flex items-center justify-center">
                  <i class="ti ti-check text-green-600 dark:text-green-400 text-lg sm:text-xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm sm:text-base font-semibold text-green-800 dark:text-green-300 mb-1">
                    Orden entregada
                  </p>
                  <p class="text-sm sm:text-base text-green-700 dark:text-green-400 leading-relaxed">
                    Esta orden fue entregada el {{ formatDate(localOrder.actual_delivery_date) }}. No admite modificaciones.
                  </p>
                </div>
              </div>

              <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                <div
                  class="rounded-lg p-3 sm:p-5"
                  :style="{
                    backgroundColor: colorInactive,
                    border: `2px solid ${colorActive}`,
                    borderLeftWidth: '4px'
                  }"
                >
                  <h4 class="font-semibold mb-2 sm:mb-3 text-base sm:text-lg flex items-center" :style="{ color: textActive }">
                    <i class="ti ti-info-circle mr-2 text-lg sm:text-xl"></i>Información de la orden
                  </h4>
                  <div class="space-y-1 sm:space-y-2 text-sm sm:text-base">
                    <div class="flex justify-between items-center gap-2 py-1.5 sm:py-2">
                      <span class="font-medium" :style="{ color: textActive }">Folio:</span>
                      <span class="font-bold" :style="{ color: textActive }">{{ orderCode }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-2 py-1.5 sm:py-2">
                      <span class="font-medium" :style="{ color: textActive }">Fecha de creación:</span>
                      <span class="font-bold text-right" :style="{ color: textActive }">{{ formatDate(localOrder.created_at) }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-2 py-1.5 sm:py-2">
                      <span class="font-medium" :style="{ color: textActive }">Entrega estimada:</span>
                      <span class="font-bold" :style="{ color: textActive }">{{ formatDateOnly(localOrder.estimated_delivery_date) }}</span>
                    </div>
                    <div v-if="localOrder.actual_delivery_date" class="flex justify-between items-center gap-2 py-1.5 sm:py-2">
                      <span class="font-medium" :style="{ color: textActive }">Entrega real:</span>
                      <span class="font-bold" :style="{ color: textActive }">{{ formatDate(localOrder.actual_delivery_date) }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-2 py-1.5 sm:py-2">
                      <span class="font-medium" :style="{ color: textActive }">Estado:</span>
                      <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 text-sm sm:text-base rounded-full" :class="statusClass(localOrder.status)">
                        <i :class="statusIcon(localOrder.status)" class="text-base"></i>
                        {{ statusLabel(localOrder.status) }}
                      </span>
                    </div>
                    <div v-if="localOrder.pose_number" class="flex justify-between items-center gap-2 py-1.5 sm:py-2">
                      <span class="font-medium" :style="{ color: textActive }">Toma:</span>
                      <span class="font-bold" :style="{ color: textActive }">#{{ localOrder.pose_number }}</span>
                    </div>
                  </div>
                </div>

                <div class="rounded-lg p-3 sm:p-5 bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-300 dark:border-blue-600" style="border-left-width: 4px;">
                  <h4 class="font-semibold mb-2 sm:mb-3 text-base sm:text-lg flex items-center text-blue-800 dark:text-blue-300">
                    <i class="ti ti-user mr-2 text-lg sm:text-xl"></i>Cliente
                  </h4>
                  <div class="space-y-1 sm:space-y-2 text-sm sm:text-base">
                    <div class="flex justify-between items-center gap-2 py-1.5 sm:py-2">
                      <span class="font-medium text-blue-800 dark:text-blue-300">Nombre:</span>
                      <span class="font-bold text-blue-800 dark:text-blue-300 text-right ml-2 break-words">{{ localOrder.customer?.name || 'Sin cliente' }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-2 py-1.5 sm:py-2">
                      <span class="font-medium text-blue-800 dark:text-blue-300">Teléfono:</span>
                      <span class="font-bold text-blue-800 dark:text-blue-300">{{ localOrder.customer?.phone || '—' }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-2 py-1.5 sm:py-2">
                      <span class="font-medium text-blue-800 dark:text-blue-300">Correo:</span>
                      <span class="font-bold text-blue-800 dark:text-blue-300 truncate ml-2">{{ localOrder.customer?.email || '—' }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="localOrder.notes" class="rounded-lg p-3 sm:p-5 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 shadow-sm">
                <h4 class="font-semibold text-gray-800 dark:text-gray-100 text-base sm:text-lg flex items-center mb-2 sm:mb-3">
                  <i class="ti ti-notes mr-2 text-lg sm:text-xl"></i>Observaciones
                </h4>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                  <p class="text-gray-700 dark:text-gray-300 text-sm sm:text-base whitespace-pre-wrap break-words">{{ localOrder.notes }}</p>
                </div>
              </div>

              <div
                v-if="localOrder.order_packages && localOrder.order_packages.length"
                class="rounded-lg p-3 sm:p-5 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 shadow-sm"
              >
                <PackagesSummaryList :packages="localOrder.order_packages" />
              </div>

              <div
                v-if="localOrder.order_photo_types && localOrder.order_photo_types.length"
                class="rounded-lg p-3 sm:p-5 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 shadow-sm"
              >
                <PhotoTypesSummaryList :lines="localOrder.order_photo_types" />
              </div>

              <div
                v-if="localOrder.services && localOrder.services.length"
                class="rounded-lg p-3 sm:p-5 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 shadow-sm"
              >
                <AdditionalServicesSummaryList :services="localOrder.services" />
              </div>

              <div class="rounded-lg p-3 sm:p-5 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 shadow-sm">
                <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-base sm:text-lg flex items-center mb-2 sm:mb-3">
                  <i class="ti ti-cash mr-2 text-lg sm:text-xl"></i>Pagos
                  <span class="ml-2 text-sm sm:text-base px-2.5 sm:px-3 py-0.5 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-300 rounded">{{ payments.length }}</span>
                </h4>

                <div
                  v-if="hasInitialTotalDiff"
                  class="bg-white dark:bg-gray-800 rounded-lg p-3 sm:p-4 border border-gray-200 dark:border-gray-600 text-center shadow-sm mb-3 sm:mb-4"
                >
                  <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400">Total inicial</p>
                  <p class="text-base sm:text-lg lg:text-xl font-bold text-gray-700 dark:text-gray-300">{{ formatCurrency(localOrder.initial_total) }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3 mb-3 sm:mb-4">
                  <div class="bg-white dark:bg-gray-800 rounded-lg p-3 sm:p-4 border border-gray-200 dark:border-gray-600 shadow-sm flex items-center justify-between sm:block sm:text-center">
                    <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400">Total</p>
                    <p class="text-base sm:text-lg lg:text-xl font-bold text-gray-800 dark:text-gray-200">{{ formatCurrency(localOrder.total) }}</p>
                  </div>
                  <div class="bg-white dark:bg-gray-800 rounded-lg p-3 sm:p-4 border border-gray-200 dark:border-gray-600 shadow-sm flex items-center justify-between sm:block sm:text-center">
                    <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400">Pagado</p>
                    <p class="text-base sm:text-lg lg:text-xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(totalPaid) }}</p>
                  </div>
                  <div class="bg-white dark:bg-gray-800 rounded-lg p-3 sm:p-4 border border-gray-200 dark:border-gray-600 shadow-sm flex items-center justify-between sm:block sm:text-center">
                    <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400">Saldo</p>
                    <p class="text-base sm:text-lg lg:text-xl font-bold" :class="localOrder.balance_due > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                      {{ formatCurrency(localOrder.balance_due) }}
                    </p>
                  </div>
                </div>

                <div v-if="payments.length" class="space-y-2 max-h-64 overflow-y-auto pr-1 mb-3 sm:mb-4">
                  <div
                    v-for="payment in payments"
                    :key="payment.id"
                    class="flex items-center justify-between gap-2 p-3 sm:p-4 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 shadow-sm"
                  >
                    <div class="flex items-center min-w-0 flex-1">
                      <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center mr-2.5 sm:mr-3 flex-shrink-0"
                           :class="payment.type === 'settlement' ? 'bg-green-100 dark:bg-green-900/40' : 'bg-blue-100 dark:bg-blue-900/40'">
                        <i
                          :class="payment.type === 'settlement' ? 'ti ti-check text-green-600 dark:text-green-300' : 'ti ti-coin text-blue-600 dark:text-blue-300'"
                          class="text-base sm:text-xl"
                        ></i>
                      </div>
                      <div class="truncate">
                        <p class="font-medium text-gray-800 dark:text-gray-300 text-sm sm:text-base truncate">
                          {{ payment.type === 'settlement' ? 'Liquidación' : 'Abono' }}
                          <span v-if="payment.payment_method" class="text-gray-500 dark:text-gray-400"> • {{ payment.payment_method }}</span>
                        </p>
                        <p class="text-xs sm:text-base text-gray-500 dark:text-gray-400 truncate">
                          {{ formatDate(payment.paid_at) }}
                          <span v-if="payment.notes" class="italic"> • {{ payment.notes }}</span>
                        </p>
                      </div>
                    </div>
                    <span class="font-bold text-gray-800 dark:text-gray-200 text-base sm:text-lg ml-2 whitespace-nowrap">{{ formatCurrency(payment.amount) }}</span>
                  </div>
                </div>

                <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm sm:text-base mb-3 sm:mb-4">
                  <i class="ti ti-cash-off text-xl sm:text-2xl mb-2 block"></i>
                  No hay pagos registrados
                </div>

                <div v-if="localOrder.balance_due > 0" class="border-t border-gray-300 dark:border-gray-600 pt-3 sm:pt-4">
                  <p class="text-sm sm:text-base font-semibold text-gray-700 dark:text-gray-300 mb-2 sm:mb-3">Registrar nuevo pago</p>
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                      <label class="text-sm sm:text-base text-gray-500 dark:text-gray-400 mb-1 sm:mb-1.5 block">Monto</label>
                      <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm sm:text-base">$</span>
                        <input
                          v-model.number="newPayment.amount"
                          type="number"
                          step="0.01"
                          min="0.01"
                          :max="localOrder.balance_due"
                          placeholder="0.00"
                          class="w-full text-base rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 py-3 pl-8 pr-3"
                        />
                      </div>
                    </div>
                    <div>
                      <label class="text-sm sm:text-base text-gray-500 dark:text-gray-400 mb-1 sm:mb-1.5 block">Método</label>
                      <select
                        v-model="newPayment.payment_method"
                        class="w-full text-base rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 py-3 px-3"
                      >
                        <option value="">Seleccionar</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Tarjeta">Tarjeta</option>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Depósito">Depósito</option>
                      </select>
                    </div>
                    <div class="flex items-end">
                      <button
                        @click="addPayment"
                        :disabled="addingPayment || !canAddPayment"
                        class="w-full py-3 text-base font-medium text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 rounded-lg flex items-center justify-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <span v-if="addingPayment"><i class="ti ti-refresh animate-spin"></i></span>
                        <span v-else class="flex items-center gap-1"><i class="ti ti-plus"></i>Registrar</span>
                      </button>
                    </div>
                  </div>
                  <p v-if="paymentError" class="text-sm sm:text-base text-red-500 dark:text-red-400 mt-2">{{ paymentError }}</p>
                </div>

                <div v-else class="border-t border-gray-300 dark:border-gray-600 pt-3 sm:pt-4 text-center">
                  <p class="text-sm sm:text-base text-green-700 dark:text-green-400 font-medium">
                    <i class="ti ti-check-circle mr-1"></i>
                    Orden totalmente pagada
                  </p>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </transition>

    <ConfirmModal
      :show="showConfirmDeliver"
      title="¿Marcar como entregada?"
      :message="`¿Confirmas que la orden ${orderCode} ha sido entregada al cliente?`"
      confirm-text="Sí, entregar"
      cancel-text="Cancelar"
      @confirm="confirmDeliver"
      @cancel="showConfirmDeliver = false"
    />

    <SuccessModal
      :show="showSuccess"
      :title="successTitle"
      :message="successMessage"
      @closed="onSuccessClosed"
    />

    <AlertModal
      :show="showAlert"
      title="Atención"
      :message="alertMessage"
      @closed="showAlert = false"
    />
  </Teleport>
</template>

<script>
import axios from 'axios';
import ConfirmModal from '../common/ConfirmModal.vue';
import SuccessModal from '../common/SuccessModal.vue';
import AlertModal from '../common/AlertModal.vue';
import PackagesSummaryList from '../order-quote-shared/detail-parts/PackagesSummaryList.vue';
import PhotoTypesSummaryList from '../order-quote-shared/detail-parts/PhotoTypesSummaryList.vue';
import AdditionalServicesSummaryList from '../order-quote-shared/detail-parts/AdditionalServicesSummaryList.vue';

export default {
  name: 'OrderDetailModal',
  components: { ConfirmModal, SuccessModal, AlertModal, PackagesSummaryList, PhotoTypesSummaryList, AdditionalServicesSummaryList },
  props: {
    show: { type: Boolean, default: false },
    order: { type: Object, default: null },
    columns: { type: Array, default: () => [] },
    activeColumn: { type: Number, default: 0 }
  },
  emits: ['close', 'updated', 'notify', 'edit'],
  data() {
    return {
      localOrder: null,
      loading: false,
      addingPayment: false,
      showConfirmDeliver: false,
      showSuccess: false,
      successTitle: '',
      successMessage: '',
      showAlert: false,
      alertMessage: '',
      newPayment: {
        amount: null,
        payment_method: ''
      }
    };
  },
  computed: {
    canEdit() {
      if (!this.localOrder) return false;
      return this.localOrder.status !== 'delivered';
    },
    activeColumnData() {
      return this.columns[this.activeColumn] || null;
    },
    colorActive() {
      return this.activeColumnData?.color?.active || '#3b82f6';
    },
    colorInactive() {
      return this.activeColumnData?.color?.inactive || '#eff6ff';
    },
    textActive() {
      return this.activeColumnData?.textColor?.active || '#1e40af';
    },
    orderCode() {
      if (!this.localOrder) return '';
      return 'ORD-' + String(this.localOrder.id).padStart(3, '0');
    },
    payments() {
      return this.localOrder?.payments || [];
    },
    totalPaid() {
      return this.payments.reduce((sum, p) => sum + Number(p.amount || 0), 0);
    },
    hasInitialTotalDiff() {
      if (!this.localOrder) return false;
      const initial = Number(this.localOrder.initial_total ?? 0);
      const total = Number(this.localOrder.total ?? 0);
      return Math.abs(initial - total) >= 0.01;
    },
    canDeliver() {
      if (!this.localOrder) return false;
      if (this.localOrder.status === 'delivered') return false;
      return Number(this.localOrder.balance_due) === 0;
    },
    canAddPayment() {
      const amt = Number(this.newPayment.amount);
      if (!amt || amt <= 0) return false;
      if (amt > Number(this.localOrder?.balance_due || 0)) return false;
      return true;
    },
    paymentError() {
      const amt = Number(this.newPayment.amount);
      if (!amt || amt <= 0) return null;
      if (amt > Number(this.localOrder?.balance_due || 0)) {
        return 'El monto excede el saldo pendiente';
      }
      return null;
    }
  },
  watch: {
    show(val) {
      if (val && this.order) {
        this.localOrder = this.order;
        this.silentRefresh();
      } else if (!val) {
        this.resetState();
      }
    }
  },
  methods: {

    requestEdit() {
      if (!this.canEdit) return;
      this.$emit('edit', this.localOrder);
    },

    async loadOrderDetail() {
      this.loading = true;
      try {
        const res = await axios.get(`/orders/${this.order.id}`);
        this.localOrder = res.data;
        return true;
      } catch (error) {
        console.error('Error cargando orden:', error);
        this.$emit('notify', {
          title: 'Error',
          message: 'No se pudo actualizar el detalle de la orden',
          type: 'error'
        });
        return false;
      } finally {
        this.loading = false;
      }
    },
    async silentRefresh() {
      try {
        const res = await axios.get(`/orders/${this.order.id}`);
        this.localOrder = res.data;
      } catch (error) {
        console.error('Error refrescando orden:', error);
      }
    },
    async addPayment() {
      if (!this.canAddPayment || this.addingPayment) return;
      this.addingPayment = true;
      try {
        const amount = Number(this.newPayment.amount);
        const balance = Number(this.localOrder.balance_due);
        const type = amount >= balance ? 'settlement' : 'advance';

        await axios.post(`/orders/${this.localOrder.id}/payments`, {
          amount,
          payment_method: this.newPayment.payment_method || null,
          type,
          notes: null
        });

        this.newPayment = { amount: null, payment_method: '' };
        const refreshed = await this.loadOrderDetail();

        this.successTitle = 'Pago registrado';
        this.successMessage = refreshed
          ? 'El pago se registró correctamente'
          : 'El pago se registró correctamente, pero no se pudo actualizar la vista. Cierra y vuelve a abrir la orden.';
        this.showSuccess = true;
        this.$emit('updated', this.localOrder);
      } catch (error) {
        this.alertMessage = error.response?.data?.message || 'No se pudo registrar el pago';
        this.showAlert = true;
      } finally {
        this.addingPayment = false;
      }
    },
    requestDeliver() {
      if (!this.canDeliver) {
        this.alertMessage = 'No se puede entregar la orden: tiene un saldo pendiente de ' + this.formatCurrency(this.localOrder.balance_due);
        this.showAlert = true;
        return;
      }
      this.showConfirmDeliver = true;
    },
    async confirmDeliver() {
      this.showConfirmDeliver = false;
      try {
        const res = await axios.patch(`/orders/${this.localOrder.id}/deliver`);
        this.localOrder = { ...this.localOrder, ...res.data.order };
        this.successTitle = 'Orden entregada';
        this.successMessage = 'La orden se marcó como entregada correctamente';
        this.showSuccess = true;
        this.$emit('updated', this.localOrder);
      } catch (error) {
        this.alertMessage = error.response?.data?.message || 'No se pudo entregar la orden';
        this.showAlert = true;
      }
    },
    onSuccessClosed() {
      this.showSuccess = false;
    },
    resetState() {
      this.localOrder = null;
      this.newPayment = { amount: null, payment_method: '' };
      this.showConfirmDeliver = false;
      this.showSuccess = false;
      this.showAlert = false;
      this.loading = false;
    },
    handleClose() {
      this.$emit('close');
    },
    formatCurrency(value) {
      const num = Number(value || 0);
      return '$' + num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    },
    formatDateOnly(dateString) {
      if (!dateString) return '';
      const datePart = String(dateString).substring(0, 10);
      const [y, m, d] = datePart.split('-');
      if (!y || !m || !d) return dateString;
      return `${d}/${m}/${y}`;
    },
    statusLabel(status) {
      const labels = {
        pending: 'Pendiente',
        processing: 'En proceso',
        ready: 'Lista',
        delivered: 'Entregada'
      };
      return labels[status] || status;
    },
    statusClass(status) {
      const colors = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        processing: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
        ready: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        delivered: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
      };
      return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    },
    statusIcon(status) {
      const icons = {
        pending: 'ti ti-clock',
        processing: 'ti ti-progress',
        ready: 'ti ti-check',
        delivered: 'ti ti-checkbox'
      };
      return icons[status] || 'ti ti-question-mark';
    }
  }
};
</script>

<style scoped>
.modal-overlay {
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-zoom {
  width: 100%;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 4px;
}

.dark .overflow-y-auto::-webkit-scrollbar-track {
  background: #374151;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb {
  background: #4b5563;
}
</style>