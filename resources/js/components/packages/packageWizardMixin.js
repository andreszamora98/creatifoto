import axios from 'axios';

const TOTAL_STEPS = 3;
const AUTO_DISCOUNT_PERCENT = 10;
const freshStepAttempts = () => ({ 1: false, 2: false, 3: false });

const optionalGet = (url) =>
  axios.get(url).catch((error) => {
    console.warn(`No se pudo cargar ${url}:`, error);
    return { data: [] };
  });

export default {
  data() {
    return {
      currentStep: 1,
      navDirection: 'forward',
      stepAttempted: freshStepAttempts(),
      submittingAttempted: false,
      submitting: false,
      initialState: null,

      loadingCatalogs: false,
      schools: [],
      photoTypes: [],
      paperTypes: [],
      backgrounds: [],
      additionalServices: [],
      existingPackages: [],

      showConfirmModal: false,
      showAlertModal: false,
      alertMessage: '',
      showSuccessModal: false,
      successModalTitle: '',
      successModalMessage: '',
      successModalNext: null
    };
  },
  computed: {
    totalSteps() {
      return TOTAL_STEPS;
    },
    autoDiscountPercent() {
      return AUTO_DISCOUNT_PERCENT;
    },
    autoDiscountEligibility() {
      if (!Array.isArray(this.photoLines) || !Array.isArray(this.photoTypes)) {
        return { eligible: false, reason: null };
      }

      const direct = this.photoLines.filter(l => l.photo_type_id);
      if (direct.length === 0) {
        return { eligible: false, reason: null };
      }

      const grouped = {};
      direct.forEach(l => {
        const id = Number(l.photo_type_id);
        grouped[id] = (grouped[id] || 0) + Number(l.quantity || 0);
      });

      let matchedA = null;
      let completeTypes = 0;

      for (const [typeId, total] of Object.entries(grouped)) {
        const type = this.photoTypes.find(t => Number(t.id) === Number(typeId));
        if (!type) continue;
        const base = Math.max(1, Number(type.base_quantity || 1));
        if (matchedA === null && total >= 2 * base) {
          matchedA = type;
        }
        if (total >= base) {
          completeTypes++;
        }
      }

      if (matchedA) {
        return { eligible: true, reason: `2+ órdenes completas de "${matchedA.name}"` };
      }
      if (completeTypes >= 3) {
        return { eligible: true, reason: `${completeTypes} tipos de fotografía con cantidad completa` };
      }
      return { eligible: false, reason: null };
    },
    transitionName() {
      return this.navDirection === 'forward' ? 'slide-next' : 'slide-prev';
    },
    showErrorsStep1() {
      return this.submittingAttempted || this.stepAttempted[1];
    },
    showErrorsStep2() {
      return this.submittingAttempted || this.stepAttempted[2];
    },
    showErrorsStep3() {
      return this.submittingAttempted || this.stepAttempted[3];
    },
    canSubmit() {
      return !Object.values(this.fieldErrors).some(Boolean);
    }
  },
  watch: {
    async show(val) {
      if (val) {
        await this.loadCatalogs();
        this.$nextTick(() => {
          this.syncAutoDiscount();
          this.initialState = this.getCurrentState();
        });
      } else {
        this.resetForm();
        this.initialState = null;
      }
    },
    photoLines: {
      deep: true,
      handler() {
        this.syncAutoDiscount();
      }
    }
  },
  methods: {
    syncAutoDiscount() {
      if (!this.pricing || this.pricing.discount_source !== 'auto') return;

      const e = this.autoDiscountEligibility;
      if (e.eligible) {
        this.pricing = {
          ...this.pricing,
          discount_type: 'percent',
          discount_value: this.autoDiscountPercent,
          auto_discount_reason: e.reason
        };
      } else {
        this.pricing = {
          ...this.pricing,
          discount_type: null,
          discount_value: null,
          auto_discount_reason: null
        };
      }
    },

    setDiscountSource(source) {
      if (!this.pricing) return;

      if (source === 'auto') {
        const e = this.autoDiscountEligibility;
        this.pricing = {
          ...this.pricing,
          discount_source: 'auto',
          discount_type: e.eligible ? 'percent' : null,
          discount_value: e.eligible ? this.autoDiscountPercent : null,
          auto_discount_reason: e.eligible ? e.reason : null
        };
      } else if (source === 'manual') {
        this.pricing = {
          ...this.pricing,
          discount_source: 'manual',
          discount_type: this.pricing.discount_type || 'percent',
          discount_value: this.pricing.discount_value || this.autoDiscountPercent,
          auto_discount_reason: null
        };
      } else {
        this.pricing = {
          ...this.pricing,
          discount_source: 'none',
          discount_type: null,
          discount_value: null,
          discount_reason: '',
          auto_discount_reason: null
        };
      }
    },

    keysValid(keys) {
      return !keys.some(k => this.fieldErrors[k]);
    },

    getValidationMessage() {
      return 'Por favor, completa todos los campos marcados con * antes de continuar.';
    },

    goNext() {
      this.stepAttempted[this.currentStep] = true;

      if (!this.currentStepValid) {
        this.alertMessage = this.getValidationMessage();
        this.showAlertModal = true;
        return;
      }

      this.stepAttempted[this.currentStep] = false;
      if (this.currentStep < TOTAL_STEPS) {
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

    runSubmitChecks() {
      this.submittingAttempted = true;
      this.stepAttempted = { 1: true, 2: true, 3: true };

      if (this.canSubmit) return true;

      const firstInvalid = [1, 2, 3].find(s => !this.keysValid(this.stepKeys[s] || []));
      if (firstInvalid && firstInvalid !== this.currentStep) {
        this.navDirection = firstInvalid > this.currentStep ? 'forward' : 'backward';
        this.currentStep = firstInvalid;
      }

      this.alertMessage = this.getValidationMessage();
      this.showAlertModal = true;
      return false;
    },

    async loadCatalogs() {
      this.loadingCatalogs = true;
      try {
        const [schoolsRes, photoTypesRes, packagesRes, paperTypesRes, servicesRes, backgroundsRes] = await Promise.all([
          axios.get('/schools'),
          axios.get('/photo-types'),
          axios.get('/packages'),
          optionalGet('/paper-types'),
          optionalGet('/additional-services'),
          optionalGet('/backgrounds')
        ]);
        this.schools = schoolsRes.data;
        this.photoTypes = photoTypesRes.data;
        this.existingPackages = packagesRes.data;
        this.paperTypes = paperTypesRes.data;
        this.additionalServices = servicesRes.data;
        this.backgrounds = backgroundsRes.data;
      } catch (error) {
        console.error('Error cargando catálogos:', error);
        this.$emit('notify', { title: 'Error', message: 'No se pudieron cargar los catálogos', type: 'error' });
      } finally {
        this.loadingCatalogs = false;
      }
    },

    openSuccess(title, message, next = null) {
      this.successModalTitle = title;
      this.successModalMessage = message;
      this.successModalNext = next;
      this.showSuccessModal = true;
    },

    onSuccessClosed() {
      this.showSuccessModal = false;
      const next = this.successModalNext;
      this.successModalNext = null;
      this.submitting = false;
      if (typeof next === 'function') next();
    },

    resetWizardState() {
      this.currentStep = 1;
      this.navDirection = 'forward';
      this.stepAttempted = freshStepAttempts();
      this.submittingAttempted = false;
      this.successModalTitle = '';
      this.successModalMessage = '';
      this.successModalNext = null;
    },

    handleClose() {
      const isChanged = JSON.stringify(this.getCurrentState()) !== JSON.stringify(this.initialState);
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
    }
  }
};