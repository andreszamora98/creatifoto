<template>
  <div class="relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-4 space-y-3">

    <div class="flex items-center justify-between">
      <span class="text-base font-semibold text-gray-500 dark:text-gray-400">
        Tipo {{ index + 1 }}
        <span v-if="includedInPackage" class="ml-2 text-xs font-medium text-purple-600 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/30 px-2 py-0.5 rounded">
          Incluido en paquete
        </span>
      </span>
      <button v-if="canRemove" type="button" @click="onRemove"
              class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 p-1.5 rounded-lg transition-colors">
        <i class="ti ti-trash text-xl"></i>
      </button>
    </div>

    <div class="grid grid-cols-[1fr_auto] gap-x-3 gap-y-1 items-end">
      <span></span>
      <span class="text-sm font-medium text-gray-500 dark:text-gray-400 text-center whitespace-nowrap">Cantidad</span>

      <div class="relative min-w-[10rem]" :data-photo-type-dropdown="editorId" @focusout="onFocusOut">
        <div
          class="w-full text-lg p-3 rounded-lg border text-gray-800 dark:text-gray-200 flex items-center gap-2"
          :class="showError && !line.photo_type_id
            ? 'border-red-500 dark:border-red-500'
            : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700'"
        >
          <i class="ti ti-photo text-xl text-gray-400 dark:text-gray-500 flex-shrink-0"></i>
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
          <span v-if="line.photo_type_id && !isOpen" class="text-sm font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap flex-shrink-0">
            {{ displayPrice }}
          </span>
          <button
            v-if="line.photo_type_id && !isOpen"
            type="button"
            @mousedown.prevent="onClear"
            class="text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 flex-shrink-0 p-1 rounded-lg transition-colors"
            title="Limpiar selección"
          >
            <i class="ti ti-x text-2xl"></i>
          </button>
        </div>

        <div v-if="isOpen" class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
          <div class="max-h-56 overflow-y-auto">
            <button
              v-for="(pt, pIdx) in filteredPhotoTypes"
              :key="pt.id"
              type="button"
              @mousedown.prevent="onSelect(pt.id)"
              @mouseover="onHighlight(pIdx)"
              class="w-full px-3 py-2.5 text-base flex items-center gap-2 text-gray-800 dark:text-gray-200"
              :class="pIdx === highlightedIdx ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
            >
              <i v-if="pt.id === line.photo_type_id" class="ti ti-check text-blue-600 dark:text-blue-400 text-base flex-shrink-0"></i>
              <span v-else class="w-4 flex-shrink-0"></span>
              <span class="flex-1 text-left truncate">{{ pt.name }}</span>
              <span class="text-gray-500 dark:text-gray-400 text-sm whitespace-nowrap">${{ Number(pt.base_price).toFixed(2) }} c/u</span>
            </button>
            <p v-if="filteredPhotoTypes.length === 0" class="text-sm text-gray-400 dark:text-gray-500 text-center py-3">Sin coincidencias</p>
          </div>
        </div>
      </div>

      <input
        type="number" min="1" v-model.number="line.quantity"
        @blur="onQuantityBlur"
        v-blur-on-enter
        enterkeyhint="done"
        :disabled="!line.photo_type_id || includedInPackage"
        class="form-control form-control-sm text-lg p-3 w-28 text-center rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
      >
    </div>

    <div v-if="showFormatAndPaper" class="grid grid-cols-2 gap-3">
      <div class="relative" :data-format-dropdown="editorId">
        <button
          type="button"
          @click="onToggleFormat"
          @blur="onCloseFormat"
          class="w-full text-base p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 flex items-center justify-between"
        >
          <span class="flex items-center gap-2 truncate">
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
          <i class="ti ti-chevron-down text-sm flex-shrink-0"></i>
        </button>

        <div v-if="formatOpen" class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
          <button @mousedown.prevent="onSelectFormat('color')"
                  class="w-full px-3 py-2.5 text-base hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-800 dark:text-gray-200">
            <span class="relative isolate z-10 inline-block w-[18px] h-[18px]">
              <span class="absolute top-0 left-0 w-3 h-3 rounded-full bg-cyan-400 mix-blend-multiply"></span>
              <span class="absolute top-0 left-1.5 w-3 h-3 rounded-full bg-fuchsia-500 mix-blend-multiply"></span>
              <span class="absolute top-1.5 left-[3px] w-3 h-3 rounded-full bg-yellow-300 mix-blend-multiply"></span>
            </span>
            Color
          </button>
          <button @mousedown.prevent="onSelectFormat('bn')"
                  class="w-full px-3 py-2.5 text-base hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-800 dark:text-gray-200">
            <span class="inline-flex -space-x-1">
              <span class="w-3.5 h-3.5 rounded-full bg-white border border-gray-400 dark:border-gray-500"></span>
              <span class="w-3.5 h-3.5 rounded-full bg-black border border-gray-300 dark:border-gray-400"></span>
            </span>
            Blanco/negro
          </button>
        </div>
      </div>

      <select v-model="line.paper_type_id"
              class="form-control form-control-sm text-base p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
        <option :value="null">Papel (opcional)</option>
        <!-- Papel histórico: ya no está en el catálogo pero la línea lo usa -->
        <option
          v-if="line.paper_type_id && !paperTypes.some(p => p.id === line.paper_type_id)"
          :value="line.paper_type_id"
          class="bg-white dark:bg-gray-800"
        >
          {{ line.paper_type_name_snapshot || 'Papel eliminado' }}
        </option>
        <option v-for="p in paperTypes" :key="p.id" :value="p.id" class="bg-white dark:bg-gray-800">{{ p.name }}</option>
      </select>
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
  computed: {
    displayPrice() {
      if (this.includedInPackage) return 'Incluido';
      const locked = this.line.locked_unit_price;
      if (locked !== undefined && locked !== null) {
        return '$' + Number(locked).toFixed(2) + ' c/u';
      }
      return '$' + Number(this.photoTypePrice(this.line.photo_type_id)).toFixed(2) + ' c/u';
    }
  }
};
</script>