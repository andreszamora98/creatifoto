<template>
  <section class="space-y-3 sm:space-y-4 short:space-y-2">
    <h4 v-if="sectionTitle" class="text-sm sm:text-base short:text-sm font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 flex items-center gap-2">
      <span v-if="sectionBadge !== null" class="w-6 h-6 sm:w-8 sm:h-8 short:w-6 short:h-6 rounded-full bg-blue-500 text-white text-xs sm:text-sm short:text-xs flex items-center justify-center font-bold">{{ sectionBadge }}</span>
      {{ sectionTitle }}
    </h4>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 short:gap-3">
      <div class="lg:col-span-2 bg-gray-50 dark:bg-gray-700/40 rounded-lg p-3 sm:p-5 short:p-2 border border-gray-200 dark:border-gray-700">
        <ChargeSummary
          title="Resumen de la cotización"
          :lines="summaryLines"
          :estimated-total="estimatedTotal"
          :discount-type="discountType"
          :discount-value="discountValue"
          :discount-amount="discountAmount"
          :discount-reason="discountReason"
          total-label="Total"
          empty-message="Agrega tipos de fotografía o paquetes para ver el resumen."
        />
      </div>

      <div class="lg:col-span-1 bg-gray-50 dark:bg-gray-700/40 rounded-lg p-3 sm:p-5 short:p-2 border border-gray-200 dark:border-gray-700">
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-5 short:p-2 h-full">
          <label class="form-label text-sm sm:text-lg short:text-sm font-medium mb-1.5 sm:mb-2 short:mb-1 block text-gray-700 dark:text-gray-300">Vigencia de la cotización</label>
          <input
            :value="validUntil"
            @input="$emit('update:validUntil', $event.target.value)"
            type="date"
            :min="minDateStr"
            class="form-control form-control-sm w-full text-sm sm:text-lg short:text-sm p-2.5 sm:p-4 short:p-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200"
          >
          <p class="text-xs sm:text-sm short:text-xs text-gray-400 dark:text-gray-500 mt-1">
            Opcional. Después de esta fecha la cotización se puede marcar como vencida.
          </p>
        </div>
      </div>
    </div>

    <slot name="discount" />
  </section>
</template>

<script>
import ChargeSummary from '../../order-quote-shared/ChargeSummary.vue';

