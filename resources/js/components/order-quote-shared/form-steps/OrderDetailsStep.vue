<template>
  <section class="space-y-7">
    <h4 v-if="sectionTitle" class="text-base font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 flex items-center gap-2">
      <span v-if="sectionBadge !== null" class="w-8 h-8 rounded-full bg-blue-500 text-white text-sm flex items-center justify-center font-bold">{{ sectionBadge }}</span>
      {{ sectionTitle }}
    </h4>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

      <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5 border-2"
           :class="showErrors && fieldErrors.photo_types ? 'border-red-500' : 'border-transparent'">
        <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1 flex items-center">
          <i class="ti ti-photo mr-2 text-xl"></i>Tipos de fotografía
        </h4>
        <p v-if="showErrors && fieldErrors.photo_types" class="text-base text-red-600 dark:text-red-400 mb-3 font-medium">
          Campo obligatorio*
        </p>

        <div v-if="loadingCatalogs" class="text-center py-4 text-lg text-gray-500 dark:text-gray-400 mt-3">
          <i class="ti ti-refresh animate-spin"></i> Cargando catálogo...
        </div>

        <div v-else class="space-y-3 mt-3">
          <p v-if="photoLines.length === 0" class="text-base text-gray-400 dark:text-gray-500 italic">
            Sin tipos de fotografía. Se puede dejar vacío si la orden lleva paquetes escolares.
          </p>

          <div v-for="(line, idx) in photoLines" :key="lineKey(line)"
               class="relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-4 space-y-3">

            <div class="flex items-center justify-between">
              <span class="text-base font-semibold text-gray-500 dark:text-gray-400">Tipo {{ idx + 1 }}</span>
              <button type="button" @click="removePhotoLine(idx)"
                      class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 p-1.5 rounded-lg transition-colors">
                <i class="ti ti-trash text-xl"></i>
              </button>
            </div>

            <div class="grid grid-cols-[1fr_auto] gap-x-3 gap-y-1 items-end">
              <span></span>
              <span class="text-sm font-medium text-gray-500 dark:text-gray-400 text-center whitespace-nowrap">Cantidad de fotos</span>

              <div class="relative min-w-[10rem]"
                   data-photo-type-dropdown
                   @focusout="handlePhotoTypeFocusOut(line, $event)">
                <div
                  class="w-full text-lg p-3 rounded-lg border text-gray-800 dark:text-gray-200 flex items-center gap-2"
                  :class="showErrors && fieldErrors.photo_types && !line.photo_type_id
                    ? 'border-red-500 dark:border-red-500'
                    : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700'"
                >
                  <i class="ti ti-photo text-xl text-gray-400 dark:text-gray-500 flex-shrink-0"></i>
                  <input
                    :value="photoTypeInputValue(line)"
                    @focus="openPhotoTypeDropdown(line)"
                    @input="onPhotoTypeInput(line, $event)"
                    @keydown.esc="closePhotoTypeDropdown(lineKey(line))"
                    @keydown.down.prevent="moveHighlight(1)"
                    @keydown.up.prevent="moveHighlight(-1)"
                    @keydown.enter.prevent="selectHighlighted(idx, line)"
                    type="text"
                    :placeholder="line.photo_type_id ? 'Buscar otro tipo...' : 'Buscar o seleccionar tipo...'"
                    class="flex-1 min-w-0 bg-transparent border-0 outline-none text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 truncate"
                    autocomplete="off"
                  >
                  <span v-if="line.photo_type_id && openPhotoTypeDropdownIdx !== lineKey(line)" class="text-sm font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap flex-shrink-0">
                    ${{ photoTypePrice(line.photo_type_id).toFixed(2) }} c/u
                  </span>
                  <button
                    v-if="line.photo_type_id && openPhotoTypeDropdownIdx !== lineKey(line)"
                    type="button"
                    @mousedown.prevent="clearPhotoType(idx)"
                    class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 flex-shrink-0 p-1 rounded-lg transition-colors"
                    title="Limpiar selección"
                  >
                    <i class="ti ti-x text-2xl"></i>
                  </button>
                </div>

                <div v-if="openPhotoTypeDropdownIdx === lineKey(line)"
                    class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
                  <div class="max-h-56 overflow-y-auto">
                    <button
                      v-for="(pt, pIdx) in filteredPhotoTypes"
                      :key="pt.id"
                      type="button"
                      @mousedown.prevent="selectPhotoTypeAndReset(idx, line, pt.id)"
                      @mouseover="highlightedPhotoTypeIdx = pIdx"
                      class="w-full px-3 py-2.5 text-base flex items-center gap-2 text-gray-800 dark:text-gray-200"
                      :class="[
                        pIdx === highlightedPhotoTypeIdx
                          ? 'bg-blue-50 dark:bg-blue-900/20'
                          : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                      ]"
                    >
                      <i
                        v-if="pt.id === line.photo_type_id"
                        class="ti ti-check text-blue-600 dark:text-blue-400 text-base flex-shrink-0"
                      ></i>
                      <span v-else class="w-4 flex-shrink-0"></span>
                      <span class="flex-1 text-left truncate">{{ pt.name }}</span>
                      <span class="text-gray-500 dark:text-gray-400 text-sm whitespace-nowrap">
                        ${{ Number(pt.base_price).toFixed(2) }} c/u
                      </span>
                    </button>
                    <p v-if="filteredPhotoTypes.length === 0" class="text-sm text-gray-400 dark:text-gray-500 text-center py-3">
                      Sin coincidencias
                    </p>
                  </div>
                </div>
              </div>

              <input
                type="number" min="1" v-model.number="line.quantity"
                @blur="sanitizeQuantity(line)"
                v-blur-on-enter
                enterkeyhint="done"
                :disabled="!line.photo_type_id"
                class="form-control form-control-sm text-lg p-3 w-28 text-center rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
              >
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="relative" data-format-dropdown>
                <button
                  type="button"
                  @click="toggleFormatDropdown(lineKey(line))"
                  @blur="closeFormatDropdown(lineKey(line))"
                  class="w-full text-base p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 flex items-center justify-between"
                >
                  <span class="flex items-center gap-2 truncate">
                    <span v-if="line.format === 'color'" class="relative isolate inline-block w-[18px] h-[18px] flex-shrink-0">
                      <span class="absolute top-0 left-0 w-3 h-3 rounded-full bg-cyan-400 mix-blend-multiply"></span>
                      <span class="absolute top-0 left-1.5 w-3 h-3 rounded-full bg-fuchsia-500 mix-blend-multiply"></span>
                      <span class="absolute top-1.5 left-[3px] w-3 h-3 rounded-full bg-yellow-300 mix-blend-multiply"></span>
                    </span>
                    <span v-else class="inline-flex -space-x-1 flex-shrink-0">
                      <span class="w-3.5 h-3.5 rounded-full bg-white border border-gray-400 dark:border-gray-500"></span>
                      <span class="w-3.5 h-3.5 rounded-full bg-black border border-gray-300 dark:border-gray-400"></span>
                    </span>
                    <span class="truncate">{{ line.format === 'color' ? 'Color' : 'Blanco/negro' }}</span>
                  </span>
                  <i class="ti ti-chevron-down text-sm flex-shrink-0"></i>
                </button>

                <div v-if="openFormatDropdownId === lineKey(line)"
                    class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
                  <button @mousedown.prevent="selectFormat(idx, 'color')"
                          class="w-full px-3 py-2.5 text-base hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-800 dark:text-gray-200">
                    <span class="relative isolate z-10 inline-block w-[18px] h-[18px]">
                      <span class="absolute top-0 left-0 w-3 h-3 rounded-full bg-cyan-400 mix-blend-multiply"></span>
                      <span class="absolute top-0 left-1.5 w-3 h-3 rounded-full bg-fuchsia-500 mix-blend-multiply"></span>
                      <span class="absolute top-1.5 left-[3px] w-3 h-3 rounded-full bg-yellow-300 mix-blend-multiply"></span>
                    </span>
                    Color
                  </button>
                  <button @mousedown.prevent="selectFormat(idx, 'bn')"
                          class="w-full px-3 py-2.5 text-base hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-800 dark:text-gray-200">
                    <span class="inline-flex -space-x-1">
                      <span class="w-3.5 h-3.5 rounded-full bg-white border border-gray-400 dark:border-gray-500"></span>
                      <span class="w-3.5 h-3.5 rounded-full bg-black border border-gray-300 dark:border-gray-400"></span>
                    </span>
                    Blanco/negro
                  </button>
                </div>
              </div>

              <select v-model="line.paper_type_id"
                      class="form-control form-control-sm text-base p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                <option :value="null">Papel (opcional)</option>
                <option v-for="p in paperTypes" :key="p.id" :value="p.id" class="bg-white dark:bg-gray-800">{{ p.name }}</option>
              </select>
            </div>
          </div>

          <button type="button" @click="addPhotoLine"
                  class="mt-2 text-base font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center gap-1">
            <i class="ti ti-plus text-lg"></i> {{ photoLines.length === 0 ? 'Agregar tipo' : 'Agregar otro tipo' }}
          </button>
        </div>
      </div>

      <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5">
        <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center">
          <i class="ti ti-package mr-2 text-xl"></i>Paquetes escolares (opcional)
        </h4>
        <div v-if="!loadingCatalogs && packages.length === 0" class="text-lg text-gray-500 dark:text-gray-400 italic">
          No hay paquetes disponibles.
        </div>
        <div v-else class="space-y-3 max-h-[28rem] overflow-y-auto pr-1">
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

    </div>

    <div class="flex items-center justify-between bg-blue-50 dark:bg-blue-900/20 rounded-lg p-5">
      <span class="text-lg font-semibold text-blue-800 dark:text-blue-300">Total estimado</span>
      <span class="text-2xl font-bold text-blue-800 dark:text-blue-300">${{ estimatedTotal.toFixed(2) }}</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-7 gap-6">
      <div v-if="!hideDeliveryDate" class="md:col-span-2">
        <DeliveryDateTimeFields
          stacked
          :delivery-date="form.estimated_delivery_date"
          :delivery-time="form.estimated_delivery_time"
          :show-errors="showErrors && (fieldErrors.delivery_date || fieldErrors.delivery_time)"
          @update:deliveryDate="emitFormUpdate('estimated_delivery_date', $event)"
          @update:deliveryTime="emitFormUpdate('estimated_delivery_time', $event)"
        />
      </div>

      <div :class="hideDeliveryDate ? 'md:col-span-7' : 'md:col-span-5'">
        <label class="form-label text-lg font-medium mb-2 block text-gray-700 dark:text-gray-300">Observaciones</label>
        <textarea
          ref="notesTextarea"
          :value="form.notes"
          @input="onNotesInput"
          v-blur-on-enter
          enterkeyhint="done"
          rows="4"
          class="form-control form-control-sm w-full text-lg p-4 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 resize-none"
        ></textarea>
        <div class="flex justify-end mt-1">
          <span class="text-sm" :class="wordCount >= 500 ? 'text-red-500 dark:text-red-400 font-medium' : 'text-gray-400 dark:text-gray-500'">
            {{ wordCount }}/500 palabras
          </span>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import DeliveryDateTimeFields from '../DeliveryDateTimeFields.vue';

