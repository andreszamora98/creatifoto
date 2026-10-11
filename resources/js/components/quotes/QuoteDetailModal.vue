<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-0 sm:p-4 lg:p-6 short:p-2" @click="handleOverlayClick">
      <div class="bg-white dark:bg-gray-800 shadow-xl w-full max-w-4xl h-full sm:h-auto sm:max-h-[85vh] sm:rounded-lg flex flex-col overflow-hidden" @click.stop>

        <div class="flex justify-between items-start p-3 sm:p-6 short:p-2 border-b border-gray-200 dark:border-gray-700 flex-shrink-0 gap-2">
          <div class="min-w-0">
            <h3 class="text-base sm:text-2xl short:text-sm font-bold text-gray-800 dark:text-gray-200 truncate">Cotización {{ quoteCode }}</h3>
            <span class="inline-flex items-center gap-1.5 px-2 sm:px-3 py-0.5 sm:py-1 mt-1 sm:mt-2 text-xs sm:text-sm short:text-xs rounded-full" :class="statusClass(quote?.status)">
              {{ statusLabel(quote?.status) }}
            </span>
          </div>
          <button @click="handleClose" aria-label="Cerrar" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1.5 sm:p-2 short:p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex-shrink-0">
            <i class="ti ti-x text-lg sm:text-2xl short:text-lg"></i>
          </button>
        </div>

        <div class="flex-1 overflow-y-auto p-3 sm:p-6 short:p-2 space-y-3 sm:space-y-5 short:space-y-2">

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4 short:gap-2">

            <div v-show="!editingCustomer"
                 class="rounded-lg p-3 sm:p-5 short:p-3 bg-gray-50 dark:bg-gray-700/40 border-2 border-gray-300 dark:border-gray-600 shadow-sm"
                 style="border-left-width:4px;">
              <div class="flex items-center justify-between mb-2 sm:mb-3 gap-2">
                <h4 class="font-semibold text-base sm:text-lg short:text-sm flex items-center text-gray-700 dark:text-gray-300 min-w-0">
                  <i class="ti ti-info-circle mr-1.5 sm:mr-2 text-base sm:text-xl short:text-base"></i>
                  <span class="truncate">Vigencia y observaciones</span>
                </h4>
                <button v-if="!editingDetails && canEdit" @click="startEditingDetails" type="button" aria-label="Editar vigencia" class="text-gray-400 hover:text-blue-600 flex-shrink-0 p-1"><i class="ti ti-pencil"></i></button>
              </div>

              <template v-if="!editingDetails">
                <div class="space-y-1 sm:space-y-2 text-sm sm:text-base short:text-sm">
                  <div class="flex justify-between items-center py-1.5 sm:py-2 gap-2">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Total:</span>
                    <span class="font-bold text-gray-700 dark:text-gray-300 text-right ml-2 break-words">{{ formatCurrency(quote?.total) }}</span>
                  </div>
                  <div class="flex justify-between items-center py-1.5 sm:py-2 gap-2">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Vigencia:</span>
                    <span class="font-bold text-gray-700 dark:text-gray-300">{{ formatDateOnly(quote?.valid_until) }}</span>
                  </div>
                  <div v-if="quote?.order" class="flex justify-between items-center py-1.5 sm:py-2 gap-2">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Orden generada:</span>
                    <span class="font-bold text-green-600 dark:text-green-400">#{{ quote.order.id }}</span>
                  </div>
                </div>
                <div v-if="quote?.notes" class="mt-2 sm:mt-3 bg-white dark:bg-gray-800 rounded p-2 sm:p-3 border border-gray-200 dark:border-gray-700">
                  <p class="text-sm sm:text-base short:text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-words">{{ quote.notes }}</p>
                </div>
              </template>

              <template v-else>
                <div class="mb-3 sm:mb-4">
                  <label class="text-sm sm:text-base short:text-sm font-medium block mb-1.5 sm:mb-2 text-gray-700 dark:text-gray-300">Vigencia</label>
                  <input v-model="detailsForm.validUntil" type="date" :min="minDateStr" class="form-control text-sm sm:text-base short:text-sm p-2.5 sm:p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 w-full">
                </div>
                <div class="mb-3 sm:mb-4">
                  <label class="text-sm sm:text-base short:text-sm font-medium block mb-1.5 sm:mb-2 text-gray-700 dark:text-gray-300">Observaciones</label>
                  <textarea v-model="detailsForm.notes" v-blur-on-enter enterkeyhint="done" rows="3" class="form-control text-sm sm:text-base short:text-sm p-2.5 sm:p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 w-full resize-none"></textarea>
                </div>
                <div class="flex gap-2 sm:gap-3">
                  <button @click="cancelEditingDetails" type="button" class="flex-1 py-2 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-xs rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Cancelar</button>
                  <button @click="saveDetails" :disabled="savingDetails" type="button" class="flex-1 py-2 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-xs rounded-lg bg-blue-500 hover:bg-blue-600 text-white disabled:opacity-50">
                    {{ savingDetails ? 'Guardando...' : 'Guardar' }}
                  </button>
                </div>
              </template>
            </div>

            <div class="rounded-lg p-3 sm:p-5 short:p-3 bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-300 dark:border-blue-600"
                 :class="editingCustomer ? 'lg:col-span-2' : ''"
                 style="border-left-width:4px;">
              <div class="flex items-center justify-between mb-2 sm:mb-3 gap-2">
                <h4 class="font-semibold text-base sm:text-lg short:text-sm flex items-center text-blue-800 dark:text-blue-300">
                  <i class="ti ti-user mr-1.5 sm:mr-2 text-base sm:text-xl short:text-base"></i>Cliente
                </h4>
                <button v-if="!editingCustomer && canEdit" @click="startEditingCustomer" type="button" aria-label="Editar cliente" class="text-blue-400 hover:text-blue-600 flex-shrink-0 p-1"><i class="ti ti-pencil"></i></button>
              </div>

              <template v-if="!editingCustomer">
                <div class="space-y-1 sm:space-y-2 text-sm sm:text-base short:text-sm">
                  <div class="flex justify-between items-center py-1.5 sm:py-2 gap-2">
                    <span class="font-medium text-blue-800 dark:text-blue-300 flex-shrink-0">Nombre:</span>
                    <span class="font-bold text-blue-800 dark:text-blue-300 text-right ml-2 min-w-0 break-words">{{ quote?.customer?.name || 'Sin cliente' }}</span>
                  </div>
                  <div class="flex justify-between items-center py-1.5 sm:py-2 gap-2">
                    <span class="font-medium text-blue-800 dark:text-blue-300 flex-shrink-0">Teléfono:</span>
                    <span class="font-bold text-blue-800 dark:text-blue-300">{{ quote?.customer?.phone || '—' }}</span>
                  </div>
                  <div class="flex justify-between items-center py-1.5 sm:py-2 gap-2">
                    <span class="font-medium text-blue-800 dark:text-blue-300 flex-shrink-0">Correo:</span>
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
                <div class="flex gap-2 sm:gap-3 mt-3 sm:mt-4">
                  <button @click="cancelEditingCustomer" type="button" class="flex-1 py-2 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-xs rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Cancelar</button>
                  <button @click="saveCustomer" :disabled="savingCustomer" type="button" class="flex-1 py-2 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-xs rounded-lg bg-blue-500 hover:bg-blue-600 text-white disabled:opacity-50">
                    {{ savingCustomer ? 'Guardando...' : 'Guardar' }}
                  </button>
                </div>
              </template>
            </div>

          </div>

          <div class="bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 rounded-lg p-3 sm:p-5 short:p-3 shadow-sm">
            <div class="flex items-center justify-between mb-2 sm:mb-3">
              <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-base sm:text-lg short:text-sm flex items-center">
                <i class="ti ti-photo mr-1.5 sm:mr-2 text-base sm:text-xl short:text-base"></i>Conceptos
              </h4>
              <button v-if="!editingConcepts && canEdit" @click="startEditingConcepts" type="button" aria-label="Editar conceptos" class="text-gray-400 hover:text-blue-600 flex-shrink-0 p-1"><i class="ti ti-pencil"></i></button>
            </div>

            <template v-if="!editingConcepts">
              <ChargeSummary
                :lines="summaryLines"
                :estimated-total="Number(quote?.total || 0)"
                hide-total
                empty-message="Sin conceptos registrados."
              />
            </template>

            <template v-else>
              <PackagesSummaryList v-if="quote?.quote_packages?.length" :packages="quote.quote_packages" />

              <div class="mt-3 sm:mt-4 p-3 sm:p-4 short:p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 shadow-sm">
                <h5 class="font-medium text-sm sm:text-base short:text-sm text-gray-700 dark:text-gray-300 mb-2 sm:mb-3">Tipos de fotografía</h5>
                <PhotoTypeLinesEditor
                  :lines="photoLines"
                  :photo-types="photoTypes"
                  :paper-types="paperTypes"
                  :loading="loadingCatalogs"
                  :show-errors="conceptsSubmittingAttempted"
                  :error="fieldErrors.photo_lines || fieldErrors.order_content"
                  title=""
                  empty-hint="Sin tipos de fotografía."
                />
                <div class="flex gap-2 sm:gap-3 mt-3 sm:mt-4">
                  <button @click="cancelEditingConcepts" type="button" class="flex-1 py-2 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-xs rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Cancelar</button>
                  <button @click="saveConcepts" :disabled="savingConcepts" type="button" class="flex-1 py-2 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-xs rounded-lg bg-blue-500 hover:bg-blue-600 text-white disabled:opacity-50">
                    {{ savingConcepts ? 'Guardando...' : 'Guardar' }}
                  </button>
                </div>
              </div>

              <div
                v-if="quote?.services?.length"
                class="mt-3 sm:mt-4 p-3 sm:p-4 short:p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 shadow-sm"
              >
                <AdditionalServicesSummaryList :services="quote.services" nested />
              </div>
            </template>
          </div>

        </div>

        <div class="flex gap-2 sm:gap-3 p-3 sm:p-6 short:p-2 border-t border-gray-200 dark:border-gray-700 flex-shrink-0"
             style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom));">
          <button v-if="isPending" @click="updateStatus('rejected')" class="flex-1 py-2 sm:py-3 short:py-1.5 text-sm sm:text-base short:text-xs rounded-lg border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
            Rechazar
          </button>
          <button v-if="canConvert" @click="requestConvert" class="flex-1 py-2 sm:py-3 short:py-1.5 text-sm sm:text-base short:text-xs rounded-lg bg-blue-500 hover:bg-blue-600 text-white transition-colors">
            <span class="hidden sm:inline">Convertir a orden</span>
            <span class="sm:hidden">Convertir</span>
          </button>
          <button v-if="!canConvert" @click="handleClose" class="flex-1 py-2 sm:py-3 short:py-1.5 text-sm sm:text-base short:text-xs rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">
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
import PackagesSummaryList from '../order-quote-shared/detail-parts/PackagesSummaryList.vue';
import AdditionalServicesSummaryList from '../order-quote-shared/detail-parts/AdditionalServicesSummaryList.vue';
import PhotoTypeLinesEditor from '../order-quote-shared/photo-lines/PhotoTypeLinesEditor.vue';
import ChargeSummary from '../order-quote-shared/ChargeSummary.vue';
import ConfirmModal from '../common/ConfirmModal.vue';
import SuccessModal from '../common/SuccessModal.vue';
import AlertModal from '../common/AlertModal.vue';

