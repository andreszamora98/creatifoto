<template>
  <div class="p-6">

    <div class="inline-flex bg-gray-100 dark:bg-gray-800 rounded-lg p-1 mb-6">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="activeTab = tab.key"
        class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
        :class="activeTab === tab.key
          ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow'
          : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
      >
        <i :class="tab.icon" class="mr-1.5"></i>{{ tab.label }}
      </button>
    </div>

    <PhotoTypesPanel v-if="activeTab === 'photo-types'" @notify="handleNotify" />
    <AdditionalServicesPanel v-else @notify="handleNotify" />

    <transition name="fade-slide">
      <div v-if="showNotification" class="fixed top-3 right-3 z-50 max-w-xs w-full">
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
          <button @click="showNotification = false" class="ml-2 flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
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
      activeTab: 'photo-types',
      tabs: [
        { key: 'photo-types', label: 'Tipos de fotografía', icon: 'ti ti-photo' },
        { key: 'additional-services', label: 'Servicios adicionales', icon: 'ti ti-stars' }
      ],
      showNotification: false,
      notificationTitle: '',
      notificationMessage: '',
      notificationType: 'info',
      notificationTimeout: null
    };
  },
  mounted() {
    setPageTitle('Catálogos');
    const params = new URLSearchParams(window.location.search);
    const tab = params.get('tab');
    if (tab && this.tabs.some(t => t.key === tab)) {
      this.activeTab = tab;
    }
    window.addEventListener('catalog-tab-request', this.onTabRequest);
  },
  beforeUnmount() {
    window.removeEventListener('catalog-tab-request', this.onTabRequest);
    if (this.notificationTimeout) clearTimeout(this.notificationTimeout);
  },
  watch: {
    activeTab(val) {
      const url = new URL(window.location.href);
      url.searchParams.set('tab', val);
      window.history.replaceState({}, '', url);
      window.dispatchEvent(new CustomEvent('catalog-tab-changed', { detail: { tab: val } }));
    }
  },
  methods: {
    onTabRequest(e) {
      const tab = e.detail?.tab;
      if (tab && this.tabs.some(t => t.key === tab) && this.activeTab !== tab) {
        this.activeTab = tab;
      }
    },
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