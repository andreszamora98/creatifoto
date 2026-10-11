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
      if (!this.quote) return [];

      const packages = this.quote.quote_packages || [];
      const photos = this.quote.quote_photo_types || [];
      const services = this.quote.services || [];

      const result = [];

      photos.filter(l => !l.quote_package_id).forEach((l, idx) => {
        result.push({
          key: `pt-${l.id ?? l.photo_type_id}-${idx}`,
          label: `${l.photo_type_name_snapshot || 'Tipo de foto'} x${l.quantity}`,
          amount: Number(l.unit_price || 0) * Number(l.quantity || 0),
          unitPrice: Number(l.unit_price || 0),
          quantity: Number(l.quantity || 0),
          format: l.format
        });
      });

      packages.forEach(pkg => {
        const childPhotos = photos.filter(l => l.quote_package_id === pkg.id);
        const childServices = services.filter(s => s.quote_package_id === pkg.id);
        const children = [];

        childPhotos.forEach((l, idx) => {
          const formatLabel = l.format === 'color' ? 'Color' : 'Blanco/negro';
          children.push({
            key: `pkg-${pkg.id}-pt-${l.id}-${idx}`,
            label: `${l.photo_type_name_snapshot || 'Tipo de foto'} x${l.quantity} · ${formatLabel}`,
            amount: Number(l.unit_price || 0) * Number(l.quantity || 0),
            unitPrice: Number(l.unit_price || 0),
            quantity: Number(l.quantity || 0),
            format: l.format
          });
        });

        childServices.forEach((s, idx) => {
          children.push({
            key: `pkg-${pkg.id}-svc-${s.id}-${idx}`,
            label: `${s.additional_service_name_snapshot || 'Servicio adicional'} x${s.quantity}`,
            amount: Number(s.price || 0) * Number(s.quantity || 0),
            unitPrice: Number(s.price || 0),
            quantity: Number(s.quantity || 0),
            icon: 'ti ti-tool'
          });
        });

        result.push({
          key: `pkg-${pkg.id}`,
          isPackage: true,
          packageName: pkg.package_name_snapshot || ('Paquete #' + pkg.package_id),
          schoolName: pkg.school_name_snapshot || '',
          amount: Number(pkg.price_snapshot || 0),
          children
        });
      });

      services.filter(s => !s.quote_package_id).forEach((s, idx) => {
        result.push({
          key: `svc-${s.id}-${idx}`,
          label: `${s.additional_service_name_snapshot || 'Servicio adicional'} x${s.quantity}`,
          amount: Number(s.price || 0) * Number(s.quantity || 0),
          unitPrice: Number(s.price || 0),
          quantity: Number(s.quantity || 0),
          icon: 'ti ti-tool'
        });
      });

      return result;
    }
  }
};
</script>