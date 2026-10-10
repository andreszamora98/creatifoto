<template>
  <Teleport to="body">
    <transition name="fade">
      <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-60 p-3 sm:p-4 short:p-2">
        <div class="bg-yellow-50 dark:bg-amber-950 rounded-2xl short:rounded-lg shadow-xl w-full max-w-md p-4 sm:p-6 short:p-3 flex flex-col items-center text-center">
          <div class="w-12 h-12 sm:w-16 sm:h-16 short:w-10 short:h-10 rounded-full bg-yellow-200 dark:bg-yellow-800/40 flex items-center justify-center mb-2.5 sm:mb-4 short:mb-2">
            <i class="ti ti-alert-triangle text-2xl sm:text-3xl short:text-xl text-yellow-700 dark:text-yellow-300"></i>
          </div>

          <h3 class="text-base sm:text-xl short:text-sm font-bold text-yellow-800 dark:text-yellow-300 mb-1.5 sm:mb-2 short:mb-1 w-full break-words">
            {{ title }}
          </h3>

          <p class="text-sm sm:text-base short:text-xs text-yellow-700 dark:text-yellow-200/80 mb-4 sm:mb-6 short:mb-3 w-full max-w-full break-words">
            {{ message }}
          </p>

          <div class="flex gap-2 sm:gap-3 short:gap-2 w-full">
            <button
              @click="cancel"
              class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 short:py-1.5 text-xs sm:text-sm short:text-xs font-medium text-yellow-800 dark:text-yellow-100 bg-yellow-200 dark:bg-yellow-800 rounded-lg hover:bg-yellow-300 dark:hover:bg-yellow-700 transition-colors"
            >
              {{ cancelText }}
            </button>
            <button
              @click="confirm"
              class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 short:py-1.5 text-xs sm:text-sm short:text-xs font-medium text-white bg-yellow-600 dark:text-amber-950 dark:bg-yellow-500 rounded-lg hover:bg-yellow-700 dark:hover:bg-yellow-400 transition-colors"
            >
              {{ confirmText }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script>
export default {
  name: 'ConfirmModal',
  props: {
    show: { type: Boolean, default: false },
    title: { type: String, default: '¿Estás seguro?' },
    message: { type: String, default: '¿Deseas continuar con esta acción?' },
    confirmText: { type: String, default: 'Confirmar' },
    cancelText: { type: String, default: 'Cancelar' },
  },
  emits: ['confirm', 'cancel'],
  methods: {
    confirm() {
      this.$emit('confirm');
    },
    cancel() {
      this.$emit('cancel');
    },
  },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>