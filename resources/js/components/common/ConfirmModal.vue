<template>
  <Teleport to="body">
    <transition name="fade">
      <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-60 p-3">
        <div class="bg-yellow-50 dark:bg-amber-950 rounded-2xl shadow-xl w-full max-w-md p-6 flex flex-col items-center text-center">
          <div class="w-16 h-16 rounded-full bg-yellow-200 dark:bg-yellow-800/40 flex items-center justify-center mb-4">
            <i class="ti ti-alert-triangle text-3xl text-yellow-700 dark:text-yellow-300"></i>
          </div>

          <h3 class="text-xl font-bold text-yellow-800 dark:text-yellow-300 mb-2 w-full break-words">
            {{ title }}
          </h3>

          <p class="text-base text-yellow-700 dark:text-yellow-200/80 mb-6 w-full max-w-full break-words">
            {{ message }}
          </p>

          <div class="flex gap-3 w-full">
            <button
              @click="cancel"
              class="flex-1 px-4 py-2.5 text-sm font-medium text-yellow-800 dark:text-yellow-100 bg-yellow-200 dark:bg-yellow-800 rounded-lg hover:bg-yellow-300 dark:hover:bg-yellow-700 transition-colors"
            >
              {{ cancelText }}
            </button>
            <button
              @click="confirm"
              class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-yellow-600 dark:text-amber-950 dark:bg-yellow-500 rounded-lg hover:bg-yellow-700 dark:hover:bg-yellow-400 transition-colors"
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