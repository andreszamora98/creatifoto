<template>
  <section class="space-y-6">
    <h4 v-if="sectionTitle" class="text-base font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 flex items-center gap-2">
      <span v-if="sectionBadge !== null" class="w-8 h-8 rounded-full bg-blue-500 text-white text-sm flex items-center justify-center font-bold">{{ sectionBadge }}</span>
      {{ sectionTitle }}
    </h4>

    <div class="space-y-3">
      <h5 class="text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
        Información del paquete
      </h5>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div>
          <label class="form-label text-lg font-medium block mb-2 text-gray-700 dark:text-gray-300">
            Escuela <span class="text-red-500">*</span>
          </label>
          <div class="flex gap-2">
            <div class="flex-1 min-w-0">
              <SchoolSelect
                :model-value="form.school_id"
                :schools="schools"
                :disabled="loadingCatalogs"
                :invalid="showErrors && fieldErrors.school"
                @update:modelValue="onSchoolSelect"
              />
            </div>
            <button
              type="button"
              @click="$emit('new-school')"
              title="Nueva escuela"
              aria-label="Nueva escuela"
              class="flex-shrink-0 px-4 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
            >
              <i class="ti ti-plus text-xl"></i>
            </button>
          </div>
          <p v-if="showErrors && fieldErrors.school" class="text-base text-red-600 dark:text-red-400 mt-1 font-medium">
            Campo obligatorio*
          </p>
        </div>

        <div>
          <label class="form-label text-lg font-medium block mb-2 text-gray-700 dark:text-gray-300">
            Número de paquete <span class="text-red-500">*</span>
          </label>
          <input
            type="number"
            min="1"
            step="1"
            :value="form.package_number ?? ''"
            @input="onNumberInput"
            v-blur-on-enter
            enterkeyhint="next"
            placeholder="Ej. 1"
            :class="fieldClass((showErrors && fieldErrors.package_number) || fieldErrors.package_number_duplicate)"
          />
          <p v-if="showErrors && fieldErrors.package_number" class="text-base text-red-600 dark:text-red-400 mt-1 font-medium">
            Campo obligatorio*
          </p>
          <p v-else-if="fieldErrors.package_number_duplicate" class="text-base text-red-600 dark:text-red-400 mt-1 font-medium">
            <i class="ti ti-alert-triangle mr-1"></i>Ya existe el paquete {{ pad2(form.package_number) }} en esta escuela.
          </p>
          <p v-if="nextPackageNumber && Number(form.package_number) !== nextPackageNumber" class="text-sm text-gray-400 dark:text-gray-500 mt-1">
            Siguiente disponible: {{ pad2(nextPackageNumber) }}.
            <button type="button" @click="applyNextNumber" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">Usar</button>
          </p>
        </div>

        <div>
          <label class="form-label text-lg font-medium block mb-2 text-gray-700 dark:text-gray-300">
            Nombre (opcional)
          </label>
          <input
            type="text"
            :value="form.name"
            @input="emitFormUpdate('name', $event.target.value)"
            v-blur-on-enter
            enterkeyhint="done"
            maxlength="255"
            placeholder="Ej. Paquete Premium"
            :class="fieldClass(false)"
          />
        </div>
      </div>
    </div>

    <div class="space-y-3">
      <h5 class="text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
        Características
      </h5>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <NotesField
          label="Características mujeres"
          :model-value="form.women_features_text"
          :max-words="1000"
          :rows="5"
          @update:modelValue="emitFormUpdate('women_features_text', $event)"
        />
        <NotesField
          label="Características hombres"
          :model-value="form.men_features_text"
          :max-words="1000"
          :rows="5"
          @update:modelValue="emitFormUpdate('men_features_text', $event)"
        />
      </div>
    </div>
  </section>
</template>

<script>
import NotesField from '../../order-quote-shared/NotesField.vue';
import SchoolSelect from './SchoolSelect.vue';

const FIELD_BASE =
  'form-control form-control-sm w-full text-lg p-4 rounded-lg border bg-gray-50 dark:bg-gray-700 ' +
  'text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent ' +
  'disabled:opacity-60 disabled:cursor-not-allowed';

export default {
  name: 'PackageInfoStep',
  components: { NotesField, SchoolSelect },
  props: {
    sectionBadge: { type: [Number, String], default: 1 },
    sectionTitle: { type: String, default: 'Información del paquete' },
    form: { type: Object, required: true },
    schools: { type: Array, default: () => [] },
    loadingCatalogs: { type: Boolean, default: false },
    showErrors: { type: Boolean, default: false },
    fieldErrors: { type: Object, required: true },
    nextPackageNumber: { type: Number, default: null }
  },
  emits: ['update:form', 'new-school'],
  methods: {
    pad2(n) {
      return String(n ?? '').padStart(2, '0');
    },
    fieldClass(invalid, extra = '') {
      return [
        FIELD_BASE,
        extra,
        invalid ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'
      ];
    },
    emitFormUpdate(key, value) {
      this.$emit('update:form', { ...this.form, [key]: value });
    },
    onSchoolSelect(id) {
      this.emitFormUpdate('school_id', id ? Number(id) : null);
    },
    onNumberInput(event) {
      const raw = event.target.value;
      const n = raw === '' ? null : Math.floor(Number(raw));
      this.emitFormUpdate('package_number', Number.isFinite(n) ? n : null);
    },
    applyNextNumber() {
      this.emitFormUpdate('package_number', this.nextPackageNumber);
    }
  }
};
</script>