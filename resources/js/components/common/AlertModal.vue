<template>
  <Teleport to="body">
    <transition name="fade">
      <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-60 p-3">
        <div class="bg-yellow-50 rounded-2xl shadow-xl w-full max-w-md p-6 flex flex-col items-center text-center">
          <!-- Icono de advertencia -->
          <div class="w-16 h-16 rounded-full bg-yellow-200 flex items-center justify-center mb-4">
            <i class="ti ti-alert-circle text-3xl text-yellow-700"></i>
          </div>

          <!-- Título -->
          <h3 class="text-xl font-bold text-yellow-800 mb-2">
            {{ title }}
          </h3>

          <!-- Mensaje -->
          <p class="text-base text-yellow-700 mb-6 max-w-xs">
            {{ message }}
          </p>

          <!-- Botón único -->
          <button
            @click="acknowledge"
            class="w-full px-4 py-2.5 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 transition-colors"
          >
            {{ buttonText }}
          </button>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script>
export default {
  name: 'AlertModal',
  props: {
    show: { type: Boolean, default: false },
    title: { type: String, default: 'Faltan datos obligatorios' },
    message: { type: String, default: 'Por favor, completa todos los campos marcados con * antes de continuar.' },
    buttonText: { type: String, default: 'Entendido' },
  },
  emits: ['closed'],
  methods: {
    acknowledge() {
      this.$emit('closed');
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