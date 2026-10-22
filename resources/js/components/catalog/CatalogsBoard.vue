<template>
  <div class="h-full flex flex-col overflow-hidden">
    <div class="flex-shrink-0 mb-4 sm:mb-6 short:mb-2">
      <div class="flex items-center justify-between gap-3 flex-wrap">
        <div class="inline-flex bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            class="px-3 sm:px-4 py-2 short:py-1 rounded-md text-xs sm:text-sm short:text-xs font-medium transition-colors whitespace-nowrap"
            :class="activeTab === tab.key
              ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow'
              : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
          >
            <i :class="tab.icon" class="mr-1.5"></i>{{ tab.label }}
          </button>
        </div>

        <router-link
          to="/catalogos/caracteristicas-fotograficas"
          class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 short:py-1 rounded-lg text-xs sm:text-sm short:text-xs font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex-shrink-0"
        >
          <i class="ti ti-adjustments-horizontal text-base"></i>
          <span class="hidden sm:inline">Configurar características fotográficas</span>
          <span class="sm:hidden">Características</span>
        </router-link>
      </div>
    </div>

    <div class="flex-1 overflow-y-auto min-h-0">
      <PhotoTypesPanel v-if="activeTab === 'photo-types'" @notify="handleNotify" />
      <AdditionalServicesPanel v-else @notify="handleNotify" />
    </div>

    <transition name="fade-slide">
      <div v-if="showNotification" class="fixed top-3 right-3 left-3 sm:left-auto z-50 max-w-xs sm:w-full mx-auto sm:mx-0">
        <div class="flex items-center p-3 rounded-lg shadow-lg"
             :class="{
               'bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800': notificationType === 'success',
               'bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800': notificationType === 'error',
               'bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800': notificationType === 'info'
             }">
          <div class="flex-1 min-w-0">
            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-0.5 text-xs">{{ notificationTitle }}</h4>
            <p class="text-xs text-gray-600 dark:text-gray-400">{{ notificationMessage }}</p>
          </div>
          <button @click="showNotification = false" class="ml-2 flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" aria-label="Cerrar notificación">
            <i class="ti ti-x text-sm"></i>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { setPageTitle } from '../../store';
import PhotoTypesPanel from './PhotoTypesPanel.vue';
import AdditionalServicesPanel from './AdditionalServicesPanel.vue';

export default {
  name: 'CatalogsBoard',
  components: { PhotoTypesPanel, AdditionalServicesPanel },
  data() {
    return {
      tabs: [
        { key: 'photo-types', label: 'Tipos de fotografía', icon: 'ti ti-camera' },
        { key: 'additional-services', label: 'Servicios adicionales', icon: 'ti ti-tool' }
      ],
      showNotification: false,
      notificationTitle: '',
      notificationMessage: '',
      notificationType: 'info',
      notificationTimeout: null
    };
  },
  computed: {
    activeTab: {
      get() {
        const tab = this.$route.query.tab;
        return this.tabs.some(t => t.key === tab) ? tab : 'photo-types';
      },
      set(key) {
        if (key === this.activeTab) return;
        this.$router.replace({ query: { ...this.$route.query, tab: key } });
      }
    }
  },
  mounted() {
    setPageTitle('Catálogos');
  },
  beforeUnmount() {
    if (this.notificationTimeout) clearTimeout(this.notificationTimeout);
  },
  methods: {
    handleNotify({ title, message, type }) {
      this.notificationTitle = title;
      this.notificationMessage = message;
      this.notificationType = type;
      this.showNotification = true;
      if (this.notificationTimeout) clearTimeout(this.notificationTimeout);
      this.notificationTimeout = setTimeout(() => { this.showNotification = false; }, 3000);
    }
  }
};
</script>

<style scoped>
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.3s ease; }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateX(20px); }
</style>