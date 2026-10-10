<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-3 sm:p-6 short:p-0" @click="$emit('close')">
      <div class="bg-white dark:bg-gray-800 shadow-xl w-full max-w-md h-auto max-h-[92dvh] short:h-full short:max-h-full rounded-lg short:rounded-none p-4 sm:p-6 short:p-3 flex flex-col overflow-hidden" @click.stop>
        <div class="flex justify-between items-center mb-3 sm:mb-5 short:mb-2 gap-2 flex-shrink-0">
          <div class="flex items-center gap-2 sm:gap-3 short:gap-2 min-w-0">
            <i :class="[icon, 'text-lg sm:text-2xl short:text-base text-blue-500 dark:text-blue-400 shrink-0']"></i>
            <h3 class="text-base sm:text-xl short:text-sm font-bold text-gray-800 dark:text-gray-200 truncate">{{ title }}</h3>
          </div>
          <button
            @click="$emit('close')"
            aria-label="Cerrar"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1.5 sm:p-2 short:p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex-shrink-0"
          >
            <i class="ti ti-x text-lg sm:text-xl short:text-base"></i>
          </button>
        </div>

        <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain space-y-3 sm:space-y-4 short:space-y-2">
          <div>
            <label class="text-sm sm:text-base short:text-xs font-medium block mb-1.5 sm:mb-2 short:mb-1 text-gray-700 dark:text-gray-300">{{ nameLabel }} <span class="text-red-500">*</span></label>
            <input
              ref="nameInput"
              :value="name"
              @input="syncFields"
              @compositionupdate="syncFields"
              @compositionend="syncFields"
              @change="syncFields"
              @keyup="syncFields"
              type="text"
              v-blur-on-enter
              enterkeyhint="next"
              autocomplete="off"
              autocorrect="off"
              autocapitalize="sentences"
              spellcheck="false"
              :placeholder="namePlaceholder"
              class="form-control text-sm sm:text-base short:text-sm p-2.5 sm:p-3 short:p-2 rounded-lg border w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
              :class="showErrors && !name.trim() ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
            >
            <p v-if="showErrors && !name.trim()" class="text-xs sm:text-sm short:text-xs text-red-600 dark:text-red-400 mt-1 font-medium">Campo obligatorio*</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 short:gap-2">
            <div>
              <label class="text-sm sm:text-base short:text-xs font-medium block mb-1.5 sm:mb-2 short:mb-1 text-gray-700 dark:text-gray-300">{{ priceLabel }} <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute left-2.5 sm:left-3 short:left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm short:text-xs pointer-events-none">$</span>
                <input
                  ref="priceInput"
                  :value="priceText"
                  @input="syncFields"
                  @change="syncFields"
                  @keyup="syncFields"
                  type="number"
                  min="0"
                  step="0.01"
                  inputmode="decimal"
                  v-blur-on-enter
                  enterkeyhint="next"
                  placeholder="0.00"
                  class="form-control text-sm sm:text-base short:text-sm p-2.5 sm:p-3 short:p-2 pl-7 sm:pl-8 short:pl-6 rounded-lg border w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                  :class="showErrors && (price === null || price < 0) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                >
              </div>
              <p v-if="showErrors && (price === null || price < 0)" class="text-xs sm:text-sm short:text-xs text-red-600 dark:text-red-400 mt-1 font-medium">Campo obligatorio*</p>
            </div>

            <div v-if="showBaseQuantity">
              <label class="text-sm sm:text-base short:text-xs font-medium block mb-1.5 sm:mb-2 short:mb-1 text-gray-700 dark:text-gray-300">{{ baseQuantityLabel }} <span class="text-red-500">*</span></label>
              <input
                ref="quantityInput"
                :value="quantityText"
                @input="syncFields"
                @change="syncFields"
                @keyup="syncFields"
                type="number"
                min="1"
                step="1"
                inputmode="numeric"
                v-blur-on-enter
                enterkeyhint="done"
                placeholder="1"
                class="form-control text-sm sm:text-base short:text-sm p-2.5 sm:p-3 short:p-2 rounded-lg border w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                :class="showErrors && (baseQuantity === null || baseQuantity < 1) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
              >
              <p v-if="showErrors && (baseQuantity === null || baseQuantity < 1)" class="text-xs sm:text-sm short:text-xs text-red-600 dark:text-red-400 mt-1 font-medium">Campo obligatorio*</p>
            </div>
          </div>
        </div>

        <div class="flex gap-2 sm:gap-3 short:gap-2 mt-4 sm:mt-6 short:mt-3 flex-shrink-0"
             style="padding-bottom: max(0px, env(safe-area-inset-bottom));">
          <button
            @mousedown.prevent
            @click="$emit('close')"
            class="flex-1 py-2 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
          >
            Cancelar
          </button>
          <button
            @mousedown.prevent
            @click="submit"
            :disabled="submitting || !canSubmit"
            class="flex-1 py-2 sm:py-2.5 short:py-1.5 text-sm sm:text-base short:text-sm rounded-lg bg-blue-500 hover:bg-blue-600 text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            {{ submitting ? 'Guardando...' : (editing ? 'Actualizar' : 'Agregar') }}
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
const parseNumber = (value) => {
  const raw = String(value ?? '').trim().replace(',', '.');
  if (raw === '') return null;
  const n = Number(raw);
  return Number.isFinite(n) ? n : null;
};

