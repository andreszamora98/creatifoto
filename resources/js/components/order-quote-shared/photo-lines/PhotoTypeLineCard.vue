<template>
  <div class="relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-2.5 sm:p-4 short:p-2 space-y-2 sm:space-y-3 short:space-y-2">

    <div class="flex items-center justify-between gap-2">
      <span class="text-sm sm:text-base short:text-xs font-semibold text-gray-500 dark:text-gray-400 truncate">
        Tipo {{ index + 1 }}
        <span v-if="includedInPackage" class="ml-1.5 sm:ml-2 text-[10px] sm:text-xs short:text-[10px] font-medium text-purple-600 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/30 px-1.5 sm:px-2 py-0.5 rounded whitespace-nowrap">
          Incluido en paquete
        </span>
      </span>
      <button v-if="canRemove" type="button" @click="onRemove" aria-label="Eliminar tipo"
              class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 p-1 sm:p-1.5 short:p-1 rounded-lg transition-colors flex-shrink-0">
        <i class="ti ti-trash text-base sm:text-xl short:text-base"></i>
      </button>
    </div>

    <div class="grid grid-cols-[1fr_auto] gap-x-1.5 sm:gap-x-3 short:gap-x-1.5 gap-y-1 items-end">
      <span></span>
      <span class="text-[10px] sm:text-sm short:text-[10px] font-medium text-gray-500 dark:text-gray-400 text-center whitespace-nowrap">Cantidad</span>

      <div class="relative min-w-0 sm:min-w-[10rem]" :data-photo-type-dropdown="editorId" @focusout="onFocusOut">
        <div
          class="w-full text-sm sm:text-lg short:text-sm p-2 sm:p-3 short:p-1.5 rounded-lg border text-gray-800 dark:text-gray-200 flex items-center gap-1.5 sm:gap-2"
          :class="showError && !line.photo_type_id
            ? 'border-red-500 dark:border-red-500'
            : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700'"
        >
          <i class="ti ti-photo text-base sm:text-xl short:text-base text-gray-400 dark:text-gray-500 flex-shrink-0"></i>
          <input
            :value="inputValue"
            @focus="onOpen"
            @input="onInput($event.target.value)"
            @keydown.esc="onEsc"
            @keydown.down.prevent="onMoveHighlight(1)"
            @keydown.up.prevent="onMoveHighlight(-1)"
            @keydown.enter.prevent="onSelectHighlighted"
            type="text"
            :placeholder="line.photo_type_id ? 'Buscar otro tipo...' : 'Buscar o seleccionar tipo...'"
            class="flex-1 min-w-0 bg-transparent border-0 outline-none text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 truncate"
            autocomplete="off"
          >
          <span v-if="line.photo_type_id && !isOpen" class="text-xs sm:text-sm short:text-xs font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap flex-shrink-0">
            {{ displayPrice }}
          </span>
          <button
            v-if="line.photo_type_id && !isOpen"
            type="button"
            @mousedown.prevent="onClear"
            class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 flex-shrink-0 p-1 short:p-0.5 rounded-lg transition-colors"
            title="Limpiar selección"
          >
            <i class="ti ti-x text-base sm:text-2xl short:text-base"></i>
          </button>
        </div>

        <transition name="dropdown">
          <div v-if="isOpen" class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
            <div class="max-h-48 sm:max-h-56 short:max-h-32 overflow-y-auto">
              <button
                v-for="(pt, pIdx) in filteredPhotoTypes"
                :key="pt.id"
                type="button"
                @mousedown.prevent="onSelect(pt.id)"
                @mouseover="onHighlight(pIdx)"
                class="w-full px-2.5 sm:px-3 short:px-2 py-1.5 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-sm flex items-center gap-1.5 sm:gap-2 text-gray-800 dark:text-gray-200"
                :class="pIdx === highlightedIdx ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
              >
                <i v-if="pt.id === line.photo_type_id" class="ti ti-check text-blue-600 dark:text-blue-400 text-sm sm:text-base flex-shrink-0"></i>
                <span v-else class="w-3 sm:w-4 flex-shrink-0"></span>
                <span class="flex-1 text-left truncate">{{ pt.name }}</span>
                <span class="text-xs sm:text-sm short:text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap flex-shrink-0">${{ Number(pt.base_price).toFixed(2) }} c/u</span>
              </button>
              <p v-if="filteredPhotoTypes.length === 0" class="text-xs sm:text-sm short:text-xs text-gray-400 dark:text-gray-500 text-center py-2 sm:py-3">Sin coincidencias</p>
            </div>
          </div>
        </transition>
      </div>

      <input
        type="number" min="1" v-model.number="line.quantity"
        @blur="onQuantityBlur"
        v-blur-on-enter
        enterkeyhint="done"
        :disabled="!line.photo_type_id || includedInPackage"
        class="form-control form-control-sm text-sm sm:text-lg short:text-sm p-2 sm:p-3 short:p-1.5 w-14 sm:w-28 short:w-12 text-center rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
      >
    </div>

    <div v-if="showFormatAndPaper" class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 short:gap-2">
      <div class="relative min-w-0" :data-format-dropdown="editorId">
        <button
          type="button"
          @click="onToggleFormat"
          @blur="onCloseFormat"
          class="w-full text-sm sm:text-base short:text-sm p-2 sm:p-2.5 short:p-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 flex items-center justify-between"
        >
          <span class="flex items-center gap-1.5 sm:gap-2 truncate">
            <span v-if="line.format === 'color'" class="relative isolate inline-block w-[18px] h-[18px] flex-shrink-0">
              <span class="absolute top-0 left-0 w-3 h-3 rounded-full bg-cyan-400 mix-blend-multiply"></span>
              <span class="absolute top-0 left-1.5 w-3 h-3 rounded-full bg-fuchsia-500 mix-blend-multiply"></span>
              <span class="absolute top-1.5 left-[3px] w-3 h-3 rounded-full bg-yellow-300 mix-blend-multiply"></span>
            </span>
            <span v-else class="inline-flex -space-x-1 flex-shrink-0">
              <span class="w-3.5 h-3.5 rounded-full bg-white border border-gray-400 dark:border-gray-500"></span>
              <span class="w-3.5 h-3.5 rounded-full bg-black border border-gray-300 dark:border-gray-400"></span>
            </span>
            <span class="truncate">{{ line.format === 'color' ? 'Color' : 'Blanco/negro' }}</span>
          </span>
          <i class="ti ti-chevron-down text-xs sm:text-sm flex-shrink-0"></i>
        </button>

        <transition name="dropdown">
          <div v-if="formatOpen" class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
            <button @mousedown.prevent="onSelectFormat('color')"
                    class="w-full px-2.5 sm:px-3 short:px-2 py-1.5 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-1.5 sm:gap-2 text-gray-800 dark:text-gray-200">
              <span class="relative isolate z-10 inline-block w-[18px] h-[18px]">
                <span class="absolute top-0 left-0 w-3 h-3 rounded-full bg-cyan-400 mix-blend-multiply"></span>
                <span class="absolute top-0 left-1.5 w-3 h-3 rounded-full bg-fuchsia-500 mix-blend-multiply"></span>
                <span class="absolute top-1.5 left-[3px] w-3 h-3 rounded-full bg-yellow-300 mix-blend-multiply"></span>
              </span>
              Color
            </button>
            <button @mousedown.prevent="onSelectFormat('bn')"
                    class="w-full px-2.5 sm:px-3 short:px-2 py-1.5 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-1.5 sm:gap-2 text-gray-800 dark:text-gray-200">
              <span class="inline-flex -space-x-1">
                <span class="w-3.5 h-3.5 rounded-full bg-white border border-gray-400 dark:border-gray-500"></span>
                <span class="w-3.5 h-3.5 rounded-full bg-black border border-gray-300 dark:border-gray-400"></span>
              </span>
              Blanco/negro
            </button>
          </div>
        </transition>
      </div>

      <div class="relative min-w-0" :data-paper-dropdown="editorId">
        <button
          type="button"
          @click="togglePaperDropdown"
          @blur="closePaperDropdown"
          class="w-full text-sm sm:text-base short:text-sm p-2 sm:p-2.5 short:p-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 flex items-center justify-between"
        >
          <span class="truncate text-left">{{ paperLabel }}</span>
          <i class="ti ti-chevron-down text-xs sm:text-sm flex-shrink-0"></i>
        </button>

        <transition name="dropdown">
          <div v-if="paperDropdownOpen" class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
            <div class="max-h-48 sm:max-h-56 short:max-h-32 overflow-y-auto">
              <button
                @mousedown.prevent="selectPaper(null)"
                class="w-full px-2.5 sm:px-3 short:px-2 py-1.5 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-1.5 sm:gap-2 text-gray-800 dark:text-gray-200"
                :class="!line.paper_type_id ? 'bg-blue-50 dark:bg-blue-900/20' : ''"
              >
                <i v-if="!line.paper_type_id" class="ti ti-check text-blue-600 dark:text-blue-400 text-sm flex-shrink-0"></i>
                <span v-else class="w-3 sm:w-4 flex-shrink-0"></span>
                <span class="truncate">Papel (opcional)</span>
              </button>
              <button
                v-if="line.paper_type_id && !paperTypes.some(p => p.id === line.paper_type_id)"
                @mousedown.prevent="selectPaper(line.paper_type_id)"
                class="w-full px-2.5 sm:px-3 short:px-2 py-1.5 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-sm bg-blue-50 dark:bg-blue-900/20 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-1.5 sm:gap-2 text-gray-800 dark:text-gray-200"
              >
                <i class="ti ti-check text-blue-600 dark:text-blue-400 text-sm flex-shrink-0"></i>
                <span class="truncate">{{ line.paper_type_name_snapshot || 'Papel eliminado' }}</span>
              </button>
              <button
                v-for="p in paperTypes"
                :key="p.id"
                @mousedown.prevent="selectPaper(p.id)"
                class="w-full px-2.5 sm:px-3 short:px-2 py-1.5 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-1.5 sm:gap-2 text-gray-800 dark:text-gray-200"
                :class="p.id === line.paper_type_id ? 'bg-blue-50 dark:bg-blue-900/20' : ''"
              >
                <i v-if="p.id === line.paper_type_id" class="ti ti-check text-blue-600 dark:text-blue-400 text-sm flex-shrink-0"></i>
                <span v-else class="w-3 sm:w-4 flex-shrink-0"></span>
                <span class="truncate">{{ p.name }}</span>
              </button>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PhotoTypeLineCard',
  props: {
    line: { type: Object, required: true },
    index: { type: Number, required: true },
    editorId: { type: String, required: true },
    paperTypes: { type: Array, default: () => [] },
    showFormatAndPaper: { type: Boolean, default: true },
    canRemove: { type: Boolean, default: true },
    isOpen: { type: Boolean, default: false },
    formatOpen: { type: Boolean, default: false },
    inputValue: { type: String, default: '' },
    highlightedIdx: { type: Number, default: 0 },
    filteredPhotoTypes: { type: Array, required: true },
    photoTypePrice: { type: Function, required: true },
    showError: { type: Boolean, default: false },
    includedInPackage: { type: Boolean, default: false },
    onOpen: { type: Function, required: true },
    onInput: { type: Function, required: true },
    onEsc: { type: Function, required: true },
    onMoveHighlight: { type: Function, required: true },
    onSelectHighlighted: { type: Function, required: true },
    onSelect: { type: Function, required: true },
    onClear: { type: Function, required: true },
    onFocusOut: { type: Function, required: true },
    onHighlight: { type: Function, required: true },
    onQuantityBlur: { type: Function, required: true },
    onRemove: { type: Function, required: true },
    onToggleFormat: { type: Function, required: true },
    onCloseFormat: { type: Function, required: true },
    onSelectFormat: { type: Function, required: true }
  },
  data() {
    return {
      paperDropdownOpen: false
    };
  },
  computed: {
    displayPrice() {
      if (this.includedInPackage) return 'Incluido';
      const locked = this.line.locked_unit_price;
      if (locked !== undefined && locked !== null) {
        return '$' + Number(locked).toFixed(2) + ' c/u';
      }
      return '$' + Number(this.photoTypePrice(this.line.photo_type_id)).toFixed(2) + ' c/u';
    },
    paperLabel() {
      if (!this.line.paper_type_id) return 'Papel (opcional)';
      const p = this.paperTypes.find(pt => pt.id === this.line.paper_type_id);
      if (p) return p.name;
      return this.line.paper_type_name_snapshot || 'Papel eliminado';
    }
  },
  methods: {
    togglePaperDropdown() {
      this.paperDropdownOpen = !this.paperDropdownOpen;
    },
    closePaperDropdown() {
      setTimeout(() => { this.paperDropdownOpen = false; }, 200);
    },
    selectPaper(id) {
      this.line.paper_type_id = id;
      this.paperDropdownOpen = false;
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