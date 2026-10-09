<template>
  <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-gray-900">
    <transition name="fade">
      <div
        v-if="isMobile && sidebarOpen"
        class="fixed inset-0 z-40 bg-black/50"
        @click="closeSidebar"
      ></div>
    </transition>

    <div
      class="shrink-0 z-50 transition-transform duration-300"
      :class="[
        isMobile ? 'fixed inset-y-0 left-0 transform' : 'relative',
        isMobile && !sidebarOpen ? '-translate-x-full' : 'translate-x-0',
      ]"
    >
      <Sidebar
        :is-open="isMobile ? true : sidebarOpen"
        :is-mobile="isMobile"
        @close-sidebar="closeSidebar"
      />
    </div>

    <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300 min-w-0">
      <Navbar
        :user-name="userName"
        :user-email="userEmail"
        :profile-pic="profilePic"
        :is-dark="darkMode"
        :page-title="pageTitle"
        :sidebar-open="sidebarOpen"
        @toggle-sidebar="toggleSidebar"
        @toggle-theme="toggleTheme"
      />
      <main class="flex-1 overflow-y-auto p-4">
        <slot />
      </main>
    </div>
  </div>
</template>

<script>
import { computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { state, setSidebarOpen, setDarkMode } from '../../state';
import { store } from '../../store';
import Sidebar from './Sidebar.vue';
import Navbar from './Navbar.vue';

export default {
  name: 'AppLayout',
  components: { Sidebar, Navbar },
  props: {
    userName: { type: String, default: 'Usuario' },
    userEmail: { type: String, default: 'usuario@ejemplo.com' },
    profilePic: { type: String, default: 'https://ui-avatars.com/api/?name=User&background=random' },
  },
  setup() {
    const route = useRoute();

    const sidebarOpen = computed(() => state.sidebarOpen);
    const isMobile = computed(() => state.isMobile);
    const darkMode = computed({
      get: () => state.darkMode,
      set: (val) => setDarkMode(val),
    });
    const pageTitle = computed(() => store.pageTitle);

    function toggleSidebar() { setSidebarOpen(!state.sidebarOpen); }
    function closeSidebar()  { setSidebarOpen(false); }
    function toggleTheme()   { setDarkMode(!state.darkMode); }

    watch(() => route.fullPath, () => {
      if (state.isMobile && state.sidebarOpen) setSidebarOpen(false);
    });

    return { sidebarOpen, isMobile, darkMode, pageTitle, toggleSidebar, closeSidebar, toggleTheme };
  },
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>