export default {
  name: 'CatalogItemModal',
  props: {
    show: { type: Boolean, default: false },
    editing: { type: Boolean, default: false },
    title: { type: String, required: true },
    icon: { type: String, default: 'ti ti-camera' },
    nameLabel: { type: String, default: 'Nombre' },
    namePlaceholder: { type: String, default: '' },
    priceLabel: { type: String, default: 'Precio' },
    submitting: { type: Boolean, default: false },
    initialName: { type: String, default: '' },
    initialPrice: { type: [Number, null], default: null },
    showBaseQuantity: { type: Boolean, default: false },
    baseQuantityLabel: { type: String, default: 'Cantidad base' },
    initialBaseQuantity: { type: [Number, null], default: null }
  },
  emits: ['close', 'save'],
  data() {
    return {
      name: '',
      priceText: '',
      quantityText: '',
      showErrors: false
    };
  },
  computed: {
    price() {
      return parseNumber(this.priceText);
    },
    baseQuantity() {
      return parseNumber(this.quantityText);
    },
    isValid() {
      if (!this.name.trim()) return false;
      if (this.price === null || this.price < 0) return false;
      if (this.showBaseQuantity && (this.baseQuantity === null || this.baseQuantity < 1)) return false;
      return true;
    },
    baseline() {
      return {
        name: (this.initialName || '').trim(),
        price: parseNumber(this.initialPrice),
        baseQuantity: this.showBaseQuantity ? parseNumber(this.initialBaseQuantity ?? 1) : null
      };
    },
    hasChanges() {
      const base = this.baseline;
      if (this.name.trim() !== base.name) return true;
      if (this.price !== base.price) return true;
      if (this.showBaseQuantity && this.baseQuantity !== base.baseQuantity) return true;
      return false;
    },
    canSubmit() {
      return this.isValid && (!this.editing || this.hasChanges);
    }
  },
  watch: {
    show(val) {
      if (val) {
        this.name = this.initialName || '';
        this.priceText = String(this.initialPrice ?? '');
        this.quantityText = this.showBaseQuantity ? String(this.initialBaseQuantity ?? 1) : '';
        this.showErrors = false;
      }
    }
  },
  methods: {
    syncFields() {
      const nameEl = this.$refs.nameInput;
      if (nameEl && nameEl.value !== this.name) this.name = nameEl.value;
      const priceEl = this.$refs.priceInput;
      if (priceEl && priceEl.value !== this.priceText) this.priceText = priceEl.value;
      const quantityEl = this.$refs.quantityInput;
      if (quantityEl && quantityEl.value !== this.quantityText) this.quantityText = quantityEl.value;
    },
    submit() {
      this.syncFields();
      this.showErrors = true;
      if (this.submitting || !this.canSubmit) return;
      const payload = { name: this.name.trim(), price: this.price };
      if (this.showBaseQuantity) payload.baseQuantity = this.baseQuantity;
      this.$emit('save', payload);
    }
  }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>