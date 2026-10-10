<template>
  <div class="h-full flex flex-col overflow-hidden">
    <div class="flex-shrink-0 mb-3 short:mb-1">
      <div class="flex flex-col md:flex-row md:items-center gap-2 short:gap-1">
        <div class="flex-1 w-full md:max-w-sm">
          <div class="relative">
            <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Buscar por escuela, paquete, precio..."
              class="w-full pl-9 pr-3 py-2 short:py-1 text-sm short:text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
          </div>
        </div>

        <div class="grid grid-cols-3 md:flex md:items-center gap-2 short:gap-1">
          <button
            @click="refreshAll"
            :disabled="loading"
            class="justify-center bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-3 py-2 short:py-1 rounded-lg text-sm flex items-center gap-2 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
            aria-label="Actualizar"
          >
            <i class="ti ti-refresh text-base" :class="{ 'animate-spin': loading }"></i>
            <span class="hidden sm:inline short:hidden">Actualizar</span>
          </button>

          <button
            @click="openCreateSchool()"
            class="justify-center bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-3 py-2 short:py-1 rounded-lg text-sm flex items-center gap-2 transition-colors"
          >
            <i class="ti ti-school text-base"></i>
            <span class="hidden sm:inline short:hidden">Nueva escuela</span>
            <span class="sm:hidden short:inline">Escuela</span>
          </button>

          <button
            @click="openCreate()"
            class="justify-center bg-blue-500 dark:bg-blue-600 hover:bg-blue-600 dark:hover:bg-blue-700 text-white px-3 py-2 short:py-1 rounded-lg text-sm flex items-center gap-2 transition-colors"
          >
            <i class="ti ti-plus text-base"></i>
            <span class="hidden sm:inline short:hidden">Nuevo paquete</span>
            <span class="sm:hidden short:inline">Paquete</span>
          </button>
        </div>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow flex-1 flex flex-col overflow-hidden min-h-0">
      <div class="flex-shrink-0 p-2 sm:p-3 short:p-1.5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-2 flex-wrap">
        <span class="text-xs text-gray-500 dark:text-gray-400">
          {{ groupedPackages.length }}
          {{ groupedPackages.length === 1 ? 'escuela' : 'escuelas' }}
          <span class="mx-1 text-gray-300 dark:text-gray-600">·</span>
          {{ filteredPackages.length }}
          {{ filteredPackages.length === 1 ? 'paquete' : 'paquetes' }}
          <span v-if="searchQuery" class="text-gray-400 ml-2 short:hidden">(filtrado)</span>
        </span>
      </div>

      <div class="flex-1 overflow-hidden min-h-0 relative">
        <div v-if="loading && !hasLoaded" class="absolute inset-0 flex items-center justify-center p-4">
          <div class="text-center">
            <i class="ti ti-refresh animate-spin text-xl text-blue-500 mb-2"></i>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Cargando paquetes...</p>
          </div>
        </div>

        <div v-else-if="groupedPackages.length === 0" class="absolute inset-0 flex items-center justify-center p-4">
          <div class="text-center max-w-xs">
            <div class="w-12 h-12 mx-auto mb-3 rounded-full flex items-center justify-center bg-gray-100 dark:bg-gray-700">
              <i class="ti ti-package-off text-lg text-gray-400 dark:text-gray-500"></i>
            </div>
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
              {{ searchQuery ? 'No se encontraron resultados' : 'No hay escuelas ni paquetes registrados' }}
            </h3>
            <p class="text-gray-500 dark:text-gray-400 text-xs">
              {{ searchQuery ? 'Intenta con otros términos de búsqueda.' : 'Crea una escuela o un paquete para comenzar.' }}
            </p>
            <button
              v-if="searchQuery"
              @click="searchQuery = ''"
              class="mt-3 text-xs text-blue-600 dark:text-blue-400 hover:underline"
            >
              Limpiar búsqueda
            </button>
          </div>
        </div>

        <template v-else>
          <div class="lg:hidden h-full overflow-y-auto p-2 sm:p-3 short:p-1.5 space-y-3 short:space-y-2">
            <div
              v-for="group in paginatedGroups"
              :key="group.key"
              class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
            >
              <button
                class="w-full flex items-center justify-between gap-2 p-2 sm:p-3 short:p-1.5 bg-gray-50 dark:bg-gray-700/40 text-left transition-colors"
                :class="group.school?.id ? 'hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200 dark:active:bg-gray-600' : 'cursor-default'"
                @click="group.school?.id && openEditSchool(group.school)"
              >
                <div class="flex items-center gap-2 min-w-0">
                  <span class="flex-shrink-0 w-7 h-7 sm:w-8 sm:h-8 short:w-6 short:h-6 rounded-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 flex items-center justify-center text-xs short:text-[10px] font-bold text-gray-700 dark:text-gray-200">
                    {{ group.schoolNumber }}
                  </span>
                  <span class="text-sm short:text-xs font-bold text-gray-800 dark:text-gray-200 truncate">{{ group.school.name }}</span>
                </div>
                <i v-if="group.school?.id" class="ti ti-pencil text-gray-400 flex-shrink-0"></i>
              </button>

              <div v-if="group.packages.length === 0" class="px-3 py-2 short:py-1 text-xs italic text-gray-400 dark:text-gray-500">
                Sin paquetes registrados
              </div>

              <ul v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                <li
                  v-for="pkg in group.packages"
                  :key="pkg.id"
                  class="flex items-center gap-3 p-2 sm:p-3 short:p-1.5"
                >
                  <button
                    class="flex-1 min-w-0 text-left"
                    @click="openEdit(pkg, false)"
                  >
                    <div class="flex items-center gap-2 mb-0.5">
                      <span class="text-xs short:text-[10px] font-mono text-gray-500 dark:text-gray-400 flex-shrink-0">
                        #{{ String(pkg.package_number).padStart(2, '0') }}
                      </span>
                      <span class="text-sm short:text-xs font-medium text-gray-800 dark:text-gray-200 truncate">
                        {{ pkg.name || ('Paquete ' + pkg.package_number) }}
                      </span>
                    </div>
                    <div class="text-xs short:text-[11px] font-medium text-gray-600 dark:text-gray-400">
                      {{ formatCurrency(pkg.total_price) }}
                    </div>
                  </button>
                  <button
                    type="button"
                    @click.stop="requestDelete(pkg)"
                    aria-label="Eliminar paquete"
                    class="flex-shrink-0 text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 active:bg-red-200 dark:active:bg-red-900/60 p-2 short:p-1.5 rounded-lg transition-colors"
                  >
                    <i class="ti ti-trash text-lg short:text-base"></i>
                  </button>
                </li>
              </ul>
            </div>
          </div>

          <div class="hidden lg:block h-full overflow-auto px-3 pb-3">
            <table class="w-full table-fixed text-sm border-separate border-spacing-0">
              <colgroup>
                <col style="width: 64px;">
                <col style="width: 22%;">
                <col style="width: 84px;">
                <col style="width: 200px;">
                <col style="width: 96px;">
                <col style="width: 56px;">
              </colgroup>

              <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800">
                <tr>
                  <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-normal break-words leading-tight border-b border-b-gray-200 dark:border-b-gray-700 border-r border-r-gray-200 dark:border-r-gray-700">
                    No. escuela
                  </th>
                  <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-normal break-words leading-tight border-b border-b-gray-200 dark:border-b-gray-700 border-r border-r-gray-200 dark:border-r-gray-700">
                    Escuela
                  </th>
                  <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-normal break-words leading-tight border-b border-b-gray-200 dark:border-b-gray-700 border-r border-r-gray-200 dark:border-r-gray-700">
                    No. paquete
                  </th>
                  <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-normal break-words leading-tight border-b border-b-gray-200 dark:border-b-gray-700 border-r border-r-gray-200 dark:border-r-gray-700">
                    Nombre del paquete
                  </th>
                  <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-normal break-words leading-tight border-b border-b-gray-200 dark:border-b-gray-700 border-r border-r-gray-200 dark:border-r-gray-700">
                    Precio
                  </th>
                  <th scope="col" class="px-2 py-2 border-b border-b-gray-200 dark:border-b-gray-700"></th>
                </tr>
              </thead>

              <tbody v-for="group in paginatedGroups" :key="group.key">
                <tr aria-hidden="true">
                  <td colspan="6" class="h-3 p-0"></td>
                </tr>

                <tr v-if="group.packages.length === 0">
                  <td class="w-[64px] px-2 py-2 text-center align-middle whitespace-nowrap text-sm font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700/40 pointer-events-none" :class="schoolCellClasses(true)">
                    {{ group.schoolNumber }}
                  </td>
                  <td class="px-3 py-2 text-center align-middle text-sm font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700/40 cursor-pointer transition-colors hover:bg-gray-200 dark:hover:bg-gray-600 whitespace-normal break-words table-wrap-cell" :class="schoolCellClasses(false)" @click.stop="openEditSchool(group.school)">
                    {{ group.school.name }}
                  </td>
                  <td class="px-2 py-2 text-center text-sm text-gray-400 dark:text-gray-500" :class="packageCellClasses(0, 1, false)"></td>
                  <td class="px-3 py-2 text-sm text-gray-400 dark:text-gray-500 italic" :class="packageCellClasses(0, 1, false)">Sin paquetes registrados</td>
                  <td class="px-3 py-2 text-sm text-gray-400 dark:text-gray-500" :class="packageCellClasses(0, 1, false)"></td>
                  <td class="px-2 py-2" :class="packageCellClasses(0, 1, true)"></td>
                </tr>

                <tr
                  v-else
                  v-for="(pkg, i) in group.packages"
                  :key="pkg.id"
                  class="cursor-pointer"
                  @click="openEdit(pkg, false)"
                >
                  <template v-if="i === 0">
                    <td :rowspan="group.packages.length" class="w-[64px] px-2 py-2 text-center align-middle whitespace-nowrap text-sm font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700/40 pointer-events-none" :class="schoolCellClasses(true)">
                      {{ group.schoolNumber }}
                    </td>
                    <td :rowspan="group.packages.length" class="px-3 py-2 text-center align-middle text-sm font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700/40 cursor-pointer transition-colors hover:bg-gray-200 dark:hover:bg-gray-600 whitespace-normal break-words table-wrap-cell" :class="schoolCellClasses(false)" @click.stop="openEditSchool(group.school)">
                      {{ group.school.name }}
                    </td>
                  </template>

                  <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400 text-center transition-colors" :class="[packageCellClasses(i, group.packages.length, false), { 'bg-gray-200 dark:bg-gray-600': hoveredPackageId === pkg.id }]" @mouseenter="onPackageMouseEnter(pkg.id)" @mouseleave="onPackageMouseLeave(pkg.id)">
                    {{ String(pkg.package_number).padStart(2, '0') }}
                  </td>
                  <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300 transition-colors whitespace-normal break-words table-wrap-cell" :class="[packageCellClasses(i, group.packages.length, false), { 'bg-gray-200 dark:bg-gray-600': hoveredPackageId === pkg.id }]" @mouseenter="onPackageMouseEnter(pkg.id)" @mouseleave="onPackageMouseLeave(pkg.id)">
                    {{ pkg.name || ('Paquete ' + pkg.package_number) }}
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400 transition-colors" :class="[packageCellClasses(i, group.packages.length, false), { 'bg-gray-200 dark:bg-gray-600': hoveredPackageId === pkg.id }]" @mouseenter="onPackageMouseEnter(pkg.id)" @mouseleave="onPackageMouseLeave(pkg.id)">
                    {{ formatCurrency(pkg.total_price) }}
                  </td>
                  <td class="px-2 py-2 text-center transition-colors" :class="[packageCellClasses(i, group.packages.length, true), { 'bg-gray-200 dark:bg-gray-600': hoveredPackageId === pkg.id }]" @mouseenter="onPackageMouseEnter(pkg.id)" @mouseleave="onPackageMouseLeave(pkg.id)">
                    <button type="button" @click.stop="requestDelete(pkg)" class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 p-1.5 rounded-lg transition-colors" aria-label="Eliminar paquete">
                      <i class="ti ti-trash text-xl"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>
      </div>

      <div v-if="totalPages > 0" class="flex-shrink-0 mt-2 sm:mt-3 short:mt-1 pb-2 sm:pb-3 short:pb-1 pt-2 sm:pt-3 short:pt-1 border-t border-gray-200 dark:border-gray-700 px-3">
        <ResponsivePagination v-model:current="currentPage" :total="totalPages" />
      </div>

      <div v-if="totalPages > 0" class="flex-shrink-0 hidden sm:flex short:hidden flex-wrap items-center justify-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400 pb-2">
        <span>
          {{ groupedPackages.length === 1 ? 'Escuela' : 'Escuelas' }}
          <template v-if="groupedPackages.length > 1">
            {{ rangeStart }}–{{ rangeEnd }} de {{ groupedPackages.length }}
          </template>
          <template v-else>
            1 de 1
          </template>
        </span>
        <span v-if="totalPages > 1">
          Página {{ currentPage }} de {{ totalPages }}
        </span>
      </div>
    </div>

    <PackageWizardModal
      :show="showWizard"
      @close="showWizard = false"
      @created="handlePackageCreated"
      @school-created="handleSchoolCreated"
      @notify="handleNotify"
    />

    <PackageModal
      :show="showModal"
      :package-id="selectedPackageId"
      :readonly="modalReadonly"
      @close="showModal = false"
      @saved="handleSaved"
      @notify="handleNotify"
    />

    <SchoolModal
      :show="showSchoolModal"
      :school="selectedSchool"
      @close="showSchoolModal = false"
      @created="handleSchoolSaved"
      @updated="handleSchoolSaved"
      @deleted="handleSchoolDeleted"
      @notify="handleNotify"
    />

    <ConfirmModal
      :show="showConfirmDelete"
      title="¿Eliminar paquete?"
      :message="deleteMessage"
      confirm-text="Sí, eliminar"
      cancel-text="Cancelar"
      @confirm="confirmDelete"
      @cancel="showConfirmDelete = false"
    />

    <transition name="fade-slide">
      <div v-if="showNotification" class="fixed top-3 right-3 left-3 sm:left-auto z-50 max-w-xs sm:w-full mx-auto sm:mx-0">
        <div class="flex items-center p-3 rounded-lg shadow-lg"
          :class="{
            'bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800': notificationType === 'success',
            'bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800': notificationType === 'error',
            'bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800': notificationType === 'info'
          }">
          <div class="flex-1 min-w-0">
            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-0.5 text-xs">{{ notificationTitle }}</h4>
            <p class="text-xs text-gray-600 dark:text-gray-400">{{ notificationMessage }}</p>
          </div>
          <button @click="showNotification = false" aria-label="Cerrar notificación" class="ml-2 flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <i class="ti ti-x text-sm"></i>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from 'axios';
