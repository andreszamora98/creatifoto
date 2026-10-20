<template>
  <section class="space-y-3 sm:space-y-4 short:space-y-2">
    <h4 class="text-base sm:text-lg short:text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center">
      <i class="ti ti-package mr-1.5 sm:mr-2 text-base sm:text-xl short:text-base"></i>Resumen del paquete
    </h4>

    <div>
      <p class="text-base sm:text-lg short:text-sm font-semibold text-gray-800 dark:text-gray-200 break-words">{{ school?.name || '—' }}</p>
      <p class="text-sm sm:text-base short:text-xs text-gray-500 dark:text-gray-400 break-words">{{ title }}</p>
    </div>

    <div>
      <h5 class="text-xs sm:text-sm short:text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1.5 sm:mb-2 short:mb-1">
        Tipos de fotografía
      </h5>
      <ul v-if="lines.length" class="space-y-1.5 sm:space-y-2 short:space-y-1.5">
        <li
          v-for="(line, idx) in lines"
          :key="idx"
          class="flex items-center justify-between gap-2 sm:gap-3 short:gap-2 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg px-2.5 sm:px-4 short:px-2.5 py-2 sm:py-3 short:py-2"
        >
          <div class="flex items-center gap-2 sm:gap-3 short:gap-2 min-w-0">
            <span
              v-if="line.format"
              class="relative w-7 h-7 sm:w-9 sm:h-9 short:w-6 short:h-6 rounded-md overflow-hidden flex-shrink-0 flex items-center justify-center"
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

            <div class="min-w-0">
              <p class="text-sm sm:text-base short:text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ line.name }}</p>
              <p class="text-xs sm:text-sm short:text-xs text-gray-500 dark:text-gray-400">
                {{ line.quantity }} × ${{ line.unit_price.toFixed(2) }}
                <span v-if="line.paper_name"> · {{ line.paper_name }}</span>
                <span v-if="line.background && line.background !== 'blanco'"> · Fondo: {{ line.background }}</span>
              </p>
            </div>
          </div>
          <span class="text-sm sm:text-base short:text-sm font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">
            ${{ line.subtotal.toFixed(2) }}
          </span>
        </li>
      </ul>
      <p v-else class="text-sm sm:text-base short:text-sm text-gray-400 dark:text-gray-500 italic">Sin tipos de fotografía.</p>
    </div>

    <div v-if="services.length">
      <h5 class="text-xs sm:text-sm short:text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1.5 sm:mb-2 short:mb-1">
        Servicios adicionales
      </h5>
      <ul class="space-y-1.5 sm:space-y-2 short:space-y-1.5">
        <li
          v-for="s in services"
          :key="s.additional_service_id"
          class="flex items-center justify-between gap-2 sm:gap-3 short:gap-2 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg px-2.5 sm:px-4 short:px-2.5 py-2 sm:py-3 short:py-2"
        >
          <div class="flex items-center gap-2 sm:gap-3 short:gap-2 min-w-0">
            <span class="w-7 h-7 sm:w-9 sm:h-9 short:w-6 short:h-6 rounded-md flex-shrink-0 flex items-center justify-center bg-gray-200 dark:bg-gray-700">
              <i class="ti ti-tool text-base sm:text-xl short:text-sm text-gray-500 dark:text-gray-400"></i>
            </span>
            <div class="min-w-0">
              <p class="text-sm sm:text-base short:text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ s.name }}</p>
              <p class="text-xs sm:text-sm short:text-xs text-gray-500 dark:text-gray-400">
                {{ s.quantity }} × ${{ Number(s.price).toFixed(2) }}
                <span v-if="s.notes"> · {{ s.notes }}</span>
              </p>
            </div>
          </div>
          <span class="text-sm sm:text-base short:text-sm font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">
            ${{ Number(s.subtotal ?? (s.price * (s.quantity || 1))).toFixed(2) }}
          </span>
        </li>
      </ul>
    </div>

    <div v-if="womenText || menText" class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 short:gap-2">
      <div v-if="womenText">
        <h5 class="text-xs sm:text-sm short:text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Mujeres</h5>
        <p class="text-xs sm:text-sm short:text-xs text-gray-600 dark:text-gray-400 whitespace-pre-line max-h-24 sm:max-h-28 short:max-h-20 overflow-y-auto">{{ womenText }}</p>
      </div>
      <div v-if="menText">
        <h5 class="text-xs sm:text-sm short:text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Hombres</h5>
        <p class="text-xs sm:text-sm short:text-xs text-gray-600 dark:text-gray-400 whitespace-pre-line max-h-24 sm:max-h-28 short:max-h-20 overflow-y-auto">{{ menText }}</p>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'PackageSummary',
  props: {
    school: { type: Object, default: null },
    packageNumber: { type: [Number, String], default: null },
    name: { type: String, default: '' },
    lines: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    womenText: { type: String, default: '' },
    menText: { type: String, default: '' }
  },
  computed: {
    title() {
      const num = this.packageNumber ? 'Paquete ' + String(this.packageNumber).padStart(2, '0') : '';
      return [num, (this.name || '').trim()].filter(Boolean).join(' · ');
    }
  }
};
</script>