<template>
  <Teleport to="body">
    <transition name="fade">
      <div v-if="show" class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-60 p-4">
        <div class="bg-green-50 rounded-2xl shadow-xl w-full max-w-sm p-6 flex flex-col items-center text-center">
          <div class="w-16 h-16 rounded-full bg-green-200 flex items-center justify-center mb-4">
            <i class="ti ti-check text-4xl text-green-700"></i>
          </div>

          <h3 class="text-xl font-bold text-green-800 mb-2">
            {{ title }}
          </h3>

          <p class="text-base text-green-700">
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