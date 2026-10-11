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
        isPackage: true,
        packageName: pkg.name || ('Paquete ' + pkg.package_number),
        schoolName: pkg.school?.name || '',
        amount: Number(pkg.total_price || 0),
        children: this.buildPackageChildren(pkg)
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
  },
  methods: {
    buildPackageChildren(pkg) {
      const children = [];

      const photoTypes = Array.isArray(pkg.photo_types) ? pkg.photo_types : [];
      photoTypes.forEach((pt, idx) => {
        const quantity = Number(pt.pivot?.quantity || 0);
        const unitPrice = Number(pt.pivot?.unit_price ?? pt.base_price ?? 0);
        const format = pt.pivot?.format || 'color';
        const formatLabel = format === 'color' ? 'Color' : 'Blanco/negro';
        children.push({
          key: `pkg-${pkg.id}-pt-${pt.id}-${idx}`,
          label: `${pt.name || 'Tipo de foto'} x${quantity} · ${formatLabel}`,
          amount: unitPrice * quantity,
          unitPrice,
          quantity,
          format
        });
      });

      const services = Array.isArray(pkg.additional_services) ? pkg.additional_services : [];
      services.forEach((svc, idx) => {
        const quantity = Number(svc.pivot?.quantity || 0);
        const unitPrice = Number(svc.pivot?.unit_price ?? svc.default_price ?? 0);
        children.push({
          key: `pkg-${pkg.id}-svc-${svc.id}-${idx}`,
          label: `${svc.name || 'Servicio adicional'} x${quantity}`,
          amount: unitPrice * quantity,
          unitPrice,
          quantity,
          icon: 'ti ti-tool'
        });
      });

      return children;
    }
  }
};
</script>