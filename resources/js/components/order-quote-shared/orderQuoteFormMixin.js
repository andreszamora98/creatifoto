import axios from 'axios';

export default {
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
      dateInputOpen: false,
      submittingAttempted: false,
      stepAttempted: { 1: false, 2: false, 3: false },
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
      photoLines: [],

      initialState: null
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
      return this.photoLines
        .filter(l => l.photo_type_id)
        .map(l => ({
          photo_type_id: l.photo_type_id,
          quantity: l.quantity,
          format: l.format,
          paper_type_id: l.paper_type_id
        }));
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
    showErrorsStep1() {
      return this.submittingAttempted || this.stepAttempted[1];
    },
    showErrorsStep2() {
      return this.submittingAttempted || this.stepAttempted[2];
    },
    canSubmit() {
      return !Object.values(this.fieldErrors).some(Boolean);
    }
  },
  watch: {
    customerPhone(val) {
      if (/^\d{10}$/.test(val.trim())) {
        this.phoneTouched = false;
      }
    },
    async show(val) {
      if (val) {
        await this.loadCatalogs();
        this.afterCatalogsLoaded();
        this.$nextTick(() => {
          this.initialState = this.getCurrentState();
          this.isDirty = false;
        });
      } else {
        this.resetForm();
        this.initialState = null;
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
    afterCatalogsLoaded() {},
    afterContactUpdated() {},

    openSuccess(title, message, next = null) {
      this.successModalTitle = title;
      this.successModalMessage = message;
      this.successModalNext = next;
      this.showSuccessModal = true;
    },
    clearPhone() {
      this.customerPhone = '';
      this.phoneTouched = false;
    },
    clearEmail() {
      this.customerEmail = '';
    },
    onPhoneBlur() {
      if (this.customerPhone.trim()) this.phoneTouched = true;
    },
    confirmContinueWithoutContact() {
      this.showMissingContactModal = false;
      this.stepAttempted[this.currentStep] = false;
      this.navDirection = 'forward';
      this.currentStep++;
    },
    cancelContinueWithoutContact() {
      this.showMissingContactModal = false;
    },
    openCustomerDropdown() {
      this.showCustomerDropdown = true;
    },
    toggleDatePicker(event) {
      const input = event.currentTarget;
      if (!input) return;
      if (typeof input.showPicker !== 'function') {
        input.focus();
        return;
      }
      if (this.dateInputOpen) {
        input.blur();
        this.dateInputOpen = false;
      } else {
        try {
          input.showPicker();
          this.dateInputOpen = true;
        } catch (e) {
          input.focus();
        }
      }
    },
    closeDatePicker() {
      this.dateInputOpen = false;
    },
    getCustomerConflictMessages() {
      const messages = [];
      if (this.phoneExists) {
        messages.push(`El teléfono ${this.customerPhone.trim()} ya está registrado a nombre de "${this.phoneExists.name}".`);
      }
      if (this.emailExists) {
        messages.push(`El correo ${this.customerEmail.trim()} ya está registrado a nombre de "${this.emailExists.name}".`);
      }
      return messages;
    },
    keysValid(keys) {
      return !keys.some(k => this.fieldErrors[k]);
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
    toggleEditPhone() {
      this.editingPhone = true;
    },
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
        this.afterContactUpdated(updated);
        this.openSuccess('Teléfono actualizado', 'El teléfono del cliente se actualizó correctamente.');
      } catch (error) {
        this.$emit('notify', {
          title: 'Error',
          message: error.response?.data?.message || 'No se pudo actualizar el teléfono',
          type: 'error'
        });
      } finally {
        this.savingContact = false;
      }
    },
    cancelPhoneEdit() {
      this.phoneTouched = false;
      this.customerPhone = this.selectedCustomer ? (this.selectedCustomer.phone || '') : '';
      this.editingPhone = false;
    },
    toggleEditEmail() {
      this.editingEmail = true;
    },
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
        this.afterContactUpdated(updated);
        this.openSuccess('Correo actualizado', 'El correo del cliente se actualizó correctamente.');
      } catch (error) {
        this.$emit('notify', {
          title: 'Error',
          message: error.response?.data?.message || 'No se pudo actualizar el correo',
          type: 'error'
        });
      } finally {
        this.savingContact = false;
      }
    },
    cancelEmailEdit() {
      this.customerEmail = this.selectedCustomer ? (this.selectedCustomer.email || '') : '';
      this.editingEmail = false;
    },
    markDirty() {
      this.isDirty = true;
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

        this.photoLines = [];
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
    clearCustomerSearch() {
      this.customerSearch = '';
      this.selectedCustomer = null;
    },
    hideCustomerDropdown() {
      setTimeout(() => { this.showCustomerDropdown = false; }, 200);
    },

    resetSharedFields() {
      this.successModalTitle = '';
      this.successModalMessage = '';
      this.successModalNext = null;
      this.phoneTouched = false;
      this.showMissingContactModal = false;
      this.selectedCustomer = null;
      this.customerSearch = '';
      this.customerPhone = '';
      this.customerEmail = '';
      this.selectedPackageIds = [];
      this.currentStep = 1;
      this.navDirection = 'forward';
      this.editingPhone = false;
      this.editingEmail = false;
      this.photoLines = [];
      this.isDirty = false;
      this.submittingAttempted = false;
      this.stepAttempted = { 1: false, 2: false, 3: false };
    },

    getSharedState() {
      return {
        customerSearch: this.customerSearch,
        selectedCustomerId: this.selectedCustomer ? this.selectedCustomer.id : null,
        customerPhone: this.customerPhone,
        customerEmail: this.customerEmail,
        selectedPackageIds: [...this.selectedPackageIds],
        photoLines: JSON.parse(JSON.stringify(this.photoLines))
      };
    },

    runSharedSubmitChecks() {
      this.submittingAttempted = true;
      this.stepAttempted = { 1: true, 2: true, 3: true };

      const trimmedPhone = this.customerPhone.trim();
      if (trimmedPhone && !/^\d{10}$/.test(trimmedPhone)) {
        this.alertMessage = 'El número de teléfono debe tener 10 dígitos. Bórralo o edítalo para continuar';
        this.showAlertModal = true;
        return false;
      }

      const conflictMessages = this.getCustomerConflictMessages();
      if (conflictMessages.length > 0) {
        this.alertMessage = conflictMessages.join('\n');
        this.showAlertModal = true;
        return false;
      }

      if (!this.canSubmit) {
        this.alertMessage = 'Por favor, completa todos los campos marcados con * antes de continuar.';
        this.showAlertModal = true;
        return false;
      }

      return true;
    },

    resolveCustomerPayload() {
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
      return { customer_id: customerId, new_customer: newCustomer };
    },

    handleClose() {
      const currentState = this.getCurrentState();
      const isChanged = JSON.stringify(currentState) !== JSON.stringify(this.initialState);
      if (isChanged) {
        this.showConfirmModal = true;
      } else {
        this.close();
      }
    },
    closeConfirmed() {
      this.showConfirmModal = false;
      this.close();
    },
    close() {
      this.$emit('close');
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