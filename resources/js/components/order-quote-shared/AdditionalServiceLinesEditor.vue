<template>
  <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-2.5 sm:p-5 short:p-2 border-2"
       :class="showErrors && error ? 'border-red-500' : 'border-transparent'">
    <h4 v-if="title" class="text-base sm:text-xl short:text-base font-semibold text-gray-700 dark:text-gray-300 mb-1 flex items-center">
      <i :class="icon" class="mr-1.5 sm:mr-2 text-xl sm:text-2xl short:text-xl"></i>{{ title }}
    </h4>
    <p v-if="showErrors && error" class="text-sm sm:text-lg short:text-sm text-red-600 dark:text-red-400 mb-2 sm:mb-3 short:mb-1.5 font-medium">{{ errorMessage }}</p>

    <div v-if="loading" class="text-center py-3 sm:py-4 short:py-2 text-base sm:text-lg short:text-base text-gray-500 dark:text-gray-400 mt-2 sm:mt-3 short:mt-1">
      <i class="ti ti-refresh animate-spin"></i> Cargando catálogo...
    </div>

    <div v-else class="mt-2 sm:mt-3 short:mt-1">
      <p v-if="lines.length === 0 && emptyHint" class="text-sm sm:text-lg short:text-sm text-gray-400 dark:text-gray-500 italic">{{ emptyHint }}</p>

      <div v-if="lines.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-2 sm:gap-3 short:gap-2">
        <div v-for="(line, idx) in lines" :key="lineKey(line)"
             class="relative bg-white dark:bg-gray-800 border rounded-lg p-2.5 sm:p-4 short:p-2 space-y-2 sm:space-y-3 short:space-y-2"
             :class="showErrors && !line.service_id
               ? 'border-red-500 dark:border-red-500'
               : 'border-gray-200 dark:border-gray-600'">
          <div class="flex items-center justify-between gap-2">
            <span class="text-base sm:text-lg short:text-base font-semibold text-gray-500 dark:text-gray-400 truncate">Servicio {{ idx + 1 }}</span>
            <button v-if="canRemove" type="button" @click="removeLine(idx)" aria-label="Eliminar servicio"
                    class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 p-1 sm:p-1.5 short:p-1 rounded-lg transition-colors flex-shrink-0">
              <i class="ti ti-trash text-lg sm:text-2xl short:text-lg"></i>
            </button>
          </div>

          <div class="grid grid-cols-[1fr_auto] gap-x-1.5 sm:gap-x-3 short:gap-x-1.5 gap-y-1 items-end">
            <span></span>
            <span class="text-xs sm:text-base short:text-xs font-medium text-gray-500 dark:text-gray-400 text-center whitespace-nowrap">Cantidad</span>

            <div class="relative min-w-0 sm:min-w-[10rem]" :data-service-dropdown="editorId" @focusout="onFocusOut(line, $event)">
              <div
                class="w-full text-base sm:text-lg short:text-base p-2 sm:p-3 short:p-1.5 rounded-lg border text-gray-800 dark:text-gray-200 flex items-center gap-1.5 sm:gap-2"
                :class="showErrors && !line.service_id
                  ? 'border-red-500 dark:border-red-500'
                  : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700'"
              >
                <i class="ti ti-tool text-lg sm:text-xl short:text-lg text-gray-400 dark:text-gray-500 flex-shrink-0"></i>
                <input
                  :value="inputValueFor(line)"
                  @focus="openDropdown(line)"
                  @input="onInput(line, $event.target.value)"
                  @keydown.esc="closeDropdown()"
                  @keydown.down.prevent="moveHighlight(1)"
                  @keydown.up.prevent="moveHighlight(-1)"
                  @keydown.enter.prevent="selectHighlighted(idx, line)"
                  type="text"
                  :placeholder="line.service_id ? 'Buscar otro servicio...' : 'Buscar o seleccionar servicio...'"
                  class="flex-1 min-w-0 bg-transparent border-0 outline-none text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 truncate"
                  autocomplete="off"
                >
                <span v-if="line.service_id && !isOpen(line)" class="text-xs sm:text-sm short:text-xs font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap flex-shrink-0">
                  ${{ servicePriceForLine(line).toFixed(2) }} c/u
                </span>
                <button
                  v-if="line.service_id && !isOpen(line)"
                  type="button"
                  @mousedown.prevent="clearLine(idx)"
                  class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 flex-shrink-0 p-1 short:p-0.5 rounded-lg transition-colors"
                  title="Limpiar selección"
                >
                  <i class="ti ti-x text-lg sm:text-2xl short:text-lg"></i>
                </button>
              </div>

              <transition name="dropdown">
                <div v-if="isOpen(line)"
                     class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
                  <div class="max-h-48 sm:max-h-56 short:max-h-32 overflow-y-auto">
                    <button
                      v-for="(svc, sIdx) in filteredServices"
                      :key="svc.id"
                      type="button"
                      @mousedown.prevent="selectAndReset(idx, line, svc.id)"
                      @mouseover="highlightedIdx = sIdx"
                      class="w-full px-2.5 sm:px-3 short:px-2 py-1.5 sm:py-2.5 short:py-1.5 text-base sm:text-lg short:text-base flex items-center gap-1.5 sm:gap-2 text-gray-800 dark:text-gray-200"
                      :class="sIdx === highlightedIdx ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                    >
                      <i v-if="svc.id === line.service_id" class="ti ti-check text-blue-600 dark:text-blue-400 text-base sm:text-lg flex-shrink-0"></i>
                      <span v-else class="w-4 sm:w-5 flex-shrink-0"></span>
                      <span class="flex-1 text-left truncate">{{ svc.name }}</span>
                      <span class="text-xs sm:text-sm short:text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap flex-shrink-0">${{ Number(svc.default_price).toFixed(2) }} c/u</span>
                    </button>
                    <p v-if="filteredServices.length === 0" class="text-sm sm:text-base short:text-sm text-gray-400 dark:text-gray-500 text-center py-2 sm:py-3">Sin coincidencias</p>
                  </div>
                </div>
              </transition>
            </div>

            <input
              type="number" min="1" v-model.number="line.quantity"
              @blur="sanitizeQuantity(line)"
              v-blur-on-enter
              enterkeyhint="done"
              :disabled="!line.service_id"
              class="form-control form-control-sm text-base sm:text-lg short:text-base p-2 sm:p-3 short:p-1.5 w-16 sm:w-28 short:w-14 text-center rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
          </div>
        </div>
      </div>

      <button type="button" @click="addLine"
              class="mt-2 sm:mt-3 short:mt-1 py-1 sm:py-0 text-sm sm:text-lg short:text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center gap-1">
        <i class="ti ti-plus text-base sm:text-xl short:text-base"></i> {{ lines.length === 0 ? addLabelFirst : addLabel }}
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdditionalServiceLinesEditor',
  props: {
    lines: { type: Array, required: true },
    services: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    showErrors: { type: Boolean, default: false },
    error: { type: Boolean, default: false },
    errorMessage: { type: String, default: 'Campo obligatorio*' },
    title: { type: String, default: 'Servicios adicionales' },
    icon: { type: String, default: 'ti ti-tool' },
    emptyHint: { type: String, default: '' },
    canRemove: { type: Boolean, default: true },
    addLabel: { type: String, default: 'Agregar otro servicio' },
    addLabelFirst: { type: String, default: 'Agregar servicio' }
  },
  data() {
    return {
      editorId: 'ase-' + Math.random().toString(36).slice(2),
      serviceSearch: '',
      highlightedIdx: 0,
      openDropdownKey: null
    };
  },
  computed: {
    filteredServices() {
      const q = this.normalize(this.serviceSearch);
      if (!q) return this.services;
      return this.services.filter(s => this.normalize(s.name).startsWith(q));
    }
  },
  created() {
    this._lineKeyMap = new WeakMap();
    this._nextLineKey = 1;
  },
  mounted() {
    this._outsideTapHandler = (event) => {
      if (this.openDropdownKey !== null && !event.target.closest(`[data-service-dropdown="${this.editorId}"]`)) {
        this.closeDropdown();
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
    serviceName(id) {
      const fromCatalog = this.services.find(s => s.id === id);
      if (fromCatalog) return fromCatalog.name;
      return '';
    },
    servicePrice(id) {
      return Number(this.services.find(s => s.id === id)?.default_price || 0);
    },
    servicePriceForLine(line) {
      if (line.price !== undefined && line.price !== null) {
        return Number(line.price);
      }
      return this.servicePrice(line.service_id);
    },
    serviceDefaultQuantity(id) {
      const svc = this.services.find(s => s.id === id);
      const q = Number(svc?.default_quantity);
      return Number.isFinite(q) && q >= 1 ? Math.floor(q) : 1;
    },
    isOpen(line) {
      return this.openDropdownKey === this.lineKey(line);
    },
    inputValueFor(line) {
      const key = this.lineKey(line);
      if (this.openDropdownKey === key) return this.serviceSearch;
      if (!line.service_id) return '';
      const fromCatalog = this.services.find(s => s.id === line.service_id);
      if (fromCatalog) return fromCatalog.name;
      return line.additional_service_name_snapshot || '';
    },
    openDropdown(line) {
      const key = this.lineKey(line);
      if (this.openDropdownKey === key) return;
      this.serviceSearch = '';
      this.highlightedIdx = 0;
      this.openDropdownKey = key;
    },
    closeDropdown() {
      this.openDropdownKey = null;
      this.serviceSearch = '';
    },
    onInput(line, value) {
      const key = this.lineKey(line);
      this.serviceSearch = value;
      this.highlightedIdx = 0;
      if (this.openDropdownKey !== key) this.openDropdownKey = key;
    },
    onFocusOut(line, event) {
      const key = this.lineKey(line);
      if (this.openDropdownKey !== key) return;
      if (!event.currentTarget.contains(event.relatedTarget)) this.closeDropdown();
    },
    moveHighlight(delta) {
      const len = this.filteredServices.length;
      if (!len) return;
      this.highlightedIdx = (this.highlightedIdx + delta + len) % len;
    },
    selectHighlighted(idx, line) {
      const svc = this.filteredServices[this.highlightedIdx];
      if (!svc) return;
      this.selectAndReset(idx, line, svc.id);
    },
    selectAndReset(idx, line, serviceId) {
      const isExisting = Boolean(line.id);
      line.service_id = serviceId;
      if (!isExisting) {
        line.price = this.servicePrice(serviceId);
        line.quantity = this.serviceDefaultQuantity(serviceId);
      }
      this.closeDropdown();
    },
    clearLine(idx) {
      const line = this.lines[idx];
      if (!line) return;
      line.service_id = null;
      line.quantity = 1;
      line.price = null;
      this.serviceSearch = '';
    },
    sanitizeQuantity(line) {
      const value = Number(line.quantity);
      line.quantity = Number.isFinite(value) && value >= 1 ? Math.floor(value) : 1;
    },
    addLine() {
      this.lines.push({ service_id: null, quantity: 1, price: null });
    },
    removeLine(idx) {
      this.lines.splice(idx, 1);
    }
  }
};
</script>

<style scoped>
.dropdown-enter-active { transition: opacity 0.18s ease, transform 0.18s ease; transform-origin: top center; }
.dropdown-leave-active { transition: opacity 0.14s ease, transform 0.14s ease; transform-origin: top center; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: scaleY(0.9) translateY(-4px); }
</style>