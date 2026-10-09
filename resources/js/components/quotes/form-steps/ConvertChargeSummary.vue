<template>
  <ChargeSummary
    title="Resumen de la cotización"
    :lines="summaryLines"
    :estimated-total="estimatedTotal"
    total-label="Total a cobrar"
    empty-message="Esta cotización no tiene conceptos registrados."
  />
</template>

<script>
import ChargeSummary from '../../order-quote-shared/ChargeSummary.vue';

export default {
  name: 'ConvertChargeSummary',
  components: { ChargeSummary },
  props: {
    quote: { type: Object, default: null },
    estimatedTotal: { type: Number, default: 0 }
  },
  computed: {
    summaryLines() {
      const photoLines = (this.quote?.quote_photo_types || []).map(l => {
        const included = Boolean(l.quote_package_id);
        return {
          key: 'pt-' + (l.id ?? l.photo_type_id),
          label: `${l.photo_type_name_snapshot || 'Tipo de foto'} x${l.quantity}`,
          amount: included ? 0 : Number(l.unit_price || 0) * Number(l.quantity || 0),
          format: l.format,
          icon: included ? 'ti ti-package' : undefined
        };
      });
      const packageLines = (this.quote?.quote_packages || []).map(pkg => ({
        key: 'pkg-' + pkg.id,
        label: pkg.package_name_snapshot || ('Paquete #' + pkg.package_id),
        amount: Number(pkg.price_snapshot || 0)
      }));
      return [...photoLines, ...packageLines];
    }
  }
};
</script>