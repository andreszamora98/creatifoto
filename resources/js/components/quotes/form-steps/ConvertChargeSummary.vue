<template>
  <section>
    <h4 class="text-base font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-5">
      Resumen de la cotización
    </h4>

    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5 space-y-3">
      <div v-for="line in summaryLines" :key="line.key" class="flex justify-between items-center text-lg text-gray-700 dark:text-gray-300">
        <span>{{ line.label }}</span>
        <span class="font-medium">${{ line.amount.toFixed(2) }}</span>
      </div>

      <div v-if="!summaryLines.length" class="text-lg text-gray-400 dark:text-gray-500 italic">
        Esta cotización no tiene conceptos registrados.
      </div>

      <div class="border-t border-gray-300 dark:border-gray-600 pt-3 flex justify-between items-center">
        <span class="text-xl font-semibold text-gray-800 dark:text-gray-200">Total a cobrar</span>
        <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">${{ estimatedTotal.toFixed(2) }}</span>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'ConvertChargeSummary',
  props: {
    quote: { type: Object, default: null },
    estimatedTotal: { type: Number, default: 0 }
  },
  computed: {
    summaryLines() {
      const photoLines = (this.quote?.quote_photo_types || []).map(l => ({
        key: 'pt-' + (l.id ?? l.photo_type_id),
        label: `${l.photo_type?.name || 'Tipo de foto'} x${l.quantity}`,
        amount: Number(l.unit_price || 0) * Number(l.quantity || 0)
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