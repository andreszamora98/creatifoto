<template>
  <Teleport to="body">
    <transition name="fade">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-0 sm:p-4 bg-black/40 backdrop-blur-[1px] modal-overlay"
      >
        <div class="modal-zoom w-full h-full sm:h-auto sm:max-w-3xl">
          <div class="bg-white dark:bg-gray-800 sm:rounded-lg shadow-xl w-full h-full sm:h-auto sm:max-h-[85vh] overflow-hidden flex flex-col isolate">

            <div class="relative p-3 sm:p-5 border-b border-gray-200 dark:border-gray-700 flex items-start justify-between gap-3 flex-shrink-0 bg-white dark:bg-gray-800">
              <div class="min-w-0 flex-1">
                <h3 class="text-lg sm:text-2xl font-semibold text-gray-800 dark:text-gray-200 break-words">
                  <i class="ti ti-info-circle mr-2"></i>Detalles del paquete:<span v-if="packageName"> <span class="ml-1 text-blue-600 dark:text-blue-400 italic">{{ packageName }}</span></span>
                </h3>
                <p v-if="schoolName" class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-0.5 sm:mt-1 break-words">
                  {{ schoolName }}
                </p>
              </div>

              <button
                @click="$emit('close')"
                aria-label="Cerrar"
                class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200 transition-colors"
              >
                <i class="ti ti-x text-xl sm:text-2xl"></i>
              </button>
            </div>

            <div class="p-3 sm:p-5 space-y-3 sm:space-y-4 overflow-y-auto flex-1 min-h-0">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <div class="rounded-lg p-3 sm:p-5 bg-gray-50 dark:bg-gray-700/40 border-2 border-gray-300 dark:border-gray-600 shadow-sm" style="border-left-width: 4px;">
                  <h4 class="font-semibold mb-2 sm:mb-3 text-base sm:text-lg flex items-center text-gray-700 dark:text-gray-300">
                    <i class="ti ti-woman mr-2 text-lg sm:text-xl"></i>Mujeres
                  </h4>
                  <p v-if="womenFeatures" class="text-gray-700 dark:text-gray-300 text-sm sm:text-base whitespace-pre-wrap break-words">{{ womenFeatures }}</p>
                  <p v-else class="text-gray-400 dark:text-gray-500 italic text-sm sm:text-base">Sin características.</p>
                </div>

                <div class="rounded-lg p-3 sm:p-5 bg-gray-50 dark:bg-gray-700/40 border-2 border-gray-300 dark:border-gray-600 shadow-sm" style="border-left-width: 4px;">
                  <h4 class="font-semibold mb-2 sm:mb-3 text-base sm:text-lg flex items-center text-gray-700 dark:text-gray-300">
                    <i class="ti ti-man mr-2 text-lg sm:text-xl"></i>Hombres
                  </h4>
                  <p v-if="menFeatures" class="text-gray-700 dark:text-gray-300 text-sm sm:text-base whitespace-pre-wrap break-words">{{ menFeatures }}</p>
                  <p v-else class="text-gray-400 dark:text-gray-500 italic text-sm sm:text-base">Sin características.</p>
                </div>
              </div>

              <div class="rounded-lg p-3 sm:p-5 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 shadow-sm">
                <h4 class="font-semibold text-gray-800 dark:text-gray-100 text-base sm:text-lg flex items-center mb-2 sm:mb-3">
                  <i class="ti ti-notes mr-2 text-lg sm:text-xl"></i>Observaciones adicionales
                </h4>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                  <p v-if="packageNotes" class="text-gray-700 dark:text-gray-300 text-sm sm:text-base whitespace-pre-wrap break-words">{{ packageNotes }}</p>
                  <p v-else class="text-gray-400 dark:text-gray-500 italic text-sm sm:text-base">Sin observaciones registradas.</p>
                </div>
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
.modal-overlay {
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-zoom {
  width: 100%;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>