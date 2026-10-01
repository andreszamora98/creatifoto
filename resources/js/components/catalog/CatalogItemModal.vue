<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-6" @click="$emit('close')">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md p-6" @click.stop>
        <div class="flex justify-between items-center mb-5">
          <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200">{{ title }}</h3>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
          >
            <i class="ti ti-x text-2xl"></i>
          </button>
        </div>

        <div class="space-y-4">
          <div>
            <label class="text-sm font-medium block mb-1.5 text-gray-700 dark:text-gray-300">{{ nameLabel }} <span class="text-red-500">*</span></label>
            <input
              v-model="name"
              type="text"
              v-blur-on-enter
              enterkeyhint="next"
              :placeholder="namePlaceholder"
              class="form-control text-sm p-2.5 rounded-lg border w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
              :class="showErrors && !name.trim() ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
            >
            <p v-if="showErrors && !name.trim()" class="text-xs text-red-600 dark:text-red-400 mt-1 font-medium">Campo obligatorio*</p>
          </div>

          <div>
            <label class="text-sm font-medium block mb-1.5 text-gray-700 dark:text-gray-300">{{ priceLabel }} <span class="text-red-500">*</span></label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
              <input
                v-model.number="price"
                type="number"
                min="0"
                step="0.01"
                v-blur-on-enter
                :enterkeyhint="showBaseQuantity ? 'next' : 'done'"
                placeholder="0.00"
                class="form-control text-sm p-2.5 pl-7 rounded-lg border w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                :class="showErrors && (price === null || price < 0) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
              >
            </div>
            <p v-if="showErrors && (price === null || price < 0)" class="text-xs text-red-600 dark:text-red-400 mt-1 font-medium">Campo obligatorio*</p>
          </div>

          <div v-if="showBaseQuantity">
            <label class="text-sm font-medium block mb-1.5 text-gray-700 dark:text-gray-300">{{ baseQuantityLabel }} <span class="text-red-500">*</span></label>
            <input
              v-model.number="baseQuantity"
              type="number"
              min="1"
              step="1"
              v-blur-on-enter
              enterkeyhint="done"
              placeholder="1"
              class="form-control text-sm p-2.5 rounded-lg border w-full bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
              :class="showErrors && (baseQuantity === null || baseQuantity < 1) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
            >
            <p v-if="showErrors && (baseQuantity === null || baseQuantity < 1)" class="text-xs text-red-600 dark:text-red-400 mt-1 font-medium">Campo obligatorio*</p>
            <p v-else class="text-xs text-gray-400 dark:text-gray-500 mt-1">Cantidad que se precarga al agregar este tipo a una orden o cotización.</p>
          </div>
        </div>

        <div class="flex gap-3 mt-6">
          <button @click="$emit('close')" class="flex-1 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Cancelar</button>
          <button @click="submit" :disabled="submitting" class="flex-1 py-2.5 rounded-lg bg-blue-500 hover:bg-blue-600 text-white disabled:opacity-50">
            {{ submitting ? 'Guardando...' : (editing ? 'Actualizar' : 'Agregar') }}
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: 'CatalogItemModal',
  props: {
    show: { type: Boolean, default: false },
    editing: { type: Boolean, default: false },
    title: { type: String, required: true },
    nameLabel: { type: String, default: 'Nombre' },
    namePlaceholder: { type: String, default: '' },
    priceLabel: { type: String, default: 'Precio' },
    submitting: { type: Boolean, default: false },
    initialName: { type: String, default: '' },
    initialPrice: { type: [Number, null], default: null },
    showBaseQuantity: { type: Boolean, default: false },
    baseQuantityLabel: { type: String, default: 'Cantidad base de fotografías' },
    initialBaseQuantity: { type: [Number, null], default: null }
  },
  emits: ['close', 'save'],
  data() {
    return {
      name: '',
      price: null,
      baseQuantity: null,
      showErrors: false
    };
  },
  watch: {
    show(val) {
      if (val) {
        this.name = this.initialName || '';
        this.price = this.initialPrice ?? null;
        this.baseQuantity = this.showBaseQuantity ? (this.initialBaseQuantity ?? 1) : null;
        this.showErrors = false;
      }
    }
  },
  methods: {
    submit() {
      this.showErrors = true;
      if (!this.name.trim() || this.price === null || this.price < 0) return;
      if (this.showBaseQuantity && (this.baseQuantity === null || this.baseQuantity < 1)) return;

      const payload = { name: this.name.trim(), price: this.price };
      if (this.showBaseQuantity) {
        payload.baseQuantity = this.baseQuantity;
      }
      this.$emit('save', payload);
    }
  }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>