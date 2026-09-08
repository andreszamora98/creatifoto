<template>
  <section class="space-y-7">
    <h4 class="text-base font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 flex items-center gap-2">
      <span class="w-8 h-8 rounded-full bg-blue-500 text-white text-sm flex items-center justify-center font-bold">2</span>
      Datos de la orden
    </h4>

    <div class="grid grid-cols-1 gap-6">
      <div>
        <label class="form-label text-lg font-medium mb-2 block text-gray-700 dark:text-gray-300">Fecha de entrega estimada <span class="text-red-500">*</span></label>
        <div class="relative">
          <input
            v-model="form.estimated_delivery_date"
            type="date"
            @click.prevent="toggleDatePicker($event)"
            @change="closeDatePicker"
            @blur="closeDatePicker"
            class="form-control form-control-sm w-full text-lg p-4 pr-11 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 [&::-webkit-calendar-picker-indicator]:opacity-0"
            :class="showErrors && fieldErrors.delivery_date
              ? 'border-red-500 dark:border-red-500'
              : 'border-gray-300 dark:border-gray-600'"
          >
          <i class="ti ti-calendar absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 pointer-events-none text-xl"></i>
        </div>
        <p v-if="showErrors && fieldErrors.delivery_date" class="text-base text-red-600 dark:text-red-400 mt-1 font-medium">
          Campo obligatorio*
        </p>
      </div>
      <div>
        <label class="form-label text-lg font-medium mb-2 block text-gray-700 dark:text-gray-300">Toma seleccionada <span class="text-red-500">*</span></label>
        <input v-model.number="form.pose_number" type="number" min="1"
               class="form-control form-control-sm w-full text-lg p-4 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
               :class="showErrors && fieldErrors.pose_number
                 ? 'border-red-500 dark:border-red-500'
                 : 'border-gray-300 dark:border-gray-600'"
               placeholder="Número de toma">
        <p v-if="showErrors && fieldErrors.pose_number" class="text-base text-red-600 dark:text-red-400 mt-1 font-medium">
          Campo obligatorio*
        </p>
      </div>
    </div>

    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5 border-2"
         :class="showErrors && fieldErrors.photo_types ? 'border-red-500' : 'border-transparent'">
      <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1 flex items-center">
        <i class="ti ti-photo mr-2 text-xl"></i>Tipos de fotografía <span class="text-red-500 ml-1">*</span>
      </h4>
      <p v-if="showErrors && fieldErrors.photo_types" class="text-base text-red-600 dark:text-red-400 mb-3 font-medium">
        Campo obligatorio*
      </p>
      <div v-if="loadingCatalogs" class="text-center py-4 text-lg text-gray-500 dark:text-gray-400 mt-3">
        <i class="ti ti-refresh animate-spin"></i> Cargando catálogo...
      </div>
      <div v-else
           class="space-y-3 pr-1 mt-3"
           :class="photoTypes.length > 4 ? 'max-h-80 overflow-y-auto' : ''">
        <div v-for="pt in photoTypes" :key="pt.id"
            class="p-4 rounded border transition-all cursor-pointer"
            :class="lines[pt.id] && lines[pt.id].selected
              ? 'bg-white dark:bg-gray-800 border-blue-500 dark:border-blue-400'
              : 'bg-gray-100 dark:bg-gray-800/50 border-gray-200 dark:border-gray-600 opacity-60'"
            @click="togglePhotoType(pt.id)">
          <div class="flex items-center gap-4">
            <div class="w-7 h-7 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                :class="lines[pt.id] && lines[pt.id].selected
                  ? 'border-blue-500 bg-blue-500'
                  : 'border-gray-400 dark:border-gray-500'">
              <i v-if="lines[pt.id] && lines[pt.id].selected" class="ti ti-check text-white text-base"></i>
            </div>
            <span class="flex-1 text-lg font-medium text-gray-700 dark:text-gray-300">{{ pt.name }}</span>
            <span class="text-base text-gray-500 dark:text-gray-400">${{ Number(pt.base_price).toFixed(2) }} c/u</span>
            <input v-if="lines[pt.id] && lines[pt.id].selected"
                  type="number" min="1" v-model.number="lines[pt.id].quantity"
                  @click.stop
                  @blur="onQuantityBlur(pt.id)"
                  class="form-control form-control-sm text-lg p-3 w-28 text-center rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
          </div>
          <div v-if="lines[pt.id] && lines[pt.id].selected" class="grid grid-cols-1 gap-4 mt-3 pl-11" @click.stop>
            <div class="relative">
              <button
                type="button"
                @click="toggleFormatDropdown(pt.id)"
                @blur="closeFormatDropdown(pt.id)"
                class="w-full text-lg p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 flex items-center justify-between"
              >
                <span class="flex items-center gap-2">
                  <i v-if="lines[pt.id].format === 'color'" class="ti ti-palette text-xl text-pink-500"></i>
                  <span v-else class="inline-flex -space-x-1">
                    <span class="w-4 h-4 rounded-full bg-white border border-gray-400 dark:border-gray-500"></span>
                    <span class="w-4 h-4 rounded-full bg-black border border-gray-300 dark:border-gray-400"></span>
                  </span>
                  <span>{{ lines[pt.id].format === 'color' ? 'Color' : 'Blanco y negro' }}</span>
                </span>
                <i class="ti ti-chevron-down text-base"></i>
              </button>

              <div v-if="openFormatDropdownId === pt.id"
                   class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
                <button
                  @mousedown.prevent="selectFormat(pt.id, 'color')"
                  class="w-full px-3 py-3 text-lg hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-800 dark:text-gray-200"
                >
                  <i class="ti ti-palette text-xl text-pink-500"></i> Color
                </button>
                <button
                  @mousedown.prevent="selectFormat(pt.id, 'bn')"
                  class="w-full px-3 py-3 text-lg hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-800 dark:text-gray-200"
                >
                  <span class="inline-flex -space-x-1">
                    <span class="w-4 h-4 rounded-full bg-white border border-gray-400 dark:border-gray-500"></span>
                    <span class="w-4 h-4 rounded-full bg-black border border-gray-300 dark:border-gray-400"></span>
                  </span>
                  Blanco y negro
                </button>
              </div>
            </div>

            <select v-model="lines[pt.id].paper_type_id" class="form-control form-control-sm text-lg p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
              <option :value="null">Papel (opcional)</option>
              <option v-for="p in paperTypes" :key="p.id" :value="p.id" class="bg-white dark:bg-gray-800">{{ p.name }}</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5">
      <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center">
        <i class="ti ti-package mr-2 text-xl"></i>Paquetes escolares (opcional)
      </h4>
      <div v-if="!loadingCatalogs && packages.length === 0" class="text-lg text-gray-500 dark:text-gray-400 italic">
        No hay paquetes disponibles.
      </div>
      <div v-else class="space-y-3 max-h-64 overflow-y-auto pr-1">
        <label v-for="pkg in packages" :key="pkg.id"
               class="flex items-center justify-between p-4 rounded bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 cursor-pointer text-gray-700 dark:text-gray-300">
          <span class="flex items-center gap-4 text-lg">
            <input type="checkbox" :value="pkg.id" v-model="selectedPackageIdsProxy" class="w-6 h-6 text-blue-600 dark:text-blue-500">
            {{ pkg.name || ('Paquete ' + pkg.package_number) }}
            <span class="text-gray-400 dark:text-gray-500 text-base" v-if="pkg.school">— {{ pkg.school.name }}</span>
          </span>
          <span class="text-lg font-medium text-gray-600 dark:text-gray-400">${{ Number(pkg.total_price).toFixed(2) }}</span>
        </label>
      </div>
    </div>

    <div class="flex items-center justify-between bg-blue-50 dark:bg-blue-900/20 rounded-lg p-5">
      <span class="text-lg font-semibold text-blue-800 dark:text-blue-300">Total estimado</span>
      <span class="text-2xl font-bold text-blue-800 dark:text-blue-300">${{ estimatedTotal.toFixed(2) }}</span>
    </div>

    <div>
      <label class="form-label text-lg font-medium mb-2 block text-gray-700 dark:text-gray-300">Observaciones</label>
      <textarea v-model="form.notes" rows="4" class="form-control form-control-sm w-full text-lg p-4 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 resize-none"></textarea>
      <div class="flex justify-end mt-1">
        <span class="text-sm" :class="notesWordCount >= 1000 ? 'text-red-500 dark:text-red-400 font-medium' : 'text-gray-400 dark:text-gray-500'">
          {{ notesWordCount }}/1000 palabras
        </span>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'OrderDetailsStep',
  props: {
    form: { type: Object, required: true },
    showErrors: { type: Boolean, default: false },
    fieldErrors: { type: Object, required: true },
    loadingCatalogs: { type: Boolean, default: false },
    photoTypes: { type: Array, default: () => [] },
    lines: { type: Object, required: true },
    paperTypes: { type: Array, default: () => [] },
    packages: { type: Array, default: () => [] },
    selectedPackageIds: { type: Array, required: true },
    estimatedTotal: { type: Number, default: 0 },
    notesWordCount: { type: Number, default: 0 },
    openFormatDropdownId: { type: [Number, String], default: null },
    toggleDatePicker: { type: Function, required: true },
    closeDatePicker: { type: Function, required: true },
    togglePhotoType: { type: Function, required: true },
    onQuantityBlur: { type: Function, required: true },
    toggleFormatDropdown: { type: Function, required: true },
    closeFormatDropdown: { type: Function, required: true },
    selectFormat: { type: Function, required: true }
  },
  emits: ['update:selectedPackageIds'],
  computed: {
    selectedPackageIdsProxy: {
      get() {
        return this.selectedPackageIds;
      },
      set(value) {
        this.$emit('update:selectedPackageIds', value);
      }
    }
  }
};
</script>