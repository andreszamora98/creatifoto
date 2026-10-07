<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-[60] p-6">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-lg p-7" @click.stop>
        <div class="flex justify-between items-center mb-5">
          <div class="flex items-center gap-3 min-w-0">
            <i class="ti ti-school text-2xl text-blue-500 dark:text-blue-400 shrink-0"></i>
            <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-200 truncate">{{ editing ? 'Editar escuela' : 'Nueva escuela' }}</h3>
          </div>
          <div class="flex items-center gap-1 shrink-0">
            <button
              v-if="editing"
              type="button"
              @click="requestDelete"
              class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 p-2 rounded-lg transition-colors"
              title="Eliminar escuela"
            >
              <i class="ti ti-trash text-xl"></i>
            </button>
            <button
              @click="$emit('close')"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
            >
              <i class="ti ti-x text-2xl"></i>
            </button>
          </div>
        </div>

        <label class="text-base font-medium block mb-2 text-gray-700 dark:text-gray-300">Nombre <span class="text-red-500">*</span></label>
        <input
          v-model="name"
          type="text"
          v-blur-on-enter
          enterkeyhint="done"
          placeholder="Ej. Colegio Bilingüe del Valle"
          class="form-control text-base p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 w-full"
        >
        <p v-if="showError" class="text-sm text-red-600 dark:text-red-400 mt-1 font-medium">Campo obligatorio*</p>

        <div class="flex gap-3 mt-7">
          <button
            @click="$emit('close')"
            class="flex-1 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
          >
            Cancelar
          </button>
          <button
            @click="submit"
            :disabled="submitting || !hasChanges"
            class="flex-1 py-2.5 rounded-lg bg-blue-500 hover:bg-blue-600 text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            {{ submitting ? 'Guardando...' : (editing ? 'Actualizar' : 'Crear') }}
          </button>
        </div>
      </div>
    </div>
  </transition>

  <ConfirmModal
    :show="showConfirmDelete"
    title="¿Eliminar escuela?"
    :message="`¿Confirmas que deseas eliminar '${school?.name}'? Se eliminarán también todos sus paquetes registrados.`"
    confirm-text="Sí, eliminar"
    cancel-text="Cancelar"
    @confirm="confirmDelete"
    @cancel="showConfirmDelete = false"
  />

  <SuccessModal
    :show="showSuccess"
    title="¡Escuela creada!"
    :message="`La escuela '${createdSchool?.name || ''}' se registró correctamente.`"
    @closed="handleSuccessClosed"
  />
</template>

<script>
import axios from 'axios';
import ConfirmModal from '../common/ConfirmModal.vue';
import SuccessModal from '../common/SuccessModal.vue';

export default {
  name: 'SchoolModal',
  components: { ConfirmModal, SuccessModal },
  props: {
    show: { type: Boolean, default: false },
    school: { type: Object, default: null }
  },
  emits: ['close', 'created', 'updated', 'deleted', 'notify'],
  data() {
    return {
      name: '',
      showError: false,
      submitting: false,
      showConfirmDelete: false,
      showSuccess: false,
      createdSchool: null
    };
  },
  computed: {
    editing() {
      return !!this.school;
    },
    hasChanges() {
      const current = this.name.trim();
      const original = (this.school?.name || '').trim();
      return current.length > 0 && current !== original;
    }
  },
  watch: {
    show(val) {
      if (val) {
        this.name = this.school?.name || '';
        this.showError = false;
        this.showConfirmDelete = false;
        this.showSuccess = false;
        this.createdSchool = null;
      }
    }
  },
  methods: {
    async submit() {
      if (!this.name.trim()) {
        this.showError = true;
        return;
      }
      this.submitting = true;
      try {
        if (this.editing) {
          const res = await axios.put(`/schools/${this.school.id}`, { name: this.name.trim() });
          this.$emit('updated', res.data.school);
        } else {
          const res = await axios.post('/schools', { name: this.name.trim() });
          this.createdSchool = res.data.school;
          this.showSuccess = true;
        }
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: error.response?.data?.message || 'No se pudo guardar la escuela', type: 'error' });
      } finally {
        this.submitting = false;
      }
    },
    handleSuccessClosed() {
      this.showSuccess = false;
      if (this.createdSchool) {
        this.$emit('created', this.createdSchool);
        this.createdSchool = null;
      }
    },
    requestDelete() {
      this.showConfirmDelete = true;
    },
    async confirmDelete() {
      this.showConfirmDelete = false;
      try {
        await axios.delete(`/schools/${this.school.id}`);
        this.$emit('deleted', this.school.id);
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: error.response?.data?.message || 'No se pudo eliminar la escuela', type: 'error' });
      }
    }
  }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>