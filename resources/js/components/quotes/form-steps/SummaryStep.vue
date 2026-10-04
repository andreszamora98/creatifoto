<template>
  <section>
    <h4 v-if="sectionTitle" class="text-base font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-5 flex items-center gap-2">
      <span v-if="sectionBadge !== null" class="w-8 h-8 rounded-full bg-blue-500 text-white text-sm flex items-center justify-center font-bold">{{ sectionBadge }}</span>
      {{ sectionTitle }}
    </h4>

    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5 space-y-2">
      <div
        v-for="line in summaryLines"
        :key="line.key"
        class="flex justify-between items-center text-lg text-gray-700 dark:text-gray-300 p-3 rounded-lg border"
        :class="lineBackgroundClass(line)"
      >
        <span class="flex items-center gap-3 min-w-0">
          <span
            v-if="line.format"
            class="relative w-9 h-9 rounded-md overflow-hidden flex-shrink-0 flex items-center justify-center"
            :class="line.format === 'bn' ? 'bg-gray-200 dark:bg-gray-700' : ''"
          >
            <template v-if="line.format === 'color'">
              <span
                class="absolute inset-0 blur-md bg-[radial-gradient(circle_at_25%_25%,theme(colors.cyan.300),transparent_60%),radial-gradient(circle_at_75%_25%,theme(colors.fuchsia.300),transparent_60%),radial-gradient(circle_at_50%_80%,theme(colors.yellow.300),transparent_60%)] dark:bg-[radial-gradient(circle_at_25%_25%,theme(colors.cyan.700),transparent_60%),radial-gradient(circle_at_75%_25%,theme(colors.fuchsia.700),transparent_60%),radial-gradient(circle_at_50%_80%,theme(colors.yellow.700),transparent_60%)]"
              ></span>
              <span class="relative z-10 inline-block w-[18px] h-[18px]">
                <span class="absolute top-0 left-0 w-3 h-3 rounded-full bg-cyan-400 mix-blend-multiply"></span>
                <span class="absolute top-0 left-1.5 w-3 h-3 rounded-full bg-fuchsia-500 mix-blend-multiply"></span>
                <span class="absolute top-1.5 left-[3px] w-3 h-3 rounded-full bg-yellow-300 mix-blend-multiply"></span>
              </span>
            </template>
            <span v-else-if="line.format === 'bn'" class="inline-flex -space-x-1">
              <span class="w-3.5 h-3.5 rounded-full bg-white border border-gray-400 dark:border-gray-500"></span>
              <span class="w-3.5 h-3.5 rounded-full bg-black border border-gray-300 dark:border-gray-400"></span>
            </span>
          </span>
          <span class="truncate">{{ line.label }}</span>
        </span>
        <span class="font-medium whitespace-nowrap ml-2">${{ line.amount.toFixed(2) }}</span>
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
    selectedServices: { type: Array, default: () => [] },
    additionalServices: { type: Array, default: () => [] },
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
      const lines = this.activeLines.map((l, idx) => {
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
      const serviceLines = this.selectedServices.map(s => {
        const svc = this.additionalServices.find(a => a.id === s.additional_service_id);
        return {
          key: 'svc-' + s.additional_service_id,
          label: svc?.name || 'Servicio adicional',
          amount: Number(s.price) || 0
        };
      });
      return [...lines, ...packageLines, ...serviceLines];
    }
  },
  methods: {
    lineBackgroundClass(line) {
      const background = ['color', 'bn'].includes(line.format)
        ? 'bg-gray-100'
        : 'bg-white';

      return `${background} dark:bg-gray-800 border-gray-200 dark:border-gray-600`;
    }
  }
};
</script>