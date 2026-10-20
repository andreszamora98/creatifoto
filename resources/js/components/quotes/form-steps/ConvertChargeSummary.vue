<template>
  <ChargeSummary
    title="Resumen de la cotización"
    :lines="summaryLines"
    :estimated-total="estimatedTotal"
    :discount-type="effectiveDiscountType"
    :discount-value="effectiveDiscountValue"
    :discount-amount="discountAmount"
    :discount-reason="effectiveDiscountReason"
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
    estimatedTotal: { type: Number, default: 0 },
    overrideDiscount: { type: Object, default: null }
  },
  computed: {
    effectiveDiscountType() {
      if (this.overrideDiscount !== null) {
        return this.overrideDiscount.type ?? null;
      }
      return this.quote?.discount_type ?? null;
    },
    effectiveDiscountValue() {
      if (this.overrideDiscount !== null) {
        return this.overrideDiscount.value ?? null;
      }
      return this.quote?.discount_value ?? null;
    },
    effectiveDiscountReason() {
      if (this.overrideDiscount !== null) {
        return this.overrideDiscount.reason ?? null;
      }
      return this.quote?.discount_reason ?? null;
    },
    baseDirect() {
      if (!this.quote) return 0;

      const photos = (this.quote.quote_photo_types || []).filter(l => !l.quote_package_id);
      const photoSum = photos.reduce((sum, l) => sum + Number(l.unit_price || 0) * Number(l.quantity || 0), 0);

      const services = (this.quote.services || []).filter(s => !s.quote_package_id);
      const serviceSum = services.reduce((sum, s) => sum + Number(s.price || 0) * Number(s.quantity || 0), 0);

      return photoSum + serviceSum;
    },
    discountAmount() {
      if (!this.effectiveDiscountType) return 0;
      const value = Number(this.effectiveDiscountValue || 0);
      if (!Number.isFinite(value) || value <= 0) return 0;

      if (this.effectiveDiscountType === 'percent') {
        const clamped = Math.max(0, Math.min(100, value));
        return Number((this.baseDirect * clamped / 100).toFixed(2));
      }

      if (this.effectiveDiscountType === 'fixed') {
        return Number(Math.min(Math.max(0, value), this.baseDirect).toFixed(2));
      }

      return 0;
    },
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