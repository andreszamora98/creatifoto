<template>
  <section>
    <h4 v-if="sectionTitle" class="text-sm sm:text-base short:text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-3 sm:mb-5 short:mb-2 flex items-center gap-2">
      <span v-if="sectionBadge !== null" class="w-6 h-6 sm:w-8 sm:h-8 short:w-5 short:h-5 rounded-full bg-blue-500 text-white text-xs sm:text-sm short:text-[10px] flex items-center justify-center font-bold">{{ sectionBadge }}</span>
      {{ sectionTitle }}
    </h4>

    <div>
      <label class="form-label text-sm sm:text-lg short:text-sm font-medium block mb-1.5 sm:mb-2 short:mb-1 text-gray-700 dark:text-gray-300">Cliente <span class="text-red-500">*</span></label>
      <div class="flex flex-col sm:flex-row sm:items-stretch gap-2 sm:gap-3 short:gap-2">
        <div class="relative flex-1 min-w-0">
          <i class="ti ti-user absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-base sm:text-xl short:text-sm"></i>
          <input
            type="text"
            :value="customerSearch"
            @input="$emit('update:customerSearch', $event.target.value)"
            @focus="openCustomerDropdown"
            @blur="hideCustomerDropdown"
            v-blur-on-enter
            enterkeyhint="next"
            placeholder="Escribe el nombre completo del cliente..."
            class="form-control form-control-sm w-full text-sm sm:text-lg short:text-sm p-2.5 sm:p-4 short:p-2 pl-9 sm:pl-11 short:pl-9 pr-9 sm:pr-10 short:pr-8 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :class="showErrors && fieldErrors.customer
              ? 'border-red-500 dark:border-red-500'
              : 'border-gray-300 dark:border-gray-600'"
          />
          <button v-if="customerSearch" @click="clearCustomerSearch" type="button" aria-label="Limpiar búsqueda"
                  class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 p-1 sm:p-1.5 short:p-1 rounded-lg transition-colors">
            <i class="ti ti-x text-base sm:text-xl short:text-base"></i>
          </button>
          <div v-if="showCustomerDropdown && filteredCustomers.length"
              class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-40 sm:max-h-52 short:max-h-32 overflow-y-auto">
            <div v-for="c in filteredCustomers" :key="c.id"
                @mousedown.prevent="selectCustomer(c)"
                class="px-2.5 sm:px-4 short:px-2.5 py-2 sm:py-3 short:py-1.5 text-sm sm:text-lg short:text-sm hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer flex items-center gap-2 sm:gap-3 short:gap-2 text-gray-800 dark:text-gray-200">
              <i class="ti ti-user text-gray-400 dark:text-gray-500 flex-shrink-0 text-sm sm:text-lg short:text-sm"></i>
              <span class="truncate">{{ c.name }}</span>
              <span class="text-gray-400 dark:text-gray-500 text-xs sm:text-base short:text-xs flex items-center gap-1 flex-shrink-0" v-if="c.phone">
                <i class="ti ti-phone text-xs sm:text-sm"></i>{{ c.phone }}
              </span>
              <span class="text-gray-400 dark:text-gray-500 text-xs sm:text-base short:text-xs flex items-center gap-1 flex-shrink-0 hidden sm:inline-flex" v-if="c.email">
                <i class="ti ti-mail text-xs sm:text-sm"></i>{{ c.email }}
              </span>
            </div>
          </div>
        </div>

        <button
          v-if="showAddPerson"
          type="button"
          @click="$emit('add-person')"
          class="flex-shrink-0 flex items-center justify-center gap-1.5 whitespace-nowrap px-3 sm:px-4 py-2.5 short:py-2 text-sm sm:text-lg short:text-sm font-medium rounded-lg border border-blue-500 dark:border-blue-400 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
        >
          <i class="ti ti-plus"></i><span>Añadir persona</span>
        </button>
      </div>
      <p v-if="selectedCustomer" class="text-xs sm:text-lg short:text-xs text-green-600 dark:text-green-400 mt-1 sm:mt-2 short:mt-1">
        <i class="ti ti-check"></i> Cliente ya existe en el registro: {{ selectedCustomer.name }}
      </p>
      <p v-else-if="customerSearch.trim() && !exactCustomerMatch" class="text-xs sm:text-lg short:text-xs text-blue-600 dark:text-blue-400 mt-1 sm:mt-2 short:mt-1">
        <i class="ti ti-user-plus"></i> Se creará un nuevo cliente: {{ customerSearch }}
      </p>
      <p v-if="showErrors && fieldErrors.customer" class="text-xs sm:text-base short:text-xs text-red-600 dark:text-red-400 mt-1 font-medium">
        Campo obligatorio*
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 short:gap-2 mt-3 sm:mt-5 short:mt-2">
        <div>
          <div class="flex items-center justify-between mb-1.5 sm:mb-2 short:mb-1">
            <label class="form-label text-sm sm:text-lg short:text-sm font-medium block text-gray-700 dark:text-gray-300">Teléfono</label>
            <div class="flex items-center gap-3">
              <button v-if="selectedCustomer && !editingPhone" @click="toggleEditPhone" type="button" aria-label="Editar teléfono"
                      class="text-gray-400 hover:text-blue-500 transition-colors">
                <i class="ti ti-pencil text-base sm:text-xl short:text-base"></i>
              </button>
              <template v-if="editingPhone">
                <button v-if="canConfirmPhone" @click="confirmPhoneChange" type="button"
                        class="text-green-500 hover:text-green-600 transition-colors" title="Guardar cambio">
                  <i class="ti ti-check text-base sm:text-xl short:text-base"></i>
                </button>
                <button @click="cancelPhoneEdit" type="button"
                        class="text-red-500 hover:text-red-600 transition-colors" title="Dejar igual">
                  <i class="ti ti-x text-base sm:text-xl short:text-base"></i>
                </button>
              </template>
            </div>
          </div>
          <div class="relative">
            <i class="ti ti-phone absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-base sm:text-xl short:text-sm"></i>
            <input
              type="text"
              :value="customerPhone"
              @input="onPhoneInput"
              @blur="onPhoneBlur"
              v-blur-on-enter
              enterkeyhint="next"
              maxlength="10"
              inputmode="numeric"
              :disabled="selectedCustomer && !editingPhone"
              placeholder="Ej. 4421234567"
              class="form-control form-control-sm w-full text-sm sm:text-lg short:text-sm p-2.5 sm:p-4 short:p-2 pl-9 sm:pl-11 short:pl-9 pr-9 sm:pr-10 short:pr-8 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-60 disabled:cursor-not-allowed"
              :class="phoneError ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
            />
            <button
              v-if="customerPhone && (!selectedCustomer || !selectedCustomer.phone || editingPhone)"
              @click="clearPhone"
              type="button"
              aria-label="Limpiar teléfono"
              class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
              <i class="ti ti-eraser text-base sm:text-xl short:text-base"></i>
            </button>
          </div>
          <p v-if="phoneError" class="text-xs sm:text-sm short:text-[11px] text-red-600 dark:text-red-400 mt-1 font-medium">
            {{ phoneError }}
          </p>
          <p v-if="phoneExists" class="text-xs sm:text-sm short:text-[11px] text-red-600 dark:text-red-400 mt-1 font-medium">
            <i class="ti ti-alert-triangle mr-1"></i>
            Teléfono ya registrado a nombre de: {{ phoneExists.name }}
          </p>
          <p v-else-if="selectedCustomer && selectedCustomer.phone && !editingPhone" class="text-xs sm:text-sm short:text-[11px] text-gray-400 dark:text-gray-500 mt-1">
            Dato del cliente registrado. Pulsa <i class="ti ti-pencil"></i> para editarlo.
          </p>
          <p v-else-if="selectedCustomer && selectedCustomer.phone && editingPhone" class="text-xs sm:text-sm short:text-[11px] text-amber-500 dark:text-amber-400 mt-1">
            <i class="ti ti-alert-triangle"></i> Confirma con <i class="ti ti-check"></i> o descarta con <i class="ti ti-x"></i>.
          </p>
        </div>

        <div>
          <div class="flex items-center justify-between mb-1.5 sm:mb-2 short:mb-1">
            <label class="form-label text-sm sm:text-lg short:text-sm font-medium block text-gray-700 dark:text-gray-300">Correo</label>
            <div class="flex items-center gap-3">
              <button v-if="selectedCustomer && !editingEmail" @click="toggleEditEmail" type="button" aria-label="Editar correo"
                      class="text-gray-400 hover:text-blue-500 transition-colors">
                <i class="ti ti-pencil text-base sm:text-xl short:text-base"></i>
              </button>
              <template v-if="editingEmail">
                <button v-if="emailChanged" @click="confirmEmailChange" type="button"
                        class="text-green-500 hover:text-green-600 transition-colors" title="Guardar cambio">
                  <i class="ti ti-check text-base sm:text-xl short:text-base"></i>
                </button>
                <button @click="cancelEmailEdit" type="button"
                        class="text-red-500 hover:text-red-600 transition-colors" title="Dejar igual">
                  <i class="ti ti-x text-base sm:text-xl short:text-base"></i>
                </button>
              </template>
            </div>
          </div>
          <div class="relative">
            <i class="ti ti-mail absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-base sm:text-xl short:text-sm"></i>
            <input
              type="email"
              :value="customerEmail"
              @input="$emit('update:customerEmail', $event.target.value)"
              v-blur-on-enter
              maxlength="255"
              :disabled="selectedCustomer && !editingEmail"
              placeholder="Ej. cliente@correo.com"
              class="form-control form-control-sm w-full text-sm sm:text-lg short:text-sm p-2.5 sm:p-4 short:p-2 pl-9 sm:pl-11 short:pl-9 pr-9 sm:pr-10 short:pr-8 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-60 disabled:cursor-not-allowed"
              :class="emailError ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
            />
            <button
              v-if="customerEmail && (!selectedCustomer || !selectedCustomer.email || editingEmail)"
              @click="clearEmail"
              type="button"
              aria-label="Limpiar correo"
              class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
              <i class="ti ti-eraser text-base sm:text-xl short:text-base"></i>
            </button>
          </div>
          <p v-if="emailError" class="text-xs sm:text-sm short:text-[11px] text-red-600 dark:text-red-400 mt-1 font-medium">
            {{ emailError }}
          </p>
          <p v-if="emailExists" class="text-xs sm:text-sm short:text-[11px] text-red-600 dark:text-red-400 mt-1 font-medium">
            <i class="ti ti-alert-triangle mr-1"></i>
            Correo ya registrado a nombre de: {{ emailExists.name }}
          </p>
          <p v-else-if="selectedCustomer && selectedCustomer.email && !editingEmail" class="text-xs sm:text-sm short:text-[11px] text-gray-400 dark:text-gray-500 mt-1">
            Dato del cliente registrado. Pulsa <i class="ti ti-pencil"></i> para editarlo.
          </p>
          <p v-else-if="selectedCustomer && selectedCustomer.email && editingEmail" class="text-xs sm:text-sm short:text-[11px] text-amber-500 dark:text-amber-400 mt-1">
            <i class="ti ti-alert-triangle"></i> Confirma con <i class="ti ti-check"></i> o descarta con <i class="ti ti-x"></i>.
          </p>
        </div>
      </div>
    </div>

    <slot name="pickups" />
  </section>
