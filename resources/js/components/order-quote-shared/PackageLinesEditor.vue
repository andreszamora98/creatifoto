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
             class="relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-4 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-base font-semibold text-gray-500 dark:text-gray-400">Paquete {{ idx + 1 }}</span>
            <button v-if="canRemove" type="button" @click="removeLine(idx)"
                    class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 p-1.5 rounded-lg transition-colors">
              <i class="ti ti-trash text-xl"></i>
            </button>
          </div>

          <div>
            <label class="text-sm font-medium block mb-1 text-gray-500 dark:text-gray-400">Escuela</label>
            <div class="relative" :data-school-dropdown="editorId" @focusout="onSchoolFocusOut(line, $event)">
              <div
                class="w-full text-lg p-3 rounded-lg border text-gray-800 dark:text-gray-200 flex items-center gap-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700"
              >
                <i class="ti ti-building-community text-xl text-gray-400 dark:text-gray-500 flex-shrink-0"></i>
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
                  class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 flex-shrink-0 p-1 rounded-lg transition-colors"
                  title="Limpiar escuela"
                >
                  <i class="ti ti-x text-2xl"></i>
                </button>
              </div>

              <transition name="dropdown">
                <div v-if="isSchoolOpen(line)"
                     class="absolute z-30 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
                  <div class="max-h-56 overflow-y-auto">
                    <button
                      v-for="(school, sIdx) in filteredSchools"
                      :key="school.id"
                      type="button"
                      @mousedown.prevent="selectSchool(idx, line, school.id)"
                      @mouseover="schoolHighlightedIdx = sIdx"
                      class="w-full px-3 py-2.5 text-base flex items-center gap-2 text-gray-800 dark:text-gray-200"
                      :class="sIdx === schoolHighlightedIdx ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                    >
                      <i v-if="school.id === line.school_id" class="ti ti-check text-blue-600 dark:text-blue-400 text-base flex-shrink-0"></i>
                      <span v-else class="w-4 flex-shrink-0"></span>
                      <span class="flex-1 text-left truncate">{{ school.name }}</span>
                    </button>
                    <p v-if="filteredSchools.length === 0" class="text-sm text-gray-400 dark:text-gray-500 text-center py-3">Sin coincidencias</p>
                  </div>
                </div>
              </transition>
            </div>
          </div>

          <div>
            <label class="text-sm font-medium block mb-1 text-gray-500 dark:text-gray-400">Paquete</label>
            <div class="relative" :data-package-dropdown="editorId" @focusout="onPackageFocusOut(line, $event)">
              <div
                class="w-full text-lg p-3 rounded-lg border text-gray-800 dark:text-gray-200 flex items-center gap-2"
                :class="showErrors && error && !line.package_id
                  ? 'border-red-500 dark:border-red-500'
                  : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700'"
              >
                <i class="ti ti-school text-xl text-gray-400 dark:text-gray-500 flex-shrink-0"></i>
                <input
                  :value="packageInputValueFor(line)"
                  @focus="openPackageDropdown(line)"
                  @input="onPackageInput(line, $event.target.value)"
                  @keydown.esc="closePackageDropdown()"
                  @keydown.down.prevent="movePackageHighlight(1)"
                  @keydown.up.prevent="movePackageHighlight(-1)"
                  @keydown.enter.prevent="selectPackageHighlighted(idx, line)"
                  type="text"
                  :placeholder="line.package_id ? 'Buscar otro paquete...' : 'Buscar o seleccionar paquete...'"
                  class="flex-1 min-w-0 bg-transparent border-0 outline-none text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 truncate"
                  autocomplete="off"
                >
                <span v-if="line.package_id && !isPackageOpen(line)" class="text-sm font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap flex-shrink-0">
                  ${{ packagePrice(line.package_id).toFixed(2) }}
                </span>
                <button
                  v-if="line.package_id && !isPackageOpen(line)"
                  type="button"
                  @mousedown.prevent="clearPackage(idx)"
                  class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 flex-shrink-0 p-1 rounded-lg transition-colors"
                  title="Limpiar selección"
                >
                  <i class="ti ti-x text-2xl"></i>
                </button>
              </div>

              <transition name="dropdown">
                <div v-if="isPackageOpen(line)"
                     class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
                  <div class="max-h-56 overflow-y-auto">
                    <button
                      v-for="(pkg, pIdx) in filteredPackagesFor(line)"
                      :key="pkg.id"
                      type="button"
                      @mousedown.prevent="selectPackageFromList(idx, line, pkg.id)"
                      @mouseover="packageHighlightedIdx = pIdx"
                      class="w-full px-3 py-2.5 text-base flex items-center gap-2 text-gray-800 dark:text-gray-200"
                      :class="pIdx === packageHighlightedIdx ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                    >
                      <i v-if="pkg.id === line.package_id" class="ti ti-check text-blue-600 dark:text-blue-400 text-base flex-shrink-0"></i>
                      <span v-else class="w-4 flex-shrink-0"></span>
                      <span class="flex-1 text-left truncate">{{ packageLabel(pkg) }}</span>
                      <span class="text-gray-500 dark:text-gray-400 text-sm whitespace-nowrap">${{ Number(pkg.total_price).toFixed(2) }}</span>
                    </button>
                    <p v-if="filteredPackagesFor(line).length === 0" class="text-sm text-gray-400 dark:text-gray-500 text-center py-3">Sin coincidencias</p>
                  </div>
                </div>
              </transition>
            </div>
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
  name: 'PackageLinesEditor',
  props: {
    lines: { type: Array, required: true },
    packages: { type: Array, default: () => [] },
    schools: { type: Array, default: () => [] },
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
      return this.packages.find(p => p.id === id) || null;
    },
    schoolById(id) {
      return this.schools.find(s => Number(s.id) === Number(id)) || null;
    },
    packageLabel(pkg) {
      return pkg.name || ('Paquete ' + pkg.package_number);
    },
    packagePrice(id) {
      return Number(this.packageById(id)?.total_price || 0);
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
      return line.school_id ? (this.schoolById(line.school_id)?.name || '') : '';
    },
    packageInputValueFor(line) {
      const key = this.lineKey(line);
      if (this.openPackageDropdownKey === key) return this.packageSearch;
      return line.package_id ? this.packageLabel(this.packageById(line.package_id) || {}) : '';
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
      return list.filter(p => this.normalize(this.packageLabel(p)).startsWith(q));
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