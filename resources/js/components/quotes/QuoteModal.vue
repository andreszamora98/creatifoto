<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-6">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-5xl max-h-[85vh] overflow-y-auto">
        <div class="p-8">
          <div class="flex justify-between items-center mb-3">
            <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-200">
              <i class="ti ti-file-description mr-2"></i>Nueva cotización
            </h3>
            <button @click="handleClose" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
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
                hide-delivery-date
              />

              <SummaryStep
                v-else
                key="step-3"
                :active-lines="activeLines"
                :photo-types="photoTypes"
                :selected-packages="selectedPackagesData"
                :estimated-total="estimatedTotal"
                :valid-until="validUntil"
                @update:validUntil="validUntil = $event"
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
              <span v-if="submitting"><i class="ti ti-refresh animate-spin mr-2"></i>Guardando...</span>
              <span v-else><i class="ti ti-check mr-2"></i>Guardar cotización</span>
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
import SummaryStep from './form-steps/SummaryStep.vue';

export default {
  name: 'QuoteModal',
  components: { ConfirmModal, SuccessModal, AlertModal, CustomerStep, OrderDetailsStep, SummaryStep },
  props: {
    show: { type: Boolean, default: false }
  },
  emits: ['close', 'created', 'notify'],
  data() {
    return {
      successModalTitle: '',
      successModalMessage: '',
      successModalNext: null,
      savingContact: false,
      phoneTouched: false,
      showMissingContactModal: false,
      alertMessage: '',
      showAlertModal: false,
      editingPhone: false,
      editingEmail: false,
      showSuccessModal: false,
      loadingCatalogs: false,
      submitting: false,
      isDirty: false,
      showConfirmModal: false,
      openFormatDropdownId: null,
      currentStep: 1,
      navDirection: 'forward',
      photoTypes: [],
      paperTypes: [],
      packages: [],
      customers: [],
      customerSearch: '',
      customerPhone: '',
      customerEmail: '',
      showCustomerDropdown: false,
      selectedCustomer: null,
      selectedPackageIds: [],
      lines: {},
      validUntil: '',
      stepAttempted: {1: false, 2: false, 3: false},
      submittingAttempted: false,
      form: { notes: '' }
    };
  },
  computed: {
    transitionName() {
      return this.navDirection === 'forward' ? 'slide-next' : 'slide-prev';
    },
    phoneExists() {
      const phone = this.customerPhone.trim();
      if (!phone) return null;
      return this.customers.find(c =>
        c.phone && c.phone.trim() === phone &&
        (!this.selectedCustomer || c.id !== this.selectedCustomer.id)
      ) || null;
    },
    emailExists() {
      const email = this.customerEmail.trim();
      if (!email) return null;
      return this.customers.find(c =>
        c.email && c.email.toLowerCase().trim() === email.toLowerCase() &&
        (!this.selectedCustomer || c.id !== this.selectedCustomer.id)
      ) || null;
    },
    canConfirmPhone() {
      const trimmed = this.customerPhone.trim();
      const original = ((this.selectedCustomer && this.selectedCustomer.phone) || '').trim();
      if (trimmed === original) return false;
      if (trimmed === '') return true;
      return /^\d{10}$/.test(trimmed);
    },
    phoneError() {
      const trimmed = this.customerPhone.trim();
      if (!trimmed || !this.phoneTouched) return null;
      if (!/^\d{10}$/.test(trimmed)) return 'El teléfono debe tener exactamente 10 dígitos.';
      return null;
    },
    emailError() {
      const trimmed = this.customerEmail.trim();
      if (!trimmed) return null;
      if (trimmed.length > 255) return 'El correo no puede superar 255 caracteres.';
      return null;
    },
    exactCustomerMatch() {
      const trimmed = this.customerSearch.trim().toLowerCase();
      if (!trimmed) return null;
      return this.customers.find(c => c.name.trim().toLowerCase() === trimmed) || null;
    },
    filteredCustomers() {
      if (!this.customerSearch.trim()) return [];
      const q = this.customerSearch.toLowerCase().trim();
      return this.customers.filter(c => (c.name || '').toLowerCase().startsWith(q));
    },
    emailChanged() {
      if (!this.selectedCustomer) return false;
      return this.customerEmail.trim() !== (this.selectedCustomer.email || '').trim();
    },
    activeLines() {
      return Object.entries(this.lines)
        .filter(([, l]) => l.selected)
        .map(([photo_type_id, l]) => ({ photo_type_id: Number(photo_type_id), quantity: l.quantity, format: l.format, paper_type_id: l.paper_type_id }));
    },
    selectedPackagesData() {
      return this.packages.filter(p => this.selectedPackageIds.includes(p.id));
    },
    estimatedTotal() {
      const linesTotal = this.activeLines.reduce((sum, l) => {
        const pt = this.photoTypes.find(p => p.id === l.photo_type_id);
        return sum + (Number(pt?.base_price || 0) * l.quantity);
      }, 0);
      const packagesTotal = this.selectedPackagesData.reduce((sum, p) => sum + Number(p.total_price || 0), 0);
      return linesTotal + packagesTotal;
    },
    fieldErrors() {
      const hasCustomer = !!this.selectedCustomer || this.customerSearch.trim().length > 0;
      return {
        customer: !hasCustomer,
        photo_types: this.activeLines.length === 0
      };
    },
    showErrorsStep1() {
      return this.submittingAttempted || this.stepAttempted[1];
    },
    showErrorsStep2() {
      return this.submittingAttempted || this.stepAttempted[2];
    },
    currentStepValid() {
      const keysByStep = { 1: ['customer'], 2: ['photo_types'], 3: [] };
      const keys = keysByStep[this.currentStep] || [];
      return !keys.some(k => this.fieldErrors[k]);
    },
    canSubmit() {
      return !Object.values(this.fieldErrors).some(Boolean);
    }
  },
  watch: {
    async show(val) {
      if (val) {
        await this.loadCatalogs();
        this.$nextTick(() => { this.isDirty = false; });
      } else {
        this.resetForm();
      }
    },
    customerSearch(newVal) {
      const trimmed = newVal.trim();
      if (this.selectedCustomer && trimmed.toLowerCase() !== this.selectedCustomer.name.trim().toLowerCase()) {
        this.selectedCustomer = null;
      }
      if (!this.selectedCustomer && trimmed) {
        const exactMatch = this.customers.find(c => c.name.trim().toLowerCase() === trimmed.toLowerCase());
        if (exactMatch) this.selectedCustomer = exactMatch;
      }
    },
    selectedCustomer(newVal) {
      if (newVal) {
        this.customerPhone = newVal.phone || '';
        this.customerEmail = newVal.email || '';
      } else {
        this.customerPhone = '';
        this.customerEmail = '';
      }
      this.editingPhone = false;
      this.editingEmail = false;
    }
  },
  methods: {
    openSuccess(title, message, next = null) {
      this.successModalTitle = title;
      this.successModalMessage = message;
      this.successModalNext = next;
      this.showSuccessModal = true;
    },
    clearPhone() { this.customerPhone = ''; this.phoneTouched = false; },
    clearEmail() { this.customerEmail = ''; },
    onPhoneBlur() { if (this.customerPhone.trim()) this.phoneTouched = true; },
    confirmContinueWithoutContact() {
      this.showMissingContactModal = false;
      this.stepAttempted[this.currentStep] = false;
      this.navDirection = 'forward';
      this.currentStep++;
    },
    cancelContinueWithoutContact() { this.showMissingContactModal = false; },
    openCustomerDropdown() { this.showCustomerDropdown = true; },
    closeDatePicker() {},
    toggleDatePicker() {},
    getCustomerConflictMessages() {
      const messages = [];
      if (this.phoneExists) messages.push(`El teléfono ${this.customerPhone.trim()} ya está registrado a nombre de "${this.phoneExists.name}".`);
      if (this.emailExists) messages.push(`El correo ${this.customerEmail.trim()} ya está registrado a nombre de "${this.emailExists.name}".`);
      return messages;
    },
    goNext() {
      this.stepAttempted[this.currentStep] = true;
      if (this.currentStep === 1) {
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
      }
      if (!this.currentStepValid) {
        this.alertMessage = 'Por favor, completa todos los campos marcados con * antes de continuar.';
        this.showAlertModal = true;
        return;
      }
      if (this.currentStep === 1 && !this.customerPhone.trim() && !this.customerEmail.trim()) {
        this.showMissingContactModal = true;
        return;
      }
      this.stepAttempted[this.currentStep] = false;
      if (this.currentStep < 3) {
        this.navDirection = 'forward';
        this.currentStep++;
      }
    },
    goBack() {
      if (this.currentStep > 1) {
        this.navDirection = 'backward';
        this.currentStep--;
        this.stepAttempted[this.currentStep] = false;
      }
    },
    toggleEditPhone() { this.editingPhone = true; },
    async confirmPhoneChange() {
      if (this.savingContact) return;
      const phone = this.customerPhone.trim() || null;
      if (phone === (this.selectedCustomer.phone || null)) {
        this.editingPhone = false;
        this.phoneTouched = false;
        return;
      }
      this.savingContact = true;
      try {
        const res = await axios.patch(`/customers/${this.selectedCustomer.id}/contact`, { phone });
        const updated = res.data;
        const idx = this.customers.findIndex(c => c.id === updated.id);
        if (idx !== -1) this.customers.splice(idx, 1, updated);
        this.selectedCustomer = updated;
        this.editingPhone = false;
        this.phoneTouched = false;
        this.openSuccess('Teléfono actualizado', 'El teléfono del cliente se actualizó correctamente.');
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: error.response?.data?.message || 'No se pudo actualizar el teléfono', type: 'error' });
      } finally {
        this.savingContact = false;
      }
    },
    cancelPhoneEdit() {
      this.phoneTouched = false;
      this.customerPhone = this.selectedCustomer ? (this.selectedCustomer.phone || '') : '';
      this.editingPhone = false;
    },
    toggleEditEmail() { this.editingEmail = true; },
    async confirmEmailChange() {
      if (this.savingContact) return;
      const email = this.customerEmail.trim() || null;
      if (email === (this.selectedCustomer.email || null)) {
        this.editingEmail = false;
        return;
      }
      this.savingContact = true;
      try {
        const res = await axios.patch(`/customers/${this.selectedCustomer.id}/contact`, { email });
        const updated = res.data;
        const idx = this.customers.findIndex(c => c.id === updated.id);
        if (idx !== -1) this.customers.splice(idx, 1, updated);
        this.selectedCustomer = updated;
        this.editingEmail = false;
        this.openSuccess('Correo actualizado', 'El correo del cliente se actualizó correctamente.');
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: error.response?.data?.message || 'No se pudo actualizar el correo', type: 'error' });
      } finally {
        this.savingContact = false;
      }
    },
    cancelEmailEdit() {
      this.customerEmail = this.selectedCustomer ? (this.selectedCustomer.email || '') : '';
      this.editingEmail = false;
    },
    markDirty() { this.isDirty = true; },
    togglePhotoType(id) {
      const line = this.lines[id];
      if (!line) return;
      line.selected = !line.selected;
      if (line.selected) {
        if (!line.quantity || line.quantity < 1) line.quantity = 1;
      } else {
        line.quantity = 0;
      }
      this.markDirty();
    },
    onQuantityBlur(id) {
      const line = this.lines[id];
      if (!line) return;
      if (!line.quantity || line.quantity < 1) {
        line.selected = false;
        line.quantity = 0;
      }
    },
    toggleFormatDropdown(id) { this.openFormatDropdownId = this.openFormatDropdownId === id ? null : id; },
    closeFormatDropdown(id) {
      setTimeout(() => {
        if (this.openFormatDropdownId === id) this.openFormatDropdownId = null;
      }, 200);
    },
    selectFormat(id, value) {
      this.lines[id].format = value;
      this.openFormatDropdownId = null;
      this.markDirty();
    },
    async loadCatalogs() {
      this.loadingCatalogs = true;
      try {
        const [ptRes, paperRes, pkgRes, custRes] = await Promise.all([
          axios.get('/photo-types'),
          axios.get('/paper-types'),
          axios.get('/packages'),
          axios.get('/customers')
        ]);
        this.photoTypes = ptRes.data;
        this.paperTypes = paperRes.data;
        this.packages = pkgRes.data;
        this.customers = custRes.data;
        this.lines = {};
        this.photoTypes.forEach(pt => {
          this.lines[pt.id] = { selected: false, quantity: 0, format: 'bn', paper_type_id: null };
        });
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: 'No se pudieron cargar los catálogos', type: 'error' });
      } finally {
        this.loadingCatalogs = false;
      }
    },
    selectCustomer(c) {
      this.phoneTouched = false;
      this.selectedCustomer = c;
      this.customerSearch = c.name;
      this.showCustomerDropdown = false;
      this.markDirty();
    },
    clearCustomerSearch() { this.customerSearch = ''; this.selectedCustomer = null; },
    hideCustomerDropdown() { setTimeout(() => { this.showCustomerDropdown = false; }, 200); },
    resetForm() {
      this.selectedCustomer = null;
      this.customerSearch = '';
      this.customerPhone = '';
      this.customerEmail = '';
      this.selectedPackageIds = [];
      this.currentStep = 1;
      this.navDirection = 'forward';
      this.validUntil = '';
      this.editingPhone = false;
      this.editingEmail = false;
      this.form = { notes: '' };
      this.photoTypes.forEach(pt => {
        this.lines[pt.id] = { selected: false, quantity: 0, format: 'bn', paper_type_id: null };
      });
      this.isDirty = false;
      this.submittingAttempted = false;
      this.stepAttempted = {1: false, 2: false, 3: false};
    },
    handleClose() {
      if (this.isDirty) {
        this.showConfirmModal = true;
      } else {
        this.close();
      }
    },
    closeConfirmed() { this.showConfirmModal = false; this.close(); },
    close() { this.$emit('close'); },
    async submit() {
      this.submittingAttempted = true;
      this.stepAttempted = {1: true, 2: true, 3: true};

      if (!this.canSubmit) {
        this.alertMessage = 'Por favor, completa todos los campos marcados con * antes de continuar.';
        this.showAlertModal = true;
        return;
      }
      if (this.submitting) return;
      this.submitting = true;
      try {
        let customerId = null;
        let newCustomer = null;
        if (this.selectedCustomer) {
          customerId = this.selectedCustomer.id;
        } else if (this.customerSearch.trim()) {
          newCustomer = {
            name: this.customerSearch.trim(),
            phone: this.customerPhone.trim() || null,
            email: this.customerEmail.trim() || null,
          };
        }

        const payload = {
          customer_id: customerId,
          new_customer: newCustomer,
          valid_until: this.validUntil || null,
          notes: this.form.notes,
          photo_types: this.activeLines,
          package_ids: this.selectedPackageIds,
        };

        const res = await axios.post('/quotes', payload);
        this.$emit('created', res.data.quote);
        this.openSuccess('¡Cotización guardada!', 'La cotización se registró correctamente.', () => {
          this.isDirty = false;
          this.close();
        });
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: error.response?.data?.message || 'No se pudo crear la cotización', type: 'error' });
        this.submitting = false;
      }
    },
    onSuccessClosed() {
      this.showSuccessModal = false;
      const next = this.successModalNext;
      this.successModalNext = null;
      this.submitting = false;
      if (typeof next === 'function') next();
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