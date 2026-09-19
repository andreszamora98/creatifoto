<template>
  <section>
    <h4 class="text-base font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-5 flex items-center gap-2">
      <span class="w-8 h-8 rounded-full bg-blue-500 text-white text-sm flex items-center justify-center font-bold">1</span>
      Datos del cliente
    </h4>

    <div>
      <label class="form-label text-lg font-medium block mb-2 text-gray-700 dark:text-gray-300">Cliente <span class="text-red-500">*</span></label>
      <div class="relative">
        <i class="ti ti-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xl"></i>
        <input
          type="text"
          :value="customerSearch"
          @input="$emit('update:customerSearch', $event.target.value)"
          @focus="openCustomerDropdown"
          @blur="hideCustomerDropdown"
          placeholder="Escribe el nombre completo del cliente..."
          class="form-control form-control-sm w-full text-lg p-4 pl-11 pr-10 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          :class="showErrors && fieldErrors.customer
            ? 'border-red-500 dark:border-red-500'
            : 'border-gray-300 dark:border-gray-600'"
        />
        <button v-if="customerSearch" @click="clearCustomerSearch" type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
          <i class="ti ti-x text-xl"></i>
        </button>
        <div v-if="showCustomerDropdown && filteredCustomers.length"
            class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-52 overflow-y-auto">
          <div v-for="c in filteredCustomers" :key="c.id"
              @mousedown.prevent="selectCustomer(c)"
              class="px-4 py-3 text-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer flex items-center gap-3 text-gray-800 dark:text-gray-200">
            <i class="ti ti-user text-gray-400 dark:text-gray-500 flex-shrink-0 text-lg"></i>
            <span>{{ c.name }}</span>
            <span class="text-gray-400 dark:text-gray-500 text-base flex items-center gap-1" v-if="c.phone">
              <i class="ti ti-phone text-sm"></i>{{ c.phone }}
            </span>
            <span class="text-gray-400 dark:text-gray-500 text-base flex items-center gap-1" v-if="c.email">
              <i class="ti ti-mail text-sm"></i>{{ c.email }}
            </span>
          </div>
        </div>
      </div>
      <p v-if="selectedCustomer" class="text-lg text-green-600 dark:text-green-400 mt-2">
        <i class="ti ti-check"></i> Cliente ya existe en el registro: {{ selectedCustomer.name }}
      </p>
      <p v-else-if="customerSearch.trim() && !exactCustomerMatch" class="text-lg text-blue-600 dark:text-blue-400 mt-2">
        <i class="ti ti-user-plus"></i> Se creará un nuevo cliente: {{ customerSearch }}
      </p>
      <p v-if="showErrors && fieldErrors.customer" class="text-base text-red-600 dark:text-red-400 mt-1 font-medium">
        Campo obligatorio*
      </p>

      <div class="grid grid-cols-2 gap-4 mt-5">
        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="form-label text-lg font-medium block text-gray-700 dark:text-gray-300">Teléfono</label>
            <div class="flex items-center gap-3">
              <button v-if="selectedCustomer && selectedCustomer.phone && !editingPhone" @click="toggleEditPhone" type="button"
                      class="text-gray-400 hover:text-blue-500 transition-colors">
                <i class="ti ti-pencil text-xl"></i>
              </button>
              <template v-if="editingPhone">
                <button v-if="canConfirmPhone" @click="confirmPhoneChange" type="button"
                        class="text-green-500 hover:text-green-600 transition-colors" title="Guardar cambio">
                  <i class="ti ti-check text-xl"></i>
                </button>
                <button @click="cancelPhoneEdit" type="button"
                        class="text-red-500 hover:text-red-600 transition-colors" title="Dejar igual">
                  <i class="ti ti-x text-xl"></i>
                </button>
              </template>
            </div>
          </div>
          <div class="relative">
            <i class="ti ti-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xl"></i>
            <input
              type="text"
              :value="customerPhone"
              @input="onPhoneInput"
              @blur="onPhoneBlur"
              maxlength="10"
              inputmode="numeric"
              :disabled="selectedCustomer && selectedCustomer.phone && !editingPhone"
              placeholder="Ej. 4421234567"
              class="form-control form-control-sm w-full text-lg p-4 pl-11 pr-10 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-60 disabled:cursor-not-allowed"
              :class="phoneError ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
            />
            <button
              v-if="customerPhone && (!selectedCustomer || !selectedCustomer.phone || editingPhone)"
              @click="clearPhone"
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
              <i class="ti ti-eraser text-xl"></i>
            </button>
          </div>
          <p v-if="phoneError" class="text-sm text-red-600 dark:text-red-400 mt-1 font-medium">
            {{ phoneError }}
          </p>
          <p v-if="phoneExists" class="text-sm text-red-600 dark:text-red-400 mt-1 font-medium">
            <i class="ti ti-alert-triangle mr-1"></i>
            Teléfono ya registrado a nombre de: {{ phoneExists.name }}
          </p>
          <p v-else-if="selectedCustomer && selectedCustomer.phone && !editingPhone" class="text-sm text-gray-400 dark:text-gray-500 mt-1">
            Dato del cliente registrado. Pulsa <i class="ti ti-pencil"></i> para editarlo.
          </p>
          <p v-else-if="selectedCustomer && selectedCustomer.phone && editingPhone" class="text-sm text-amber-500 dark:text-amber-400 mt-1">
            <i class="ti ti-alert-triangle"></i> Confirma con <i class="ti ti-check"></i> o descarta con <i class="ti ti-x"></i>.
          </p>
        </div>

        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="form-label text-lg font-medium block text-gray-700 dark:text-gray-300">Correo</label>
            <div class="flex items-center gap-3">
              <button v-if="selectedCustomer && selectedCustomer.email && !editingEmail" @click="toggleEditEmail" type="button"
                      class="text-gray-400 hover:text-blue-500 transition-colors">
                <i class="ti ti-pencil text-xl"></i>
              </button>
              <template v-if="editingEmail">
                <button v-if="emailChanged" @click="confirmEmailChange" type="button"
                        class="text-green-500 hover:text-green-600 transition-colors" title="Guardar cambio">
                  <i class="ti ti-check text-xl"></i>
                </button>
                <button @click="cancelEmailEdit" type="button"
                        class="text-red-500 hover:text-red-600 transition-colors" title="Dejar igual">
                  <i class="ti ti-x text-xl"></i>
                </button>
              </template>
            </div>
          </div>
          <div class="relative">
            <i class="ti ti-mail absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xl"></i>
            <input
              type="email"
              :value="customerEmail"
              @input="$emit('update:customerEmail', $event.target.value)"
              maxlength="255"
              :disabled="selectedCustomer && selectedCustomer.email && !editingEmail"
              placeholder="Ej. cliente@correo.com"
              class="form-control form-control-sm w-full text-lg p-4 pl-11 pr-10 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-60 disabled:cursor-not-allowed"
              :class="emailError ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
            />
            <button
              v-if="customerEmail && (!selectedCustomer || !selectedCustomer.email || editingEmail)"
              @click="clearEmail"
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
              <i class="ti ti-eraser text-xl"></i>
            </button>
          </div>
          <p v-if="emailError" class="text-sm text-red-600 dark:text-red-400 mt-1 font-medium">
            {{ emailError }}
          </p>
          <p v-if="emailExists" class="text-sm text-red-600 dark:text-red-400 mt-1 font-medium">
            <i class="ti ti-alert-triangle mr-1"></i>
            Correo ya registrado a nombre de: {{ emailExists.name }}
          </p>
          <p v-else-if="selectedCustomer && selectedCustomer.email && !editingEmail" class="text-sm text-gray-400 dark:text-gray-500 mt-1">
            Dato del cliente registrado. Pulsa <i class="ti ti-pencil"></i> para editarlo.
          </p>
          <p v-else-if="selectedCustomer && selectedCustomer.email && editingEmail" class="text-sm text-amber-500 dark:text-amber-400 mt-1">
            <i class="ti ti-alert-triangle"></i> Confirma con <i class="ti ti-check"></i> o descarta con <i class="ti ti-x"></i>.
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'CustomerStep',
  props: {
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
  emits: ['update:customerSearch', 'update:customerPhone', 'update:customerEmail'],
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