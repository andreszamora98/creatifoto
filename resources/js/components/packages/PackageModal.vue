<template>
  <Teleport to="body">
    <transition name="fade">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-0 sm:p-4 bg-black/40 backdrop-blur-[1px] modal-overlay"
      >
        <div class="modal-zoom w-full h-full sm:h-auto sm:max-w-4xl">
          <div class="bg-white dark:bg-gray-800 sm:rounded-lg shadow-xl w-full h-full sm:h-auto sm:max-h-[85vh] overflow-y-auto isolate">

            <!-- Header -->
            <div class="relative sm:flex sm:items-center sm:justify-between sm:gap-3 p-3 sm:p-5 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800 z-30">
              <div class="min-w-0 flex-1 pr-10 sm:pr-0">
                <h3 class="text-lg sm:text-2xl font-semibold text-gray-800 dark:text-gray-200 truncate">
                  <i class="ti ti-package mr-2"></i>{{ modalTitle }}
                </h3>
                <p v-if="modalSubtitle" class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-0.5 sm:mt-1 truncate">
                  {{ modalSubtitle }}
                </p>
              </div>

              <div v-if="canEdit || editing" class="mt-3 sm:mt-0 flex items-center justify-center sm:justify-end gap-2 flex-shrink-0">
                <button
                  v-if="canEdit && !editing"
                  @click="startEditing"
                  :disabled="loading"
                  class="px-3 sm:px-5 py-2 sm:py-3 text-sm sm:text-base font-medium text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 rounded-lg flex items-center gap-1.5 sm:gap-2 transition-colors disabled:opacity-50"
                  title="Editar paquete"
                >
                  <i class="ti ti-pencil text-base sm:text-lg"></i>
                  Editar
                </button>

                <template v-if="editing">
                  <button
                    @click="cancelEditing"
                    type="button"
                    class="px-3 sm:px-5 py-2 sm:py-3 text-sm sm:text-base font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                  >
                    Cancelar
                  </button>
                  <button
                    @click="submit"
                    type="button"
                    :disabled="submitting || loading || !!nameDuplicatedMessage"
                    class="px-3 sm:px-5 py-2 sm:py-3 text-sm sm:text-base font-medium text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 rounded-lg flex items-center gap-1.5 sm:gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <i v-if="submitting" class="ti ti-refresh animate-spin text-base sm:text-lg"></i>
                    <i v-else class="ti ti-device-floppy text-base sm:text-lg"></i>
                    {{ submitting ? 'Guardando...' : 'Guardar' }}
                  </button>
                </template>
              </div>

              <button
                @click="$emit('close')"
                aria-label="Cerrar"
                class="absolute top-3 right-3 sm:static sm:flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2 sm:p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200 transition-colors"
              >
                <i class="ti ti-x text-xl sm:text-2xl"></i>
              </button>
            </div>

            <!-- Cargando -->
            <div v-if="loading" class="text-center py-10 sm:py-14">
              <i class="ti ti-refresh animate-spin text-2xl text-blue-500"></i>
            </div>

            <div v-else class="p-3 sm:p-5 space-y-4 sm:space-y-5">

              <!-- ============ MODO VISTA ============ -->
              <template v-if="!editing">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                  <div class="rounded-lg p-3 sm:p-5 bg-gray-50 dark:bg-gray-700/40 border-2 border-gray-300 dark:border-gray-600 shadow-sm" style="border-left-width: 4px;">
                    <h4 class="font-semibold mb-2 sm:mb-3 text-base sm:text-lg flex items-center text-gray-700 dark:text-gray-300">
                      <i class="ti ti-info-circle mr-2 text-lg sm:text-xl"></i>Información del paquete
                    </h4>
                    <div class="space-y-1 sm:space-y-2 text-sm sm:text-base">
                      <div v-for="row in infoRows" :key="row.label" class="flex justify-between items-center gap-2 py-1.5 sm:py-2">
                        <span class="font-medium text-gray-700 dark:text-gray-300 flex-shrink-0">{{ row.label }}:</span>
                        <span class="font-bold text-gray-700 dark:text-gray-300 text-right ml-2 min-w-0 break-words">{{ row.value }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="rounded-lg p-3 sm:p-5 bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-300 dark:border-blue-600" style="border-left-width: 4px;">
                    <h4 class="font-semibold mb-2 sm:mb-3 text-base sm:text-lg flex items-center text-blue-800 dark:text-blue-300">
                      <i class="ti ti-school mr-2 text-lg sm:text-xl"></i>Escuela
                    </h4>
                    <div class="space-y-1 sm:space-y-2 text-sm sm:text-base">
                      <div v-for="row in schoolRows" :key="row.label" class="flex justify-between items-center gap-2 py-1.5 sm:py-2">
                        <span class="font-medium text-blue-800 dark:text-blue-300 flex-shrink-0">{{ row.label }}:</span>
                        <span class="font-bold text-blue-800 dark:text-blue-300 text-right ml-2 min-w-0 break-words">{{ row.value }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Características -->
                <div class="rounded-lg p-3 sm:p-5 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 shadow-sm">
                  <h4 class="font-semibold text-gray-800 dark:text-gray-100 text-base sm:text-lg flex items-center mb-2 sm:mb-3">
                    <i class="ti ti-list-details mr-2 text-lg sm:text-xl"></i>Características
                  </h4>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                    <div v-for="tab in featureTabs" :key="tab.key" class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                      <p class="text-sm sm:text-base font-semibold text-gray-600 dark:text-gray-400 mb-1.5 flex items-center">
                        <i :class="['ti', tab.icon, 'mr-1.5 text-lg']"></i>{{ tab.label }}
                      </p>
                      <p v-if="featureText(tab.key)" class="text-gray-700 dark:text-gray-300 text-sm sm:text-base whitespace-pre-wrap break-words">{{ featureText(tab.key) }}</p>
                      <p v-else class="text-gray-400 dark:text-gray-500 text-sm sm:text-base italic">Sin características.</p>
                    </div>
                  </div>
                </div>

                <!-- Tipos de fotografía -->
                <div class="rounded-lg p-3 sm:p-5 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 shadow-sm">
                  <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-base sm:text-lg flex items-center mb-2 sm:mb-3">
                    <i class="ti ti-camera mr-2 text-lg sm:text-xl"></i>Tipos de fotografía
                    <span class="ml-2 text-sm sm:text-base px-2.5 sm:px-3 py-0.5 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-300 rounded">{{ viewPhotoLines.length }}</span>
                  </h4>
                  <div v-if="viewPhotoLines.length" class="space-y-2">
                    <div
                      v-for="line in viewPhotoLines"
                      :key="line.key"
                      class="flex items-center justify-between gap-2 p-3 sm:p-4 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 shadow-sm"
                    >
                      <div class="flex items-center min-w-0 flex-1">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center mr-2.5 sm:mr-3 flex-shrink-0 bg-blue-100 dark:bg-blue-900/40">
                          <i class="ti ti-camera text-base sm:text-xl text-blue-600 dark:text-blue-300"></i>
                        </div>
                        <div class="truncate">
                          <p class="font-medium text-gray-800 dark:text-gray-300 text-sm sm:text-base truncate">{{ line.name }}</p>
                          <p class="text-xs sm:text-base text-gray-500 dark:text-gray-400 truncate">
                            {{ line.format }}<span v-if="line.paper"> • {{ line.paper }}</span>
                          </p>
                        </div>
                      </div>
                      <span class="font-bold text-gray-800 dark:text-gray-200 text-base sm:text-lg ml-2 whitespace-nowrap">x{{ line.quantity }}</span>
                    </div>
                  </div>
                  <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm sm:text-base">
                    <i class="ti ti-camera-off text-xl sm:text-2xl mb-2 block"></i>
                    Sin tipos de fotografía.
                  </div>
                </div>

                <!-- Servicios adicionales -->
                <div class="rounded-lg p-3 sm:p-5 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 shadow-sm">
                  <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-base sm:text-lg flex items-center mb-2 sm:mb-3">
                    <i class="ti ti-tool mr-2 text-lg sm:text-xl"></i>Servicios adicionales
                    <span class="ml-2 text-sm sm:text-base px-2.5 sm:px-3 py-0.5 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-300 rounded">{{ viewServices.length }}</span>
                  </h4>
                  <div v-if="viewServices.length" class="space-y-2">
                    <div
                      v-for="svc in viewServices"
                      :key="svc.key"
                      class="flex items-center justify-between gap-2 p-3 sm:p-4 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 shadow-sm"
                    >
                      <div class="flex items-center min-w-0 flex-1">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center mr-2.5 sm:mr-3 flex-shrink-0 bg-gray-100 dark:bg-gray-700">
                          <i class="ti ti-tool text-base sm:text-xl text-gray-600 dark:text-gray-300"></i>
                        </div>
                        <p class="font-medium text-gray-800 dark:text-gray-300 text-sm sm:text-base truncate">{{ svc.name }}</p>
                      </div>
                      <span class="font-bold text-gray-800 dark:text-gray-200 text-base sm:text-lg ml-2 whitespace-nowrap">x{{ svc.quantity }}</span>
                    </div>
                  </div>
                  <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm sm:text-base">
                    Sin servicios adicionales.
                  </div>
                </div>
              </template>

              <!-- ============ MODO EDICIÓN ============ -->
              <template v-else>
                <div class="rounded-lg p-3 sm:p-5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 shadow-sm space-y-3 sm:space-y-6">
                  <PackageInfoStep
                    section-title=""
                    :form="form"
                    :schools="schools"
                    :loading-catalogs="loading"
                    :show-errors="showErrors || !!nameDuplicatedMessage"
                    :field-errors="infoFieldErrors"
                    :next-package-number="null"
                    @update:form="form = $event"
                    @new-school="showSchoolModal = true"
                  />

                  <div class="space-y-2 sm:space-y-3">
                    <h5 class="text-xs sm:text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                      Precio del paquete
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-5">
                      <div>
                        <label class="form-label text-sm sm:text-lg font-medium block mb-1.5 sm:mb-2 text-gray-700 dark:text-gray-300">
                          Precio total <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                          <span class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm sm:text-lg">$</span>
                          <input
                            v-model.number="form.total_price"
                            v-blur-on-enter
                            enterkeyhint="done"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                            :class="[fieldClass(showErrors && priceMissing), 'pl-7 sm:pl-9']"
                          >
                        </div>
                        <p v-if="showErrors && priceMissing" class="text-xs sm:text-base text-red-600 dark:text-red-400 mt-1 font-medium">
                          Campo obligatorio*
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <PhotoTypeLinesEditor
                  :lines="photoLines"
                  :photo-types="photoTypes"
                  :paper-types="paperTypes"
                  :loading="loading"
                  :show-errors="showErrors"
                  :error="photoLines.length === 0"
                  error-message="Agrega al menos un tipo de fotografía."
                  title="Tipos de fotografía incluidos"
                  icon="ti ti-camera"
                  empty-hint="Agrega al menos un tipo de fotografía."
                />

                <AdditionalServiceLinesEditor
                  :lines="serviceLines"
                  :services="additionalServices"
                  :loading="loading"
                  :show-errors="showErrors"
                  :error="servicePricesInvalid"
                  title="Servicios adicionales"
                  empty-hint="Sin servicios adicionales agregados."
                />

                <div class="space-y-2 sm:space-y-3">
                  <h5 class="text-xs sm:text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Características
                  </h5>
                  <div>
                    <div class="relative z-10 flex w-full lg:w-fit rounded-t-lg bg-gray-200 dark:bg-gray-800" role="tablist">
                      <button
                        v-for="(tab, index) in featureTabs"
                        :key="tab.key"
                        type="button"
                        role="tab"
                        :aria-selected="featureTab === tab.key"
                        @click="featureTab = tab.key"
                        class="relative flex-1 min-w-0 lg:flex-initial flex justify-center items-center gap-2 sm:gap-2.5 px-3.5 sm:px-5 py-2.5 sm:py-3 text-base sm:text-xl font-medium rounded-t-lg"
                        :class="featureTabClasses(tab, index)"
                      >
                        <i :class="['ti', tab.icon, 'text-xl sm:text-2xl flex-shrink-0']"></i>
                        <span class="min-w-0 text-center">{{ tab.label }}</span>
                      </button>
                    </div>

                    <div class="relative bg-gray-50 dark:bg-gray-700 rounded-b-lg lg:rounded-tr-lg p-2 sm:p-3 transition-none">
                      <div
                        v-show="featureTab === 'men'"
                        class="absolute top-0 left-0 w-2 h-2 bg-gray-200 dark:bg-gray-800 pointer-events-none"
                        aria-hidden="true"
                      >
                        <div class="w-full h-full rounded-tl-lg bg-gray-50 dark:bg-gray-700"></div>
                      </div>

                      <div v-show="featureTab === 'women'" role="tabpanel">
                        <textarea
                          :value="form.women_features_text"
                          @input="updateFeature('women_features_text', $event.target.value)"
                          rows="4"
                          placeholder="Describe las características incluidas para este grupo..."
                          class="w-full text-sm sm:text-base p-2.5 sm:p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 resize-y min-h-[5rem] sm:min-h-[7rem]"
                        ></textarea>
                      </div>
                      <div v-show="featureTab === 'men'" role="tabpanel">
                        <textarea
                          :value="form.men_features_text"
                          @input="updateFeature('men_features_text', $event.target.value)"
                          rows="4"
                          placeholder="Describe las características incluidas para este grupo..."
                          class="w-full text-sm sm:text-base p-2.5 sm:p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 resize-y min-h-[5rem] sm:min-h-[7rem]"
                        ></textarea>
                      </div>
                    </div>
                  </div>
                </div>
              </template>

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
import PackageInfoStep from './form-steps/PackageInfoStep.vue';
import PhotoTypeLinesEditor from '../order-quote-shared/photo-lines/PhotoTypeLinesEditor.vue';
import AdditionalServiceLinesEditor from '../order-quote-shared/AdditionalServiceLinesEditor.vue';

const FIELD_BASE =
  'form-control form-control-sm w-full text-sm sm:text-lg p-2.5 sm:p-4 rounded-lg border bg-gray-50 dark:bg-gray-700 ' +
  'text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent';

const pad = (n, len = 2) => String(n ?? '').padStart(len, '0');

export default {
  name: 'PackageModal',
  components: {
    AlertModal, SchoolModal, PackageInfoStep, PhotoTypeLinesEditor, AdditionalServiceLinesEditor
  },
  props: {
    show: { type: Boolean, default: false },
    packageId: { type: Number, default: null },
    // Solo lectura: nunca muestra el botón "Editar".
    readonly: { type: Boolean, default: false }
  },
  emits: ['close', 'saved', 'notify'],
  data() {
    return {
      editing: false,
      loading: false,
      submitting: false,
      showErrors: false,
      showAlertModal: false,
      showSchoolModal: false,
      alertMessage: '',
      backendNameError: '',
      featureTab: 'women',
      packageNumber: null,
      createdAt: null,
      schools: [],
      photoTypes: [],
      paperTypes: [],
      additionalServices: [],
      existingPackages: [],
      photoLines: [],
      serviceLines: [],
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
    featureTabs() {
      return [
        { key: 'women', label: 'Mujeres', icon: 'ti-woman' },
        { key: 'men', label: 'Hombres', icon: 'ti-man' }
      ];
    },
    canEdit() {
      return !this.readonly && !!this.packageId;
    },
    packageCode() {
      return this.packageId ? 'PAQ-' + pad(this.packageId, 3) : '';
    },
    schoolName() {
      return this.schools.find(s => Number(s.id) === Number(this.form.school_id))?.name || '';
    },
    modalTitle() {
      if (!this.packageId) return 'Nuevo paquete';
      return `Paquete: ${this.packageCode}`;
    },
    modalSubtitle() {
      if (!this.packageId) return 'Los campos marcados con * son obligatorios';
      if (this.editing) return 'Editando paquete';
      return this.createdAt ? `Creado el ${this.formatDate(this.createdAt)}` : this.schoolName;
    },
    infoRows() {
      const rows = [
        { label: 'Folio', value: this.packageCode },
        { label: 'Nombre', value: this.form.name || '—' },
        { label: 'Precio total', value: this.formatCurrency(this.form.total_price) }
      ];
      if (this.createdAt) rows.push({ label: 'Fecha de creación', value: this.formatDate(this.createdAt) });
      return rows;
    },
    schoolRows() {
      const rows = [{ label: 'Nombre', value: this.schoolName || 'Sin escuela' }];
      if (this.packageNumber) rows.push({ label: 'Paquete de la escuela', value: pad(this.packageNumber) });
      return rows;
    },
    viewPhotoLines() {
      return this.photoLines
        .filter(l => l.photo_type_id)
        .map((l, idx) => {
          const pt = this.photoTypes.find(p => p.id === l.photo_type_id);
          const paper = l.paper_type_id ? this.paperTypes.find(p => p.id === l.paper_type_id) : null;
          return {
            key: `${l.photo_type_id}-${idx}`,
            name: pt?.name || 'Tipo de foto',
            quantity: l.quantity,
            format: l.format === 'color' ? 'Color' : 'Blanco/negro',
            paper: paper?.name || ''
          };
        });
    },
    viewServices() {
      return this.serviceLines
        .filter(l => l.service_id)
        .map((l, idx) => ({
          key: `${l.service_id}-${idx}`,
          name: this.additionalServices.find(s => s.id === l.service_id)?.name || 'Servicio adicional',
          quantity: l.quantity
        }));
    },
    priceMissing() {
      return this.form.total_price === null
        || this.form.total_price === undefined
        || this.form.total_price === '';
    },
    nameMissing() {
      return !String(this.form.name || '').trim();
    },
    localNameDuplicated() {
      const name = String(this.form.name || '').trim().toLowerCase();
      if (!name || !this.form.school_id) return false;
      return this.existingPackages.some(p =>
        Number(p.school_id) === Number(this.form.school_id)
        && Number(p.id) !== Number(this.packageId)
        && String(p.name || '').trim().toLowerCase() === name
      );
    },
    nameDuplicatedMessage() {
      if (this.backendNameError) return this.backendNameError;
      if (this.localNameDuplicated) return 'Ya existe un paquete con ese nombre en esta escuela.';
      return '';
    },
    infoFieldErrors() {
      return {
        school: !this.form.school_id,
        name: this.nameMissing,
        name_duplicated: !!this.nameDuplicatedMessage
      };
    },
    servicePricesInvalid() {
      return this.serviceLines.some(l =>
        l.service_id && !this.additionalServices.find(s => s.id === l.service_id)
      );
    }
  },
  watch: {
    async show(val) {
      if (val) {
        await this.loadCatalogs();
        if (this.packageId) {
          await this.loadPackage();
          this.editing = false;
        } else {
          this.resetForm();
          this.editing = !this.readonly;
        }
      } else {
        this.resetForm();
      }
    },
    'form.name'() {
      if (this.backendNameError) this.backendNameError = '';
    },
    'form.school_id'() {
      if (this.backendNameError) this.backendNameError = '';
    }
  },
  methods: {
    fieldClass(invalid) {
      return [
        FIELD_BASE,
        invalid ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'
      ];
    },
    featureText(key) {
      const text = key === 'women' ? this.form.women_features_text : this.form.men_features_text;
      return String(text || '').trim();
    },
    featureTabClasses(tab, index) {
      const isActive = this.featureTab === tab.key;
      const divider = index > 0
        ? 'before:absolute before:left-0 before:top-3 before:bottom-3 before:w-px before:bg-gray-300 dark:before:bg-gray-600'
        : '';
      const stateClasses = isActive
        ? 'bg-gray-50 dark:bg-gray-700 text-blue-600 dark:text-blue-400 after:absolute after:inset-x-0 after:-bottom-px after:h-px after:bg-gray-50 dark:after:bg-gray-700'
        : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300';
      return [divider, stateClasses];
    },
    updateFeature(key, value) {
      this.form = { ...this.form, [key]: value };
    },
    startEditing() {
      this.showErrors = false;
      this.featureTab = 'women';
      this.editing = true;
    },
    async cancelEditing() {
      if (!this.packageId) {
        this.$emit('close');
        return;
      }
      this.editing = false;
      this.showErrors = false;
      this.backendNameError = '';
      await this.loadPackage(); // descarta los cambios y recupera lo guardado
    },
    async loadCatalogs() {
      this.loading = true;
      try {
        const [schoolsRes, photoTypesRes, paperTypesRes, packagesRes, servicesRes] = await Promise.all([
          axios.get('/schools'),
          axios.get('/photo-types'),
          axios.get('/paper-types'),
          axios.get('/packages'),
          axios.get('/additional-services')
        ]);
        this.schools = schoolsRes.data;
        this.photoTypes = photoTypesRes.data;
        this.paperTypes = paperTypesRes.data;
        this.existingPackages = packagesRes.data;
        this.additionalServices = servicesRes.data;
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
        this.packageNumber = pkg.package_number ?? null;
        this.createdAt = pkg.created_at ?? null;
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
        this.serviceLines = (pkg.additional_services || []).map(svc => ({
          service_id: svc.id,
          quantity: svc.pivot?.quantity || 1
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
      this.serviceLines = [];
      this.packageNumber = null;
      this.createdAt = null;
      this.featureTab = 'women';
      this.editing = false;
      this.showErrors = false;
      this.backendNameError = '';
    },
    handleSchoolCreated(school) {
      this.schools.push(school);
      this.form = { ...this.form, school_id: school.id };
      this.showSchoolModal = false;
    },
    async submit() {
      if (this.readonly || this.submitting) return;
      this.showErrors = true;
      const validLines = this.photoLines.filter(l => l.photo_type_id);
      const validServices = this.serviceLines.filter(l => l.service_id);
      if (!this.form.school_id || this.priceMissing || validLines.length === 0 || this.nameMissing || this.nameDuplicatedMessage) {
        if (!this.nameDuplicatedMessage) {
          this.alertMessage = 'Completa todos los campos obligatorios antes de continuar.';
          this.showAlertModal = true;
        }
        return;
      }
      this.submitting = true;
      this.backendNameError = '';
      try {
        const payload = {
          ...this.form,
          name: String(this.form.name || '').trim(),
          photo_types: validLines.map(l => ({
            photo_type_id: l.photo_type_id,
            quantity: l.quantity,
            format: l.format,
            paper_type_id: l.paper_type_id
          })),
          additional_services: validServices.map(l => {
            const svc = this.additionalServices.find(s => s.id === l.service_id);
            const unitPrice = Number(svc?.default_price || 0);
            const quantity = Number(l.quantity) || 1;
            return {
              additional_service_id: l.service_id,
              price: unitPrice,
              quantity,
              subtotal: unitPrice * quantity
            };
          })
        };
        if (this.packageId) {
          await axios.put(`/packages/${this.packageId}`, payload);
          this.editing = false;
        } else {
          await axios.post('/packages', payload);
        }
        this.$emit('saved');
      } catch (error) {
        const data = error.response?.data;
        const nameError = data?.errors?.name?.[0];
        if (nameError) {
          this.backendNameError = nameError;
        } else {
          this.alertMessage = data?.message || 'No se pudo guardar el paquete';
          this.showAlertModal = true;
        }
      } finally {
        this.submitting = false;
      }
    },
    formatCurrency(value) {
      const num = Number(value || 0);
      return '$' + num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
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