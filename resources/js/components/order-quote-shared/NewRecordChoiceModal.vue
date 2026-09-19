<template>
  <transition name="modal-fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-6">
      <div class="modal-box bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-lg p-8 relative">
        <button
          @click="$emit('close')"
          class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
        >
          <i class="ti ti-x text-2xl"></i>
        </button>

        <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6 pr-8">¿Qué deseas crear?</h3>
        <div class="grid grid-cols-1 gap-4">
          <button @click="$emit('choose', 'order')"
                  class="text-left p-5 rounded-lg border-2 border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
            <span class="block text-xl font-semibold text-blue-700 dark:text-blue-300">
              <i class="ti ti-shopping-cart mr-2"></i>Nueva orden
            </span>
            <span class="block text-base text-gray-500 dark:text-gray-400 mt-1">Crear un trabajo que será procesado.</span>
          </button>
          <button @click="$emit('choose', 'quote')"
                  class="text-left p-5 rounded-lg border-2 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <span class="block text-xl font-semibold text-gray-700 dark:text-gray-300">
              <i class="ti ti-file-description mr-2"></i>Nueva cotización
            </span>
            <span class="block text-base text-gray-500 dark:text-gray-400 mt-1">Crear una propuesta para un cliente.</span>
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: 'NewRecordChoiceModal',
  props: { show: { type: Boolean, default: false } },
  emits: ['close', 'choose']
};
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.25s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-active .modal-box,
.modal-fade-leave-active .modal-box {
  transition: transform 0.25s ease, opacity 0.25s ease;
}
.modal-fade-enter-from .modal-box,
.modal-fade-leave-to .modal-box {
  transform: scale(0.95);
  opacity: 0;
}
</style>