<template>
  <div class="relative inline-block" @focusout="onFocusOut">
    <button
      type="button"
      @click="open = !open"
      aria-label="Acciones"
      class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1.5 short:p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
    >
      <i class="ti ti-dots-vertical text-lg short:text-base"></i>
    </button>

    <transition name="dropdown">
      <div v-if="open" class="absolute right-0 z-20 mt-1 w-40 short:w-36 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg overflow-hidden">
        <button
          @mousedown.prevent="onEdit"
          class="w-full text-left px-3 sm:px-4 py-2 sm:py-2.5 short:py-1.5 text-sm short:text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 short:gap-1.5"
        >
          <i class="ti ti-edit text-base short:text-sm"></i> Editar
        </button>
        <button
          @mousedown.prevent="onDelete"
          class="w-full text-left px-3 sm:px-4 py-2 sm:py-2.5 short:py-1.5 text-sm short:text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2 short:gap-1.5"
        >
          <i class="ti ti-trash text-base short:text-sm"></i> Eliminar
        </button>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  name: 'RowActionsMenu',
  emits: ['edit', 'delete'],
  data() {
    return { open: false };
  },
  methods: {
    onFocusOut(event) {
      if (!event.currentTarget.contains(event.relatedTarget)) this.open = false;
    },
    onEdit() {
      this.open = false;
      this.$emit('edit');
    },
    onDelete() {
      this.open = false;
      this.$emit('delete');
    }
  }
};
</script>

<style scoped>
.dropdown-enter-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
  transform-origin: top right;
}
.dropdown-leave-active {
  transition: opacity 0.14s ease, transform 0.14s ease;
  transform-origin: top right;
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