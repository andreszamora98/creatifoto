export default {
  data() {
    return {
      liquidateAll: false,
      openPaymentMethodDropdownIdx: null
    };
  },
  computed: {
    totalPaid() {
      return this.payments.reduce((sum, p) => sum + (Number(p.amount) || 0), 0);
    },
    remainingBalance() {
      return Math.max(this.estimatedTotal - this.totalPaid, 0);
    },
    paymentConcept() {
      return this.remainingBalance > 0 ? 'Anticipo' : 'De contado';
    },
    showBalanceBadge() {
      return this.estimatedTotal > 0 && this.payments.length > 0;
    }
  },
  methods: {
    safeMarkDirty() {
      if (typeof this.markDirty === 'function') this.markDirty();
    },
    maxAmountFor(idx) {
      const othersSum = this.payments.reduce(
        (sum, p, i) => (i === idx ? sum : sum + (Number(p.amount) || 0)),
        0
      );
      return Math.max(this.estimatedTotal - othersSum, 0);
    },
    toggleLiquidateAll() {
      this.liquidateAll = !this.liquidateAll;
      if (this.liquidateAll) {
        const method = this.payments[0]?.payment_method || '';
        this.payments = [{ amount: this.estimatedTotal, payment_method: method }];
      } else {
        this.payments[0].amount = 0;
      }
      this.safeMarkDirty();
    },
    onPaymentAmountInput(idx) {
      const payment = this.payments[idx];
      const amount = Number(payment.amount) || 0;

      if (this.estimatedTotal > 0 && amount >= this.estimatedTotal) {
        payment.amount = this.estimatedTotal;
        this.payments = [payment];
        this.liquidateAll = true;
        this.safeMarkDirty();
        return;
      }

      const max = this.maxAmountFor(idx);
      if (amount > max) payment.amount = max;
      if (this.liquidateAll) this.liquidateAll = false;
      this.safeMarkDirty();
    },
    addPayment() {
      if (this.remainingBalance <= 0 || this.liquidateAll) return;
      this.payments.push({ amount: null, payment_method: '' });
      this.safeMarkDirty();
    },
    removePayment(idx) {
      this.payments.splice(idx, 1);
      this.safeMarkDirty();
    },
    togglePaymentMethodDropdown(idx) {
      this.openPaymentMethodDropdownIdx = this.openPaymentMethodDropdownIdx === idx ? null : idx;
    },
    closePaymentMethodDropdown(idx) {
      setTimeout(() => {
        if (this.openPaymentMethodDropdownIdx === idx) this.openPaymentMethodDropdownIdx = null;
      }, 200);
    },
    selectLinePaymentMethod(idx, method) {
      this.payments[idx].payment_method = method;
      this.openPaymentMethodDropdownIdx = null;
      this.safeMarkDirty();
    }
  }
};