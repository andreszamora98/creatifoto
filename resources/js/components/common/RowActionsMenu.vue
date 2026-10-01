<template>
  <div class="relative inline-block" @focusout="onFocusOut">
    <button
      type="button"
      @click="open = !open"
      class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
    >
      <i class="ti ti-dots-vertical text-lg"></i>
    </button>

    <div v-if="open" class="absolute right-0 z-20 mt-1 w-40 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg overflow-hidden">
      <button
        @mousedown.prevent="onEdit"
        class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
      >
        <i class="ti ti-edit text-base"></i> Editar
      </button>
      <button
        @mousedown.prevent="onDelete"
        class="w-full text-left px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2"
      >
        <i class="ti ti-trash text-base"></i> Eliminar
      </button>
    </div>
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