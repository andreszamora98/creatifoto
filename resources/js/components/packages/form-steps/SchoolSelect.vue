<template>
  <div class="relative" ref="root" @focusout="onFocusOut">
    <div class="relative" ref="trigger">
      <i class="ti ti-building-community absolute left-2.5 sm:left-3 short:left-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-base sm:text-xl short:text-base pointer-events-none"></i>
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
        aria-label="Limpiar selección"
        class="absolute right-2 sm:right-3 short:right-2 top-1/2 -translate-y-1/2 text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 p-1 short:p-0.5 rounded-lg transition-colors"
        title="Limpiar selección"
      >
        <i class="ti ti-x text-base sm:text-2xl short:text-base"></i>
      </button>
    </div>

    <transition name="dropdown">
      <div
        v-if="isOpen"
        :style="dropdownStyle"
        class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden"
      >
        <div ref="list" class="max-h-32 sm:max-h-44 short:max-h-32 overflow-y-auto">
          <button
            v-for="(s, idx) in filteredSchools"
            :key="s.id"
            type="button"
            :data-idx="idx"
            @mousedown.prevent="select(s.id)"
            @mouseover="highlightedIdx = idx"
            class="w-full px-2.5 sm:px-3 short:px-2 py-1.5 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-sm flex items-center gap-1.5 sm:gap-2 short:gap-1.5 text-gray-800 dark:text-gray-200"
            :class="idx === highlightedIdx ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
          >
            <i v-if="Number(s.id) === Number(modelValue)" class="ti ti-check text-blue-600 dark:text-blue-400 text-sm sm:text-base short:text-sm flex-shrink-0"></i>
            <span v-else class="w-3 sm:w-4 flex-shrink-0"></span>
            <span class="flex-1 text-left truncate">{{ s.name }}</span>
          </button>
          <p v-if="filteredSchools.length === 0" class="text-xs sm:text-sm short:text-xs text-gray-400 dark:text-gray-500 text-center py-2 sm:py-3 short:py-2">
            Sin coincidencias
          </p>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
const FIELD_BASE =
  'form-control form-control-sm w-full text-sm sm:text-lg short:text-sm p-2.5 sm:p-4 short:p-2 ' +
  'pl-9 sm:pl-11 short:pl-9 rounded-lg border bg-gray-50 dark:bg-gray-700 ' +
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
      highlightedIdx: 0,
      dropdownStyle: {}
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
        this.selectedSchool && !this.isOpen ? 'pr-9 sm:pr-11 short:pr-9 truncate' : '',
        this.invalid ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'
      ];
    }
  },
  mounted() {
    this._outsideTapHandler = (event) => {
      if (!this.isOpen) return;
      const inRoot = this.$refs.root && this.$refs.root.contains(event.target);
      const inList = this.$refs.list && this.$refs.list.contains(event.target);
      if (!inRoot && !inList) this.closeDropdown();
    };
    document.addEventListener('touchstart', this._outsideTapHandler, true);
  },
  beforeUnmount() {
    document.removeEventListener('touchstart', this._outsideTapHandler, true);
    this.removePositionListeners();
  },
  methods: {
    normalize(value) {
      return String(value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
    },
    openDropdown() {
      if (this.disabled || this.isOpen) return;
      this.search = '';
      this.highlightedIdx = 0;
      this.updatePosition();
      this.isOpen = true;
      this.addPositionListeners();
    },
    closeDropdown() {
      this.isOpen = false;
      this.search = '';
      this.removePositionListeners();
    },
    addPositionListeners() {
      window.addEventListener('resize', this.updatePosition);
      window.addEventListener('scroll', this.updatePosition, true);
    },
    removePositionListeners() {
      window.removeEventListener('resize', this.updatePosition);
      window.removeEventListener('scroll', this.updatePosition, true);
    },
    updatePosition() {
      const trigger = this.$refs.trigger;
      if (!trigger) return;
      const rect = trigger.getBoundingClientRect();
      const viewportHeight = window.innerHeight;
      const dropdownHeight = 180;
      const margin = 24;
      const spaceBelow = viewportHeight - rect.bottom;
      const openUp = spaceBelow < dropdownHeight + margin;

      const style = {
        position: 'fixed',
        left: rect.left + 'px',
        width: rect.width + 'px',
        zIndex: 100
      };

      if (openUp) {
        style.bottom = (viewportHeight - rect.top + 4) + 'px';
      } else {
        style.top = (rect.bottom + 4) + 'px';
      }

      this.dropdownStyle = style;
    },
    onInput(value) {
      this.search = value;
      this.highlightedIdx = 0;
      if (!this.isOpen) {
        this.updatePosition();
        this.isOpen = true;
        this.addPositionListeners();
      }
    },
    onFocusOut(event) {
      if (!this.isOpen) return;
      const next = event.relatedTarget;
      const inRoot = this.$refs.root && this.$refs.root.contains(next);
      const inList = this.$refs.list && this.$refs.list.contains(next);
      if (!inRoot && !inList) this.closeDropdown();
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