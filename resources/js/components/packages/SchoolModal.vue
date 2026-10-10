<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-[60] p-3 sm:p-6 short:p-0">
      <div class="bg-white dark:bg-gray-800 shadow-xl w-full max-w-lg h-auto max-h-[92dvh] short:h-full short:max-h-full rounded-lg short:rounded-none p-4 sm:p-7 short:p-3 flex flex-col overflow-hidden" @click.stop>
        <div class="flex justify-between items-center mb-3 sm:mb-5 short:mb-2 gap-2 flex-shrink-0">
          <div class="flex items-center gap-2 sm:gap-3 short:gap-2 min-w-0">
            <i class="ti ti-school text-lg sm:text-2xl short:text-base text-blue-500 dark:text-blue-400 shrink-0"></i>
            <h3 class="text-base sm:text-2xl short:text-sm font-bold text-gray-800 dark:text-gray-200 truncate">{{ editing ? 'Editar escuela' : 'Nueva escuela' }}</h3>
          </div>
          <div class="flex items-center gap-0.5 sm:gap-1 shrink-0">
            <button
              v-if="editing"
              type="button"
              @click="requestDelete"
              class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 p-1.5 sm:p-2 short:p-1 rounded-lg transition-colors"
              title="Eliminar escuela"
              aria-label="Eliminar escuela"
            >
              <i class="ti ti-trash text-base sm:text-xl short:text-base"></i>
            </button>
            <button
              @click="$emit('close')"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1.5 sm:p-2 short:p-1 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
              aria-label="Cerrar"
            >
              <i class="ti ti-x text-lg sm:text-2xl short:text-base"></i>
            </button>
          </div>
        </div>

        <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain">
          <label class="text-sm sm:text-base short:text-xs font-medium block mb-1.5 sm:mb-2 short:mb-1 text-gray-700 dark:text-gray-300">Nombre <span class="text-red-500">*</span></label>
          <div class="relative">
            <input
              ref="nameInput"
              :value="name"
              @input="syncName"
              @compositionupdate="syncName"
              @compositionend="syncName"
              @change="syncName"
              @keyup="syncName"
              type="text"
              v-blur-on-enter
              enterkeyhint="done"
              autocomplete="off"
              autocorrect="off"
              autocapitalize="words"
              spellcheck="false"
              placeholder="Ej. Colegio Bilingüe del Valle"
              class="form-control text-sm sm:text-base short:text-sm p-2.5 sm:p-3 short:p-2 pr-10 sm:pr-11 short:pr-9 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 w-full"
            >
            <button
              v-if="name"
              type="button"
              @mousedown.prevent="clearName"
              aria-label="Limpiar nombre"
              class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 short:p-0.5 rounded-lg transition-colors"
            >
              <i class="ti ti-eraser text-base sm:text-xl short:text-base"></i>
            </button>
          </div>
          <p v-if="showError" class="text-xs sm:text-sm short:text-xs text-red-600 dark:text-red-400 mt-1 font-medium">Campo obligatorio*</p>
        </div>

        <div class="flex gap-2 sm:gap-3 short:gap-2 mt-4 sm:mt-7 short:mt-3 flex-shrink-0"
             style="padding-bottom: max(0px, env(safe-area-inset-bottom));">
          <button
            @mousedown.prevent
            @click="$emit('close')"
            class="flex-1 py-2 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
          >
            Cancelar
          </button>
          <button
            @mousedown.prevent
            @click="submit"
            :disabled="submitting || !hasChanges"
            class="flex-1 py-2 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-sm rounded-lg bg-blue-500 hover:bg-blue-600 text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
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
    syncName() {
      const el = this.$refs.nameInput;
      if (el && el.value !== this.name) this.name = el.value;
    },
    clearName() {
      this.name = '';
      const el = this.$refs.nameInput;
      if (el) el.value = '';
    },
    async submit() {
      this.syncName();
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