export default {
  name: 'SummaryStep',
  components: { ChargeSummary },
  props: {
    sectionBadge: { type: [Number, String], default: 3 },
    sectionTitle: { type: String, default: 'Resumen y precio' },
    activeLines: { type: Array, required: true },
    photoTypes: { type: Array, required: true },
    paperTypes: { type: Array, default: () => [] },
    selectedPackages: { type: Array, required: true },
    selectedServices: { type: Array, default: () => [] },
    additionalServices: { type: Array, default: () => [] },
    estimatedTotal: { type: Number, default: 0 },
    discountType: { type: String, default: null },
    discountValue: { type: [Number, String], default: null },
    discountReason: { type: String, default: null },
    validUntil: { type: String, default: '' }
  },
  emits: ['update:validUntil'],
  computed: {
    minDateStr() {
      const today = new Date();
      const y = today.getFullYear();
      const m = String(today.getMonth() + 1).padStart(2, '0');
      const d = String(today.getDate()).padStart(2, '0');
      return `${y}-${m}-${d}`;
    },
    baseDirect() {
      const photoSum = (this.activeLines || []).reduce((sum, l) => {
        const pt = this.photoTypes.find(p => p.id === l.photo_type_id);
        const unitPrice = Number(pt?.base_price || 0);
        const quantity = Number(l.quantity || 0);
        return sum + unitPrice * quantity;
      }, 0);

      const serviceSum = (this.selectedServices || [])
        .filter(s => !s.order_package_id && !s.quote_package_id)
        .reduce((sum, s) => {
          const svc = this.additionalServices.find(a => a.id === s.additional_service_id);
          const quantity = Number(s.quantity) || 1;
          const unitPrice = Number(s.price ?? svc?.default_price ?? 0);
          return sum + unitPrice * quantity;
        }, 0);

      return photoSum + serviceSum;
    },
    discountAmount() {
      if (!this.discountType) return 0;
      const v = Number(this.discountValue || 0);
      if (!Number.isFinite(v) || v <= 0) return 0;

      if (this.discountType === 'percent') {
        const clamped = Math.max(0, Math.min(100, v));
        return Number((this.baseDirect * clamped / 100).toFixed(2));
      }

      if (this.discountType === 'fixed') {
        return Number(Math.min(v, this.baseDirect).toFixed(2));
      }

      return 0;
    },
    summaryLines() {
      const photoLines = (this.activeLines || []).map((l, idx) => {
        const pt = this.photoTypes.find(p => p.id === l.photo_type_id);
        const unitPrice = Number(pt?.base_price || 0);
        const quantity = Number(l.quantity || 0);
        const paper = l.paper_type_id ? this.paperTypes.find(p => Number(p.id) === Number(l.paper_type_id)) : null;
        const name = pt?.name || 'Tipo de foto';
        return {
          key: `pt-${l.photo_type_id}-${idx}`,
          name,
          quantity,
          label: `${name} x${quantity}`,
          amount: unitPrice * quantity,
          unitPrice,
          format: l.format,
          background: l.background || null,
          paper: paper?.name || null
        };
      });

      const packageLines = (this.selectedPackages || []).map(pkg => ({
        key: 'pkg-' + pkg.id,
        isPackage: true,
        packageName: pkg.name || ('Paquete ' + pkg.package_number),
        schoolName: pkg.school?.name || '',
        amount: Number(pkg.total_price || 0),
        children: this.buildPackageChildren(pkg)
      }));

      const serviceLines = (this.selectedServices || [])
        .filter(s => !s.order_package_id && !s.quote_package_id)
        .map(s => {
          const svc = this.additionalServices.find(a => a.id === s.additional_service_id);
          const qty = Number(s.quantity) || 1;
          const unitPrice = Number(s.price ?? svc?.default_price ?? 0);
          const subtotal = Number(s.subtotal ?? (unitPrice * qty)) || 0;
          const name = svc?.name || 'Servicio adicional';
          return {
            key: 'svc-' + s.additional_service_id,
            name,
            quantity: qty,
            label: `${name} x${qty}`,
            amount: subtotal,
            unitPrice,
            icon: 'ti ti-tool'
          };
        });

      return [...photoLines, ...packageLines, ...serviceLines];
    }
  },
  methods: {
    buildPackageChildren(pkg) {
      const children = [];

      const photoTypes = Array.isArray(pkg.photo_types) ? pkg.photo_types : [];
      photoTypes.forEach((pt, idx) => {
        const quantity = Number(pt.pivot?.quantity || 0);
        const unitPrice = Number(pt.pivot?.unit_price ?? pt.base_price ?? 0);
        const format = pt.pivot?.format || 'color';
        const paperId = pt.pivot?.paper_type_id;
        const paper = paperId ? this.paperTypes.find(p => Number(p.id) === Number(paperId)) : null;
        const name = pt.name || 'Tipo de foto';
        children.push({
          key: `pkg-${pkg.id}-pt-${pt.id}-${idx}`,
          name,
          quantity,
          label: `${name} x${quantity}`,
          amount: unitPrice * quantity,
          unitPrice,
          format,
          background: pt.pivot?.background || null,
          paper: paper?.name || null
        });
      });

      const services = Array.isArray(pkg.additional_services) ? pkg.additional_services : [];
      services.forEach((svc, idx) => {
        const quantity = Number(svc.pivot?.quantity || 0);
        const unitPrice = Number(svc.pivot?.unit_price ?? svc.default_price ?? 0);
        const name = svc.name || 'Servicio adicional';
        children.push({
          key: `pkg-${pkg.id}-svc-${svc.id}-${idx}`,
          name,
          quantity,
          label: `${name} x${quantity}`,
          amount: unitPrice * quantity,
          unitPrice,
          icon: 'ti ti-tool'
        });
      });

      return children;
    }
  }
};
</script>