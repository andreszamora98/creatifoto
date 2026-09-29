<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-6" @click="handleOverlayClick">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-4xl max-h-[85vh] flex flex-col overflow-hidden" @click.stop>

        <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
          <div>
            <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Cotización {{ quoteCode }}</h3>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 mt-2 text-sm rounded-full" :class="statusClass(quote?.status)">
              {{ statusLabel(quote?.status) }}
            </span>
          </div>
          <button @click="handleClose" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <i class="ti ti-x text-2xl"></i>
          </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-5">

          <!-- Dos tarjetas lado a lado (mismo esquema que OrderDetailModal): Vigencia y observaciones | Cliente -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            <!-- Vigencia y observaciones (se oculta mientras se edita el cliente para darle todo el ancho) -->
            <div v-show="!editingCustomer"
                 class="rounded-lg p-5 bg-gray-50 dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600"
                 style="border-left-width:4px;">
              <div class="flex items-center justify-between mb-3">
                <h4 class="font-semibold text-lg flex items-center text-gray-700 dark:text-gray-300">
                  <i class="ti ti-info-circle mr-2 text-xl"></i>Vigencia y observaciones
                </h4>
                <button v-if="!editingDetails && canEdit" @click="startEditingDetails" type="button" class="text-gray-400 hover:text-blue-600"><i class="ti ti-pencil"></i></button>
              </div>

              <template v-if="!editingDetails">
                <div class="space-y-2 text-base">
                  <div class="flex justify-between items-center py-2">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Total:</span>
                    <span class="font-bold text-gray-700 dark:text-gray-300">{{ formatCurrency(quote?.total) }}</span>
                  </div>
                  <div class="flex justify-between items-center py-2">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Vigencia:</span>
                    <span class="font-bold text-gray-700 dark:text-gray-300">{{ formatDateOnly(quote?.valid_until) }}</span>
                  </div>
                  <div v-if="quote?.order" class="flex justify-between items-center py-2">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Orden generada:</span>
                    <span class="font-bold text-green-600 dark:text-green-400">#{{ quote.order.id }}</span>
                  </div>
                </div>
                <div v-if="quote?.notes" class="mt-3 bg-white dark:bg-gray-800 rounded p-3 border border-gray-200 dark:border-gray-700">
                  <p class="text-gray-700 dark:text-gray-300 text-base whitespace-pre-wrap break-words">{{ quote.notes }}</p>
                </div>
              </template>

              <template v-else>
                <div class="mb-4">
                  <label class="text-base font-medium block mb-2 text-gray-700 dark:text-gray-300">Vigencia</label>
                  <input v-model="detailsForm.validUntil" type="date" :min="minDateStr" class="form-control text-base p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 w-full">
                </div>
                <div class="mb-4">
                  <label class="text-base font-medium block mb-2 text-gray-700 dark:text-gray-300">Observaciones</label>
                  <textarea v-model="detailsForm.notes" v-blur-on-enter enterkeyhint="done" rows="3" class="form-control text-base p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 w-full resize-none"></textarea>
                </div>
                <div class="flex gap-3">
                  <button @click="cancelEditingDetails" type="button" class="flex-1 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Cancelar</button>
                  <button @click="saveDetails" :disabled="savingDetails" type="button" class="flex-1 py-2.5 rounded-lg bg-blue-500 hover:bg-blue-600 text-white disabled:opacity-50">
                    {{ savingDetails ? 'Guardando...' : 'Guardar' }}
                  </button>
                </div>
              </template>
            </div>

            <!-- Cliente (ocupa todo el ancho mientras se edita con CustomerStep) -->
            <div class="rounded-lg p-5 bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-300 dark:border-blue-600"
                 :class="editingCustomer ? 'lg:col-span-2' : ''"
                 style="border-left-width:4px;">
              <div class="flex items-center justify-between mb-3">
                <h4 class="font-semibold text-lg flex items-center text-blue-800 dark:text-blue-300">
                  <i class="ti ti-user mr-2 text-xl"></i>Cliente
                </h4>
                <button v-if="!editingCustomer && canEdit" @click="startEditingCustomer" type="button" class="text-blue-400 hover:text-blue-600"><i class="ti ti-pencil"></i></button>
              </div>

              <template v-if="!editingCustomer">
                <div class="space-y-2 text-base">
                  <div class="flex justify-between items-center py-2">
                    <span class="font-medium text-blue-800 dark:text-blue-300">Nombre:</span>
                    <span class="font-bold text-blue-800 dark:text-blue-300 text-right ml-2 min-w-0 break-words">{{ quote?.customer?.name || 'Sin cliente' }}</span>
                  </div>
                  <div class="flex justify-between items-center py-2">
                    <span class="font-medium text-blue-800 dark:text-blue-300">Teléfono:</span>
                    <span class="font-bold text-blue-800 dark:text-blue-300">{{ quote?.customer?.phone || '—' }}</span>
                  </div>
                  <div class="flex justify-between items-center py-2">
                    <span class="font-medium text-blue-800 dark:text-blue-300">Correo:</span>
                    <span class="font-bold text-blue-800 dark:text-blue-300 text-right ml-2 min-w-0 break-all">{{ quote?.customer?.email || '—' }}</span>
                  </div>
                </div>
              </template>

              <template v-else>
                <CustomerStep
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
                  :show-errors="customerSubmittingAttempted"
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
                <div class="flex gap-3 mt-4">
                  <button @click="cancelEditingCustomer" type="button" class="flex-1 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Cancelar</button>
                  <button @click="saveCustomer" :disabled="savingCustomer" type="button" class="flex-1 py-2.5 rounded-lg bg-blue-500 hover:bg-blue-600 text-white disabled:opacity-50">
                    {{ savingCustomer ? 'Guardando...' : 'Guardar' }}
                  </button>
                </div>
              </template>
            </div>

          </div>

          <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-5">
            <div class="flex items-center justify-between mb-3">
              <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-lg flex items-center">
                <i class="ti ti-photo mr-2 text-xl"></i>Conceptos
              </h4>
              <button v-if="!editingConcepts && canEdit" @click="startEditingConcepts" type="button" class="text-gray-400 hover:text-blue-600"><i class="ti ti-pencil"></i></button>
            </div>

            <template v-if="!editingConcepts">
              <PackagesSummaryList v-if="quote?.packages?.length" :packages="quote.packages" />
              <PhotoTypesSummaryList v-if="quote?.quote_photo_types?.length" :lines="quote.quote_photo_types" />
              <p v-if="!quote?.packages?.length && !quote?.quote_photo_types?.length" class="text-base text-gray-500 dark:text-gray-400 italic">
                Sin conceptos registrados.
              </p>
            </template>

            <template v-else>
              <OrderDetailsStep
                :section-badge="null"
                section-title=""
                :form="conceptsForm"
                :show-errors="conceptsSubmittingAttempted"
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
                @update:form="conceptsForm = $event"
                hide-delivery-date
              />
              <div class="flex gap-3 mt-4">
                <button @click="cancelEditingConcepts" type="button" class="flex-1 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Cancelar</button>
                <button @click="saveConcepts" :disabled="savingConcepts" type="button" class="flex-1 py-2.5 rounded-lg bg-blue-500 hover:bg-blue-600 text-white disabled:opacity-50">
                  {{ savingConcepts ? 'Guardando...' : 'Guardar' }}
                </button>
              </div>
            </template>
          </div>

        </div>

        <div class="flex gap-3 p-6 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
          <button v-if="isPending" @click="updateStatus('rejected')" class="flex-1 py-3 rounded-lg border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
            Rechazar
          </button>
          <button v-if="canConvert" @click="requestConvert" class="flex-1 py-3 rounded-lg bg-blue-500 hover:bg-blue-600 text-white transition-colors">
            Convertir a orden
          </button>
          <button v-if="!canConvert" @click="handleClose" class="flex-1 py-3 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">
            Cerrar
          </button>
        </div>

      </div>
    </div>
  </transition>

  <ConfirmModal
    :show="showConfirmModal"
    title="¿Descartar cambios?"
    message="Hay una edición sin guardar. ¿Estás seguro de que quieres salir sin guardarla?"
    confirm-text="Descartar"
    cancel-text="Seguir editando"
    @confirm="closeConfirmed"
    @cancel="showConfirmModal = false"
  />

  <SuccessModal :show="showSuccessModal" :title="successModalTitle" :message="successModalMessage" @closed="showSuccessModal = false" />
  <AlertModal :show="showAlertModal" title="Atención" :message="alertMessage" @closed="showAlertModal = false" />
