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
      const photoLines = (this.quote?.quote_photo_types || []).map(l => ({
        key: 'pt-' + (l.id ?? l.photo_type_id),
        label: `${l.photo_type?.name || 'Tipo de foto'} x${l.quantity}`,
        amount: Number(l.unit_price || 0) * Number(l.quantity || 0),
        format: l.format
      }));
      const packageLines = (this.quote?.packages || []).map(pkg => ({
        key: 'pkg-' + pkg.id,
        label: pkg.pivot?.package_name_snapshot || pkg.name || ('Paquete ' + pkg.package_number),
        amount: Number(pkg.pivot?.price_snapshot || pkg.total_price || 0)
      }));
      return [...photoLines, ...packageLines];
    }
  }
};
</script>