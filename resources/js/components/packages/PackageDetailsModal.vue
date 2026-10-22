<template>
  <Teleport to="body">
    <transition name="modal-fade">
      <div
        v-if="show"
        class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-4 sm:p-6 short:p-2"
      >
        <div
          class="modal-box bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full h-auto max-h-[92dvh] sm:max-w-3xl short:max-h-full short:max-w-3xl relative flex flex-col overflow-hidden"
        >
          <!-- Encabezado -->
          <div class="relative p-4 sm:p-5 short:p-3 pr-14 sm:pr-16 short:pr-12 border-b border-gray-200 dark:border-gray-700 flex-shrink-0 bg-white dark:bg-gray-800">
            <h3 class="text-lg sm:text-2xl short:text-base font-semibold text-gray-800 dark:text-gray-200 break-words">
              <i class="ti ti-info-circle mr-2"></i>Detalles del paquete:<span v-if="packageName"> <span class="ml-1 text-blue-600 dark:text-blue-400 italic">{{ packageName }}</span></span>
            </h3>
            <p v-if="schoolName" class="text-sm sm:text-base short:text-xs text-gray-600 dark:text-gray-400 mt-0.5 sm:mt-1 break-words">
              {{ schoolName }}
            </p>

            <button
              @click="$emit('close')"
              aria-label="Cerrar"
              class="absolute top-3 right-3 sm:top-4 sm:right-4 short:top-2 short:right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2 short:p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200 transition-colors"
            >
              <i class="ti ti-x text-xl sm:text-2xl short:text-lg"></i>
            </button>
          </div>

          <!-- Contenido -->
          <div class="p-4 sm:p-5 short:p-3 space-y-3 sm:space-y-4 short:space-y-2 overflow-y-auto overscroll-contain flex-1 min-h-0">
            <div class="grid grid-cols-1 sm:grid-cols-2 short:grid-cols-2 gap-3 sm:gap-4 short:gap-3">
              <!-- Mujeres (rosa sutil) -->
              <div
                class="rounded-lg p-3 sm:p-5 short:p-3 bg-pink-50/60 dark:bg-pink-900/10 border border-pink-200 dark:border-pink-900/40 border-l-4 border-l-pink-300 dark:border-l-pink-500/60 shadow-sm"
              >
                <h4 class="font-semibold mb-2 sm:mb-3 short:mb-1.5 text-base sm:text-lg short:text-sm flex items-center text-pink-700 dark:text-pink-300">
                  <i class="ti ti-woman mr-2 text-lg sm:text-xl short:text-base"></i>Mujeres
                </h4>
                <p v-if="womenFeatures" class="text-gray-700 dark:text-gray-300 text-sm sm:text-base short:text-xs whitespace-pre-wrap break-words">{{ womenFeatures }}</p>
                <p v-else class="text-gray-400 dark:text-gray-500 italic text-sm sm:text-base short:text-xs">Sin características.</p>
              </div>

              <!-- Hombres (azul sutil) -->
              <div
                class="rounded-lg p-3 sm:p-5 short:p-3 bg-blue-50/60 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-900/40 border-l-4 border-l-blue-300 dark:border-l-blue-500/60 shadow-sm"
              >
                <h4 class="font-semibold mb-2 sm:mb-3 short:mb-1.5 text-base sm:text-lg short:text-sm flex items-center text-blue-700 dark:text-blue-300">
                  <i class="ti ti-man mr-2 text-lg sm:text-xl short:text-base"></i>Hombres
                </h4>
                <p v-if="menFeatures" class="text-gray-700 dark:text-gray-300 text-sm sm:text-base short:text-xs whitespace-pre-wrap break-words">{{ menFeatures }}</p>
                <p v-else class="text-gray-400 dark:text-gray-500 italic text-sm sm:text-base short:text-xs">Sin características.</p>
              </div>
            </div>

            <!-- Observaciones -->
            <div class="rounded-lg p-3 sm:p-5 short:p-3 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 shadow-sm">
              <h4 class="font-semibold text-gray-800 dark:text-gray-100 text-base sm:text-lg short:text-sm flex items-center mb-2 sm:mb-3 short:mb-1.5">
                <i class="ti ti-notes mr-2 text-lg sm:text-xl short:text-base"></i>Observaciones adicionales
              </h4>
              <div class="bg-white dark:bg-gray-800 rounded-lg p-3 short:p-2 border border-gray-200 dark:border-gray-600">
                <p v-if="packageNotes" class="text-gray-700 dark:text-gray-300 text-sm sm:text-base short:text-xs whitespace-pre-wrap break-words">{{ packageNotes }}</p>
                <p v-else class="text-gray-400 dark:text-gray-500 italic text-sm sm:text-base short:text-xs">Sin observaciones registradas.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script>
export default {
  name: 'PackageDetailsModal',
  props: {
    show: { type: Boolean, default: false },
    package: { type: Object, default: null }
  },
  emits: ['close'],
  computed: {
    packageName() {
      return this.package?.name || '';
    },
    schoolName() {
      return this.package?.school?.name || '';
    },
    womenFeatures() {
      return String(this.package?.women_features_text || '').trim();
    },
    menFeatures() {
      return String(this.package?.men_features_text || '').trim();
    },
    packageNotes() {
      return String(this.package?.package_notes || '').trim();
    }
  }
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