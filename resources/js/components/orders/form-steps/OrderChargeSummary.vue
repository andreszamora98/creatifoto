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
    estimatedTotal: { type: Number, default: 0 }
  },
  computed: {
    summaryLines() {
      const photoLines = this.activeLines.map((l, idx) => {
        const pt = this.photoTypes.find(p => p.id === l.photo_type_id);
        return {
          key: `pt-${l.photo_type_id}-${idx}`,
          label: `${pt?.name || 'Tipo de foto'} x${l.quantity}`,
          amount: Number(pt?.base_price || 0) * Number(l.quantity || 0),
          format: l.format
        };
      });
      const packageLines = this.selectedPackages.map(pkg => ({
        key: 'pkg-' + pkg.id,
        label: pkg.name || ('Paquete ' + pkg.package_number),
        amount: Number(pkg.total_price || 0)
      }));
      return [...photoLines, ...packageLines];
    }
  }
};
</script>