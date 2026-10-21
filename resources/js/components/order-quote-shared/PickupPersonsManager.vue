<template>
  <div>
    <button
      v-if="!hideAddButton && !formOpen"
      type="button"
      @click="openForm"
      class="mt-3 sm:mt-5 short:mt-2 flex items-center gap-1.5 px-3 sm:px-4 py-2.5 short:py-2 text-sm sm:text-lg short:text-sm font-medium rounded-lg border border-blue-500 dark:border-blue-400 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
    >
      <i class="ti ti-plus"></i><span>Añadir persona</span>
    </button>

    <section
      v-if="pickups.length || formOpen"
      class="mt-4 sm:mt-6 short:mt-2 pt-3 sm:pt-5 short:pt-2 border-t border-gray-200 dark:border-gray-700"
    >
      <h4 class="text-sm sm:text-base short:text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-2 sm:mb-3 short:mb-1.5 flex items-center gap-2">
        <i class="ti ti-users"></i>
        Personas autorizadas para recoger
      </h4>

      <!--
      <p v-if="primaryCustomerName" class="text-xs sm:text-lg short:text-xs text-gray-500 dark:text-gray-400 mb-2 sm:mb-3 short:mb-1.5">
        El cliente principal (<strong>{{ primaryCustomerName }}</strong>) siempre puede recoger.
      </p>
      -->
      <ul v-if="pickups.length" class="space-y-1.5 sm:space-y-2 short:space-y-1 mb-3 sm:mb-4 short:mb-2">
        <li
          v-for="(p, idx) in pickups"
          :key="`pickup-${idx}`"
          class="flex items-center gap-2 sm:gap-3 short:gap-2 bg-white dark:bg-gray-800 rounded-lg px-2.5 sm:px-4 short:px-2.5 py-2 sm:py-3 short:py-1.5 border"
          :class="editingIdx === idx
            ? 'border-blue-500 dark:border-blue-400'
            : 'border-gray-200 dark:border-gray-600'"
        >
          <i class="ti ti-user text-gray-400 dark:text-gray-500 flex-shrink-0 text-sm sm:text-lg short:text-sm"></i>
          <span class="text-sm sm:text-lg short:text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ displayName(p) }}</span>
          <span v-if="p.relationship" class="text-xs sm:text-base short:text-xs text-gray-500 dark:text-gray-400 italic flex-shrink-0">· {{ p.relationship }}</span>
          <span class="ml-auto flex items-center gap-1 sm:gap-2 short:gap-1 flex-shrink-0">
            <button
              type="button"
              @click="startEdit(idx)"
              class="text-gray-400 hover:text-blue-500 hover:bg-gray-100 dark:hover:bg-gray-700 p-1 sm:p-1.5 short:p-1 rounded-lg transition-colors"
              aria-label="Editar persona"
            >
              <i class="ti ti-pencil text-base sm:text-xl short:text-base"></i>
            </button>
            <button
              type="button"
              @click="remove(idx)"
              class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 p-1 sm:p-1.5 short:p-1 rounded-lg transition-colors"
              aria-label="Quitar persona"
            >
              <i class="ti ti-x text-base sm:text-xl short:text-base"></i>
            </button>
          </span>
        </li>
      </ul>

      <div v-if="formOpen">
        <label class="form-label text-sm sm:text-lg short:text-sm font-medium block mb-1.5 sm:mb-2 short:mb-1 text-gray-700 dark:text-gray-300">
          {{ editingIdx !== null ? 'Editar nombre completo' : 'Nombre completo' }}
        </label>
        <div class="flex flex-col sm:flex-row sm:items-stretch gap-2 sm:gap-3 short:gap-2">
          <div class="relative flex-1 min-w-0" @focusout="onFocusOut">
            <i class="ti ti-user-plus absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-base sm:text-xl short:text-sm"></i>
            <input
              ref="nameInput"
              v-model="name"
              @focus="open = true"
              @input="onNameInput"
              @keydown.enter.prevent="submitForm"
              @keydown.esc.prevent="cancelForm"
              type="text"
              maxlength="255"
              placeholder="Ej. María López García"
              autocomplete="off"
              class="form-control form-control-sm w-full text-sm sm:text-lg short:text-sm p-2.5 sm:p-4 short:p-2 pl-9 sm:pl-11 short:pl-9 pr-9 sm:pr-10 short:pr-8 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="localError ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
            />

            <transition name="dropdown">
              <div
                v-if="open && filteredCustomers.length"
                class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-40 sm:max-h-52 short:max-h-32 overflow-y-auto"
              >
                <button
                  v-for="c in filteredCustomers"
                  :key="c.id"
                  type="button"
                  @mousedown.prevent="selectExisting(c)"
                  class="w-full px-2.5 sm:px-4 short:px-2.5 py-2 sm:py-3 short:py-1.5 text-sm sm:text-lg short:text-sm text-left hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer flex items-center gap-2 sm:gap-3 short:gap-2 text-gray-800 dark:text-gray-200"
                >
                  <i class="ti ti-user text-gray-400 dark:text-gray-500 flex-shrink-0 text-sm sm:text-lg short:text-sm"></i>
                  <span class="truncate">{{ c.name }}</span>
                  <span v-if="c.phone" class="text-gray-400 dark:text-gray-500 text-xs sm:text-base short:text-xs flex items-center gap-1 flex-shrink-0">
                    <i class="ti ti-phone text-xs sm:text-sm"></i>{{ c.phone }}
                  </span>
                </button>
              </div>
            </transition>
          </div>

          <div class="flex gap-2 sm:gap-3 short:gap-2">
            <button
              type="button"
              @click="submitForm"
              class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 whitespace-nowrap px-3 sm:px-4 py-2.5 short:py-2 text-sm sm:text-lg short:text-sm font-medium rounded-lg bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white transition-colors"
            >
              <i class="ti ti-check"></i><span>{{ editingIdx !== null ? 'Guardar' : 'Agregar' }}</span>
            </button>
            <button
              type="button"
              @click="cancelForm"
              class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 whitespace-nowrap px-3 sm:px-4 py-2.5 short:py-2 text-sm sm:text-lg short:text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition-colors"
            >
              <i class="ti ti-x"></i><span>Cancelar</span>
            </button>
          </div>
        </div>
        <p v-if="localError" class="text-xs sm:text-base short:text-xs text-red-600 dark:text-red-400 mt-1 font-medium">
          {{ localError }}
        </p>
      </div>
    </section>
  </div>
