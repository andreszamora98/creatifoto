<template>
  <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-gray-900">
    <Sidebar
      :is-open="sidebarOpen"
      @toggle="toggleSidebar"
    />
    <!--dentro de sidebar para volverlo a habilitar-->
    <!-- @close-sidebar="closeSidebar" -->
    <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
      <Navbar
        :user-name="userName"
        :user-email="userEmail"
        :profile-pic="profilePic"
        :is-dark="darkMode"
        :page-title="pageTitle"
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
import { computed } from 'vue';
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
    const sidebarOpen = computed({
      get: () => state.sidebarOpen,
      set: (val) => setSidebarOpen(val),
    });

    const darkMode = computed({
      get: () => state.darkMode,
      set: (val) => setDarkMode(val),
    });

    const pageTitle = computed(() => store.pageTitle);

    function toggleSidebar() {
      setSidebarOpen(!sidebarOpen.value);
    }

    /*
    function closeSidebar() {
      setSidebarOpen(false);
    }
    */

    function toggleTheme() {
      setDarkMode(!darkMode.value);
    }

    return {
      sidebarOpen,
      darkMode,
      pageTitle,
      toggleSidebar,
      //closeSidebar,
      toggleTheme,
    };
  },
};
</script>