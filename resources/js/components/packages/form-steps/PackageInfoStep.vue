<template>
  <section class="space-y-3 sm:space-y-6 short:space-y-2">
    <h4 v-if="sectionTitle" class="text-sm sm:text-base short:text-sm font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 flex items-center gap-2">
      <span v-if="sectionBadge !== null" class="w-6 h-6 sm:w-8 sm:h-8 short:w-6 short:h-6 rounded-full bg-blue-500 text-white text-xs sm:text-sm short:text-xs flex items-center justify-center font-bold">{{ sectionBadge }}</span>
      {{ sectionTitle }}
    </h4>

    <div class="space-y-2 sm:space-y-3 short:space-y-2">
      <h5 class="text-xs sm:text-sm short:text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
        Información del paquete
      </h5>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-5 short:gap-2">
        <div>
          <label class="form-label text-sm sm:text-lg short:text-sm font-medium block mb-1.5 sm:mb-2 short:mb-1 text-gray-700 dark:text-gray-300">
            Escuela <span class="text-red-500">*</span>
          </label>
          <div class="flex gap-2 short:gap-1.5">
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
              class="flex-shrink-0 px-2.5 sm:px-4 short:px-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
            >
              <i class="ti ti-plus text-base sm:text-xl short:text-base"></i>
            </button>
          </div>
          <p v-if="showErrors && fieldErrors.school" class="text-xs sm:text-base short:text-xs text-red-600 dark:text-red-400 mt-1 font-medium">
            Campo obligatorio*
          </p>
          <p v-if="form.school_id && nextPackageNumber" class="text-xs sm:text-sm short:text-xs text-gray-400 dark:text-gray-500 mt-1">
            Este será el paquete <strong>{{ pad2(nextPackageNumber) }}</strong> de la escuela seleccionada.
          </p>
        </div>

        <div>
          <label class="form-label text-sm sm:text-lg short:text-sm font-medium block mb-1.5 sm:mb-2 short:mb-1 text-gray-700 dark:text-gray-300">
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
  </section>
</template>

<script>
import SchoolSelect from './SchoolSelect.vue';

const FIELD_BASE =
  'form-control form-control-sm w-full text-sm sm:text-lg short:text-sm p-2.5 sm:p-4 short:p-2 rounded-lg border bg-gray-50 dark:bg-gray-700 ' +
  'text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent ' +
  'disabled:opacity-60 disabled:cursor-not-allowed';

export default {
  name: 'PackageInfoStep',
  components: { SchoolSelect },
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
    }
  }
};
</script>