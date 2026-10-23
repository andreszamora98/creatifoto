<template>
  <section>
    <h4 v-if="title" class="text-sm sm:text-base short:text-sm font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-3 sm:mb-5 short:mb-2">
      {{ title }}
    </h4>

    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-5 short:p-2 space-y-1.5 sm:space-y-2 short:space-y-1.5">
      <template v-for="line in lines" :key="line.key">
        <div
          v-if="line.isPackage"
          class="rounded-lg border bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-600 overflow-hidden"
        >
          <div class="flex justify-between items-start text-sm sm:text-lg short:text-sm p-2 sm:p-3 short:p-2 gap-2">
            <span class="flex items-start gap-2 sm:gap-3 short:gap-2 min-w-0 flex-1">
              <span class="w-7 h-7 sm:w-9 sm:h-9 short:w-6 short:h-6 rounded-md flex-shrink-0 flex items-center justify-center bg-gray-200 dark:bg-gray-700 mt-0.5">
                <i class="ti ti-school text-base sm:text-xl short:text-sm text-gray-500 dark:text-gray-400"></i>
              </span>

              <span class="flex flex-col min-w-0 flex-1 gap-0.5">
                <span class="font-semibold break-words">
                  <span class="text-gray-700 dark:text-gray-300 mr-1">Paquete</span>
                  <span class="text-blue-600 dark:text-blue-400">{{ line.packageName }}</span>
                </span>
                <span
                  v-if="line.schoolName"
                  class="text-xs sm:text-sm short:text-[11px] text-gray-400 dark:text-gray-500 break-words"
                >
                  {{ line.schoolName }}
                </span>
                <span v-if="line.savedAmount > 0" class="flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-[10px] sm:text-xs short:text-[10px]">
                  <span class="inline-flex items-center gap-1 text-green-700 dark:text-green-500 font-medium">
                    <i class="ti ti-discount-2 text-[10px] sm:text-xs"></i>
                    Descuento incluido<span v-if="line.savedPercent !== null && line.savedPercent !== undefined"> {{ line.savedPercent }}%</span>
                    (-${{ line.savedAmount.toFixed(2) }})
                  </span>
                  <span v-if="line.savedReason" class="italic text-green-700 dark:text-green-400">
                    · Motivo: {{ line.savedReason }}
                  </span>
                </span>
              </span>
            </span>
            <span class="flex flex-col items-end gap-0.5 flex-shrink-0 ml-2">
              <span v-if="line.savedAmount > 0" class="text-[10px] sm:text-xs short:text-[10px] text-gray-400 dark:text-gray-500 line-through">
                ${{ (line.amount + line.savedAmount).toFixed(2) }}
              </span>
              <span class="font-semibold whitespace-nowrap text-gray-800 dark:text-gray-200">${{ line.amount.toFixed(2) }}</span>
            </span>
          </div>

          <div
            v-if="(line.children || []).length"
            class="border-t border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 px-2 sm:px-3 short:px-2 py-1.5 sm:py-2 short:py-1.5 space-y-0.5"
          >
            <div
              v-for="child in line.children"
              :key="child.key"
              class="relative flex justify-between items-start text-xs sm:text-base short:text-xs text-gray-600 dark:text-gray-400 py-1.5 sm:py-2 short:py-1 pl-3 sm:pl-4 short:pl-3 pr-2 sm:pr-3 short:pr-2 ml-4 sm:ml-6 short:ml-4 gap-2 before:content-[''] before:absolute before:left-0 before:top-1 before:bottom-1 before:w-0.5 before:bg-gray-300 dark:before:bg-gray-600 before:pointer-events-none"
            >
              <span class="flex items-start gap-2 sm:gap-3 short:gap-2 min-w-0 flex-1">
                <span
                  v-if="child.format"
                  class="relative w-5 h-5 sm:w-7 sm:h-7 short:w-5 short:h-5 rounded-md overflow-hidden flex-shrink-0 flex items-center justify-center mt-0.5"
                  :class="child.format === 'bn' ? 'bg-gray-200 dark:bg-gray-700' : ''"
                >
                  <template v-if="child.format === 'color'">
                    <span
                      class="absolute inset-0 blur-md bg-[radial-gradient(circle_at_25%_25%,theme(colors.cyan.300),transparent_60%),radial-gradient(circle_at_75%_25%,theme(colors.fuchsia.300),transparent_60%),radial-gradient(circle_at_50%_80%,theme(colors.yellow.300),transparent_60%)] dark:bg-[radial-gradient(circle_at_25%_25%,theme(colors.cyan.700),transparent_60%),radial-gradient(circle_at_75%_25%,theme(colors.fuchsia.700),transparent_60%),radial-gradient(circle_at_50%_80%,theme(colors.yellow.700),transparent_60%)]"
                    ></span>
                    <span class="relative z-10 inline-block w-[12px] h-[12px]">
                      <span class="absolute top-0 left-0 w-2 h-2 rounded-full bg-cyan-400 mix-blend-multiply"></span>
                      <span class="absolute top-0 left-1 w-2 h-2 rounded-full bg-fuchsia-500 mix-blend-multiply"></span>
                      <span class="absolute top-1 left-[2px] w-2 h-2 rounded-full bg-yellow-300 mix-blend-multiply"></span>
                    </span>
                  </template>
                  <span v-else-if="child.format === 'bn'" class="inline-flex -space-x-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-white border border-gray-400 dark:border-gray-500"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-black border border-gray-300 dark:border-gray-400"></span>
                  </span>
                </span>

                <span
                  v-else-if="child.icon"
                  class="w-5 h-5 sm:w-7 sm:h-7 short:w-5 short:h-5 rounded-md flex-shrink-0 flex items-center justify-center bg-gray-200 dark:bg-gray-700 mt-0.5"
                >
                  <i :class="[child.icon, 'text-xs sm:text-base short:text-xs text-gray-500 dark:text-gray-400']"></i>
                </span>

                <span class="flex flex-col min-w-0 flex-1 gap-0.5">
                  <span class="break-words">
                    {{ lineItemName(child) }}<span
                      v-if="child.quantity"
                      class="text-gray-300 dark:text-gray-600 mx-1"
                    >·</span><span
                      v-if="hasUnitPrice(child)"
                      class="text-green-700 dark:text-green-500 font-semibold"
                    >{{ formatCurrency(child.unitPrice) }} c/u</span><span
                      v-if="child.quantity"
                      class="text-gray-300 dark:text-gray-600 mx-1"
                    >·</span><span
                      v-if="child.quantity"
                      class="text-blue-600 dark:text-blue-400 font-semibold"
                    >x{{ child.quantity }}</span><span
                      v-if="child.included"
                      class="ml-1.5 text-purple-600 dark:text-purple-400 text-[10px] sm:text-xs italic"
                    >(incluido)</span>
                  </span>
                  <span
                    v-if="featuresList(child).length"
                    class="text-xs sm:text-sm short:text-xs text-gray-400 dark:text-gray-500 break-words"
                  >
                    {{ featuresList(child).join(' · ') }}
                  </span>
                </span>
              </span>
              <span class="font-medium whitespace-nowrap ml-2 flex-shrink-0">${{ child.amount.toFixed(2) }}</span>
            </div>
          </div>
        </div>

        <div
          v-else
          class="flex justify-between items-start text-sm sm:text-lg short:text-sm text-gray-700 dark:text-gray-300 p-2 sm:p-3 short:p-2 rounded-lg border gap-2"
          :class="lineBackgroundClass(line)"
        >
          <span class="flex items-start gap-2 sm:gap-3 short:gap-2 min-w-0 flex-1">
            <span
              v-if="line.format"
              class="relative w-7 h-7 sm:w-9 sm:h-9 short:w-6 short:h-6 rounded-md overflow-hidden flex-shrink-0 flex items-center justify-center mt-0.5"
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

            <span
              v-else-if="line.icon"
              class="w-7 h-7 sm:w-9 sm:h-9 short:w-6 short:h-6 rounded-md flex-shrink-0 flex items-center justify-center bg-gray-200 dark:bg-gray-700 mt-0.5"
            >
              <i :class="[line.icon, 'text-base sm:text-xl short:text-sm text-gray-500 dark:text-gray-400']"></i>
            </span>

            <span class="flex flex-col min-w-0 flex-1 gap-0.5">
              <span class="break-words">
                {{ lineItemName(line) }}<span
                  v-if="line.quantity"
                  class="text-gray-300 dark:text-gray-600 mx-1"
                >·</span><span
                  v-if="hasUnitPrice(line)"
                  class="text-green-700 dark:text-green-500 font-semibold"
                >{{ formatCurrency(line.unitPrice) }} c/u</span><span
                  v-if="line.quantity"
                  class="text-gray-300 dark:text-gray-600 mx-1"
                >·</span><span
                  v-if="line.quantity"
                  class="text-blue-600 dark:text-blue-400 font-semibold"
                >x{{ line.quantity }}</span><span
                  v-if="line.included"
                  class="ml-1.5 text-purple-600 dark:text-purple-400 text-xs italic"
                >(incluido)</span>
              </span>
              <span
                v-if="featuresList(line).length"
                class="text-xs sm:text-sm short:text-xs text-gray-400 dark:text-gray-500 break-words"
              >
                {{ featuresList(line).join(' · ') }}
              </span>
            </span>
          </span>
          <span class="font-medium whitespace-nowrap ml-2 flex-shrink-0">${{ line.amount.toFixed(2) }}</span>
        </div>
      </template>

      <div v-if="!lines.length" class="text-sm sm:text-lg short:text-sm text-gray-400 dark:text-gray-500 italic">
        {{ emptyMessage }}
      </div>

      <div v-if="!hideTotal" class="border-t border-gray-300 dark:border-gray-600 pt-2 sm:pt-3 short:pt-2 space-y-1">
        <template v-if="hasDiscount">
          <div class="text-[10px] sm:text-xs short:text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
            Descuentos aplicados
          </div>
          <div class="flex justify-between items-baseline gap-2 text-sm sm:text-base short:text-sm text-red-600 dark:text-red-400">
            <span class="flex items-baseline flex-wrap gap-1.5 min-w-0">
              <i class="ti ti-percent text-xs"></i>
              <span class="break-words">{{ discountLabel }}</span>
              <span v-if="discountType === 'percent'" class="text-xs">({{ percentLabel }}%)</span>
              <span v-if="discountIncludesPackages" class="text-[10px] italic">· incluye paquetes</span>
            </span>
            <span class="whitespace-nowrap font-semibold">-${{ discountAmount.toFixed(2) }}</span>
          </div>
          <div v-if="discountReason" class="text-xs text-gray-500 dark:text-gray-400 italic pl-5">
            Motivo: {{ discountReason }}
          </div>
          <div class="border-t border-dashed border-gray-300 dark:border-gray-600 my-2"></div>
          <div class="flex justify-between items-center gap-2 text-sm sm:text-base short:text-sm text-gray-500 dark:text-gray-400">
            <span>Subtotal</span>
            <span>${{ estimatedTotal.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between items-center gap-2 text-sm sm:text-base short:text-sm text-red-500 dark:text-red-400">
            <span>Descuento</span>
            <span>-${{ discountAmount.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between items-center gap-2 pt-1 border-t border-gray-300 dark:border-gray-600">
            <span class="text-base sm:text-xl short:text-base font-semibold text-gray-800 dark:text-gray-200">{{ totalLabel }}</span>
            <span class="text-lg sm:text-2xl short:text-lg font-bold text-blue-600 dark:text-blue-400">${{ netTotal.toFixed(2) }}</span>
          </div>
        </template>
        <template v-else>
          <div class="flex justify-between items-center gap-2">
            <span class="text-base sm:text-xl short:text-base font-semibold text-gray-800 dark:text-gray-200">{{ totalLabel }}</span>
            <span class="text-lg sm:text-2xl short:text-lg font-bold text-blue-600 dark:text-blue-400">${{ estimatedTotal.toFixed(2) }}</span>
          </div>
        </template>
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
    discountSource: { type: String, default: null },
    discountType: { type: String, default: null },
    discountValue: { type: [Number, String], default: null },
    discountAmount: { type: Number, default: 0 },
    discountReason: { type: String, default: null },
    discountIncludesPackages: { type: Boolean, default: false },
    totalLabel: { type: String, default: 'Total a cobrar' },
    emptyMessage: { type: String, default: 'No hay conceptos registrados.' },
    hideTotal: { type: Boolean, default: false }
  },
  computed: {
    hasDiscount() {
      return Number(this.discountAmount) > 0;
    },
    netTotal() {
      return Math.max(0, Number(this.estimatedTotal) - Number(this.discountAmount));
    },
    percentLabel() {
      const v = Number(this.discountValue);
      if (!Number.isFinite(v)) return '';
      return v % 1 === 0 ? v.toFixed(0) : v.toFixed(2);
    },
    discountLabel() {
      if (this.discountSource === 'auto') return 'Descuento automático';
      if (this.discountSource === 'manual') return 'Descuento manual';
      return 'Descuento';
    }
  },
  methods: {
    lineBackgroundClass() {
      return 'bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-600';
    },
    hasUnitPrice(line) {
      return line.unitPrice !== undefined && line.unitPrice !== null;
    },
    lineItemName(line) {
      return line.name !== undefined ? line.name : (line.label || '');
    },
    formatCurrency(value) {
      const num = Number(value || 0);
      return '$' + num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    featuresList(item) {
      const parts = [];
      if (item.format) {
        parts.push(item.format === 'color' ? 'Color' : 'B/N');
      }
      if (item.background && String(item.background).trim() !== '') {
        parts.push('Fondo ' + String(item.background).trim().toLowerCase());
      }
      if (item.paper && String(item.paper).trim() !== '') {
        parts.push('Papel ' + String(item.paper).trim().toLowerCase());
      }
      return parts;
    }
  }
};
</script>