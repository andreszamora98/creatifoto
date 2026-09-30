<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-[60] p-6" @click="$emit('close')">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md p-6" @click.stop>
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200">Nueva escuela</h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <i class="ti ti-x text-xl"></i>
          </button>
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

        <div class="flex gap-3 mt-6">
          <button @click="$emit('close')" class="flex-1 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Cancelar</button>
          <button @click="submit" :disabled="submitting" class="flex-1 py-2.5 rounded-lg bg-blue-500 hover:bg-blue-600 text-white disabled:opacity-50">
            {{ submitting ? 'Creando...' : 'Crear' }}
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import axios from 'axios';

export default {
  name: 'SchoolModal',
  props: {
    show: { type: Boolean, default: false }
  },
  emits: ['close', 'created', 'notify'],
  data() {
    return { name: '', showError: false, submitting: false };
  },
  watch: {
    show(val) {
      if (val) {
        this.name = '';
        this.showError = false;
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
        const res = await axios.post('/schools', { name: this.name.trim() });
        this.$emit('created', res.data.school);
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: error.response?.data?.message || 'No se pudo crear la escuela', type: 'error' });
      } finally {
        this.submitting = false;
      }
    }
  }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>