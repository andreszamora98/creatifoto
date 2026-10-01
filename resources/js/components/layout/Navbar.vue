<template>
    <nav class="flex items-center justify-between px-4 py-2 bg-white border-b border-gray-200 dark:bg-gray-900 dark:border-gray-700">
        <div class="flex items-center gap-3">
            <div
                class="relative"
                @mouseenter="sidebarTooltipHover = true"
                @mouseleave="sidebarTooltipHover = false"
            >
                <button
                    @click="$emit('toggle-sidebar')"
                    class="p-1 rounded-md text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none"
                >
                    <i class="ti ti-menu-2 text-xl"></i>
                </button>
                <transition name="tooltip" mode="out-in">
                    <span
                        v-if="sidebarTooltipHover"
                        :key="sidebarOpen ? 'open' : 'closed'"
                        class="absolute left-0 top-full mt-1.5 px-2 py-1 text-xs font-medium rounded-md bg-gray-800 text-white dark:bg-gray-700 dark:text-gray-200 whitespace-nowrap pointer-events-none z-50 shadow-lg"
                    >
                        {{ sidebarOpen ? 'Ocultar' : 'Desplegar' }}
                    </span>
                </transition>
            </div>
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white">{{ pageTitle }}</h1>
        </div>

        <div class="flex items-center gap-3">
            <button
                @click="$emit('toggle-theme')"
                class="p-2 rounded-full text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"
            >
                <i v-if="isDark" class="ti ti-moon text-xl"></i>
                <i v-else class="ti ti-sun text-xl"></i>
            </button>

            <div class="relative">
                <button @click="dropdownOpen = !dropdownOpen" class="flex items-center space-x-2 focus:outline-none">
                    <i class="ti ti-user-circle text-3xl text-gray-600 dark:text-gray-300"></i>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200 hidden md:block">{{ userName }}</span>
                </button>

                <div
                    v-if="dropdownOpen"
                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 border border-gray-200 dark:border-gray-700"
                >
                    <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="ti ti-user mr-2"></i> Perfil
                    </a>
                    <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="ti ti-logout mr-2"></i> Salir
                    </button>
                </div>
            </div>
        </div>
    </nav>
</template>

<script>
import axios from 'axios';

export default {
    name: 'Navbar',
    props: {
        userName: { type: String, default: 'Usuario' },
        userEmail: { type: String, default: 'usuario@ejemplo.com' },
        profilePic: { type: String, default: 'https://ui-avatars.com/api/?name=User&background=random' },
        isDark: { type: Boolean, default: false },
        pageTitle: { type: String, default: 'CreatiFoto' },
        sidebarOpen: { type: Boolean, default: true },
    },
    data() {
        return {
            dropdownOpen: false,
            sidebarTooltipHover: false,
        };
    },
    methods: {
        async logout() {
            try {
                await axios.post('/logout');
                window.location.reload();
            } catch (error) {
                console.error('Error al cerrar sesión:', error);
            }
        },
    },
};
</script>

<style scoped>
.tooltip-enter-active {
    transition: opacity 0.15s ease;
}
.tooltip-leave-active {
    transition: opacity 0.04s ease;
}
.tooltip-enter-from,
.tooltip-leave-to {
    opacity: 0;
}
</style>