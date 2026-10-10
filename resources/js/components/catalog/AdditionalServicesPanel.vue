<template>
  <div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
      <div class="relative max-w-sm w-full">
        <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Buscar servicio adicional..."
          class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
        >
      </div>
      <button @click="openCreate" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm flex items-center gap-2 transition-colors flex-shrink-0">
        <i class="ti ti-plus text-base"></i>
        <span>Nuevo servicio adicional</span>
      </button>
    </div>

    <CatalogTable
      :items="filteredItems"
      :loading="loading"
      price-key="default_price"
      price-label="Precio por defecto"
      show-base-quantity
      base-quantity-key="default_quantity"
      base-quantity-label="Cantidad por defecto"
      item-label-singular="servicio"
      item-label-plural="servicios"
      :empty-message="searchQuery ? 'No se encontraron resultados.' : 'No hay servicios adicionales registrados.'"
      @edit="openEdit"
      @delete="requestDelete"
    />

    <CatalogItemModal
      :show="showModal"
      :editing="!!editingItem"
      :title="editingItem ? 'Editar servicio adicional' : 'Nuevo servicio adicional'"
      icon="ti ti-tool"
      name-label="Nombre del servicio"
      name-placeholder="Ej. Retoque digital"
      price-label="Precio por defecto"
      show-base-quantity
      base-quantity-label="Cantidad por defecto"
      :submitting="submitting"
      :initial-name="editingItem?.name || ''"
      :initial-price="editingItem ? Number(editingItem.default_price) : null"
      :initial-base-quantity="editingItem ? Number(editingItem.default_quantity) : null"
      @close="showModal = false"
      @save="save"
    />

    <ConfirmModal
      :show="showConfirmDelete"
      title="¿Eliminar servicio adicional?"
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
  name: 'AdditionalServicesPanel',
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
    async loadItems() {
      this.loading = true;
      try {
        const res = await axios.get('/additional-services');
        this.items = res.data;
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: 'No se pudieron cargar los servicios adicionales', type: 'error' });
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
        const payload = { name, default_price: price, default_quantity: baseQuantity };
        if (this.editingItem) {
          const res = await axios.put(`/additional-services/${this.editingItem.id}`, payload);
          const idx = this.items.findIndex(i => i.id === this.editingItem.id);
          if (idx !== -1) this.items.splice(idx, 1, res.data);
        } else {
          const res = await axios.post('/additional-services', payload);
          this.items.push(res.data);
        }
        this.showModal = false;
        this.$emit('notify', { title: 'Guardado', message: 'El servicio adicional se guardó correctamente', type: 'success' });
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
        await axios.delete(`/additional-services/${this.itemToDelete.id}`);
        this.items = this.items.filter(i => i.id !== this.itemToDelete.id);
        this.$emit('notify', { title: 'Eliminado', message: 'El servicio adicional se eliminó correctamente', type: 'success' });
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: error.response?.data?.message || 'No se pudo eliminar', type: 'error' });
      } finally {
        this.itemToDelete = null;
      }
    }
  }
};
</script>