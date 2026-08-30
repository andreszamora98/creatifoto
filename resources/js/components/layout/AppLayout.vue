<template>
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-gray-900">
        <!-- Sidebar -->
        <Sidebar :is-open="sidebarOpen" @toggle="toggleSidebar" />

        <!-- Contenido principal -->
        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <Navbar
                :user-name="userName"
                :user-email="userEmail"
                :profile-pic="profilePic"
                :is-dark="darkMode"
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

        function toggleSidebar() {
            setSidebarOpen(!sidebarOpen.value);
        }

        function toggleTheme() {
            setDarkMode(!darkMode.value);
        }

        return {
            sidebarOpen,
            darkMode,
            toggleSidebar,
            toggleTheme,
        };
    },
};
</script>