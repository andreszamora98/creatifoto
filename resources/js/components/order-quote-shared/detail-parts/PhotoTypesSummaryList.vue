<template>
  <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-5">
    <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-lg flex items-center mb-3">
      <i class="ti ti-photo mr-2 text-xl"></i>Tipos de fotografía
      <span class="ml-2 text-base px-3 py-0.5 bg-gray-200 dark:bg-[#293341] text-gray-800 dark:text-gray-300 rounded">
        {{ lines.length }}
      </span>
    </h4>

    <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
      <div
        v-for="line in lines"
        :key="line.id"
        class="flex items-center justify-between p-4 rounded bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
      >
        <div class="flex items-center min-w-0 flex-1">
          <div
            class="relative w-11 h-11 rounded-md overflow-hidden mr-3 flex-shrink-0 flex items-center justify-center"
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

              <!--
              <span class="relative z-10 inline-block w-[18px] h-[18px]">
                <span class="absolute top-0 left-0 w-3 h-3 rounded-full bg-cyan-400 border border-white dark:border-gray-800"></span>
                <span class="absolute top-0 left-1.5 w-3 h-3 rounded-full bg-fuchsia-500 border border-white dark:border-gray-800"></span>
                <span class="absolute top-1.5 left-[3px] w-3 h-3 rounded-full bg-yellow-300 border border-white dark:border-gray-800"></span>
              </span>
              -->
            </template>

            <span v-else class="inline-flex -space-x-1">
              <span class="w-3.5 h-3.5 rounded-full bg-white border border-gray-400 dark:border-gray-500"></span>
              <span class="w-3.5 h-3.5 rounded-full bg-black border border-gray-300 dark:border-gray-400"></span>
            </span>
          </div>

          <div class="truncate">
            <p class="font-medium text-gray-800 dark:text-gray-300 text-base truncate">
              {{ line.photo_type?.name || 'Tipo de foto' }}
              <span class="text-gray-400 dark:text-gray-500">
                x{{ line.quantity }}
              </span>
            </p>

            <p class="text-base text-gray-500 dark:text-gray-400">
              <span v-if="line.format === 'color'">Color</span>
              <span v-else>Blanco y negro</span>
              <span v-if="line.paper_type"> • {{ line.paper_type.name }}</span>
              <span v-if="line.paper_type">
                (+{{ formatCurrency(line.paper_type.extra_price) }})
              </span>
            </p>
          </div>
        </div>

        <span class="font-semibold text-gray-700 dark:text-gray-300 text-base ml-2 whitespace-nowrap">
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