</template>

<script>
import axios from 'axios';
import orderQuoteFormMixin from '../order-quote-shared/orderQuoteFormMixin.js';
import CustomerStep from '../order-quote-shared/form-steps/CustomerStep.vue';
import OrderDetailsStep from '../order-quote-shared/form-steps/OrderDetailsStep.vue';
import PackagesSummaryList from '../order-quote-shared/detail-parts/PackagesSummaryList.vue';
import PhotoTypesSummaryList from '../order-quote-shared/detail-parts/PhotoTypesSummaryList.vue';
import ConfirmModal from '../common/ConfirmModal.vue';
import SuccessModal from '../common/SuccessModal.vue';
import AlertModal from '../common/AlertModal.vue';

export default {
  name: 'QuoteDetailModal',
  mixins: [orderQuoteFormMixin],
  components: { CustomerStep, OrderDetailsStep, PackagesSummaryList, PhotoTypesSummaryList, ConfirmModal, SuccessModal, AlertModal },
  props: {
    show: { type: Boolean, default: false },
    quote: { type: Object, default: null }
  },
  emits: ['close', 'updated', 'convert', 'notify'],
  data() {
    return {
      editingCustomer: false,
      editingDetails: false,
      editingConcepts: false,
      customerSubmittingAttempted: false,
      conceptsSubmittingAttempted: false,
      savingCustomer: false,
      savingDetails: false,
      savingConcepts: false,
      showConfirmModal: false,
      showSuccessModal: false,
      successModalTitle: '',
      successModalMessage: '',
      detailsForm: { validUntil: '', notes: '' },
      conceptsForm: { notes: '' }
    };
  },
  computed: {
    quoteCode() {
      return 'C-' + String(this.quote?.id || 0).padStart(3, '0');
    },
    canConvert() {
      return this.quote && ['pending', 'accepted'].includes(this.quote.status);
    },
    canEdit() {
      return this.quote && ['pending', 'accepted'].includes(this.quote.status);
    },
    isPending() {
      return this.quote?.status === 'pending';
    },
    fieldErrors() {
      const hasCustomer = !!this.selectedCustomer || this.customerSearch.trim().length > 0;
      return {
        customer: !hasCustomer,
        photo_types: this.activeLines.length === 0
      };
    },
    minDateStr() {
      const today = new Date();
      const y = today.getFullYear();
      const m = String(today.getMonth() + 1).padStart(2, '0');
      const d = String(today.getDate()).padStart(2, '0');
      return `${y}-${m}-${d}`;
    },
    anyEditingActive() {
      return this.editingPhone || this.editingEmail || this.editingCustomer || this.editingDetails || this.editingConcepts;
    }
  },
  methods: {
    afterCatalogsLoaded() {
      this.populateConceptsFromQuote();
    },
    // Hook del mixin: tras guardar teléfono/correo en el registro del cliente,
    // se avisa al padre para que recargue la cotización y el detalle no quede desactualizado.
    afterContactUpdated() {
      this.$emit('updated');
    },
    populateConceptsFromQuote() {
      if (!this.quote) return;
      this.photoLines = (this.quote.quote_photo_types || []).map(line => ({
        photo_type_id: line.photo_type_id,
        quantity: line.quantity,
        format: line.format,
        paper_type_id: line.paper_type_id
      }));

      this.selectedPackageIds = (this.quote.packages || []).map(p => p.id);
    },
    getCurrentState() {
      return {};
    },
    resetForm() {
      this.resetSharedFields();
      this.editingCustomer = false;
      this.editingDetails = false;
      this.editingConcepts = false;
      this.customerSubmittingAttempted = false;
      this.conceptsSubmittingAttempted = false;
      this.savingCustomer = false;
      this.savingDetails = false;
      this.savingConcepts = false;
      this.showConfirmModal = false;
    },

    // ── Cliente ──────────────────────────────────────────────
    startEditingCustomer() {
      const current = this.quote?.customer || null;
      this.customerSubmittingAttempted = false;
      this.phoneTouched = false;
      this.editingPhone = false;
      this.editingEmail = false;
      if (current) {
        // Se prefiere el objeto del catálogo (ya cargado) para que coincida con el resto de la lista
        this.selectedCustomer = this.customers.find(c => c.id === current.id) || current;
        this.customerSearch = this.selectedCustomer.name || '';
      } else {
        this.selectedCustomer = null;
        this.customerSearch = '';
        this.customerPhone = '';
        this.customerEmail = '';
      }
      this.editingCustomer = true;
    },
    cancelEditingCustomer() {
      this.editingCustomer = false;
      this.customerSubmittingAttempted = false;
      this.phoneTouched = false;
      this.editingPhone = false;
      this.editingEmail = false;
      this.showCustomerDropdown = false;
      // Al limpiar el cliente seleccionado, el watcher del mixin vacía teléfono y correo
      this.customerSearch = '';
      this.selectedCustomer = null;
    },
    async saveCustomer() {
      if (this.savingCustomer) return;
      this.customerSubmittingAttempted = true;

      if (this.fieldErrors.customer) return; // CustomerStep ya marca el campo como obligatorio

      if (this.editingPhone || this.editingEmail) {
        this.alertMessage = 'Confirma o descarta el cambio de teléfono o correo antes de guardar.';
        this.showAlertModal = true;
        return;
      }

      const trimmedPhone = this.customerPhone.trim();
      if (trimmedPhone && !/^\d{10}$/.test(trimmedPhone)) {
        this.alertMessage = 'El número de teléfono debe tener 10 dígitos. Bórralo o edítalo para continuar';
        this.showAlertModal = true;
        return;
      }

      const conflictMessages = this.getCustomerConflictMessages();
      if (conflictMessages.length > 0) {
        this.alertMessage = conflictMessages.join('\n');
        this.showAlertModal = true;
        return;
      }

      const payload = this.resolveCustomerPayload();

      // Sigue siendo el mismo cliente: los cambios de contacto ya se guardaron desde CustomerStep
      if (payload.customer_id && payload.customer_id === this.quote?.customer?.id) {
        this.cancelEditingCustomer();
        return;
      }

      this.savingCustomer = true;
      try {
        const res = await axios.put(`/quotes/${this.quote.id}/customer`, payload);
        this.cancelEditingCustomer();
        this.$emit('updated', res.data.quote);
        this.successModalTitle = 'Actualizado';
        this.successModalMessage = 'El cliente de la cotización se actualizó correctamente.';
        this.showSuccessModal = true;
      } catch (error) {
        this.alertMessage = error.response?.data?.message || 'No se pudo actualizar el cliente';
        this.showAlertModal = true;
      } finally {
        this.savingCustomer = false;
      }
    },

    // ── Vigencia y observaciones ─────────────────────────────
    startEditingDetails() {
      this.detailsForm = {
        validUntil: this.quote.valid_until ? String(this.quote.valid_until).substring(0, 10) : '',
        notes: this.quote.notes || ''
      };
      this.editingDetails = true;
    },
    cancelEditingDetails() {
      this.editingDetails = false;
    },
    async saveDetails() {
      this.savingDetails = true;
      try {
        const res = await axios.patch(`/quotes/${this.quote.id}`, {
          valid_until: this.detailsForm.validUntil || null,
          notes: this.detailsForm.notes || null
        });
        this.editingDetails = false;
        this.$emit('updated', res.data.quote);
        this.successModalTitle = 'Actualizado';
        this.successModalMessage = 'La vigencia y observaciones se actualizaron correctamente.';
        this.showSuccessModal = true;
      } catch (error) {
        this.alertMessage = error.response?.data?.message || 'No se pudo actualizar la cotización';
        this.showAlertModal = true;
      } finally {
        this.savingDetails = false;
      }
    },

    // ── Conceptos ────────────────────────────────────────────
    startEditingConcepts() {
      this.populateConceptsFromQuote();
      this.conceptsSubmittingAttempted = false;
      this.editingConcepts = true;
    },
    cancelEditingConcepts() {
      this.editingConcepts = false;
      this.populateConceptsFromQuote();
    },
    async saveConcepts() {
      this.conceptsSubmittingAttempted = true;
      if (this.activeLines.length === 0) {
        this.alertMessage = 'Selecciona al menos un tipo de fotografía';
        this.showAlertModal = true;
        return;
      }
      this.savingConcepts = true;
      try {
        const res = await axios.put(`/quotes/${this.quote.id}/concepts`, {
          photo_types: this.activeLines,
          package_ids: this.selectedPackageIds
        });
        this.editingConcepts = false;
        this.$emit('updated', res.data.quote);
        this.successModalTitle = 'Actualizado';
        this.successModalMessage = 'Los conceptos se actualizaron correctamente.';
        this.showSuccessModal = true;
      } catch (error) {
        this.alertMessage = error.response?.data?.message || 'No se pudieron actualizar los conceptos';
        this.showAlertModal = true;
      } finally {
        this.savingConcepts = false;
      }
    },

    // ── Cierre y acciones ────────────────────────────────────
    handleOverlayClick() {
      if (!this.anyEditingActive) this.close();
    },
    handleClose() {
      if (this.anyEditingActive) {
        this.showConfirmModal = true;
        return;
      }
      this.close();
    },
    closeConfirmed() {
      this.showConfirmModal = false;
      this.editingCustomer = false;
      this.editingDetails = false;
      this.editingConcepts = false;
      this.cancelPhoneEdit();
      this.cancelEmailEdit();
      this.close();
    },
    requestConvert() {
      this.$emit('convert', this.quote);
    },
    async updateStatus(status) {
      try {
        await axios.patch(`/quotes/${this.quote.id}/status`, { status });
        this.$emit('updated');
        this.close();
      } catch (error) {
        this.alertMessage = error.response?.data?.message || 'No se pudo actualizar';
        this.showAlertModal = true;
      }
    },
    statusLabel(status) {
      const labels = { pending: 'Pendiente', accepted: 'Aceptada', rejected: 'Rechazada', expired: 'Vencida', converted: 'Convertida' };
      return labels[status] || status;
    },
    statusClass(status) {
      const colors = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        accepted: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        expired: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        converted: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
      };
      return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    },
    formatCurrency(value) {
      const num = Number(value || 0);
      return '$' + num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    formatDateOnly(dateString) {
      if (!dateString) return '—';
      const datePart = String(dateString).substring(0, 10);
      const [y, m, d] = datePart.split('-');
      if (!y || !m || !d) return dateString;
      return `${d}/${m}/${y}`;
    }
  }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>