<template>
  <Teleport to="body">
    <transition name="fade">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-0 sm:p-4 lg:p-6 short:p-0 bg-black/40 backdrop-blur-[1px] modal-overlay"
      >
        <div class="modal-zoom w-full h-full sm:h-auto sm:max-w-4xl">
          <div class="bg-white dark:bg-gray-800 rounded-lg short:rounded-none shadow-xl w-full h-full sm:h-auto sm:max-h-[85vh] overflow-y-auto isolate">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-3 p-3 sm:p-5 short:p-2 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800 z-30">
              <div class="min-w-0">
                <h3 class="text-base sm:text-2xl short:text-sm font-semibold text-gray-800 dark:text-gray-200 truncate">
                  <i class="ti ti-package mr-1.5 sm:mr-2"></i>{{ modalTitle }}
                </h3>
                <p class="text-xs sm:text-base short:text-xs text-gray-600 dark:text-gray-400 mt-0.5 sm:mt-1 truncate">
                  {{ modalSubtitle }}
                </p>
              </div>
              <div class="flex items-center justify-end gap-2 flex-shrink-0">
                <button
                  @click="$emit('close')"
                  aria-label="Cerrar"
                  class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1.5 sm:p-3 short:p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200 transition-colors"
                >
                  <i class="ti ti-x text-lg sm:text-2xl short:text-base"></i>
                </button>
              </div>
            </div>

            <div class="p-3 sm:p-5 short:p-2">
              <div v-if="loading" class="text-center py-6 sm:py-10 short:py-4">
                <i class="ti ti-refresh animate-spin text-xl sm:text-2xl short:text-lg text-blue-500"></i>
              </div>

              <fieldset v-else :disabled="readonly" class="min-w-0 space-y-3 sm:space-y-5 short:space-y-2">
                <div class="rounded-lg p-3 sm:p-5 short:p-2 bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-300 dark:border-blue-600" style="border-left-width: 4px;">
                  <h4 class="font-semibold mb-2.5 sm:mb-4 short:mb-2 text-base sm:text-lg short:text-sm flex items-center text-blue-800 dark:text-blue-300">
                    <i class="ti ti-info-circle mr-1.5 sm:mr-2 text-base sm:text-xl short:text-base"></i>Información del paquete
                  </h4>

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 short:gap-2">
                    <div>
                      <label class="text-sm sm:text-base short:text-xs font-medium text-blue-800 dark:text-blue-300 mb-1 sm:mb-1.5 short:mb-1 block">
                        Escuela <span class="text-red-500">*</span>
                      </label>
                      <div class="flex gap-2 short:gap-1.5">
                        <select
                          v-model.number="form.school_id"
                          :class="fieldClass(showErrors && !form.school_id)"
                        >
                          <option :value="null">Seleccionar</option>
                          <option v-for="s in schools" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                        <button
                          v-if="!readonly"
                          type="button"
                          @click="showSchoolModal = true"
                          class="flex-shrink-0 px-2.5 sm:px-4 short:px-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 active:bg-gray-100 transition-colors"
                          title="Nueva escuela"
                          aria-label="Nueva escuela"
                        >
                          <i class="ti ti-plus text-base sm:text-xl short:text-base"></i>
                        </button>
                      </div>
                      <p v-if="showErrors && !form.school_id" class="text-xs sm:text-base short:text-xs text-red-600 dark:text-red-400 mt-1 sm:mt-1.5 font-medium">Campo obligatorio*</p>
                    </div>

                    <div>
                      <label class="text-sm sm:text-base short:text-xs font-medium text-blue-800 dark:text-blue-300 mb-1 sm:mb-1.5 short:mb-1 block">Nombre (opcional)</label>
                      <input
                        v-model="form.name"
                        type="text"
                        placeholder="Ej. Paquete Premium"
                        :class="fieldClass(false)"
                      >
                    </div>

                    <div>
                      <label class="text-sm sm:text-base short:text-xs font-medium text-blue-800 dark:text-blue-300 mb-1 sm:mb-1.5 short:mb-1 block">
                        Precio total <span class="text-red-500">*</span>
                      </label>
                      <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm sm:text-base short:text-xs">$</span>
                        <input
                          v-model.number="form.total_price"
                          type="number"
                          min="0"
                          step="0.01"
                          placeholder="0.00"
                          :class="[fieldClass(showErrors && priceMissing), 'pl-8']"
                        >
                      </div>
                      <p v-if="showErrors && priceMissing" class="text-xs sm:text-base short:text-xs text-red-600 dark:text-red-400 mt-1 sm:mt-1.5 font-medium">Campo obligatorio*</p>
                    </div>
                  </div>
                </div>

                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-3 sm:p-5 short:p-2">
                  <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-base sm:text-lg short:text-sm flex items-center mb-2.5 sm:mb-4 short:mb-2">
                    <i class="ti ti-list-details mr-1.5 sm:mr-2 text-base sm:text-xl short:text-base"></i>Características
                  </h4>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 short:gap-2">
                    <NotesField
                      label="Características mujeres"
                      :model-value="form.women_features_text"
                      @update:modelValue="form.women_features_text = $event"
                      :max-words="1000"
                      :rows="6"
                    />
                    <NotesField
                      label="Características hombre"
                      :model-value="form.men_features_text"
                      @update:modelValue="form.men_features_text = $event"
                      :max-words="1000"
                      :rows="6"
                    />
                  </div>
                </div>

                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-3 sm:p-5 short:p-2">
                  <PhotoTypeLinesEditor
                    :lines="photoLines"
                    :photo-types="photoTypes"
                    :paper-types="paperTypes"
                    :loading="loading"
                    :show-errors="showErrors"
                    :error="photoLines.length === 0"
                    title="Tipos de fotografía incluidos"
                    empty-hint="Agrega al menos un tipo de fotografía."
                  />
                </div>
              </fieldset>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 short:gap-2 p-3 sm:p-5 short:p-2 border-t border-gray-200 dark:border-gray-700 sticky bottom-0 bg-white dark:bg-gray-800 z-30"
                 style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
              <button
                @click="$emit('close')"
                class="px-4 sm:px-6 short:px-4 py-2 sm:py-3 short:py-1.5 text-sm sm:text-base short:text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 active:bg-gray-100 transition-colors"
              >
                {{ readonly ? 'Cerrar' : 'Cancelar' }}
              </button>
              <button
                v-if="!readonly"
                @click="submit"
                :disabled="submitting || loading"
                class="px-4 sm:px-6 short:px-4 py-2 sm:py-3 short:py-1.5 text-sm sm:text-base short:text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 rounded-lg flex items-center justify-center gap-2 short:gap-1.5 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <i v-if="submitting" class="ti ti-refresh animate-spin"></i>
                <i v-else class="ti ti-device-floppy text-base sm:text-lg short:text-base"></i>
                <span v-if="submitting">Guardando...</span>
                <span v-else>Guardar</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <SchoolModal
      :show="showSchoolModal"
      @close="showSchoolModal = false"
      @created="handleSchoolCreated"
      @notify="$emit('notify', $event)"
    />

    <AlertModal :show="showAlertModal" title="Atención" :message="alertMessage" @closed="showAlertModal = false" />
  </Teleport>