export default {
  name: 'OrderDetailsStep',
  components: { DeliveryDateTimeFields },
  props: {
    sectionBadge: { type: [Number, String], default: 2 },
    sectionTitle: { type: String, default: 'Datos de la orden' },
    hideDeliveryDate: { type: Boolean, default: false },
    form: { type: Object, required: true },
    showErrors: { type: Boolean, default: false },
    fieldErrors: { type: Object, required: true },
    loadingCatalogs: { type: Boolean, default: false },
    photoTypes: { type: Array, default: () => [] },
    photoLines: { type: Array, required: true },
    paperTypes: { type: Array, default: () => [] },
    packages: { type: Array, default: () => [] },
    selectedPackageIds: { type: Array, required: true },
    estimatedTotal: { type: Number, default: 0 },
    openFormatDropdownId: { type: [Number, String], default: null },
    openPhotoTypeDropdownIdx: { type: [Number, String], default: null },
    toggleDatePicker: { type: Function, required: true },
    closeDatePicker: { type: Function, required: true },
    addPhotoLine: { type: Function, required: true },
    removePhotoLine: { type: Function, required: true },
    selectPhotoType: { type: Function, required: true },
    togglePhotoTypeDropdown: { type: Function, required: true },
    closePhotoTypeDropdown: { type: Function, required: true },
    photoTypeName: { type: Function, required: true },
    toggleFormatDropdown: { type: Function, required: true },
    closeFormatDropdown: { type: Function, required: true },
    selectFormat: { type: Function, required: true }
  },
  emits: ['update:selectedPackageIds', 'update:form'],
  data() {
    return {
      photoTypeSearch: '',
      highlightedPhotoTypeIdx: 0
    };
  },
  computed: {
    wordCount() {
      const trimmed = (this.form.notes || '').trim();
      return trimmed === '' ? 0 : trimmed.split(/\s+/).filter(Boolean).length;
    },
    selectedPackageIdsProxy: {
      get() { return this.selectedPackageIds; },
      set(value) { this.$emit('update:selectedPackageIds', value); }
    },
    filteredPhotoTypes() {
      const q = this.normalize(this.photoTypeSearch);
      if (!q) return this.photoTypes;
      return this.photoTypes.filter(pt => this.normalize(pt.name).startsWith(q));
    }
  },
  created() {
    this._lineKeyMap = new WeakMap();
    this._nextLineKey = 1;
  },
  mounted() {
    this._outsideTapHandler = (event) => {
      if (this.openPhotoTypeDropdownIdx !== null && this.openPhotoTypeDropdownIdx !== undefined) {
        if (!event.target.closest('[data-photo-type-dropdown]')) {
          this.closePhotoTypeDropdown(this.openPhotoTypeDropdownIdx);
          this.photoTypeSearch = '';
        }
      }
      if (this.openFormatDropdownId !== null && this.openFormatDropdownId !== undefined) {
        if (!event.target.closest('[data-format-dropdown]')) {
          this.closeFormatDropdown(this.openFormatDropdownId);
        }
      }
    };
    document.addEventListener('touchstart', this._outsideTapHandler, true);
  },
  beforeUnmount() {
    document.removeEventListener('touchstart', this._outsideTapHandler, true);
  },
  methods: {
    emitFormUpdate(key, value) {
      this.$emit('update:form', { ...this.form, [key]: value });
    },
    normalize(value) {
      return String(value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();
    },
    lineKey(line) {
      if (!this._lineKeyMap.has(line)) {
        this._lineKeyMap.set(line, this._nextLineKey++);
      }
      return this._lineKeyMap.get(line);
    },
    photoTypePrice(id) {
      const pt = this.photoTypes.find(p => p.id === id);
      return Number(pt?.base_price || 0);
    },
    photoTypeInputValue(line) {
      const key = this.lineKey(line);
      if (this.openPhotoTypeDropdownIdx === key) return this.photoTypeSearch;
      return line.photo_type_id ? this.photoTypeName(line.photo_type_id) : '';
    },
    openPhotoTypeDropdown(line) {
      const key = this.lineKey(line);
      if (this.openPhotoTypeDropdownIdx === key) return;
      this.photoTypeSearch = '';
      this.highlightedPhotoTypeIdx = 0;
      this.togglePhotoTypeDropdown(key);
    },
    onPhotoTypeInput(line, event) {
      const key = this.lineKey(line);
      this.photoTypeSearch = event.target.value;
      this.highlightedPhotoTypeIdx = 0;
      if (this.openPhotoTypeDropdownIdx !== key) {
        this.togglePhotoTypeDropdown(key);
      }
    },
    handlePhotoTypeFocusOut(line, event) {
      const key = this.lineKey(line);
      if (this.openPhotoTypeDropdownIdx !== key) return;
      if (!event.currentTarget.contains(event.relatedTarget)) {
        this.closePhotoTypeDropdown(key);
        this.photoTypeSearch = '';
      }
    },
    moveHighlight(delta) {
      const len = this.filteredPhotoTypes.length;
      if (!len) return;
      this.highlightedPhotoTypeIdx = (this.highlightedPhotoTypeIdx + delta + len) % len;
    },
    selectHighlighted(idx, line) {
      const pt = this.filteredPhotoTypes[this.highlightedPhotoTypeIdx];
      if (!pt) return;
      this.selectPhotoTypeAndReset(idx, line, pt.id);
    },
    selectPhotoTypeAndReset(idx, line, photoTypeId) {
      this.selectPhotoType(idx, photoTypeId);
      this.closePhotoTypeDropdown(this.lineKey(line));
      this.photoTypeSearch = '';
      this.highlightedPhotoTypeIdx = 0;
    },
    clearPhotoType(idx) {
      const line = this.photoLines[idx];
      if (!line) return;
      line.photo_type_id = null;
      line.quantity = 1;
      this.photoTypeSearch = '';
    },
    sanitizeQuantity(line) {
      const value = Number(line.quantity);
      line.quantity = Number.isFinite(value) && value >= 1 ? Math.floor(value) : 1;
    },
    onNotesInput(event) {
      const previous = this.form.notes || '';
      const previousWordCount = previous.trim() === ''
        ? 0
        : previous.trim().split(/\s+/).filter(Boolean).length;
      const raw = event.target.value;
      if (previousWordCount >= 500 && raw.length > previous.length) {
        const addedChars = raw.length - previous.length;
        const selStart = event.target.selectionStart ?? previous.length;
        event.target.value = previous;
        const restoredPos = Math.max(0, selStart - addedChars);
        event.target.setSelectionRange(restoredPos, restoredPos);
        return;
      }
      const words = raw.trim().split(/\s+/).filter(Boolean);
      if (words.length > 500) {
        const truncated = words.slice(0, 500).join(' ');
        event.target.value = truncated;
        event.target.setSelectionRange(truncated.length, truncated.length);
        this.$emit('update:form', { ...this.form, notes: truncated });
        return;
      }
      this.$emit('update:form', { ...this.form, notes: raw });
    }
  }
};
</script>