</template>

<script>
export default {
  name: 'PickupPersonsManager',
  props: {
    modelValue: { type: Array, default: () => [] },
    customers: { type: Array, default: () => [] },
    primaryCustomerId: { type: [Number, String], default: null },
    primaryCustomerName: { type: String, default: '' },
    showErrors: { type: Boolean, default: false },
    hideAddButton: { type: Boolean, default: false },
    addTick: { type: Number, default: 0 }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      formOpen: false,
      editingIdx: null,
      name: '',
      open: false,
      localError: ''
    };
  },
  computed: {
    pickups() {
      return this.modelValue || [];
    },
    filteredCustomers() {
      const q = this.name.trim().toLowerCase();
      if (!q) return [];
      return this.customers
        .filter(c => String(c.id) !== String(this.primaryCustomerId))
        .filter(c => !this.pickups.some((p, i) =>
          i !== this.editingIdx && String(p.customer_id) === String(c.id)))
        .filter(c => (c.name || '').toLowerCase().startsWith(q))
        .slice(0, 8);
    },
    exactMatch() {
      const q = this.name.trim().toLowerCase();
      if (!q) return null;
      return this.customers.find(c =>
        (c.name || '').toLowerCase() === q &&
        String(c.id) !== String(this.primaryCustomerId)
      ) || null;
    }
  },
  watch: {
    addTick() {
      this.openForm();
    }
  },
  methods: {
    displayName(p) {
      if (p.customer_id) {
        const c = this.customers.find(x => String(x.id) === String(p.customer_id));
        return c?.name || p.displayName || `Cliente #${p.customer_id}`;
      }
      return p.new_customer?.name || p.displayName || 'Nuevo';
    },
    focusInput() {
      this.$nextTick(() => this.$refs.nameInput?.focus());
    },
    openForm() {
      this.editingIdx = null;
      this.name = '';
      this.localError = '';
      this.formOpen = true;
      this.focusInput();
    },
    startEdit(idx) {
      this.editingIdx = idx;
      this.name = this.displayName(this.pickups[idx]);
      this.localError = '';
      this.formOpen = true;
      this.open = false;
      this.focusInput();
    },
    cancelForm() {
      this.formOpen = false;
      this.editingIdx = null;
      this.name = '';
      this.localError = '';
      this.open = false;
    },
    onNameInput() {
      this.open = true;
      this.localError = '';
    },
    onFocusOut(event) {
      if (!event.currentTarget.contains(event.relatedTarget)) {
        setTimeout(() => { this.open = false; }, 150);
      }
    },
    validate(name) {
      if (name.length < 2) return 'Escribe el nombre completo de la persona.';
      const lower = name.toLowerCase();
      if (this.primaryCustomerName && lower === this.primaryCustomerName.trim().toLowerCase()) {
        return 'Esta persona es el cliente principal, que ya puede recoger la orden.';
      }
      const duplicated = this.pickups.some((p, i) =>
        i !== this.editingIdx && this.displayName(p).trim().toLowerCase() === lower);
      if (duplicated) return 'Esta persona ya está en la lista.';
      return '';
    },
    commit(entry) {
      const prev = this.editingIdx !== null ? this.pickups[this.editingIdx] : null;
      const item = { ...entry, relationship: prev?.relationship ?? null };
      const list = [...this.pickups];
      if (prev) list.splice(this.editingIdx, 1, item);
      else list.push(item);
      this.$emit('update:modelValue', list);
      this.cancelForm();
    },
    selectExisting(customer) {
      this.name = customer.name;
      this.submitForm();
    },
    submitForm() {
      const name = this.name.trim();
      const error = this.validate(name);
      if (error) {
        this.localError = error;
        return;
      }
      if (this.exactMatch) {
        this.commit({ customer_id: this.exactMatch.id });
      } else {
        this.commit({ new_customer: { name } });
      }
    },
    remove(idx) {
      if (this.editingIdx === idx) this.cancelForm();
      this.$emit('update:modelValue', this.pickups.filter((_, i) => i !== idx));
    }
  }
};
</script>

<style scoped>
.dropdown-enter-active { transition: opacity 0.18s ease, transform 0.18s ease; transform-origin: top center; }
.dropdown-leave-active { transition: opacity 0.14s ease, transform 0.14s ease; transform-origin: top center; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: scaleY(0.9) translateY(-4px); }
</style>