export default {
  name: 'QuoteDetailModal',
  mixins: [orderQuoteFormMixin],
  components: {
    CustomerStep, PackagesSummaryList, AdditionalServicesSummaryList,
    PhotoTypeLinesEditor, ChargeSummary, ConfirmModal, SuccessModal, AlertModal
  },
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
      detailsForm: { validUntil: '', notes: '' }
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
      const hasEmptyPhotoLine = this.photoLines.some(l => !l.photo_type_id);
      const hasContent = this.activeLines.length > 0 || this.selectedPackageIds.length > 0;
      return {
        customer: !hasCustomer,
        order_content: !hasContent,
        photo_lines: hasEmptyPhotoLine
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
    },
    summaryLines() {
      if (!this.quote) return [];

      const packages = this.quote.quote_packages || [];
      const photos = this.quote.quote_photo_types || [];
      const services = this.quote.services || [];

      const result = [];

      photos.filter(l => !l.quote_package_id).forEach((l, idx) => {
        result.push({
          key: `pt-${l.id}-${idx}`,
          label: `${l.photo_type_name_snapshot || 'Tipo de foto'} x${l.quantity}`,
          amount: Number(l.unit_price || 0) * Number(l.quantity || 0),
          unitPrice: Number(l.unit_price || 0),
          quantity: Number(l.quantity || 0),
          format: l.format
        });
      });

      packages.forEach(pkg => {
        const childPhotos = photos.filter(l => l.quote_package_id === pkg.id);
        const childServices = services.filter(s => s.quote_package_id === pkg.id);
        const children = [];

        childPhotos.forEach((l, idx) => {
          const formatLabel = l.format === 'color' ? 'Color' : 'Blanco/negro';
          children.push({
            key: `pkg-${pkg.id}-pt-${l.id}-${idx}`,
            label: `${l.photo_type_name_snapshot || 'Tipo de foto'} x${l.quantity} · ${formatLabel}`,
            amount: Number(l.unit_price || 0) * Number(l.quantity || 0),
            unitPrice: Number(l.unit_price || 0),
            quantity: Number(l.quantity || 0),
            format: l.format
          });
        });

        childServices.forEach((s, idx) => {
          children.push({
            key: `pkg-${pkg.id}-svc-${s.id}-${idx}`,
            label: `${s.additional_service_name_snapshot || 'Servicio adicional'} x${s.quantity}`,
            amount: Number(s.price || 0) * Number(s.quantity || 0),
            unitPrice: Number(s.price || 0),
            quantity: Number(s.quantity || 0),
            icon: 'ti ti-tool'
          });
        });

        result.push({
          key: `pkg-${pkg.id}`,
          isPackage: true,
          packageName: pkg.package_name_snapshot || ('Paquete #' + pkg.package_id),
          schoolName: pkg.school_name_snapshot || '',
          amount: Number(pkg.price_snapshot || 0),
          children
        });
      });

      services.filter(s => !s.quote_package_id).forEach((s, idx) => {
        result.push({
          key: `svc-${s.id}-${idx}`,
          label: `${s.additional_service_name_snapshot || 'Servicio adicional'} x${s.quantity}`,
          amount: Number(s.price || 0) * Number(s.quantity || 0),
          unitPrice: Number(s.price || 0),
          quantity: Number(s.quantity || 0),
          icon: 'ti ti-tool'
        });
      });

      return result;
    }
  },
  methods: {
    afterCatalogsLoaded() {
      this.populateConceptsFromQuote();
    },
    afterContactUpdated() {
      this.$emit('updated');
    },
    populateConceptsFromQuote() {
      if (!this.quote) return;
      this.photoLines = (this.quote.quote_photo_types || []).map(line => ({
        id: line.id,
        photo_type_id: line.photo_type_id,
        quantity: line.quantity,
        format: line.format,
        paper_type_id: line.paper_type_id
      }));

      this.packageLines = (this.quote.quote_packages || []).map(p => ({ package_id: p.package_id }));
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

    startEditingCustomer() {
      const current = this.quote?.customer || null;
      this.customerSubmittingAttempted = false;
      this.phoneTouched = false;
      this.editingPhone = false;
      this.editingEmail = false;
      this.showCustomerDropdown = false;

      if (current) {
        const catalogCustomer = this.customers.find(c => c.id === current.id) || current;
        this.selectedCustomer = catalogCustomer;
        this.customerSearch = catalogCustomer.name || '';
        this.customerPhone = catalogCustomer.phone || '';
        this.customerEmail = catalogCustomer.email || '';
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
      this.customerSearch = '';
      this.selectedCustomer = null;
    },
    async saveCustomer() {
      if (this.savingCustomer) return;
      this.customerSubmittingAttempted = true;

      if (this.fieldErrors.customer) return;

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
      const isSameCustomer =
        payload.customer_id && payload.customer_id === this.quote?.customer?.id;

      this.savingCustomer = true;
      try {
        if (isSameCustomer) {
          const currentPhone = (this.selectedCustomer?.phone || '').trim() || null;
          const currentEmail = (this.selectedCustomer?.email || '').trim() || null;
          const newPhone = trimmedPhone || null;
          const newEmail = this.customerEmail.trim() || null;

          if (newPhone !== currentPhone || newEmail !== currentEmail) {
            const contactRes = await axios.patch(
              `/customers/${this.selectedCustomer.id}/contact`,
              { phone: newPhone, email: newEmail }
            );
            const updatedCustomer = contactRes.data;
            const idx = this.customers.findIndex(c => c.id === updatedCustomer.id);
            if (idx !== -1) this.customers.splice(idx, 1, updatedCustomer);
            this.selectedCustomer = updatedCustomer;
          }

          this.cancelEditingCustomer();
          this.$emit('updated');
          this.successModalTitle = 'Actualizado';
          this.successModalMessage = 'Los datos del cliente se actualizaron correctamente.';
          this.showSuccessModal = true;
          return;
        }

        const res = await axios.put(`/quotes/${this.quote.id}/customer`, payload);
        const updatedQuote = res.data.quote || res.data;
        this.cancelEditingCustomer();
        this.$emit('updated', updatedQuote);
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
        const updatedQuote = res.data.quote || res.data;
        this.editingDetails = false;
        this.$emit('updated', updatedQuote);
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

      if (this.fieldErrors.photo_lines) {
        this.alertMessage = 'Completa o elimina las líneas de fotografía sin tipo seleccionado.';
        this.showAlertModal = true;
        return;
      }
      if (this.fieldErrors.order_content) {
        this.alertMessage = 'Agrega al menos un tipo de fotografía o un paquete escolar.';
        this.showAlertModal = true;
        return;
      }

      this.savingConcepts = true;
      try {
        const res = await axios.put(`/quotes/${this.quote.id}/concepts`, {
          photo_types: this.activeLines,
          package_ids: this.selectedPackageIds
        });
        const updatedQuote = res.data.quote || res.data;
        this.editingConcepts = false;
        this.$emit('updated', updatedQuote);
        this.successModalTitle = 'Actualizado';
        this.successModalMessage = 'Los tipos de fotografía se actualizaron correctamente.';
        this.showSuccessModal = true;
      } catch (error) {
        const data = error.response?.data;
        const firstValidationError = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
        this.alertMessage = firstValidationError || data?.message || 'No se pudieron actualizar los conceptos';
        this.showAlertModal = true;
      } finally {
        this.savingConcepts = false;
      }
    },

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