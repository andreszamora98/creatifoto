<template>
  <Teleport to="body">
    <transition name="fade">
      <div v-if="show" class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-60 p-3 sm:p-4 short:p-2">
        <div class="bg-green-50 dark:bg-green-950 rounded-2xl short:rounded-lg shadow-xl w-full max-w-sm p-4 sm:p-6 short:p-3 flex flex-col items-center text-center">
          <div class="w-12 h-12 sm:w-16 sm:h-16 short:w-10 short:h-10 rounded-full bg-green-200 dark:bg-green-800/40 flex items-center justify-center mb-2.5 sm:mb-4 short:mb-2">
            <i class="ti ti-check text-3xl sm:text-4xl short:text-2xl text-green-700 dark:text-green-300"></i>
          </div>

          <h3 class="text-base sm:text-xl short:text-sm font-bold text-green-800 dark:text-green-300 mb-1.5 sm:mb-2 short:mb-1 w-full break-words">
            {{ title }}
          </h3>

          <p class="text-sm sm:text-base short:text-xs text-green-700 dark:text-green-200/80 w-full break-words">
            {{ message }}
          </p>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script>
export default {
  name: 'SuccessModal',
  props: {
    show: { type: Boolean, default: false },
    title: { type: String, default: '¡Éxito!' },
    message: { type: String, default: 'La operación se realizó correctamente.' },
    duration: { type: Number, default: 2000 },
  },
  emits: ['closed'],
  watch: {
    show(newVal) {
      if (newVal) {
        clearTimeout(this._timer);
        this._timer = setTimeout(() => {
          this.$emit('closed');
        }, this.duration);
      }
    }
  },
  beforeUnmount() {
    clearTimeout(this._timer);
  },
  data() {
    return {
      _timer: null,
    };
  },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>