<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-3">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-5">
          <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">
              <i class="ti ti-photo mr-2"></i>Tipos de fotografía
            </h3>
            <button @click="close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <i class="ti ti-x text-xl"></i>
            </button>
          </div>

          <!-- Formulario para añadir/editar -->
          <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-5">
            <div class="flex flex-col md:flex-row gap-3">
              <input
                v-model="form.name"
                type="text"
                placeholder="Nombre del tipo (ej: Credencial)"
                class="flex-1 form-control form-control-sm text-sm p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">$</span>
                <input
                  v-model.number="form.base_price"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="Precio base"
                  class="w-32 form-control form-control-sm text-sm p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
              <button
                @click="save"
                class="bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              >
                <i class="ti ti-check mr-1"></i>
                {{ editingId ? 'Actualizar' : 'Agregar' }}
              </button>
              <button
                v-if="editingId"
                @click="cancelEdit"
                class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              >
                Cancelar
              </button>
            </div>
          </div>

          <!-- Lista de tipos existentes -->
          <div class="space-y-2">
            <div v-for="pt in photoTypes" :key="pt.id"
                 class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
              <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ pt.name }}</span>
                <span class="text-sm text-gray-500 dark:text-gray-400">${{ Number(pt.base_price).toFixed(2) }}</span>
              </div>
              <div class="flex items-center gap-2">
                <button @click="edit(pt)" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                  <i class="ti ti-edit text-sm"></i>
                </button>
                <button @click="deleteItem(pt.id)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                  <i class="ti ti-trash text-sm"></i>
                </button>
              </div>
            </div>
            <div v-if="photoTypes.length === 0" class="text-center text-sm text-gray-500 dark:text-gray-400 py-4">
              No hay tipos de fotografía registrados.
            </div>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import axios from 'axios';

export default {
  name: 'PhotoTypeManager',
  props: {
    show: { type: Boolean, default: false }
  },
  emits: ['close', 'updated'],
  data() {
    return {
      loading: false,
      photoTypes: [],
      form: { name: '', base_price: null },
      editingId: null,
    };
  },
  watch: {
    show(val) {
      if (val) this.loadTypes();
    }
  },
  methods: {
    async loadTypes() {
      this.loading = true;
      try {
        const res = await axios.get('/photo-types');
        this.photoTypes = res.data;
      } catch (error) {
        console.error('Error cargando tipos:', error);
      } finally {
        this.loading = false;
      }
    },
    async save() {
      if (!this.form.name.trim() || this.form.base_price === null || this.form.base_price < 0) {
        alert('Por favor completa todos los campos correctamente.');
        return;
      }
      try {
        const payload = { name: this.form.name.trim(), base_price: this.form.base_price };
        let res;
        if (this.editingId) {
          res = await axios.put(`/photo-types/${this.editingId}`, payload);
          const index = this.photoTypes.findIndex(p => p.id === this.editingId);
          if (index !== -1) this.photoTypes.splice(index, 1, res.data);
        } else {
          res = await axios.post('/photo-types', payload);
          this.photoTypes.push(res.data);
        }
        this.$emit('updated');
        this.resetForm();
      } catch (error) {
        console.error('Error guardando tipo:', error);
        alert('No se pudo guardar el tipo de fotografía.');
      }
    },
    edit(pt) {
      this.editingId = pt.id;
      this.form.name = pt.name;
      this.form.base_price = pt.base_price;
    },
    cancelEdit() {
      this.resetForm();
    },
    resetForm() {
      this.editingId = null;
      this.form = { name: '', base_price: null };
    },
    async deleteItem(id) {
      if (!confirm('¿Eliminar este tipo de fotografía?')) return;
      try {
        await axios.delete(`/photo-types/${id}`);
        this.photoTypes = this.photoTypes.filter(p => p.id !== id);
        this.$emit('updated');
      } catch (error) {
        console.error('Error eliminando tipo:', error);
        alert('No se pudo eliminar el tipo.');
      }
    },
    close() {
      this.resetForm();
      this.$emit('close');
    }
  }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>