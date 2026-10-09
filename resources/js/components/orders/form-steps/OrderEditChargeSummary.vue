<template>
  <div class="space-y-4">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600 text-center shadow-sm">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total inicial</p>
        <p class="text-lg font-bold text-gray-700 dark:text-gray-300">{{ formatCurrency(initialTotal) }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600 text-center shadow-sm">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total actual</p>
        <p class="text-lg font-bold"
           :class="totalChanged ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'">
          {{ formatCurrency(estimatedTotal) }}
        </p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600 text-center shadow-sm">
        <p class="text-sm text-gray-500 dark:text-gray-400">Pagado</p>
        <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ formatCurrency(paidTotal) }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600 text-center shadow-sm">
        <p class="text-sm text-gray-500 dark:text-gray-400">Saldo resultante</p>
        <p class="text-lg font-bold"
           :class="balanceResult > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
          {{ formatCurrency(balanceResult) }}
        </p>
      </div>
    </div>

    <div
      v-if="balanceResult < 0"
      class="flex items-start gap-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700"
    >
      <i class="ti ti-alert-triangle text-xl text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5"></i>
      <p class="text-sm text-amber-800 dark:text-amber-300">
        El nuevo total es menor a lo ya pagado. Ajusta el contenido o reduce el monto antes de guardar.
      </p>
    </div>

    <ChargeSummary
      title="Conceptos"
      :lines="summaryLines"
      :estimated-total="estimatedTotal"
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
    packageLines: { type: Array, default: () => [] },
    packages: { type: Array, default: () => [] },
    serviceLines: { type: Array, default: () => [] },
    additionalServices: { type: Array, default: () => [] },
    estimatedTotal: { type: Number, default: 0 },
    initialTotal: { type: Number, default: 0 },
    paidTotal: { type: Number, default: 0 }
  },
  computed: {
    totalChanged() {
      return Math.abs(Number(this.estimatedTotal) - Number(this.initialTotal)) >= 0.01;
    },
    balanceResult() {
      return Number(this.estimatedTotal) - Number(this.paidTotal);
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
          return {
            key: `pt-${l.id ?? 'new'}-${idx}`,
            label: `${name} x${qty}${included ? ' (incluido)' : ''}`,
            amount: included ? 0 : unitPrice * qty,
            format: l.format
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
          return {
            key: `pkg-${l.id ?? 'new'}-${idx}`,
            label: name,
            amount,
            icon: 'ti ti-school'
          };
        });

      const serviceLines = this.serviceLines
        .filter(l => l.service_id)
        .map((l, idx) => {
          const fromCatalog = this.additionalServices.find(s => s.id === l.service_id);
          const name = fromCatalog?.name || l.additional_service_name_snapshot || 'Servicio adicional';
          const qty = Number(l.quantity || 1);
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
            label: `${name} x${qty}`,
            amount: unitPrice * qty,
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