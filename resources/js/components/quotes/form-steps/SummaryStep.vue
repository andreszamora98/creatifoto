<template>
  <section>
    <h4 v-if="sectionTitle" class="text-base font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-5 flex items-center gap-2">
      <span v-if="sectionBadge !== null" class="w-8 h-8 rounded-full bg-blue-500 text-white text-sm flex items-center justify-center font-bold">{{ sectionBadge }}</span>
      {{ sectionTitle }}
    </h4>

    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5 space-y-3">
      <div v-for="line in summaryLines" :key="line.key" class="flex justify-between items-center text-lg text-gray-700 dark:text-gray-300">
        <span>{{ line.label }}</span>
        <span class="font-medium">${{ line.amount.toFixed(2) }}</span>
      </div>

      <div v-if="!summaryLines.length" class="text-lg text-gray-400 dark:text-gray-500 italic">
        Agrega tipos de fotografía o paquetes para ver el resumen.
      </div>

      <div class="border-t border-gray-300 dark:border-gray-600 pt-3 flex justify-between items-center">
        <span class="text-xl font-semibold text-gray-800 dark:text-gray-200">Total</span>
        <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">${{ estimatedTotal.toFixed(2) }}</span>
      </div>
    </div>

    <div class="mt-6">
      <label class="form-label text-lg font-medium mb-2 block text-gray-700 dark:text-gray-300">Vigencia de la cotización</label>
      <input
        :value="validUntil"
        @input="$emit('update:validUntil', $event.target.value)"
        type="date"
        :min="minDateStr"
        class="form-control form-control-sm text-lg p-4 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
      >
      <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Opcional. Después de esta fecha la cotización se puede marcar como vencida.</p>
    </div>
  </section>
</template>

<script>
export default {
  name: 'SummaryStep',
  props: {
    sectionBadge: { type: [Number, String], default: 3 },
    sectionTitle: { type: String, default: 'Resumen y precio' },
    activeLines: { type: Array, required: true },
    photoTypes: { type: Array, required: true },
    selectedPackages: { type: Array, required: true },
    estimatedTotal: { type: Number, default: 0 },
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
    summaryLines() {
      const lines = this.activeLines.map(l => {
        const pt = this.photoTypes.find(p => p.id === l.photo_type_id);
        return {
          key: 'pt-' + l.photo_type_id,
          label: `${pt?.name || 'Tipo de foto'} x${l.quantity}`,
          amount: Number(pt?.base_price || 0) * l.quantity
        };
      });
      const packageLines = this.selectedPackages.map(pkg => ({
        key: 'pkg-' + pkg.id,
        label: pkg.name || ('Paquete ' + pkg.package_number),
        amount: Number(pkg.total_price || 0)
      }));
      return [...lines, ...packageLines];
    }
  }
};
</script>