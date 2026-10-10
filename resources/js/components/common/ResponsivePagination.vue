<template>
  <div v-if="total > 0" class="flex items-center justify-center gap-2">
    <button
      @click="$emit('update:current', current - 1)"
      :disabled="current === 1"
      class="px-2 py-1 rounded-md text-xs font-medium transition-colors"
      :class="current === 1
        ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600'
        : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
      aria-label="Página anterior"
    >
      <i class="ti ti-chevron-left text-sm"></i>
      <span class="hidden sm:inline ml-1">Anterior</span>
    </button>

    <!-- Móvil: contador compacto -->
    <span class="sm:hidden text-xs font-medium text-gray-600 dark:text-gray-400 px-2">
      {{ current }} / {{ total }}
    </span>

    <!-- Desktop: números -->
    <div class="hidden sm:flex items-center gap-1">
      <template v-for="(page, i) in visiblePages" :key="i">
        <span
          v-if="page === '…'"
          class="w-8 h-8 flex items-center justify-center text-xs text-gray-400 dark:text-gray-500"
        >…</span>
        <button
          v-else
          @click="$emit('update:current', page)"
          class="w-8 h-8 rounded-md text-xs font-medium transition-colors"
          :class="current === page
            ? 'bg-blue-500 text-white dark:bg-blue-600'
            : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
        >{{ page }}</button>
      </template>
    </div>

    <button
      @click="$emit('update:current', current + 1)"
      :disabled="current === total"
      class="px-2 py-1 rounded-md text-xs font-medium transition-colors"
      :class="current === total
        ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600'
        : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
      aria-label="Página siguiente"
    >
      <span class="hidden sm:inline mr-1">Siguiente</span>
      <i class="ti ti-chevron-right text-sm"></i>
    </button>
  </div>
</template>

<script>
export default {
  name: 'ResponsivePagination',
  props: {
    current: { type: Number, required: true },
    total: { type: Number, required: true },
  },
  emits: ['update:current'],
  computed: {
    visiblePages() {
      const t = this.total;
      const c = this.current;
      if (t <= 7) return Array.from({ length: t }, (_, i) => i + 1);
      const list = [...new Set([1, t, c - 1, c, c + 1])]
        .filter(p => p >= 1 && p <= t)
        .sort((a, b) => a - b);
      const out = [];
      list.forEach((p, i) => {
        if (i && p - list[i - 1] > 1) out.push('…');
        out.push(p);
      });
      return out;
    },
  },
};
</script>