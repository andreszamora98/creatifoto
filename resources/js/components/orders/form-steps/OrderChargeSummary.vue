<template>
  <ChargeSummary
    title="Resumen de la orden"
    :lines="summaryLines"
    :estimated-total="estimatedTotal"
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
    selectedPackages: { type: Array, required: true },
    selectedServices: { type: Array, default: () => [] },
    additionalServices: { type: Array, default: () => [] },
    estimatedTotal: { type: Number, default: 0 }
  },
  computed: {
    summaryLines() {
      const photoLines = this.activeLines.map((l, idx) => {
        const pt = this.photoTypes.find(p => p.id === l.photo_type_id);
        const unitPrice = Number(pt?.base_price || 0);
        const quantity = Number(l.quantity || 0);
        return {
          key: `pt-${l.photo_type_id}-${idx}`,
          label: `${pt?.name || 'Tipo de foto'} x${quantity}`,
          amount: unitPrice * quantity,
          unitPrice,
          quantity,
          format: l.format
        };
      });

      const packageLines = this.selectedPackages.map(pkg => ({
        key: 'pkg-' + pkg.id,
        label: pkg.name || ('Paquete ' + pkg.package_number),
        amount: Number(pkg.total_price || 0),
        icon: 'ti ti-school'
      }));

      const serviceLines = this.selectedServices.map(s => {
        const svc = this.additionalServices.find(a => a.id === s.additional_service_id);
        const quantity = Number(s.quantity) || 1;
        const unitPrice = Number(s.price ?? svc?.default_price ?? 0);
        const subtotal = Number(s.subtotal ?? (unitPrice * quantity)) || 0;
        return {
          key: 'svc-' + s.additional_service_id,
          label: `${svc?.name || 'Servicio adicional'} x${quantity}`,
          amount: subtotal,
          unitPrice,
          quantity,
          icon: 'ti ti-tool'
        };
      });

      return [...photoLines, ...packageLines, ...serviceLines];
    }
  }
};
</script>