<template>
  <div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
      <div class="relative max-w-sm w-full">
        <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Buscar tipo de fotografía..."
          class="w-full pl-9 pr-10 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
        >
        <button
          v-if="searchQuery"
          type="button"
          @click="clearSearch"
          aria-label="Borrar búsqueda"
          class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full flex items-center justify-center bg-gray-300 hover:bg-gray-400 text-gray-700 dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-gray-100 transition-colors"
        >
          <i class="ti ti-x text-sm"></i>
        </button>
      </div>
      <button @click="openCreate" class="px-4.5 py-2.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm flex items-center gap-2 transition-colors flex-shrink-0">
        <i class="ti ti-plus text-base"></i>
        <span>Nuevo tipo de fotografía</span>
      </button>
    </div>

    <CatalogTable
      :items="filteredItems"
      :loading="loading"
      price-key="base_price"
      price-label="Precio base"
      :show-base-quantity="true"
      base-quantity-key="base_quantity"
      base-quantity-label="Cantidad de fotografías"
      :empty-message="searchQuery ? 'No se encontraron resultados.' : 'No hay tipos de fotografía registrados.'"
      @edit="openEdit"
      @delete="requestDelete"
    />

    <CatalogItemModal
      :show="showModal"
      :editing="!!editingItem"
      :title="editingItem ? 'Editar tipo de fotografía' : 'Nuevo tipo de fotografía'"
      icon="ti ti-camera"
      name-label="Nombre del tipo"
      name-placeholder="Ej. Credencial"
      price-label="Precio base"
      :submitting="submitting"
      :initial-name="editingItem?.name || ''"
      :initial-price="editingItem ? Number(editingItem.base_price) : null"
      show-base-quantity
      base-quantity-label="Cantidad base de fotografías"
      :initial-base-quantity="editingItem ? Number(editingItem.base_quantity) : null"
      @close="showModal = false"
      @save="save"
    />

    <ConfirmModal
      :show="showConfirmDelete"
      title="¿Eliminar tipo de fotografía?"
      :message="`¿Confirmas que deseas eliminar '${itemToDelete?.name}'?`"
      confirm-text="Sí, eliminar"
      cancel-text="Cancelar"
      @confirm="confirmDelete"
      @cancel="showConfirmDelete = false"
    />
  </div>
</template>

<script>
import axios from 'axios';
import CatalogTable from './CatalogTable.vue';
import CatalogItemModal from './CatalogItemModal.vue';
import ConfirmModal from '../common/ConfirmModal.vue';

export default {
  name: 'PhotoTypesPanel',
  components: { CatalogTable, CatalogItemModal, ConfirmModal },
  emits: ['notify'],
  data() {
    return {
      items: [],
      loading: false,
      searchQuery: '',
      showModal: false,
      editingItem: null,
      submitting: false,
      showConfirmDelete: false,
      itemToDelete: null
    };
  },
  computed: {
    filteredItems() {
      if (!this.searchQuery.trim()) return this.items;
      const q = this.searchQuery.toLowerCase().trim();
      return this.items.filter(i => i.name.toLowerCase().includes(q));
    }
  },
  mounted() {
    this.loadItems();
  },
  methods: {
    clearSearch() {
      this.searchQuery = '';
    },
    async loadItems() {
      this.loading = true;
      try {
        const res = await axios.get('/photo-types');
        this.items = res.data;
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: 'No se pudieron cargar los tipos de fotografía', type: 'error' });
      } finally {
        this.loading = false;
      }
    },
    openCreate() {
      this.editingItem = null;
      this.showModal = true;
    },
    openEdit(item) {
      this.editingItem = item;
      this.showModal = true;
    },
    async save({ name, price, baseQuantity }) {
      this.submitting = true;
      try {
        const payload = { name, base_price: price, base_quantity: baseQuantity };
        if (this.editingItem) {
          const res = await axios.put(`/photo-types/${this.editingItem.id}`, payload);
          const idx = this.items.findIndex(i => i.id === this.editingItem.id);
          if (idx !== -1) this.items.splice(idx, 1, res.data);
        } else {
          const res = await axios.post('/photo-types', payload);
          this.items.push(res.data);
        }
        this.showModal = false;
        this.$emit('notify', { title: 'Guardado', message: 'El tipo de fotografía se guardó correctamente', type: 'success' });
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: error.response?.data?.message || 'No se pudo guardar', type: 'error' });
      } finally {
        this.submitting = false;
      }
    },
    requestDelete(item) {
      this.itemToDelete = item;
      this.showConfirmDelete = true;
    },
    async confirmDelete() {
      this.showConfirmDelete = false;
      try {
        await axios.delete(`/photo-types/${this.itemToDelete.id}`);
        this.items = this.items.filter(i => i.id !== this.itemToDelete.id);
        this.$emit('notify', { title: 'Eliminado', message: 'El tipo de fotografía se eliminó correctamente', type: 'success' });
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: error.response?.data?.message || 'No se pudo eliminar', type: 'error' });
      } finally {
        this.itemToDelete = null;
      }
    }
  }
};
</script>