</template>

<script>
import axios from 'axios';
import AlertModal from '../common/AlertModal.vue';
import SchoolModal from './SchoolModal.vue';
import PhotoTypeLinesEditor from '../order-quote-shared/photo-lines/PhotoTypeLinesEditor.vue';
import NotesField from '../order-quote-shared/NotesField.vue';

const FIELD_BASE =
  'w-full text-sm sm:text-base short:text-sm rounded-lg border bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 py-2 sm:py-3 short:py-1.5 px-2.5 sm:px-3 short:px-2.5 ' +
  'disabled:opacity-70 disabled:cursor-not-allowed';
const FIELD_OK = 'border-gray-300 dark:border-gray-600';
const FIELD_ERROR = 'border-red-400 dark:border-red-500';

export default {
  name: 'PackageModal',
  components: { AlertModal, SchoolModal, PhotoTypeLinesEditor, NotesField },
  props: {
    show: { type: Boolean, default: false },
    packageId: { type: Number, default: null },
    readonly: { type: Boolean, default: false }
  },
  emits: ['close', 'saved', 'notify'],
  data() {
    return {
      loading: false,
      submitting: false,
      showErrors: false,
      showAlertModal: false,
      showSchoolModal: false,
      alertMessage: '',
      schools: [],
      photoTypes: [],
      paperTypes: [],
      photoLines: [],
      form: {
        school_id: null,
        name: '',
        total_price: null,
        women_features_text: '',
        men_features_text: ''
      }
    };
  },
  computed: {
    priceMissing() {
      return this.form.total_price === null
        || this.form.total_price === undefined
        || this.form.total_price === '';
    },
    modalTitle() {
      if (this.readonly) return 'Detalle del paquete';
      return this.packageId ? 'Editar paquete' : 'Nuevo paquete';
    },
    modalSubtitle() {
      if (!this.packageId) return 'Los campos marcados con * son obligatorios';
      const school = this.schools.find(s => s.id === this.form.school_id);
      return school?.name || '';
    }
  },
  watch: {
    async show(val) {
      if (val) {
        await this.loadCatalogs();
        if (this.packageId) {
          await this.loadPackage();
        } else {
          this.resetForm();
        }
      } else {
        this.resetForm();
      }
    }
  },
  methods: {
    fieldClass(invalid) {
      return [FIELD_BASE, invalid ? FIELD_ERROR : FIELD_OK];
    },
    async loadCatalogs() {
      this.loading = true;
      try {
        const [schoolsRes, photoTypesRes, paperTypesRes] = await Promise.all([
          axios.get('/schools'),
          axios.get('/photo-types'),
          axios.get('/paper-types')
        ]);
        this.schools = schoolsRes.data;
        this.photoTypes = photoTypesRes.data;
        this.paperTypes = paperTypesRes.data;
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: 'No se pudieron cargar los catálogos', type: 'error' });
      } finally {
        this.loading = false;
      }
    },
    async loadPackage() {
      this.loading = true;
      try {
        const res = await axios.get(`/packages/${this.packageId}`);
        const pkg = res.data;
        this.form = {
          school_id: pkg.school_id,
          name: pkg.name || '',
          total_price: Number(pkg.total_price),
          women_features_text: pkg.women_features_text || '',
          men_features_text: pkg.men_features_text || ''
        };
        this.photoLines = (pkg.photo_types || []).map(pt => ({
          photo_type_id: pt.id,
          quantity: pt.pivot?.quantity || 1,
          format: pt.pivot?.format || 'bn',
          paper_type_id: pt.pivot?.paper_type_id || null
        }));
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: 'No se pudo cargar el paquete', type: 'error' });
      } finally {
        this.loading = false;
      }
    },
    resetForm() {
      this.form = { school_id: null, name: '', total_price: null, women_features_text: '', men_features_text: '' };
      this.photoLines = [];
      this.showErrors = false;
    },
    handleSchoolCreated(school) {
      this.schools.push(school);
      this.form.school_id = school.id;
      this.showSchoolModal = false;
    },
    async submit() {
      if (this.readonly || this.submitting) return;
      this.showErrors = true;
      const validLines = this.photoLines.filter(l => l.photo_type_id);
      if (!this.form.school_id || this.priceMissing || validLines.length === 0) {
        this.alertMessage = 'Completa todos los campos obligatorios antes de continuar.';
        this.showAlertModal = true;
        return;
      }
      this.submitting = true;
      try {
        const payload = {
          ...this.form,
          photo_types: validLines.map(l => ({
            photo_type_id: l.photo_type_id,
            quantity: l.quantity,
            format: l.format,
            paper_type_id: l.paper_type_id
          }))
        };
        if (this.packageId) {
          await axios.put(`/packages/${this.packageId}`, payload);
        } else {
          await axios.post('/packages', payload);
        }
        this.$emit('saved');
      } catch (error) {
        this.alertMessage = error.response?.data?.message || 'No se pudo guardar el paquete';
        this.showAlertModal = true;
      } finally {
        this.submitting = false;
      }
    }
  }
};
</script>

<style scoped>
.modal-overlay {
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-zoom {
  width: 100%;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 4px;
}

.dark .overflow-y-auto::-webkit-scrollbar-track {
  background: #374151;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb {
  background: #4b5563;
}
</style>