</template>

<script>
export default {
  name: 'CustomerStep',
  props: {
    sectionBadge: { type: [Number, String], default: 1 },
    sectionTitle: { type: String, default: 'Datos del cliente' },
    showAddPerson: { type: Boolean, default: false },
    onPhoneBlur: { type: Function, required: true },
    clearPhone: { type: Function, required: true },
    clearEmail: { type: Function, required: true },
    customerSearch: { type: String, default: '' },
    customerPhone: { type: String, default: '' },
    customerEmail: { type: String, default: '' },
    selectedCustomer: { type: Object, default: null },
    filteredCustomers: { type: Array, default: () => [] },
    exactCustomerMatch: { type: Object, default: null },
    showCustomerDropdown: { type: Boolean, default: false },
    showErrors: { type: Boolean, default: false },
    fieldErrors: { type: Object, required: true },
    editingPhone: { type: Boolean, default: false },
    editingEmail: { type: Boolean, default: false },
    canConfirmPhone: { type: Boolean, default: false },
    phoneError: { type: String, default: null },
    phoneExists: { type: Object, default: null },
    emailChanged: { type: Boolean, default: false },
    emailError: { type: String, default: null },
    emailExists: { type: Object, default: null },
    openCustomerDropdown: { type: Function, required: true },
    hideCustomerDropdown: { type: Function, required: true },
    clearCustomerSearch: { type: Function, required: true },
    selectCustomer: { type: Function, required: true },
    toggleEditPhone: { type: Function, required: true },
    confirmPhoneChange: { type: Function, required: true },
    cancelPhoneEdit: { type: Function, required: true },
    toggleEditEmail: { type: Function, required: true },
    confirmEmailChange: { type: Function, required: true },
    cancelEmailEdit: { type: Function, required: true }
  },
  emits: ['update:customerSearch', 'update:customerPhone', 'update:customerEmail', 'add-person'],
  methods: {
    onPhoneInput(event) {
      const filtered = event.target.value.replace(/\D/g, '');
      if (event.target.value !== filtered) {
        event.target.value = filtered;
      }
      this.$emit('update:customerPhone', filtered);
    },
  }
};
</script>