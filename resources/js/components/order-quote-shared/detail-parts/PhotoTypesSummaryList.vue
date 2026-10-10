<template>
  <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-3 sm:p-5 short:p-2">
    <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-base sm:text-lg short:text-base flex items-center mb-2 sm:mb-3 short:mb-2">
      <i class="ti ti-photo mr-1.5 sm:mr-2 text-base sm:text-xl short:text-base"></i>Tipos de fotografía
      <span class="ml-2 text-xs sm:text-base short:text-xs px-2 sm:px-3 py-0.5 bg-gray-200 dark:bg-[#293341] text-gray-800 dark:text-gray-300 rounded">
        {{ lines.length }}
      </span>
    </h4>

    <div class="space-y-2 short:space-y-1.5 max-h-60 sm:max-h-72 short:max-h-40 overflow-y-auto pr-1">
      <div
        v-for="line in lines"
        :key="line.id"
        class="flex items-center justify-between p-2.5 sm:p-4 short:p-2 rounded bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 gap-2"
      >
        <div class="flex items-center min-w-0 flex-1">
          <div
            class="relative w-9 h-9 sm:w-11 sm:h-11 short:w-8 short:h-8 rounded-md overflow-hidden mr-2 sm:mr-3 short:mr-2 flex-shrink-0 flex items-center justify-center"
            :class="line.format !== 'color' ? 'bg-gray-200 dark:bg-gray-700' : ''"
          >
            <template v-if="line.format === 'color'">
              <div
                class="absolute inset-0 blur-md bg-[radial-gradient(circle_at_25%_25%,theme(colors.cyan.300),transparent_60%),radial-gradient(circle_at_75%_25%,theme(colors.fuchsia.300),transparent_60%),radial-gradient(circle_at_50%_80%,theme(colors.yellow.300),transparent_60%)] dark:bg-[radial-gradient(circle_at_25%_25%,theme(colors.cyan.700),transparent_60%),radial-gradient(circle_at_75%_25%,theme(colors.fuchsia.700),transparent_60%),radial-gradient(circle_at_50%_80%,theme(colors.yellow.700),transparent_60%)]"
              ></div>

              <span class="relative isolate z-10 inline-block w-[18px] h-[18px]">
                <span class="absolute top-0 left-0 w-3 h-3 rounded-full bg-cyan-400 mix-blend-multiply"></span>
                <span class="absolute top-0 left-1.5 w-3 h-3 rounded-full bg-fuchsia-500 mix-blend-multiply"></span>
                <span class="absolute top-1.5 left-[3px] w-3 h-3 rounded-full bg-yellow-300 mix-blend-multiply"></span>
              </span>
            </template>

            <span v-else class="inline-flex -space-x-1">
              <span class="w-3.5 h-3.5 rounded-full bg-white border border-gray-400 dark:border-gray-500"></span>
              <span class="w-3.5 h-3.5 rounded-full bg-black border border-gray-300 dark:border-gray-400"></span>
            </span>
          </div>

          <div class="truncate">
            <p class="font-medium text-gray-800 dark:text-gray-300 text-sm sm:text-base short:text-sm truncate">
              {{ line.photo_type_name_snapshot || 'Tipo de foto' }}
              <span class="text-gray-400 dark:text-gray-500">
                x{{ line.quantity }}
              </span>
            </p>

            <p class="text-xs sm:text-base short:text-xs text-gray-500 dark:text-gray-400">
              <span v-if="line.format === 'color'">Color</span>
              <span v-else>Blanco y negro</span>
              <span v-if="line.paper_type_name_snapshot"> • {{ line.paper_type_name_snapshot }}</span>
            </p>
          </div>
        </div>

        <span
          v-if="isIncludedInPackage(line)"
          class="text-xs sm:text-sm short:text-[10px] font-semibold text-purple-600 dark:text-purple-300 ml-2 whitespace-nowrap px-1.5 sm:px-2 py-0.5 bg-purple-50 dark:bg-purple-900/30 rounded"
        >
          Incluido
        </span>
        <span v-else class="font-semibold text-gray-700 dark:text-gray-300 text-sm sm:text-base short:text-sm ml-2 whitespace-nowrap">
          {{ formatCurrency(line.unit_price * line.quantity) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PhotoTypesSummaryList',
  props: {
    lines: { type: Array, default: () => [] }
  },
  methods: {
    isIncludedInPackage(line) {
      return Boolean(line?.order_package_id || line?.quote_package_id);
    },
    formatCurrency(value) {
      const num = Number(value || 0);
      return '$' + num.toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }
  }
};
</script>