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

            <div ref="dropdown" class="relative">
                <button
                    @click="toggleDropdown"
                    class="flex items-center space-x-2 focus:outline-none"
                    aria-label="Menú de usuario"
                >
                    <i class="ti ti-user-circle text-2xl sm:text-3xl short:text-2xl text-gray-600 dark:text-gray-300"></i>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200 hidden md:block">{{ userName }}</span>
                </button>

                <transition name="dropdown">
                    <div
                        v-if="dropdownOpen"
                        class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1.5 border border-gray-200 dark:border-gray-700 z-50 origin-top-right"
                    >
                        <button
                            @click="logout"
                            :disabled="leaving"
                            class="flex items-center w-full text-left px-5 py-3 text-base text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-sm transition-colors"
                        >
                            <i class="ti ti-logout mr-3 text-lg"></i> Salir
                        </button>
                    </div>
                </transition>
            </div>
        </div>

        <Teleport to="body">
            <div class="veil veil-gold" :class="{ 'veil-on': leaving }" :style="veilStyle" aria-hidden="true"></div>
            <div class="veil veil-base" :class="{ 'veil-on': leaving }" :style="veilStyle" aria-hidden="true"></div>
        </Teleport>
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
            leaving: false,
            origin: { x: 0, y: 0 },
        };
    },
    computed: {
        hasHover() {
            return state.hasHover;
        },
        veilStyle() {
            return { '--x': this.origin.x + 'px', '--y': this.origin.y + 'px' };
        },
    },
    mounted() {
        document.addEventListener('click', this.handleClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
    },
    methods: {
        onSidebarHover(value) {
            if (!this.hasHover) return;
            this.sidebarTooltipHover = value;
        },
        toggleDropdown() {
            this.dropdownOpen = !this.dropdownOpen;
        },
        handleClickOutside(event) {
            if (!this.dropdownOpen) return;
            const el = this.$refs.dropdown;
            if (el && !el.contains(event.target)) {
                this.dropdownOpen = false;
            }
        },
        async logout() {
            if (this.leaving) return;

            const rect = this.$refs.dropdown.getBoundingClientRect();
            this.origin = { x: rect.left + 16, y: rect.top + rect.height / 2 };

            this.dropdownOpen = false;
            this.leaving = true;

            try {
                await Promise.all([
                    axios.post('/logout'),
                    new Promise((r) => window.setTimeout(r, 900)),
                ]);
                try {
                    sessionStorage.setItem('logoutFade', '1');
                } catch (e) {}
                window.location.reload();
            } catch (error) {
                this.leaving = false;
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

.dropdown-enter-active {
    transition: opacity 0.18s ease, transform 0.18s ease;
}
.dropdown-leave-active {
    transition: opacity 0.14s ease, transform 0.14s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: scale(0.95) translateY(-6px);
}

.veil {
    position: fixed;
    inset: 0;
    z-index: 100;
    pointer-events: none;
    clip-path: circle(0px at var(--x, 50%) var(--y, 50%));
    visibility: hidden;
    transition:
        clip-path 0.75s cubic-bezier(0.65, 0, 0.35, 1) var(--d, 0s),
        visibility 0s linear 0.75s;
}
.veil-on {
    pointer-events: auto;
    visibility: visible;
    clip-path: circle(150vmax at var(--x, 50%) var(--y, 50%));
    transition:
        clip-path 0.75s cubic-bezier(0.65, 0, 0.35, 1) var(--d, 0s),
        visibility 0s;
}

.veil-gold {
    background: #ffbe00;
}
.veil-base {
    --d: 0.1s;
    background: #ffffff;
}
:global(.dark .veil-base) {
    background: #0b0d12;
}

@media (prefers-reduced-motion: reduce) {
    .veil {
        clip-path: none;
        opacity: 0;
        transition: opacity 0.3s ease, visibility 0s linear 0.3s;
    }
    .veil-on {
        opacity: 1;
        transition: opacity 0.3s ease, visibility 0s;
    }
    .veil-gold {
        display: none;
    }
}
</style>