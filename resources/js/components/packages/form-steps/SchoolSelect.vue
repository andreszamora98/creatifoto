<template>
  <div class="relative" ref="root" @focusout="onFocusOut">
    <div class="relative">
      <i class="ti ti-building-community absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xl pointer-events-none"></i>
      <input
        type="text"
        :value="inputValue"
        :disabled="disabled"
        @focus="openDropdown"
        @input="onInput($event.target.value)"
        @keydown.esc="closeDropdown"
        @keydown.down.prevent="moveHighlight(1)"
        @keydown.up.prevent="moveHighlight(-1)"
        @keydown.enter.prevent="selectHighlighted"
        :placeholder="placeholder"
        autocomplete="off"
        :class="inputClass"
      />
      <button
        v-if="selectedSchool && !isOpen && !disabled"
        type="button"
        @mousedown.prevent="clear"
        class="absolute right-3 top-1/2 -translate-y-1/2 text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 p-1 rounded-lg transition-colors"
        title="Limpiar selección"
      >
        <i class="ti ti-x text-2xl"></i>
      </button>
    </div>

    <div
      v-if="isOpen"
      class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden"
    >
      <div ref="list" class="max-h-56 overflow-y-auto">
        <button
          v-for="(s, idx) in filteredSchools"
          :key="s.id"
          type="button"
          :data-idx="idx"
          @mousedown.prevent="select(s.id)"
          @mouseover="highlightedIdx = idx"
          class="w-full px-3 py-2.5 text-base flex items-center gap-2 text-gray-800 dark:text-gray-200"
          :class="idx === highlightedIdx ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
        >
          <i v-if="Number(s.id) === Number(modelValue)" class="ti ti-check text-blue-600 dark:text-blue-400 text-base flex-shrink-0"></i>
          <span v-else class="w-4 flex-shrink-0"></span>
          <span class="flex-1 text-left truncate">{{ s.name }}</span>
        </button>
        <p v-if="filteredSchools.length === 0" class="text-sm text-gray-400 dark:text-gray-500 text-center py-3">
          Sin coincidencias
        </p>
      </div>
    </div>
  </div>
</template>

<script>
const FIELD_BASE =
  'form-control form-control-sm w-full text-lg p-4 pl-11 rounded-lg border bg-gray-50 dark:bg-gray-700 ' +
  'text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent ' +
  'disabled:opacity-60 disabled:cursor-not-allowed';

export default {
  name: 'SchoolSelect',
  props: {
    modelValue: { type: [Number, String], default: null },
    schools: { type: Array, default: () => [] },
    disabled: { type: Boolean, default: false },
    invalid: { type: Boolean, default: false }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      isOpen: false,
      search: '',
      highlightedIdx: 0
    };
  },
  computed: {
    selectedSchool() {
      if (!this.modelValue) return null;
      return this.schools.find(s => Number(s.id) === Number(this.modelValue)) || null;
    },
    filteredSchools() {
      const q = this.normalize(this.search);
      if (!q) return this.schools;
      return this.schools.filter(s => this.normalize(s.name).includes(q));
    },
    inputValue() {
      if (this.isOpen) return this.search;
      return this.selectedSchool ? this.selectedSchool.name : '';
    },
    placeholder() {
      if (this.disabled) return 'Cargando...';
      return this.selectedSchool ? 'Buscar otra escuela...' : 'Buscar o seleccionar escuela...';
    },
    inputClass() {
      return [
        FIELD_BASE,
        this.selectedSchool && !this.isOpen ? 'pr-11 truncate' : '',
        this.invalid ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'
      ];
    }
  },
  mounted() {
    this._outsideTapHandler = (event) => {
      if (this.isOpen && this.$refs.root && !this.$refs.root.contains(event.target)) {
        this.closeDropdown();
      }
    };
    document.addEventListener('touchstart', this._outsideTapHandler, true);
  },
  beforeUnmount() {
    document.removeEventListener('touchstart', this._outsideTapHandler, true);
  },
  methods: {
    normalize(value) {
      return String(value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
    },
    openDropdown() {
      if (this.disabled || this.isOpen) return;
      this.search = '';
      this.highlightedIdx = 0;
      this.isOpen = true;
    },
    closeDropdown() {
      this.isOpen = false;
      this.search = '';
    },
    onInput(value) {
      this.search = value;
      this.highlightedIdx = 0;
      this.isOpen = true;
    },
    onFocusOut(event) {
      if (!this.isOpen) return;
      if (!event.currentTarget.contains(event.relatedTarget)) this.closeDropdown();
    },
    moveHighlight(delta) {
      if (!this.isOpen) {
        this.openDropdown();
        return;
      }
      const len = this.filteredSchools.length;
      if (!len) return;
      this.highlightedIdx = (this.highlightedIdx + delta + len) % len;
      this.$nextTick(() => {
        const el = this.$refs.list?.querySelector(`[data-idx="${this.highlightedIdx}"]`);
        if (el) el.scrollIntoView({ block: 'nearest' });
      });
    },
    selectHighlighted() {
      const s = this.filteredSchools[this.highlightedIdx];
      if (s) this.select(s.id);
    },
    select(id) {
      this.$emit('update:modelValue', id);
      this.closeDropdown();
    },
    clear() {
      this.$emit('update:modelValue', null);
      this.search = '';
    }
  }
};
</script>