<template>
  <Teleport to="body">
    <transition name="fade">
      <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-60 p-3">
        <div class="bg-yellow-50 rounded-2xl shadow-xl w-full max-w-md p-6 flex flex-col items-center text-center">
          <!-- Icono de advertencia -->
          <div class="w-16 h-16 rounded-full bg-yellow-200 flex items-center justify-center mb-4">
            <i class="ti ti-alert-triangle text-3xl text-yellow-700"></i>
          </div>

          <!-- Título -->
          <h3 class="text-xl font-bold text-yellow-800 mb-2">
            {{ title }}
          </h3>

          <!-- Mensaje -->
          <p class="text-base text-yellow-700 mb-6 max-w-xs">
            {{ message }}
          </p>

          <!-- Botones -->
          <div class="flex gap-3 w-full">
            <button
              @click="cancel"
              class="flex-1 px-4 py-2.5 text-sm font-medium text-yellow-800 bg-yellow-200 rounded-lg hover:bg-yellow-300 transition-colors"
            >
              {{ cancelText }}
            </button>
            <button
              @click="confirm"
              class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 transition-colors"
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