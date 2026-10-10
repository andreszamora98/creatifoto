<template>
  <transition name="modal-fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-4 sm:p-6 short:p-2">
      <div class="modal-box bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full h-auto max-h-[92dvh] sm:max-w-lg short:max-h-full short:max-w-3xl p-4 sm:p-8 short:p-3 relative flex flex-col overflow-y-auto overscroll-contain">
        <button
          @click="$emit('close')"
          aria-label="Cerrar"
          class="absolute top-3 right-3 sm:top-4 sm:right-4 short:top-2 short:right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2 short:p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
        >
          <i class="ti ti-x text-xl sm:text-2xl short:text-lg"></i>
        </button>

        <h3 class="text-lg sm:text-2xl short:text-base font-bold text-gray-800 dark:text-gray-200 mb-4 sm:mb-6 short:mb-3 pr-10">¿Qué deseas crear?</h3>

        <div class="grid grid-cols-1 gap-3 sm:gap-4 short:grid-cols-2 short:gap-3">
          <button @click="$emit('choose', 'order')"
                  class="text-left p-3 sm:p-5 short:p-3 rounded-lg border-2 border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 active:bg-blue-100 dark:active:bg-blue-900/30 transition-colors">
            <span class="block text-base sm:text-xl short:text-base font-semibold text-blue-700 dark:text-blue-300">
              <i class="ti ti-shopping-cart mr-1.5 sm:mr-2"></i>Nueva orden
            </span>
            <span class="block text-xs sm:text-base short:text-xs text-gray-500 dark:text-gray-400 mt-0.5 sm:mt-1">Crear un trabajo que será procesado.</span>
          </button>
          <button @click="$emit('choose', 'quote')"
                  class="text-left p-3 sm:p-5 short:p-3 rounded-lg border-2 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 active:bg-gray-100 dark:active:bg-gray-600 transition-colors">
            <span class="block text-base sm:text-xl short:text-base font-semibold text-gray-700 dark:text-gray-300">
              <i class="ti ti-file-description mr-1.5 sm:mr-2"></i>Nueva cotización
            </span>
            <span class="block text-xs sm:text-base short:text-xs text-gray-500 dark:text-gray-400 mt-0.5 sm:mt-1">Crear una propuesta para un cliente.</span>
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