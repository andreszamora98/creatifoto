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

      <div v-if="lines.length > 0" class="grid grid-cols-1 gap-2 sm:gap-3 short:gap-2">
        <div v-for="(line, idx) in lines" :key="lineKey(line)"
             class="relative bg-white dark:bg-gray-800 border rounded-lg p-2.5 sm:p-4 short:p-2"
             :class="showErrors && error && !line.package_id
               ? 'border-red-500 dark:border-red-500'
               : 'border-gray-200 dark:border-gray-600'">
          <div class="flex items-center justify-between gap-2 mb-2 sm:mb-3 short:mb-1.5">
            <span class="text-base sm:text-lg short:text-base font-semibold text-gray-500 dark:text-gray-400 truncate">Paquete {{ idx + 1 }}</span>
            <button v-if="canRemove" type="button" @click="removeLine(idx)" aria-label="Eliminar paquete"
                    class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 p-1 sm:p-1.5 short:p-1 rounded-lg transition-colors flex-shrink-0">
              <i class="ti ti-trash text-lg sm:text-2xl short:text-lg"></i>
            </button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 short:grid-cols-2 gap-3 sm:gap-5 short:gap-3">
            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-2.5 sm:p-5 short:p-2 space-y-2 sm:space-y-3 short:space-y-2">
              <div class="flex items-center justify-between gap-2 mb-1 sm:mb-2 short:mb-1">
                <p class="text-xs sm:text-base short:text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-200 truncate">
                  Información del paquete
                </p>
                <button
                  v-if="line.package_id"
                  type="button"
                  @click="emitViewDetails(line)"
                  class="flex-shrink-0 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 px-2 py-1 rounded-lg text-xs sm:text-sm flex items-center gap-1 transition-colors"
                >
                  <i class="ti ti-info-circle text-sm sm:text-base"></i>
                  <span class="whitespace-nowrap">Ver características</span>
                </button>
              </div>

              <div>
                <label class="text-sm sm:text-base short:text-sm font-medium block mb-1 short:mb-0.5 text-gray-500 dark:text-gray-400">Escuela</label>
                <div class="relative" :data-school-dropdown="editorId" @focusout="onSchoolFocusOut(line, $event)">
                  <div
                    class="w-full text-base sm:text-lg short:text-base p-2 sm:p-3 short:p-1.5 rounded-lg border text-gray-800 dark:text-gray-200 flex items-center gap-1.5 sm:gap-2"
                    :class="showErrors && error && !line.school_id
                      ? 'border-red-500 dark:border-red-500 bg-white dark:bg-gray-800'
                      : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800'"
                  >
                    <i class="ti ti-building-community text-lg sm:text-xl short:text-lg text-gray-400 dark:text-gray-500 flex-shrink-0"></i>
                    <input
                      :value="schoolInputValueFor(line)"
                      @focus="openSchoolDropdown(line)"
                      @input="onSchoolInput(line, $event.target.value)"
                      @keydown.esc="closeSchoolDropdown()"
                      @keydown.down.prevent="moveSchoolHighlight(1)"
                      @keydown.up.prevent="moveSchoolHighlight(-1)"
                      @keydown.enter.prevent="selectSchoolHighlighted(idx, line)"
                      type="text"
                      :placeholder="line.school_id ? 'Buscar otra escuela...' : 'Todas las escuelas'"
                      class="flex-1 min-w-0 bg-transparent border-0 outline-none text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 truncate"
                      autocomplete="off"
                    >
                    <button
                      v-if="line.school_id && !isSchoolOpen(line)"
                      type="button"
                      @mousedown.prevent="clearSchool(idx)"
                      class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 flex-shrink-0 p-1 short:p-0.5 rounded-lg transition-colors"
                      title="Limpiar escuela"
                    >
                      <i class="ti ti-x text-lg sm:text-2xl short:text-lg"></i>
                    </button>
                  </div>

                  <transition name="dropdown">
                    <div v-if="isSchoolOpen(line)"
                         class="absolute z-30 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
                      <div class="max-h-48 sm:max-h-56 short:max-h-32 overflow-y-auto">
                        <button
                          v-for="(school, sIdx) in filteredSchools"
                          :key="school.id"
                          type="button"
                          @mousedown.prevent="selectSchool(idx, line, school.id)"
                          @mouseover="schoolHighlightedIdx = sIdx"
                          class="w-full px-2.5 sm:px-3 short:px-2 py-1.5 sm:py-2.5 short:py-1.5 text-base sm:text-lg short:text-base flex items-center gap-1.5 sm:gap-2 text-gray-800 dark:text-gray-200"
                          :class="sIdx === schoolHighlightedIdx ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                        >
                          <i v-if="school.id === line.school_id" class="ti ti-check text-blue-600 dark:text-blue-400 text-base sm:text-lg flex-shrink-0"></i>
                          <span v-else class="w-4 sm:w-5 flex-shrink-0"></span>
                          <span class="flex-1 text-left truncate">{{ school.name }}</span>
                        </button>
                        <p v-if="filteredSchools.length === 0" class="text-sm sm:text-base short:text-sm text-gray-400 dark:text-gray-500 text-center py-2 sm:py-3">Sin coincidencias</p>
                      </div>
                    </div>
                  </transition>
                </div>
              </div>

              <div>
                <label class="text-sm sm:text-base short:text-sm font-medium block mb-1 short:mb-0.5 text-gray-500 dark:text-gray-400">Paquete</label>
                <div class="relative" :data-package-dropdown="editorId" @focusout="onPackageFocusOut(line, $event)">
                  <div
                    class="w-full text-base sm:text-lg short:text-base p-2 sm:p-3 short:p-1.5 rounded-lg border text-gray-800 dark:text-gray-200 flex items-center gap-1.5 sm:gap-2"
                    :class="!line.school_id
                      ? 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 opacity-60 cursor-not-allowed'
                      : showErrors && error && !line.package_id
                        ? 'border-red-500 dark:border-red-500 bg-white dark:bg-gray-800'
                        : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800'"
                  >
                    <i class="ti ti-school text-lg sm:text-xl short:text-lg text-gray-400 dark:text-gray-500 flex-shrink-0"></i>
                    <input
                      :value="packageInputValueFor(line)"
                      :disabled="!line.school_id"
                      @focus="openPackageDropdown(line)"
                      @input="onPackageInput(line, $event.target.value)"
                      @keydown.esc="closePackageDropdown()"
                      @keydown.down.prevent="movePackageHighlight(1)"
                      @keydown.up.prevent="movePackageHighlight(-1)"
                      @keydown.enter.prevent="selectPackageHighlighted(idx, line)"
                      type="text"
                      :placeholder="packagePlaceholder(line)"
                      class="flex-1 min-w-0 bg-transparent border-0 outline-none text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 truncate disabled:cursor-not-allowed"
                      autocomplete="off"
                    >
                    <span v-if="line.package_id && !isPackageOpen(line)" class="text-xs sm:text-sm short:text-xs font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap flex-shrink-0">
                      ${{ packagePriceForLine(line).toFixed(2) }}
                    </span>
                    <button
                      v-if="line.package_id && !isPackageOpen(line)"
                      type="button"
                      @mousedown.prevent="clearPackage(idx)"
                      class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 flex-shrink-0 p-1 short:p-0.5 rounded-lg transition-colors"
                      title="Limpiar selección"
                    >
                      <i class="ti ti-x text-lg sm:text-2xl short:text-lg"></i>
                    </button>
                  </div>

                  <transition name="dropdown">
                    <div v-if="isPackageOpen(line)"
                         class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
                      <div class="max-h-48 sm:max-h-56 short:max-h-32 overflow-y-auto">
                        <button
                          v-for="(pkg, pIdx) in filteredPackagesFor(line)"
                          :key="pkg.id"
                          type="button"
                          @mousedown.prevent="selectPackageFromList(idx, line, pkg.id)"
                          @mouseover="packageHighlightedIdx = pIdx"
                          class="w-full px-2.5 sm:px-3 short:px-2 py-1.5 sm:py-2.5 short:py-1.5 text-base sm:text-lg short:text-base flex items-center gap-1.5 sm:gap-2 text-gray-800 dark:text-gray-200"
                          :class="pIdx === packageHighlightedIdx ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                        >
                          <i v-if="pkg.id === line.package_id" class="ti ti-check text-blue-600 dark:text-blue-400 text-base sm:text-lg flex-shrink-0"></i>
                          <span v-else class="w-4 sm:w-5 flex-shrink-0"></span>
                          <span class="flex-1 text-left truncate">{{ packageLabel(pkg) }}</span>
                          <span class="text-xs sm:text-sm short:text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap flex-shrink-0">${{ Number(pkg.total_price).toFixed(2) }}</span>
                        </button>
                        <p v-if="filteredPackagesFor(line).length === 0" class="text-sm sm:text-base short:text-sm text-gray-400 dark:text-gray-500 text-center py-2 sm:py-3">Sin coincidencias</p>
                      </div>
                    </div>
                  </transition>
                </div>
              </div>
            </div>

            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-2.5 sm:p-5 short:p-2 space-y-1.5 sm:space-y-2 short:space-y-1.5">
              <p class="text-xs sm:text-base short:text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-200 mb-1 sm:mb-2 short:mb-1">
                Contenido del paquete
              </p>

              <p v-if="!line.package_id" class="text-sm sm:text-base short:text-sm text-gray-400 dark:text-gray-500 italic">
                Selecciona un paquete para ver su contenido.
              </p>
              <p v-else-if="packageContentFor(line).length === 0" class="text-sm sm:text-base short:text-sm text-gray-400 dark:text-gray-500 italic">
                Este paquete no tiene conceptos registrados.
              </p>

              <template v-else>
                <div
                  v-for="cl in packageContentLines(line)"
                  :key="cl.key"
                  class="flex justify-between items-start text-sm sm:text-lg short:text-sm text-gray-700 dark:text-gray-300 p-2 sm:p-3 short:p-2 rounded-lg border gap-2 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600"
                >
                  <span class="flex items-start gap-2 sm:gap-3 short:gap-2 min-w-0 flex-1">
                    <span
                      v-if="cl.format"
                      class="relative w-7 h-7 sm:w-9 sm:h-9 short:w-6 short:h-6 rounded-md overflow-hidden flex-shrink-0 flex items-center justify-center mt-0.5"
                      :class="cl.format === 'bn' ? 'bg-gray-200 dark:bg-gray-700' : ''"
                    >
                      <template v-if="cl.format === 'color'">
                        <span
                          class="absolute inset-0 blur-md bg-[radial-gradient(circle_at_25%_25%,theme(colors.cyan.300),transparent_60%),radial-gradient(circle_at_75%_25%,theme(colors.fuchsia.300),transparent_60%),radial-gradient(circle_at_50%_80%,theme(colors.yellow.300),transparent_60%)] dark:bg-[radial-gradient(circle_at_25%_25%,theme(colors.cyan.700),transparent_60%),radial-gradient(circle_at_75%_25%,theme(colors.fuchsia.700),transparent_60%),radial-gradient(circle_at_50%_80%,theme(colors.yellow.700),transparent_60%)]"
                        ></span>
                        <span class="relative z-10 inline-block w-[18px] h-[18px]">
                          <span class="absolute top-0 left-0 w-3 h-3 rounded-full bg-cyan-400 mix-blend-multiply"></span>
                          <span class="absolute top-0 left-1.5 w-3 h-3 rounded-full bg-fuchsia-500 mix-blend-multiply"></span>
                          <span class="absolute top-1.5 left-[3px] w-3 h-3 rounded-full bg-yellow-300 mix-blend-multiply"></span>
                        </span>
                      </template>
                      <span v-else class="inline-flex -space-x-1">
                        <span class="w-3.5 h-3.5 rounded-full bg-white border border-gray-400 dark:border-gray-500"></span>
                        <span class="w-3.5 h-3.5 rounded-full bg-black border border-gray-300 dark:border-gray-400"></span>
                      </span>
                    </span>
                    <span
                      v-else
                      class="w-7 h-7 sm:w-9 sm:h-9 short:w-6 short:h-6 rounded-md flex-shrink-0 flex items-center justify-center bg-gray-200 dark:bg-gray-700 mt-0.5"
                    >
                      <i :class="[cl.icon, 'text-base sm:text-xl short:text-sm text-gray-500 dark:text-gray-400']"></i>
                    </span>

                    <span class="flex flex-col min-w-0 flex-1 gap-0.5">
                      <span class="break-words">
                        {{ cl.name }}<span
                          v-if="cl.quantity"
                          class="text-gray-300 dark:text-gray-600 mx-1"
                        >·</span><span
                          v-if="cl.unitPrice !== undefined && cl.unitPrice !== null"
                          class="text-green-700 dark:text-green-500 font-semibold"
                        >{{ formatCurrency(cl.unitPrice) }} c/u</span><span
                          v-if="cl.quantity"
                          class="text-gray-300 dark:text-gray-600 mx-1"
                        >·</span><span
                          v-if="cl.quantity"
                          class="text-blue-600 dark:text-blue-400 font-semibold"
                        >x{{ cl.quantity }}</span>
                      </span>
                      <span
                        v-if="cl.features.length"
                        class="text-xs sm:text-sm short:text-xs text-gray-400 dark:text-gray-500 break-words"
                      >
                        {{ cl.features.join(' · ') }}
                      </span>
                    </span>
                  </span>
                  <span class="font-medium whitespace-nowrap ml-2 flex-shrink-0">${{ cl.amount.toFixed(2) }}</span>
                </div>

                <div class="border-t border-gray-300 dark:border-gray-600 pt-2 sm:pt-3 short:pt-2 flex justify-between items-center gap-2">
                  <span class="text-base sm:text-xl short:text-base font-semibold text-gray-800 dark:text-gray-200">Suma de referencia</span>
                  <span class="text-lg sm:text-2xl short:text-lg font-bold text-blue-600 dark:text-blue-400">${{ packageReferenceTotal(line).toFixed(2) }}</span>
                </div>
              </template>
            </div>
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
  name: 'PackageLinesEditor',
  props: {
    lines: { type: Array, required: true },
    packages: { type: Array, default: () => [] },
    schools: { type: Array, default: () => [] },
    paperTypes: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    showErrors: { type: Boolean, default: false },
    error: { type: Boolean, default: false },
    errorMessage: { type: String, default: 'Campo obligatorio*' },
    title: { type: String, default: 'Paquetes escolares' },
    icon: { type: String, default: 'ti ti-school' },
    emptyHint: { type: String, default: '' },
    canRemove: { type: Boolean, default: true },
    addLabel: { type: String, default: 'Agregar otro paquete' },
    addLabelFirst: { type: String, default: 'Agregar paquete' }
  },
  emits: ['view-package-details'],
  data() {
    return {
      editorId: 'pke-' + Math.random().toString(36).slice(2),
      schoolSearch: '',
      packageSearch: '',
      schoolHighlightedIdx: 0,
      packageHighlightedIdx: 0,
      openSchoolDropdownKey: null,
      openPackageDropdownKey: null
    };
  },
  computed: {
    filteredSchools() {
      const q = this.normalize(this.schoolSearch);
      if (!q) return this.schools;
      return this.schools.filter(s => this.normalize(s.name).startsWith(q));
    }
  },
  created() {
    this._lineKeyMap = new WeakMap();
    this._nextLineKey = 1;
  },
  mounted() {
    this._outsideTapHandler = (event) => {
      if (this.openSchoolDropdownKey !== null && !event.target.closest(`[data-school-dropdown="${this.editorId}"]`)) {
        this.closeSchoolDropdown();
      }
      if (this.openPackageDropdownKey !== null && !event.target.closest(`[data-package-dropdown="${this.editorId}"]`)) {
        this.closePackageDropdown();
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
    packageById(id) {
      if (id === null || id === undefined) return null;
      return this.packages.find(p => p.id === id) || null;
    },
    schoolById(id) {
      return this.schools.find(s => Number(s.id) === Number(id)) || null;
    },
    packageLabel(pkg) {
      const number = Number(pkg?.package_number || 0);
      const name = String(pkg?.name || '').trim();
      if (number > 0) {
        return name ? `${number}.- ${name}` : `Paquete ${number}`;
      }
      return name || 'Paquete';
    },
    packagePriceForLine(line) {
      const pkg = this.packageById(line.package_id);
      if (pkg) return Number(pkg.total_price || 0);
      return Number(line.price_snapshot || 0);
    },
    packageNameForLine(line) {
      if (!line.package_id) return '';
      const pkg = this.packageById(line.package_id);
      if (pkg) return this.packageLabel(pkg);
      return line.package_name_snapshot || ('Paquete #' + line.package_id);
    },
    schoolNameForLine(line) {
      const school = this.schoolById(line.school_id);
      if (school) return school.name;
      return line.school_name_snapshot || '';
    },
    packagePlaceholder(line) {
      if (!line.school_id) return 'Selecciona primero una escuela';
      return line.package_id ? 'Buscar otro paquete...' : 'Buscar o seleccionar paquete...';
    },

    packageContentFor(line) {
      const pkg = this.packageById(line.package_id);
      if (!pkg) return [];

      const photos = Array.isArray(pkg.photo_types)
        ? pkg.photo_types.map(pt => {
            const quantity = Number(pt.pivot?.quantity || 0);
            const unitPrice = Number(pt.pivot?.unit_price ?? pt.base_price ?? 0);
            const paperId = pt.pivot?.paper_type_id;
            const paper = paperId ? this.paperTypes.find(p => Number(p.id) === Number(paperId)) : null;
            return {
              type: 'photo',
              id: pt.id,
              name: pt.name || 'Tipo de foto',
              quantity,
              format: pt.pivot?.format || 'color',
              background: pt.pivot?.background || null,
              paper_name: paper?.name || null,
              unit_price: unitPrice,
              subtotal: unitPrice * quantity
            };
          })
        : [];

      const services = Array.isArray(pkg.additional_services)
        ? pkg.additional_services.map(svc => {
            const quantity = Number(svc.pivot?.quantity || 0);
            const unitPrice = Number(svc.pivot?.unit_price ?? svc.default_price ?? 0);
            return {
              type: 'service',
              id: svc.id,
              name: svc.name || 'Servicio adicional',
              quantity,
              icon: 'ti ti-tool',
              unit_price: unitPrice,
              subtotal: unitPrice * quantity
            };
          })
        : [];

      return [...photos, ...services];
    },
    packageContentLines(line) {
      return this.packageContentFor(line).map((item, idx) => {
        const features = [];
        if (item.type === 'photo') {
          features.push(item.format === 'color' ? 'Color' : 'B/N');
          if (item.background && String(item.background).trim() !== '') {
            features.push('Fondo ' + String(item.background).trim().toLowerCase());
          }
          if (item.paper_name && String(item.paper_name).trim() !== '') {
            features.push('Papel ' + String(item.paper_name).trim().toLowerCase());
          }
        }

        return {
          key: `pkg-${line.package_id}-${item.type}-${item.id}-${idx}`,
          name: item.name,
          quantity: item.quantity,
          format: item.format,
          icon: item.icon,
          unitPrice: item.unit_price,
          amount: item.subtotal,
          features
        };
      });
    },
    packageReferenceTotal(line) {
      return this.packageContentFor(line).reduce((sum, l) => sum + l.subtotal, 0);
    },
    formatCurrency(value) {
      const num = Number(value || 0);
      return '$' + num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    emitViewDetails(line) {
      const pkg = this.packageById(line.package_id);
      if (!pkg) return;
      this.$emit('view-package-details', pkg);
    },

    isSchoolOpen(line) {
      return this.openSchoolDropdownKey === this.lineKey(line);
    },
    isPackageOpen(line) {
      return this.openPackageDropdownKey === this.lineKey(line);
    },

    schoolInputValueFor(line) {
      const key = this.lineKey(line);
      if (this.openSchoolDropdownKey === key) return this.schoolSearch;
      return this.schoolNameForLine(line);
    },
    packageInputValueFor(line) {
      const key = this.lineKey(line);
      if (this.openPackageDropdownKey === key) return this.packageSearch;
      return this.packageNameForLine(line);
    },

    openSchoolDropdown(line) {
      const key = this.lineKey(line);
      if (this.openSchoolDropdownKey === key) return;
      this.schoolSearch = '';
      this.schoolHighlightedIdx = 0;
      this.openSchoolDropdownKey = key;
      this.closePackageDropdown();
    },
    closeSchoolDropdown() {
      this.openSchoolDropdownKey = null;
      this.schoolSearch = '';
    },
    onSchoolInput(line, value) {
      const key = this.lineKey(line);
      this.schoolSearch = value;
      this.schoolHighlightedIdx = 0;
      if (this.openSchoolDropdownKey !== key) this.openSchoolDropdownKey = key;
    },
    onSchoolFocusOut(line, event) {
      const key = this.lineKey(line);
      if (this.openSchoolDropdownKey !== key) return;
      if (!event.currentTarget.contains(event.relatedTarget)) this.closeSchoolDropdown();
    },
    moveSchoolHighlight(delta) {
      const len = this.filteredSchools.length;
      if (!len) return;
      this.schoolHighlightedIdx = (this.schoolHighlightedIdx + delta + len) % len;
    },
    selectSchoolHighlighted(idx, line) {
      const school = this.filteredSchools[this.schoolHighlightedIdx];
      if (!school) return;
      this.selectSchool(idx, line, school.id);
    },
    selectSchool(idx, line, schoolId) {
      const hadDifferentSchool = line.school_id !== null && Number(line.school_id) !== Number(schoolId);
      line.school_id = schoolId;
      if (hadDifferentSchool || !line.package_id) {
        line.package_id = null;
      } else {
        const pkg = this.packageById(line.package_id);
        if (pkg && Number(pkg.school_id) !== Number(schoolId)) {
          line.package_id = null;
        }
      }
      this.closeSchoolDropdown();
    },
    clearSchool(idx) {
      const line = this.lines[idx];
      if (!line) return;
      line.school_id = null;
      line.package_id = null;
      this.schoolSearch = '';
    },

    openPackageDropdown(line) {
      if (!line.school_id) return;
      const key = this.lineKey(line);
      if (this.openPackageDropdownKey === key) return;
      this.packageSearch = '';
      this.packageHighlightedIdx = 0;
      this.openPackageDropdownKey = key;
      this.closeSchoolDropdown();
    },
    closePackageDropdown() {
      this.openPackageDropdownKey = null;
      this.packageSearch = '';
    },
    onPackageInput(line, value) {
      if (!line.school_id) return;
      const key = this.lineKey(line);
      this.packageSearch = value;
      this.packageHighlightedIdx = 0;
      if (this.openPackageDropdownKey !== key) this.openPackageDropdownKey = key;
    },
    onPackageFocusOut(line, event) {
      const key = this.lineKey(line);
      if (this.openPackageDropdownKey !== key) return;
      if (!event.currentTarget.contains(event.relatedTarget)) this.closePackageDropdown();
    },
    filteredPackagesFor(line) {
      let list = this.packages;
      if (line.school_id) {
        list = list.filter(p => Number(p.school_id) === Number(line.school_id));
      }
      const q = this.normalize(this.packageSearch);
      if (!q) return list;
      return list.filter(p => {
        const label = this.normalize(this.packageLabel(p));
        const name = this.normalize(p.name || '');
        const number = String(p.package_number ?? '');
        return label.startsWith(q) || name.startsWith(q) || number.startsWith(q);
      });
    },
    movePackageHighlight(delta) {
      const line = this.lines.find(l => this.lineKey(l) === this.openPackageDropdownKey);
      if (!line) return;
      const len = this.filteredPackagesFor(line).length;
      if (!len) return;
      this.packageHighlightedIdx = (this.packageHighlightedIdx + delta + len) % len;
    },
    selectPackageHighlighted(idx, line) {
      const list = this.filteredPackagesFor(line);
      const pkg = list[this.packageHighlightedIdx];
      if (!pkg) return;
      this.selectPackageFromList(idx, line, pkg.id);
    },
    selectPackageFromList(idx, line, packageId) {
      line.package_id = packageId;
      const pkg = this.packageById(packageId);
      if (pkg && pkg.school_id) line.school_id = pkg.school_id;
      this.closePackageDropdown();
    },
    clearPackage(idx) {
      const line = this.lines[idx];
      if (!line) return;
      line.package_id = null;
      this.packageSearch = '';
    },

    addLine() {
      this.lines.push({ package_id: null, school_id: null });
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