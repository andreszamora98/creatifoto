<template>
  <section>
    <h5 class="text-xs sm:text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2 sm:mb-3 flex items-center gap-2">
      <i class="ti ti-users"></i>
      Personas autorizadas a recoger
      <span class="text-[10px] sm:text-xs font-normal normal-case text-gray-400 dark:text-gray-500">(opcional)</span>
    </h5>

    <p v-if="primaryCustomerName" class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-2 sm:mb-3">
      El cliente principal (<strong>{{ primaryCustomerName }}</strong>) siempre puede recoger. Agrega aquí personas adicionales.
    </p>

    <ul v-if="pickups.length" class="space-y-1.5 mb-3">
      <li
        v-for="(p, idx) in pickups"
        :key="`pickup-${idx}`"
        class="flex items-center gap-2 bg-white dark:bg-gray-800 rounded-lg px-3 py-2 border border-gray-200 dark:border-gray-600"
      >
        <i class="ti ti-user text-gray-400 dark:text-gray-500"></i>
        <span class="text-sm sm:text-base font-medium text-gray-800 dark:text-gray-200 truncate">{{ displayName(p) }}</span>
        <span v-if="p.relationship" class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 italic ml-auto flex-shrink-0">· {{ p.relationship }}</span>
        <button
          type="button"
          @click="remove(idx)"
          class="ml-1 text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 p-1 rounded-lg transition-colors flex-shrink-0"
          aria-label="Quitar persona"
        >
          <i class="ti ti-x text-sm sm:text-base"></i>
        </button>
      </li>
    </ul>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
      <div class="relative sm:col-span-2" :data-pickup-dropdown="true" @focusout="onFocusOut">
        <div
          class="w-full text-sm sm:text-base p-2 sm:p-2.5 rounded-lg border text-gray-800 dark:text-gray-200 flex items-center gap-2"
          :class="'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700'"
        >
          <i class="ti ti-search text-gray-400 dark:text-gray-500 flex-shrink-0"></i>
          <input
            v-model="search"
            @focus="open = true"
            @input="open = true"
            type="text"
            placeholder="Buscar cliente por nombre..."
            class="flex-1 min-w-0 bg-transparent border-0 outline-none text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500"
            autocomplete="off"
          >
        </div>

        <transition name="dropdown">
          <div
            v-if="open && (filteredCustomers.length || canCreateNew)"
            class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden max-h-48 overflow-y-auto"
          >
            <button
              v-for="c in filteredCustomers"
              :key="c.id"
              type="button"
              @mousedown.prevent="addExisting(c)"
              class="w-full px-3 py-2 text-sm sm:text-base text-left flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200"
            >
              <i class="ti ti-user text-gray-400 dark:text-gray-500 flex-shrink-0"></i>
              <span class="truncate flex-1">{{ c.name }}</span>
              <span v-if="c.phone" class="text-xs text-gray-400 dark:text-gray-500 flex-shrink-0">{{ c.phone }}</span>
            </button>
            <button
              v-if="canCreateNew && !exactMatch"
              type="button"
              @mousedown.prevent="addNew"
              class="w-full px-3 py-2 text-sm sm:text-base text-left flex items-center gap-2 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-t border-gray-200 dark:border-gray-600"
            >
              <i class="ti ti-user-plus flex-shrink-0"></i>
              <span class="truncate">Crear "{{ search.trim() }}" como persona autorizada</span>
            </button>
          </div>
        </transition>
      </div>

      <input
        v-model="relationship"
        type="text"
        maxlength="50"
        placeholder="Relación (opcional)"
        class="w-full text-sm sm:text-base p-2 sm:p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
      >
    </div>

    <p v-if="showErrors && pickupsHaveError" class="text-xs sm:text-sm text-red-600 dark:text-red-400 mt-1 font-medium">
      {{ pickupsError }}
    </p>
  </section>
</template>

<script>
export default {
  name: 'PickupPersonsManager',
  props: {
    modelValue: { type: Array, default: () => [] },
    customers: { type: Array, default: () => [] },
    primaryCustomerId: { type: [Number, String], default: null },
    primaryCustomerName: { type: String, default: '' },
    showErrors: { type: Boolean, default: false }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      search: '',
      relationship: '',
      open: false
    };
  },
  computed: {
    pickups() {
      return this.modelValue || [];
    },
    filteredCustomers() {
      const q = this.search.trim().toLowerCase();
      if (!q) return [];
      return this.customers
        .filter(c => String(c.id) !== String(this.primaryCustomerId))
        .filter(c => !this.pickups.some(p => String(p.customer_id) === String(c.id)))
        .filter(c => (c.name || '').toLowerCase().startsWith(q))
        .slice(0, 8);
    },
    exactMatch() {
      const q = this.search.trim().toLowerCase();
      if (!q) return null;
      return this.customers.find(c =>
        (c.name || '').toLowerCase() === q &&
        String(c.id) !== String(this.primaryCustomerId)
      );
    },
    canCreateNew() {
      return this.search.trim().length >= 2;
    },
    pickupsHaveError() {
      return false;
    },
    pickupsError() {
      return '';
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
    onFocusOut(event) {
      if (!event.currentTarget.contains(event.relatedTarget)) {
        setTimeout(() => { this.open = false; }, 150);
      }
    },
    addExisting(customer) {
      this.$emit('update:modelValue', [
        ...this.pickups,
        { customer_id: customer.id, relationship: this.relationship.trim() || null }
      ]);
      this.search = '';
      this.relationship = '';
      this.open = false;
    },
    addNew() {
      const name = this.search.trim();
      if (!name) return;
      this.$emit('update:modelValue', [
        ...this.pickups,
        { new_customer: { name }, relationship: this.relationship.trim() || null }
      ]);
      this.search = '';
      this.relationship = '';
      this.open = false;
    },
    remove(idx) {
      this.$emit('update:modelValue', this.pickups.filter((_, i) => i !== idx));
    }
  }
};
</script>

<style scoped>
.dropdown-enter-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
  transform-origin: top center;
}
.dropdown-leave-active {
  transition: opacity 0.14s ease, transform 0.14s ease;
  transform-origin: top center;
}
.dropdown-enter-from {
  opacity: 0;
  transform: scaleY(0.9) translateY(-4px);
}
.dropdown-leave-to {
  opacity: 0;
  transform: scaleY(0.9) translateY(-4px);
}
</style>