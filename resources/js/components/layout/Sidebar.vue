<template>
    <aside
        :class="[
            'flex flex-col h-full bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 transition-all duration-300 overflow-y-auto border-r border-gray-200 dark:border-gray-700',
            isOpen ? 'w-64' : 'w-16',
        ]"
    >
        <!-- Logo / Marca -->
        <div class="flex items-center justify-center h-20 border-b border-gray-200 dark:border-gray-700">
            <img v-if="isOpen" :src="'/images/creatifoto_2.png'" alt="creatifoto" class="h-14 w-auto" />
            <img v-else :src="'/images/creatifoto_1.png'" alt="creatifoto" class="h-14 w-auto" />
        </div>

        <!-- Menú -->
        <nav class="flex-1 px-2 py-4 space-y-1">
            <a
                v-for="item in menuItems"
                :key="item.path"
                :href="item.path"
                :title="!isOpen ? item.title : ''"
                @click="handleItemClick"
                class="flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                :class="{ 'bg-gray-200 dark:bg-gray-700': isActiveRoute(item.path) }"
            >
                <i :class="item.icon" class="w-6 h-6 text-xl text-gray-600 dark:text-gray-400"></i>
                <span v-if="isOpen" class="ml-3">{{ item.title }}</span>
            </a>
        </nav>

        <!-- Pie: Ajustes -->
        <div class="p-2 border-t border-gray-200 dark:border-gray-700">
            <a
                href="/settings"
                :title="!isOpen ? 'Ajustes' : ''"
                @click="handleItemClick"
                class="flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
            >
                <i class="ti ti-settings w-6 h-6 text-xl text-gray-600 dark:text-gray-400"></i>
                <span v-if="isOpen" class="ml-3">Ajustes</span>
            </a>
        </div>
    </aside>
</template>

<script>
export default {
    name: 'Sidebar',
    props: {
        isOpen: {
            type: Boolean,
            required: true,
        },
    },
    emits: ['close-sidebar'],
    data() {
        return {
            menuItems: [
                { title: 'Órdenes', path: '/', icon: 'ti ti-ticket' },
                { title: 'Cotizaciones', path: '/cotizaciones', icon: 'ti ti-file-description' },
                // { title: 'Clientes', path: '/customers', icon: 'ti ti-users' },
                // { title: 'Paquetes', path: '/packages', icon: 'ti ti-package' },
            ],
        };
    },
    methods: {
        isActiveRoute(path) {
            return window.location.pathname === path;
        },
        handleItemClick() {
            this.$emit('close-sidebar');
        },
    },
};
</script>