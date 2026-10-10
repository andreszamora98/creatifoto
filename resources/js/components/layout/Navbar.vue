<template>
    <nav class="flex items-center justify-between px-3 sm:px-4 py-1.5 sm:py-2 short:py-1 bg-white border-b border-gray-200 dark:bg-gray-900 dark:border-gray-700">
        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
            <div
                class="relative"
                @mouseenter="onSidebarHover(true)"
                @mouseleave="onSidebarHover(false)"
            >
                <button
                    @click="$emit('toggle-sidebar')"
                    aria-label="Alternar menú lateral"
                    class="p-1 short:p-0.5 rounded-md text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none"
                >
                    <i class="ti ti-menu-2 text-xl short:text-lg"></i>
                </button>
                <transition name="tooltip" mode="out-in">
                    <span
                        v-if="hasHover && sidebarTooltipHover"
                        :key="sidebarOpen ? 'open' : 'closed'"
                        class="absolute left-0 top-full mt-1.5 px-2 py-1 text-xs font-medium rounded-md bg-gray-800 text-white dark:bg-gray-700 dark:text-gray-200 whitespace-nowrap pointer-events-none z-50 shadow-lg"
                    >
                        {{ sidebarOpen ? 'Ocultar' : 'Desplegar' }}
                    </span>
                </transition>
            </div>
            <h1 class="text-base sm:text-lg short:text-sm font-semibold text-gray-800 dark:text-white truncate">{{ pageTitle }}</h1>
        </div>

        <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
            <button
                @click="$emit('toggle-theme')"
                aria-label="Alternar tema"
                class="p-1.5 sm:p-2 short:p-1 rounded-full text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"
            >
                <i v-if="isDark" class="ti ti-moon text-lg sm:text-xl short:text-lg"></i>
                <i v-else class="ti ti-sun text-lg sm:text-xl short:text-lg"></i>
            </button>

            <div class="relative">
                <button
                    @click="dropdownOpen = !dropdownOpen"
                    class="flex items-center space-x-2 focus:outline-none"
                    aria-label="Menú de usuario"
                >
                    <i class="ti ti-user-circle text-2xl sm:text-3xl short:text-2xl text-gray-600 dark:text-gray-300"></i>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200 hidden md:block">{{ userName }}</span>
                </button>

                <div
                    v-if="dropdownOpen"
                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 border border-gray-200 dark:border-gray-700 z-50"
                >
                    <button
                        @click="logout"
                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        <i class="ti ti-logout mr-2"></i> Salir
                    </button>
                </div>
            </div>
        </div>
    </nav>
</template>

<script>
import axios from 'axios';
import { state } from '../../state';

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
    computed: {
        hasHover() {
            return state.hasHover;
        },
    },
    methods: {
        onSidebarHover(value) {
            if (!this.hasHover) return;
            this.sidebarTooltipHover = value;
        },
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