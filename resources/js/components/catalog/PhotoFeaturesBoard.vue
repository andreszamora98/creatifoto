<template>
  <div class="h-full flex flex-col overflow-hidden">
    <div class="flex-shrink-0 mb-4 sm:mb-6 short:mb-2">
      <div class="flex items-start justify-between gap-3 flex-wrap">
        <div class="min-w-0">
          <h1 class="text-lg sm:text-2xl short:text-base font-bold text-gray-800 dark:text-gray-200 truncate">
            <i class="ti ti-adjustments-horizontal mr-2"></i>Características fotográficas
          </h1>
          <p class="text-xs sm:text-sm short:text-xs text-gray-500 dark:text-gray-400 mt-1">
            Administra las opciones disponibles para configurar los tipos de fotografía.
          </p>
        </div>

        <router-link
          to="/catalogos"
          class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex-shrink-0"
        >
          <i class="ti ti-arrow-left text-base"></i>
          <span class="hidden sm:inline">Volver a Catálogos</span>
          <span class="sm:hidden">Volver</span>
        </router-link>
      </div>
    </div>

    <div class="flex-shrink-0 mb-4 sm:mb-6">
      <div class="inline-flex bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="activeTab = tab.key"
          class="px-3 sm:px-4 py-2 short:py-1 rounded-md text-xs sm:text-sm short:text-xs font-medium transition-colors whitespace-nowrap"
          :class="activeTab === tab.key
            ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow'
            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
        >
          <i :class="tab.icon" class="mr-1.5"></i>{{ tab.label }}
        </button>
      </div>
    </div>

    <div class="flex-1 overflow-y-auto min-h-0">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div v-if="!loading && currentItems.length > 0" class="p-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3 flex-wrap">
          <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
            <span>{{ currentItems.length }} {{ currentItems.length === 1 ? itemLabelSingular : itemLabelPlural }}</span>
          </div>
          <button
            v-if="activeTab !== 'formats'"
            @click="openCreate"
            class="px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm bg-blue-500 hover:bg-blue-600 text-white flex items-center gap-2 transition-colors flex-shrink-0"
          >
            <i class="ti ti-plus text-base"></i>
            <span>Nuevo {{ itemLabelSingular }}</span>
          </button>
        </div>

        <div v-if="loading" class="text-center py-10">
          <i class="ti ti-refresh animate-spin text-xl text-blue-500"></i>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Cargando...</p>
        </div>

        <div v-else-if="currentItems.length === 0" class="text-center py-10 text-gray-500 dark:text-gray-400">
          <i class="ti ti-inbox text-2xl mb-2 block"></i>
          {{ emptyMessage }}
        </div>

        <template v-else>
          <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
            <div
              v-for="item in currentItems"
              :key="item.id ?? item.key"
              class="p-3"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-200 flex items-center gap-2 break-words">
                    <i :class="[currentTabIcon, 'text-blue-500 flex-shrink-0']"></i>
                    {{ item.name }}
                  </h3>
                  <div class="mt-2">
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full" :class="statusClasses(item)">
                      <i :class="item.is_active ? 'ti ti-circle-check' : 'ti ti-circle-x'" class="text-xs"></i>
                      {{ item.is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                  </div>
                </div>

                <div v-if="activeTab !== 'formats'" class="flex flex-col items-end gap-1 flex-shrink-0">
                  <button
                    type="button"
                    @click="openEdit(item)"
                    :aria-label="`Editar ${item.name}`"
                    class="text-blue-500 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 p-2 rounded-lg transition-colors"
                  >
                    <i class="ti ti-pencil text-lg"></i>
                  </button>
                  <button
                    type="button"
                    @click="askToggle(item)"
                    :aria-label="item.is_active ? 'Desactivar' : 'Reactivar'"
                    class="p-2 rounded-lg transition-colors"
                    :class="item.is_active
                      ? 'text-amber-500 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/40'
                      : 'text-green-500 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/40'"
                  >
                    <i :class="item.is_active ? 'ti ti-toggle-right' : 'ti ti-toggle-left'" class="text-lg"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
              <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                  <th class="px-3 py-2 w-12"></th>
                  <th class="px-3 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                    Nombre
                  </th>
                  <th class="px-3 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                    Estado
                  </th>
                  <th v-if="activeTab !== 'formats'" class="px-3 py-2 w-24"></th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="item in currentItems" :key="item.id ?? item.key">
                  <td class="px-3 py-2 whitespace-nowrap">
                    <i :class="[currentTabIcon, 'text-blue-500 text-lg']"></i>
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">
                    {{ item.name }}
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap">
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full" :class="statusClasses(item)">
                      <i :class="item.is_active ? 'ti ti-circle-check' : 'ti ti-circle-x'" class="text-xs"></i>
                      {{ item.is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                  </td>
                  <td v-if="activeTab !== 'formats'" class="px-3 py-2 whitespace-nowrap">
                    <div class="flex items-center gap-1">
                      <button
                        type="button"
                        @click="openEdit(item)"
                        class="text-blue-500 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 p-1.5 rounded-lg transition-colors"
                        aria-label="Editar"
                      >
                        <i class="ti ti-pencil text-xl"></i>
                      </button>
                      <button
                        type="button"
                        @click="askToggle(item)"
                        class="p-1.5 rounded-lg transition-colors"
                        :class="item.is_active
                          ? 'text-amber-500 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/40'
                          : 'text-green-500 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/40'"
                        :aria-label="item.is_active ? 'Desactivar' : 'Reactivar'"
                      >
                        <i :class="item.is_active ? 'ti ti-toggle-right' : 'ti ti-toggle-left'" class="text-xl"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>
      </div>
    </div>

    <transition name="fade">
      <div v-if="showModal" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-3 sm:p-6 short:p-0">
        <div class="bg-white dark:bg-gray-800 shadow-xl w-full max-w-md h-auto max-h-[92dvh] short:h-full short:max-h-full rounded-lg short:rounded-none p-4 sm:p-6 short:p-3 flex flex-col overflow-hidden">
          <div class="flex justify-between items-center mb-3 sm:mb-5 short:mb-2 gap-2 flex-shrink-0">
            <div class="flex items-center gap-2 sm:gap-3 short:gap-2 min-w-0">
              <i :class="[currentTabIcon, 'text-lg sm:text-2xl short:text-base text-blue-500 dark:text-blue-400 shrink-0']"></i>
              <h3 class="text-base sm:text-xl short:text-sm font-bold text-gray-800 dark:text-gray-200 truncate">
                {{ editingItem ? 'Editar' : 'Nuevo' }} {{ itemLabelSingular }}
              </h3>
            </div>
            <button
              @click="showModal = false"
              aria-label="Cerrar"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1.5 sm:p-2 short:p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex-shrink-0"
            >
              <i class="ti ti-x text-lg sm:text-xl short:text-base"></i>
            </button>
          </div>

          <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain space-y-3 sm:space-y-4 short:space-y-2">
            <div>
              <label class="text-sm sm:text-base short:text-xs font-medium block mb-1.5 sm:mb-2 short:mb-1 text-gray-700 dark:text-gray-300">
                Nombre <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                v-blur-on-enter
                enterkeyhint="done"
                autocomplete="off"
                maxlength="255"
                :placeholder="namePlaceholder"
                class="form-control text-sm sm:text-base short:text-sm p-2.5 sm:p-3 short:p-2 rounded-lg border w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                :class="showErrors && !form.name.trim() ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
              >
              <p v-if="showErrors && !form.name.trim()" class="text-xs sm:text-sm short:text-xs text-red-600 dark:text-red-400 mt-1 font-medium">
                Campo obligatorio*
              </p>
            </div>

            <div v-if="editingItem">
              <label class="text-sm sm:text-base short:text-xs font-medium block mb-1.5 sm:mb-2 short:mb-1 text-gray-700 dark:text-gray-300">
                Estado
              </label>
              <select
                v-model="form.is_active"
                class="form-control text-sm sm:text-base short:text-sm p-2.5 sm:p-3 short:p-2 rounded-lg border border-gray-300 dark:border-gray-600 w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
              >
                <option :value="true">Activo</option>
                <option :value="false">Inactivo</option>
              </select>
            </div>
          </div>

          <div class="flex gap-2 sm:gap-3 short:gap-2 mt-4 sm:mt-6 short:mt-3 flex-shrink-0"
               style="padding-bottom: max(0px, env(safe-area-inset-bottom));">
            <button
              @mousedown.prevent
              @click="showModal = false"
              class="flex-1 py-2 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            >
              Cancelar
            </button>
            <button
              @mousedown.prevent
              @click="save"
              :disabled="submitting || !canSubmit"
              class="flex-1 py-2 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-sm rounded-lg bg-blue-500 hover:bg-blue-600 text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ submitting ? 'Guardando...' : (editingItem ? 'Actualizar' : 'Agregar') }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <ConfirmModal
      :show="showToggleConfirm"
      :title="itemToToggle?.is_active ? `¿Desactivar ${itemLabelSingular}?` : `¿Reactivar ${itemLabelSingular}?`"
      :message="toggleConfirmMessage"
      :confirm-text="itemToToggle?.is_active ? 'Sí, desactivar' : 'Sí, reactivar'"
      cancel-text="Cancelar"
      @confirm="confirmToggle"
      @cancel="showToggleConfirm = false"
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
          <button @click="showNotification = false" class="ml-2 flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" aria-label="Cerrar notificación">
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
import ConfirmModal from '../common/ConfirmModal.vue';

export default {
  name: 'PhotoFeaturesBoard',
  components: { ConfirmModal },
  data() {
    return {
      tabs: [
        { key: 'formats', label: 'Formatos', icon: 'ti ti-color-swatch' },
        { key: 'paper-types', label: 'Tipos de papel', icon: 'ti ti-file' },
        { key: 'backgrounds', label: 'Fondos', icon: 'ti ti-background' }
      ],
      formatItems: [
        { key: 'color', name: 'Color', is_active: true },
        { key: 'bn', name: 'B/N', is_active: true }
      ],
      paperTypeItems: [],
      backgroundItems: [],
      loading: true,

      showModal: false,
      editingItem: null,
      form: { name: '', is_active: true },
      submitting: false,
      showErrors: false,

      showToggleConfirm: false,
      itemToToggle: null,

      showNotification: false,
      notificationTitle: '',
      notificationMessage: '',
      notificationType: 'info',
      notificationTimeout: null
    };
  },
  computed: {
    activeTab: {
      get() {
        const tab = this.$route.query.tab;
        return this.tabs.some(t => t.key === tab) ? tab : 'formats';
      },
      set(key) {
        if (key === this.activeTab) return;
        this.$router.replace({ query: { ...this.$route.query, tab: key } });
      }
    },
    currentItems() {
      if (this.activeTab === 'formats') return this.formatItems;
      if (this.activeTab === 'paper-types') return this.paperTypeItems;
      return this.backgroundItems;
    },
    currentTabIcon() {
      return this.tabs.find(t => t.key === this.activeTab)?.icon || 'ti ti-circle';
    },
    itemLabelSingular() {
      if (this.activeTab === 'formats') return 'formato';
      if (this.activeTab === 'paper-types') return 'tipo de papel';
      return 'fondo';
    },
    itemLabelPlural() {
      if (this.activeTab === 'formats') return 'formatos';
      if (this.activeTab === 'paper-types') return 'tipos de papel';
      return 'fondos';
    },
    emptyMessage() {
      if (this.activeTab === 'formats') return 'No hay formatos configurados.';
      if (this.activeTab === 'paper-types') return 'No hay tipos de papel registrados.';
      return 'No hay fondos registrados.';
    },
    namePlaceholder() {
      if (this.activeTab === 'paper-types') return 'Ej. Mate';
      if (this.activeTab === 'backgrounds') return 'Ej. Blanco';
      return 'Nombre';
    },
    apiBase() {
      if (this.activeTab === 'paper-types') return '/paper-types';
      if (this.activeTab === 'backgrounds') return '/backgrounds';
      return null;
    },
    canSubmit() {
      return !!this.form.name.trim();
    },
    toggleConfirmMessage() {
      const name = this.itemToToggle?.name || '';
      return this.itemToToggle?.is_active
        ? `¿Confirmas que deseas desactivar '${name}'? Dejará de aparecer en la selección de nuevos registros, pero los registros históricos conservarán el valor.`
        : `¿Confirmas que deseas reactivar '${name}'? Volverá a estar disponible para nuevos registros.`;
    }
  },
  mounted() {
    setPageTitle('Características fotográficas');
    this.loadAll();
  },
  beforeUnmount() {
    if (this.notificationTimeout) clearTimeout(this.notificationTimeout);
  },
  methods: {
    async loadAll() {
      this.loading = true;
      try {
        const [paperRes, bgRes] = await Promise.all([
          axios.get('/paper-types', { params: { include_inactive: 1 } }),
          axios.get('/backgrounds', { params: { include_inactive: 1 } })
        ]);
        this.paperTypeItems = paperRes.data;
        this.backgroundItems = bgRes.data;
      } catch (error) {
        this.notify('Error', 'No se pudieron cargar las características fotográficas', 'error');
      } finally {
        this.loading = false;
      }
    },
    openCreate() {
      if (this.activeTab === 'formats') return;
      this.editingItem = null;
      this.form = { name: '', is_active: true };
      this.showErrors = false;
      this.showModal = true;
    },
    openEdit(item) {
      if (this.activeTab === 'formats') return;
      this.editingItem = item;
      this.form = { name: item.name, is_active: !!item.is_active };
      this.showErrors = false;
      this.showModal = true;
    },
    async save() {
      this.showErrors = true;
      if (!this.canSubmit || this.submitting || !this.apiBase) return;
      this.submitting = true;
      try {
        const payload = { name: this.form.name.trim() };
        if (this.editingItem) {
          payload.is_active = !!this.form.is_active;
          const res = await axios.put(`${this.apiBase}/${this.editingItem.id}`, payload);
          const saved = res.data.paper_type || res.data.background;
          this.replaceItem(saved);
        } else {
          const res = await axios.post(this.apiBase, payload);
          const created = res.data.paper_type || res.data.background;
          this.appendItem(created);
        }
        this.showModal = false;
        this.notify('Guardado', `El ${this.itemLabelSingular} se guardó correctamente`, 'success');
      } catch (error) {
        const data = error.response?.data;
        const firstValidationError = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
        this.notify('Error', firstValidationError || data?.message || 'No se pudo guardar', 'error');
      } finally {
        this.submitting = false;
      }
    },
    askToggle(item) {
      if (this.activeTab === 'formats') return;
      this.itemToToggle = item;
      this.showToggleConfirm = true;
    },
    async confirmToggle() {
      const item = this.itemToToggle;
      this.showToggleConfirm = false;
      if (!item || !this.apiBase) return;
      try {
        const res = await axios.put(`${this.apiBase}/${item.id}`, {
          name: item.name,
          is_active: !item.is_active
        });
        const saved = res.data.paper_type || res.data.background;
        this.replaceItem(saved);
        this.notify(
          'Actualizado',
          !item.is_active ? `El ${this.itemLabelSingular} se reactivó` : `El ${this.itemLabelSingular} se desactivó`,
          'success'
        );
      } catch (error) {
        const data = error.response?.data;
        const firstValidationError = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
        this.notify('Error', firstValidationError || data?.message || 'No se pudo actualizar', 'error');
      } finally {
        this.itemToToggle = null;
      }
    },
    replaceItem(saved) {
      const list = this.activeTab === 'paper-types' ? this.paperTypeItems : this.backgroundItems;
      const idx = list.findIndex(i => i.id === saved.id);
      if (idx !== -1) list.splice(idx, 1, saved);
    },
    appendItem(created) {
      const list = this.activeTab === 'paper-types' ? this.paperTypeItems : this.backgroundItems;
      list.push(created);
      list.sort((a, b) => (a.name || '').localeCompare(b.name || '', 'es', { sensitivity: 'base' }));
    },
    statusClasses(item) {
      return item.is_active
        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
        : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
    },
    notify(title, message, type = 'info') {
      this.notificationTitle = title;
      this.notificationMessage = message;
      this.notificationType = type;
      this.showNotification = true;
      if (this.notificationTimeout) clearTimeout(this.notificationTimeout);
      this.notificationTimeout = setTimeout(() => { this.showNotification = false; }, 3000);
    }
  }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.3s ease; }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateX(20px); }
</style>