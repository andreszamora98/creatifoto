<template>
  <section>
    <h4 v-if="title" class="text-base font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-5">
      {{ title }}
    </h4>

    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5 space-y-3">
      <div v-for="line in lines" :key="line.key" class="flex justify-between items-center text-lg text-gray-700 dark:text-gray-300">
        <span class="flex items-center gap-2 min-w-0">
          <span v-if="line.format === 'color'" class="relative isolate inline-block w-[18px] h-[18px] flex-shrink-0">
            <span class="absolute top-0 left-0 w-3 h-3 rounded-full bg-cyan-400 mix-blend-multiply"></span>
            <span class="absolute top-0 left-1.5 w-3 h-3 rounded-full bg-fuchsia-500 mix-blend-multiply"></span>
            <span class="absolute top-1.5 left-[3px] w-3 h-3 rounded-full bg-yellow-300 mix-blend-multiply"></span>
          </span>
          <span v-else-if="line.format === 'bn'" class="inline-flex -space-x-1 flex-shrink-0">
            <span class="w-3.5 h-3.5 rounded-full bg-white border border-gray-400 dark:border-gray-500"></span>
            <span class="w-3.5 h-3.5 rounded-full bg-black border border-gray-300 dark:border-gray-400"></span>
          </span>
          <span class="truncate">{{ line.label }}</span>
        </span>
        <span class="font-medium whitespace-nowrap ml-2">${{ line.amount.toFixed(2) }}</span>
      </div>

      <div v-if="!lines.length" class="text-lg text-gray-400 dark:text-gray-500 italic">
        {{ emptyMessage }}
      </div>

      <div class="border-t border-gray-300 dark:border-gray-600 pt-3 flex justify-between items-center">
        <span class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ totalLabel }}</span>
        <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">${{ estimatedTotal.toFixed(2) }}</span>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'ChargeSummary',
  props: {
    title: { type: String, default: '' },
    lines: { type: Array, default: () => [] },
    estimatedTotal: { type: Number, default: 0 },
    totalLabel: { type: String, default: 'Total a cobrar' },
    emptyMessage: { type: String, default: 'No hay conceptos registrados.' }
  }
};
</script>