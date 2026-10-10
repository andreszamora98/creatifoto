<template>
  <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-2.5 sm:p-5 short:p-2 border-2"
       :class="showErrors && error ? 'border-red-500' : 'border-transparent'">
    <h4 v-if="title" class="text-base sm:text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1 flex items-center">
      <i :class="icon" class="mr-2 text-lg sm:text-xl"></i>{{ title }}
    </h4>
    <p v-if="showErrors && error" class="text-sm sm:text-lg short:text-sm text-red-600 dark:text-red-400 mb-2 sm:mb-3 short:mb-1.5 font-medium">{{ errorMessage }}</p>

    <div v-if="loading" class="text-center py-3 sm:py-4 short:py-2 text-base sm:text-lg short:text-base text-gray-500 dark:text-gray-400 mt-2 sm:mt-3 short:mt-1">
      <i class="ti ti-refresh animate-spin"></i> Cargando catálogo...
    </div>

    <div v-else class="mt-2 sm:mt-3 short:mt-1">
      <p v-if="lines.length === 0 && emptyHint" class="text-sm sm:text-lg short:text-sm text-gray-400 dark:text-gray-500 italic">{{ emptyHint }}</p>

      <div v-if="lines.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-2 sm:gap-3 short:gap-2">
        <PhotoTypeLineCard
          v-for="(line, idx) in lines"
          :key="lineKey(line)"
          :line="line"
          :index="idx"
          :editor-id="editorId"
          :paper-types="paperTypes"
          :show-format-and-paper="showFormatAndPaper"
          :can-remove="canRemove"
          :is-open="openDropdownKey === lineKey(line)"
          :format-open="openFormatKey === lineKey(line)"
          :input-value="inputValueFor(line)"
          :highlighted-idx="highlightedIdx"
          :filtered-photo-types="filteredPhotoTypes"
          :photo-type-price="photoTypePrice"
          :show-error="showErrors && error"
          :included-in-package="lineIsIncludedInPackage(line)"
          :on-open="() => openDropdown(line)"
          :on-input="(v) => onInput(line, v)"
          :on-esc="() => closeDropdown()"
          :on-move-highlight="moveHighlight"
          :on-select-highlighted="() => selectHighlighted(idx, line)"
          :on-select="(id) => selectAndReset(idx, line, id)"
          :on-clear="() => clearLine(idx)"
          :on-focus-out="(e) => onFocusOut(line, e)"
          :on-highlight="(i) => (highlightedIdx = i)"
          :on-quantity-blur="() => sanitizeQuantity(line)"
          :on-remove="() => removeLine(idx)"
          :on-toggle-format="() => toggleFormat(line)"
          :on-close-format="() => closeFormat(line)"
          :on-select-format="(v) => selectFormat(line, v)"
        />
      </div>

      <button type="button" @click="addLine"
              class="mt-2 sm:mt-3 short:mt-1 py-1 sm:py-0 text-sm sm:text-lg short:text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center gap-1">
        <i class="ti ti-plus text-base sm:text-xl short:text-base"></i> {{ lines.length === 0 ? addLabelFirst : addLabel }}
      </button>
    </div>
  </div>
</template>

<script>
import PhotoTypeLineCard from './PhotoTypeLineCard.vue';

