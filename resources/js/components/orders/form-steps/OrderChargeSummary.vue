<template>
  <ChargeSummary
    title="Resumen de la orden"
    :lines="summaryLines"
    :estimated-total="estimatedTotal"
    :discount-type="discountType"
    :discount-value="discountValue"
    :discount-amount="discountAmount"
    :discount-reason="discountReason"
    total-label="Total a cobrar"
    empty-message="Agrega tipos de fotografía o paquetes para ver el resumen."
  />
</template>

<script>
import ChargeSummary from '../../order-quote-shared/ChargeSummary.vue';

export default {
  name: 'OrderChargeSummary',
  components: { ChargeSummary },
  props: {
    activeLines: { type: Array, required: true },
    photoTypes: { type: Array, required: true },
    paperTypes: { type: Array, default: () => [] },
    selectedPackages: { type: Array, required: true },
    selectedServices: { type: Array, default: () => [] },
    additionalServices: { type: Array, default: () => [] },
    estimatedTotal: { type: Number, default: 0 },
    discountType: { type: String, default: null },
    discountValue: { type: [Number, String], default: null },
    discountReason: { type: String, default: null }
  },
  computed: {
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
      const value = Number(this.discountValue || 0);
      if (!Number.isFinite(value) || value <= 0) return 0;

      if (this.discountType === 'percent') {
        const clamped = Math.max(0, Math.min(100, value));
        return Number((this.baseDirect * clamped / 100).toFixed(2));
      }

      if (this.discountType === 'fixed') {
        return Number(Math.min(Math.max(0, value), this.baseDirect).toFixed(2));
      }

      return 0;
    },
    summaryLines() {
      const photoLines = (this.activeLines || []).map((l, idx) => {
        const pt = this.photoTypes.find(p => p.id === l.photo_type_id);
        const unitPrice = Number(pt?.base_price || 0);
        const quantity = Number(l.quantity || 0);
        const paper = l.paper_type_id
          ? this.paperTypes.find(p => Number(p.id) === Number(l.paper_type_id))
          : null;
        return {
          key: `pt-${l.photo_type_id}-${idx}`,
          name: pt?.name || 'Tipo de foto',
          quantity,
          label: `${pt?.name || 'Tipo de foto'} x${quantity}`,
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
          const quantity = Number(s.quantity) || 1;
          const unitPrice = Number(s.price ?? svc?.default_price ?? 0);
          const subtotal = Number(s.subtotal ?? (unitPrice * quantity)) || 0;
          const name = svc?.name || 'Servicio adicional';
          return {
            key: 'svc-' + s.additional_service_id,
            name,
            quantity,
            label: `${name} x${quantity}`,
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