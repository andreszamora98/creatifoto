<template>
  <div class="space-y-3 sm:space-y-4 short:space-y-2">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 short:gap-2">
      <div class="bg-white dark:bg-gray-800 rounded-lg p-2.5 sm:p-4 short:p-2 border border-gray-200 dark:border-gray-600 text-center shadow-sm">
        <p class="text-xs sm:text-sm short:text-xs text-gray-500 dark:text-gray-400">Total inicial</p>
        <p class="text-sm sm:text-lg short:text-sm font-bold text-gray-700 dark:text-gray-300">{{ formatCurrency(initialTotal) }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg p-2.5 sm:p-4 short:p-2 border border-gray-200 dark:border-gray-600 text-center shadow-sm">
        <p class="text-xs sm:text-sm short:text-xs text-gray-500 dark:text-gray-400">Total actual</p>
        <p class="text-sm sm:text-lg short:text-sm font-bold"
           :class="totalChanged ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'">
          {{ formatCurrency(estimatedTotal) }}
        </p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg p-2.5 sm:p-4 short:p-2 border border-gray-200 dark:border-gray-600 text-center shadow-sm">
        <p class="text-xs sm:text-sm short:text-xs text-gray-500 dark:text-gray-400">Pagado</p>
        <p class="text-sm sm:text-lg short:text-sm font-bold text-green-600 dark:text-green-400">{{ formatCurrency(paidTotal) }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg p-2.5 sm:p-4 short:p-2 border border-gray-200 dark:border-gray-600 text-center shadow-sm">
        <p class="text-xs sm:text-sm short:text-xs text-gray-500 dark:text-gray-400">Saldo resultante</p>
        <p class="text-sm sm:text-lg short:text-sm font-bold"
           :class="balanceResult > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
          {{ formatCurrency(balanceResult) }}
        </p>
      </div>
    </div>

    <div
      v-if="balanceResult < 0"
      class="flex items-start gap-2 sm:gap-3 short:gap-2 p-2.5 sm:p-3 short:p-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700"
    >
      <i class="ti ti-alert-triangle text-base sm:text-xl short:text-base text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5"></i>
      <p class="text-xs sm:text-sm short:text-xs text-amber-800 dark:text-amber-300">
        El nuevo total es menor a lo ya pagado. Ajusta el contenido o reduce el monto antes de guardar.
      </p>
    </div>

    <ChargeSummary
      title="Conceptos"
      :lines="summaryLines"
      :estimated-total="estimatedTotal"
      :discount-source="discountSource"
      :discount-type="discountType"
      :discount-value="discountValue"
      :discount-amount="discountAmount"
      :discount-reason="discountReason"
      :discount-includes-packages="discountIncludesPackages"
      total-label="Total"
      empty-message="Sin conceptos registrados."
    />
  </div>
</template>

<script>
import ChargeSummary from '../../order-quote-shared/ChargeSummary.vue';

export default {
  name: 'OrderEditChargeSummary',
  components: { ChargeSummary },
  props: {
    photoLines: { type: Array, default: () => [] },
    photoTypes: { type: Array, default: () => [] },
    paperTypes: { type: Array, default: () => [] },
    packageLines: { type: Array, default: () => [] },
    packages: { type: Array, default: () => [] },
    serviceLines: { type: Array, default: () => [] },
    additionalServices: { type: Array, default: () => [] },
    estimatedTotal: { type: Number, default: 0 },
    initialTotal: { type: Number, default: 0 },
    paidTotal: { type: Number, default: 0 },
    discountSource: { type: String, default: null },
    discountType: { type: String, default: null },
    discountValue: { type: [Number, String], default: null },
    discountReason: { type: String, default: null },
    discountIncludesPackages: { type: Boolean, default: false }
  },
  computed: {
    totalChanged() {
      return Math.abs(Number(this.estimatedTotal) - Number(this.initialTotal)) >= 0.01;
    },
    netTotal() {
      return Math.max(0, Number(this.estimatedTotal) - Number(this.discountAmount));
    },
    balanceResult() {
      return Number(this.netTotal) - Number(this.paidTotal);
    },
    baseDirect() {
      const photoSum = this.photoLines
        .filter(l => l.photo_type_id && !l.order_package_id && !l.quote_package_id)
        .reduce((sum, l) => {
          const locked = Number(l.locked_unit_price ?? 0);
          const price = l.id && locked > 0
            ? locked
            : Number(this.photoTypes.find(p => p.id === l.photo_type_id)?.base_price || 0);
          return sum + price * Number(l.quantity || 0);
        }, 0);

      const serviceSum = this.serviceLines
        .filter(l => l.service_id && !l.order_package_id && !l.quote_package_id)
        .reduce((sum, l) => {
          const price = (l.price !== undefined && l.price !== null)
            ? Number(l.price)
            : Number(this.additionalServices.find(s => s.id === l.service_id)?.default_price || 0);
          return sum + price * Number(l.quantity || 1);
        }, 0);

      let base = photoSum + serviceSum;

      if (this.discountIncludesPackages) {
        const packagesTotal = (this.packageLines || [])
          .filter(l => l.package_id)
          .reduce((sum, l) => sum + Number(l.price_snapshot ?? 0), 0);
        base += packagesTotal;
      }

      return base;
    },
    discountAmount() {
      if (!this.discountType) return 0;
      const v = Number(this.discountValue || 0);
      if (!Number.isFinite(v) || v <= 0) return 0;

      const base = Number(this.baseDirect);

      if (this.discountType === 'percent') {
        const clamped = Math.max(0, Math.min(100, v));
        return Number((base * clamped / 100).toFixed(2));
      }

      if (this.discountType === 'fixed') {
        return Number(Math.min(v, base).toFixed(2));
      }

      return 0;
    },
    summaryLines() {
      const photoLines = this.photoLines
        .filter(l => l.photo_type_id)
        .map((l, idx) => {
          const included = Boolean(l.order_package_id || l.quote_package_id);
          const fromCatalog = this.photoTypes.find(p => p.id === l.photo_type_id);
          const name = fromCatalog?.name || l.photo_type_name_snapshot || 'Tipo de foto';
          let unitPrice;
          if (l.id && l.locked_unit_price !== undefined && l.locked_unit_price !== null) {
            unitPrice = Number(l.locked_unit_price);
          } else if (fromCatalog) {
            unitPrice = Number(fromCatalog.base_price || 0);
          } else {
            unitPrice = 0;
          }
          const qty = Number(l.quantity || 0);
          const paper = l.paper_type_name_snapshot
            || (l.paper_type_id ? this.paperTypes.find(p => Number(p.id) === Number(l.paper_type_id))?.name : null);
          return {
            key: `pt-${l.id ?? 'new'}-${idx}`,
            name,
            quantity: qty,
            included,
            label: `${name} x${qty}${included ? ' (incluido)' : ''}`,
            amount: included ? 0 : unitPrice * qty,
            unitPrice,
            format: l.format,
            background: l.background || null,
            paper: paper || null
          };
        });

        const packageLines = this.packageLines
        .filter(l => l.package_id)
        .map((l, idx) => {
          const fromCatalog = this.packages.find(p => p.id === l.package_id);
          const name = fromCatalog?.name || l.package_name_snapshot || ('Paquete #' + l.package_id);
          let amount;
          if (l.id && l.price_snapshot !== undefined && l.price_snapshot !== null) {
            amount = Number(l.price_snapshot);
          } else if (fromCatalog) {
            amount = Number(fromCatalog.total_price || 0);
          } else {
            amount = 0;
          }
          let listPrice = 0;
          if (l.list_price_snapshot != null) listPrice = Number(l.list_price_snapshot);
          else if (fromCatalog?.list_price != null) listPrice = Number(fromCatalog.list_price);
          const savedAmount = listPrice > amount ? listPrice - amount : 0;
          const savedPercent = (savedAmount > 0 && fromCatalog?.discount_type === 'percent')
            ? Number(fromCatalog.discount_value || 0)
            : null;
          let savedReason = null;
          if (savedAmount > 0 && fromCatalog) {
            if (fromCatalog.discount_source === 'auto') savedReason = fromCatalog.auto_discount_reason || null;
            else if (fromCatalog.discount_source === 'manual') savedReason = fromCatalog.discount_reason || null;
          }
          return {
            key: `pkg-${l.id ?? 'new'}-${idx}`,
            label: name,
            amount,
            savedAmount,
            savedPercent,
            savedReason,
            icon: 'ti ti-school'
          };
        });

      const serviceLines = this.serviceLines
        .filter(l => l.service_id)
        .map((l, idx) => {
          const fromCatalog = this.additionalServices.find(s => s.id === l.service_id);
          const name = fromCatalog?.name || l.additional_service_name_snapshot || 'Servicio adicional';
          const qty = Number(l.quantity || 1);
          const included = Boolean(l.order_package_id || l.quote_package_id);
          let unitPrice;
          if (l.id && l.price !== undefined && l.price !== null) {
            unitPrice = Number(l.price);
          } else if (fromCatalog) {
            unitPrice = Number(fromCatalog.default_price || 0);
          } else {
            unitPrice = 0;
          }
          return {
            key: `svc-${l.id ?? 'new'}-${idx}`,
            name,
            quantity: qty,
            included,
            label: `${name} x${qty}${included ? ' (incluido)' : ''}`,
            amount: included ? 0 : unitPrice * qty,
            unitPrice,
            icon: 'ti ti-tool'
          };
        });

      return [...photoLines, ...packageLines, ...serviceLines];
    }
  },
  methods: {
    formatCurrency(value) {
      const num = Number(value || 0);
      return '$' + num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
  }
};
</script>