export default {
  name: 'PhotoTypeLinesEditor',
  components: { PhotoTypeLineCard },
  props: {
    lines: { type: Array, required: true },
    photoTypes: { type: Array, default: () => [] },
    paperTypes: { type: Array, default: () => [] },
    showFormatAndPaper: { type: Boolean, default: true },
    canRemove: { type: Boolean, default: true },
    loading: { type: Boolean, default: false },
    showErrors: { type: Boolean, default: false },
    error: { type: Boolean, default: false },
    errorMessage: { type: String, default: 'Campo obligatorio*' },
    title: { type: String, default: '' },
    icon: { type: String, default: 'ti ti-camera' },
    emptyHint: { type: String, default: '' },
    addLabel: { type: String, default: 'Agregar otro tipo' },
    addLabelFirst: { type: String, default: 'Agregar tipo' }
  },
  data() {
    return {
      editorId: 'pte-' + Math.random().toString(36).slice(2),
      photoTypeSearch: '',
      highlightedIdx: 0,
      openDropdownKey: null,
      openFormatKey: null
    };
  },
  computed: {
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
      if (this.openDropdownKey !== null && !event.target.closest(`[data-photo-type-dropdown="${this.editorId}"]`)) {
        this.closeDropdown();
      }
      if (this.openFormatKey !== null && !event.target.closest(`[data-format-dropdown="${this.editorId}"]`)) {
        this.openFormatKey = null;
      }
    };
    document.addEventListener('touchstart', this._outsideTapHandler, true);
  },
  beforeUnmount() {
    document.removeEventListener('touchstart', this._outsideTapHandler, true);
  },
  methods: {
    normalize(value) {
      return String(value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
    },
    lineKey(line) {
      if (!this._lineKeyMap.has(line)) this._lineKeyMap.set(line, this._nextLineKey++);
      return this._lineKeyMap.get(line);
    },
    photoTypeName(id) {
      const fromCatalog = this.photoTypes.find(p => p.id === id);
      if (fromCatalog) return fromCatalog.name;
      return '';
    },
    photoTypePrice(id) {
      return Number(this.photoTypes.find(p => p.id === id)?.base_price || 0);
    },
    lineIsIncludedInPackage(line) {
      return Boolean(line?.order_package_id || line?.quote_package_id);
    },
    inputValueFor(line) {
      const key = this.lineKey(line);
      if (this.openDropdownKey === key) return this.photoTypeSearch;
      if (!line.photo_type_id) return '';
      const fromCatalog = this.photoTypes.find(p => p.id === line.photo_type_id);
      if (fromCatalog) return fromCatalog.name;
      return line.photo_type_name_snapshot || '';
    },
    openDropdown(line) {
      const key = this.lineKey(line);
      if (this.openDropdownKey === key) return;
      this.photoTypeSearch = '';
      this.highlightedIdx = 0;
      this.openDropdownKey = key;
    },
    closeDropdown() {
      this.openDropdownKey = null;
      this.photoTypeSearch = '';
    },
    onInput(line, value) {
      const key = this.lineKey(line);
      this.photoTypeSearch = value;
      this.highlightedIdx = 0;
      if (this.openDropdownKey !== key) this.openDropdownKey = key;
    },
    onFocusOut(line, event) {
      const key = this.lineKey(line);
      if (this.openDropdownKey !== key) return;
      if (!event.currentTarget.contains(event.relatedTarget)) this.closeDropdown();
    },
    moveHighlight(delta) {
      const len = this.filteredPhotoTypes.length;
      if (!len) return;
      this.highlightedIdx = (this.highlightedIdx + delta + len) % len;
    },
    selectHighlighted(idx, line) {
      const pt = this.filteredPhotoTypes[this.highlightedIdx];
      if (!pt) return;
      this.selectAndReset(idx, line, pt.id);
    },
    selectAndReset(idx, line, photoTypeId) {
      const isExisting = Boolean(line.id);
      line.photo_type_id = photoTypeId;
      if (!isExisting) {
        const photoType = this.photoTypes.find(p => p.id === photoTypeId);
        const base = Number(photoType?.base_quantity);
        line.quantity = Number.isFinite(base) && base >= 1 ? base : 1;
      }
      this.closeDropdown();
    },
    clearLine(idx) {
      const line = this.lines[idx];
      if (!line) return;
      line.photo_type_id = null;
      line.quantity = 1;
      this.photoTypeSearch = '';
    },
    sanitizeQuantity(line) {
      const value = Number(line.quantity);
      line.quantity = Number.isFinite(value) && value >= 1 ? Math.floor(value) : 1;
    },
    toggleFormat(line) {
      const key = this.lineKey(line);
      this.openFormatKey = this.openFormatKey === key ? null : key;
    },
    closeFormat(line) {
      const key = this.lineKey(line);
      setTimeout(() => { if (this.openFormatKey === key) this.openFormatKey = null; }, 200);
    },
    selectFormat(line, value) {
      line.format = value;
      this.openFormatKey = null;
    },
    addLine() {
      this.lines.push({ photo_type_id: null, quantity: 1, format: 'bn', paper_type_id: null });
    },
    removeLine(idx) {
      this.lines.splice(idx, 1);
    }
  }
};
</script>