import { setPageTitle } from '../../store';
import PackageModal from './PackageModal.vue';
import PackageWizardModal from './PackageWizardModal.vue';
import SchoolModal from './SchoolModal.vue';
import ConfirmModal from '../common/ConfirmModal.vue';
import ResponsivePagination from '../common/ResponsivePagination.vue';

const currencyFormatter = new Intl.NumberFormat('es-MX', {
  style: 'currency',
  currency: 'MXN'
});

const normalize = (s) =>
  String(s ?? '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase();

const pad2 = (n) => String(n ?? '').padStart(2, '0');

const FRAME_T = 'border-t border-t-gray-300 dark:border-t-gray-600';
const FRAME_B = 'border-b border-b-gray-300 dark:border-b-gray-600';
const FRAME_L = 'border-l border-l-gray-300 dark:border-l-gray-600';
const FRAME_R = 'border-r border-r-gray-300 dark:border-r-gray-600';
const DIVIDER_T = 'border-t border-t-gray-100 dark:border-t-gray-700';
const COL_DIV = 'border-r border-r-gray-200 dark:border-r-gray-700';

export default {
  name: 'PackagesBoard',
  components: { PackageModal, PackageWizardModal, SchoolModal, ConfirmModal, ResponsivePagination },

  data() {
    return {
      packages: [],
      schools: [],
      loading: false,
      hasLoaded: false,

      showWizard: false,

      showModal: false,
      selectedPackageId: null,
      modalReadonly: false,

      showSchoolModal: false,
      selectedSchool: null,

      showConfirmDelete: false,
      packageToDelete: null,

      showNotification: false,
      notificationTitle: '',
      notificationMessage: '',
      notificationType: 'info',
      notificationTimeout: null,

      searchQuery: '',
      currentPage: 1,
      schoolsPerPage: 10,

      hoveredPackageId: null
    };
  },

  computed: {
    filteredPackages() {
      const q = normalize(this.searchQuery).trim();

      if (!q) return this.packages;

      return this.packages.filter(pkg => {
        const haystack = normalize([
          pkg.school?.name,
          pkg.school_id,
          pad2(pkg.school_id),
          pkg.package_number,
          pad2(pkg.package_number),
          pkg.name || `Paquete ${pkg.package_number}`,
          pkg.total_price,
          this.formatCurrency(pkg.total_price)
        ].join(' '));

        return haystack.includes(q);
      });
    },

    schoolIdsWithPackages() {
      return new Set(this.packages.map(p => p.school_id).filter(id => id !== null && id !== undefined));
    },

    emptySchools() {
      const q = normalize(this.searchQuery).trim();

      return this.schools
        .filter(s => !this.schoolIdsWithPackages.has(s.id))
        .filter(s => {
          if (!q) return true;
          return normalize(s.name).includes(q) || normalize(pad2(s.id)).includes(q);
        });
    },

    groupedPackages() {
      const groups = new Map();

      for (const pkg of this.filteredPackages) {
        const schoolId = pkg.school_id ?? null;
        const key = schoolId === null ? 'no-school' : String(schoolId);

        if (!groups.has(key)) {
          groups.set(key, {
            key,
            schoolId,
            school: pkg.school || { id: null, name: 'Sin escuela' },
            packages: []
          });
        }

        groups.get(key).packages.push(pkg);
      }

      for (const school of this.emptySchools) {
        const key = String(school.id);

        if (!groups.has(key)) {
          groups.set(key, {
            key,
            schoolId: school.id,
            school,
            packages: []
          });
        }
      }

      const arr = Array.from(groups.values());

      arr.sort((a, b) => {
        if (a.schoolId === null) return 1;
        if (b.schoolId === null) return -1;
        return (a.schoolId || 0) - (b.schoolId || 0);
      });

      for (const g of arr) {
        g.packages.sort(
          (a, b) => (a.package_number || 0) - (b.package_number || 0)
        );

        g.schoolNumber = g.schoolId === null ? '—' : pad2(g.school?.sequence);
      }

      return arr;
    },

    paginatedGroups() {
      const start = (this.currentPage - 1) * this.schoolsPerPage;
      return this.groupedPackages.slice(start, start + this.schoolsPerPage);
    },

    totalPages() {
      return Math.ceil(this.groupedPackages.length / this.schoolsPerPage);
    },

    rangeStart() {
      return (this.currentPage - 1) * this.schoolsPerPage + 1;
    },

    rangeEnd() {
      return Math.min(
        this.currentPage * this.schoolsPerPage,
        this.groupedPackages.length
      );
    },

    deleteMessage() {
      const pkg = this.packageToDelete;

      if (!pkg) return '';

      const label = pkg.name
        ? `"${pkg.name}" (#${pkg.package_number})`
        : `#${pkg.package_number}`;

      const school = pkg.school?.name || 'Sin escuela';

      return `¿Confirmas que deseas eliminar el paquete ${label} de ${school}?`;
    }
  },

  watch: {
    groupedPackages() {
      if (this.currentPage > this.totalPages && this.totalPages > 0) {
        this.currentPage = this.totalPages;
      }
    },

    searchQuery() {
      this.currentPage = 1;
    }
  },

  mounted() {
    setPageTitle('Paquetes escolares');
    this.refreshAll();
  },

  beforeUnmount() {
    if (this.notificationTimeout) {
      clearTimeout(this.notificationTimeout);
    }
  },

  methods: {
    async loadPackages() {
      try {
        const res = await axios.get('/packages');
        this.packages = res.data;
      } catch (error) {
        this.handleNotify({ title: 'Error', message: 'No se pudieron cargar los paquetes', type: 'error' });
      }
    },

    async loadSchools() {
      try {
        const res = await axios.get('/schools');
        this.schools = res.data;
      } catch (error) {
        this.handleNotify({ title: 'Error', message: 'No se pudieron cargar las escuelas', type: 'error' });
      }
    },

    async refreshAll() {
      if (this.loading) return;

      this.loading = true;

      try {
        await Promise.all([this.loadPackages(), this.loadSchools()]);
      } finally {
        this.loading = false;
        this.hasLoaded = true;
      }
    },

    openCreate() {
      this.showWizard = true;
    },

    async handlePackageCreated() {
      await this.refreshAll();
    },

    openEdit(pkg, readonly = false) {
      this.selectedPackageId = pkg.id;
      this.modalReadonly = readonly;
      this.showModal = true;
    },

    async handleSaved() {
      this.showModal = false;
      await this.refreshAll();

      this.handleNotify({
        title: 'Guardado',
        message: 'El paquete se guardó correctamente',
        type: 'success'
      });
    },

    openCreateSchool() {
      this.selectedSchool = null;
      this.showSchoolModal = true;
    },

    openEditSchool(school) {
      if (!school || school.id === null) return;
      this.selectedSchool = school;
      this.showSchoolModal = true;
    },

    handleSchoolCreated(school) {
      const idx = this.schools.findIndex(s => s.id === school.id);
      if (idx === -1) {
        this.schools.push(school);
      } else {
        this.schools.splice(idx, 1, school);
      }
    },

    async handleSchoolSaved() {
      this.showSchoolModal = false;
      await this.refreshAll();

      this.handleNotify({
        title: 'Guardado',
        message: 'La escuela se guardó correctamente',
        type: 'success'
      });
    },

    async handleSchoolDeleted() {
      this.showSchoolModal = false;
      await this.refreshAll();

      this.handleNotify({
        title: 'Eliminada',
        message: 'La escuela y sus paquetes se eliminaron correctamente',
        type: 'success'
      });
    },

    requestDelete(pkg) {
      this.packageToDelete = pkg;
      this.showConfirmDelete = true;
    },

    async confirmDelete() {
      this.showConfirmDelete = false;

      try {
        await axios.delete(`/packages/${this.packageToDelete.id}`);
        await this.refreshAll();

        this.handleNotify({
          title: 'Eliminado',
          message: 'El paquete se eliminó correctamente',
          type: 'success'
        });
      } catch (error) {
        this.handleNotify({
          title: 'Error',
          message: error.response?.data?.message || 'No se pudo eliminar el paquete',
          type: 'error'
        });
      } finally {
        this.packageToDelete = null;
      }
    },

    onPackageMouseEnter(id) {
      this.hoveredPackageId = id;
    },

    onPackageMouseLeave(id) {
      if (this.hoveredPackageId === id) {
        this.hoveredPackageId = null;
      }
    },

    schoolCellClasses(isFirstCol) {
      return isFirstCol
        ? [FRAME_T, FRAME_B, FRAME_L, COL_DIV, 'rounded-l-lg']
        : [FRAME_T, FRAME_B, COL_DIV];
    },

    packageCellClasses(i, total, isLastCol) {
      const c = [i === 0 ? FRAME_T : DIVIDER_T];

      if (i === total - 1) {
        c.push(FRAME_B);
      }

      if (!isLastCol) {
        c.push(COL_DIV);
      }

      if (isLastCol) {
        c.push(FRAME_R);

        if (i === 0) {
          c.push('rounded-tr-lg');
        }

        if (i === total - 1) {
          c.push('rounded-br-lg');
        }
      }

      return c;
    },

    formatCurrency(value) {
      return currencyFormatter.format(Number(value || 0));
    },

    handleNotify({ title, message, type }) {
      this.notificationTitle = title;
      this.notificationMessage = message;
      this.notificationType = type;
      this.showNotification = true;

      if (this.notificationTimeout) {
        clearTimeout(this.notificationTimeout);
      }

      this.notificationTimeout = setTimeout(() => {
        this.showNotification = false;
      }, 3000);
    }
  }
};
</script>

<style scoped>
.table-wrap-cell {
  white-space: normal;
  overflow-wrap: anywhere;
  word-break: normal;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(20px);
}
</style>