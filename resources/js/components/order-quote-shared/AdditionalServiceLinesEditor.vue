<template>
  <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5 border-2"
       :class="showErrors && error ? 'border-red-500' : 'border-transparent'">
    <h4 v-if="title" class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1 flex items-center">
      <i :class="icon" class="mr-2 text-xl"></i>{{ title }}
    </h4>
    <p v-if="showErrors && error" class="text-base text-red-600 dark:text-red-400 mb-3 font-medium">{{ errorMessage }}</p>

    <div v-if="loading" class="text-center py-4 text-lg text-gray-500 dark:text-gray-400 mt-3">
      <i class="ti ti-refresh animate-spin"></i> Cargando catálogo...
    </div>

    <div v-else class="mt-3">
      <p v-if="lines.length === 0 && emptyHint" class="text-base text-gray-400 dark:text-gray-500 italic">{{ emptyHint }}</p>

      <div v-if="lines.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div v-for="(line, idx) in lines" :key="lineKey(line)"
             class="relative bg-white dark:bg-gray-800 border rounded-lg p-4 space-y-3"
             :class="showErrors && !line.service_id
               ? 'border-red-500 dark:border-red-500'
               : 'border-gray-200 dark:border-gray-600'">
          <div class="flex items-center justify-between">
            <span class="text-base font-semibold text-gray-500 dark:text-gray-400">Servicio {{ idx + 1 }}</span>
            <button v-if="canRemove" type="button" @click="removeLine(idx)"
                    class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 p-1.5 rounded-lg transition-colors">
              <i class="ti ti-trash text-xl"></i>
            </button>
          </div>

          <div class="grid grid-cols-[1fr_auto] gap-x-3 gap-y-1 items-end">
            <span></span>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 text-center whitespace-nowrap">Cantidad</span>

            <div class="relative min-w-[10rem]" :data-service-dropdown="editorId" @focusout="onFocusOut(line, $event)">
              <div
                class="w-full text-lg p-3 rounded-lg border text-gray-800 dark:text-gray-200 flex items-center gap-2"
                :class="showErrors && !line.service_id
                  ? 'border-red-500 dark:border-red-500'
                  : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700'"
              >
                <i class="ti ti-tool text-xl text-gray-400 dark:text-gray-500 flex-shrink-0"></i>
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
                <span v-if="line.service_id && !isOpen(line)" class="text-sm font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap flex-shrink-0">
                  ${{ servicePriceForLine(line).toFixed(2) }} c/u
                </span>
                <button
                  v-if="line.service_id && !isOpen(line)"
                  type="button"
                  @mousedown.prevent="clearLine(idx)"
                  class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 flex-shrink-0 p-1 rounded-lg transition-colors"
                  title="Limpiar selección"
                >
                  <i class="ti ti-x text-2xl"></i>
                </button>
              </div>

              <transition name="dropdown">
                <div v-if="isOpen(line)"
                     class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
                  <div class="max-h-56 overflow-y-auto">
                    <button
                      v-for="(svc, sIdx) in filteredServices"
                      :key="svc.id"
                      type="button"
                      @mousedown.prevent="selectAndReset(idx, line, svc.id)"
                      @mouseover="highlightedIdx = sIdx"
                      class="w-full px-3 py-2.5 text-base flex items-center gap-2 text-gray-800 dark:text-gray-200"
                      :class="sIdx === highlightedIdx ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                    >
                      <i v-if="svc.id === line.service_id" class="ti ti-check text-blue-600 dark:text-blue-400 text-base flex-shrink-0"></i>
                      <span v-else class="w-4 flex-shrink-0"></span>
                      <span class="flex-1 text-left truncate">{{ svc.name }}</span>
                      <span class="text-gray-500 dark:text-gray-400 text-sm whitespace-nowrap">${{ Number(svc.default_price).toFixed(2) }} c/u</span>
                    </button>
                    <p v-if="filteredServices.length === 0" class="text-sm text-gray-400 dark:text-gray-500 text-center py-3">Sin coincidencias</p>
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
              class="form-control form-control-sm text-lg p-3 w-28 text-center rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
          </div>
        </div>
      </div>

      <button type="button" @click="addLine"
              class="mt-3 text-base font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center gap-1">
        <i class="ti ti-plus text-lg"></i> {{ lines.length === 0 ? addLabelFirst : addLabel }}
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
      if (isExisting) {
        // Servicio existente: conservamos su price histórico.
        // El usuario puede editarlo manualmente si lo necesita.
      } else {
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