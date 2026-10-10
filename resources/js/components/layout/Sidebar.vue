<template>
  <aside
    :class="[
      'relative flex flex-col h-full bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 transition-all duration-300 overflow-y-auto overflow-x-hidden border-r border-gray-200 dark:border-gray-700',
      isOpen ? 'w-56 lg:w-64' : 'w-14 lg:w-16',
    ]"
  >
    <div class="relative flex items-center justify-center h-14 lg:h-20 short:h-10 border-b border-gray-200 dark:border-gray-700 shrink-0">
      <img v-if="isOpen" :src="'/images/creatifoto_2.png'" alt="creatifoto" class="h-9 lg:h-14 short:h-7 w-auto" />
      <img v-else :src="'/images/creatifoto_1.png'" alt="creatifoto" class="h-9 lg:h-14 short:h-7 w-auto" />

      <button
        v-if="isMobile && isOpen"
        type="button"
        aria-label="Cerrar menú"
        class="absolute top-1 right-1 lg:top-2 lg:right-2 p-1.5 lg:p-2 short:p-1 rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        @click="$emit('close-sidebar')"
      >
        <i class="ti ti-x text-base lg:text-lg short:text-base"></i>
      </button>
    </div>

    <nav class="flex-1 px-1.5 lg:px-2 py-3 lg:py-4 short:py-1.5 space-y-0.5 lg:space-y-1 short:space-y-0.5 min-w-0">
      <template v-for="item in menuItems" :key="item.path">
        <template v-if="item.children && item.children.length">
          <button
            type="button"
            @click="handleParentClick(item)"
            :title="(hasHover && !isOpen) ? item.title : undefined"
            class="w-full flex items-center px-2 py-1.5 lg:py-2 short:py-1 text-sm lg:text-base short:text-sm font-medium rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors min-w-0"
            :class="{ 'bg-gray-200 dark:bg-gray-700': isActiveRoute(item.path) }"
          >
            <i :class="item.icon" class="w-5 h-5 lg:w-6 lg:h-6 short:w-4 short:h-4 text-lg lg:text-xl short:text-base text-gray-600 dark:text-gray-400 shrink-0"></i>
            <span v-if="isOpen" class="ml-2.5 lg:ml-3 flex-1 text-left whitespace-nowrap">{{ item.title }}</span>
            <i
              v-if="isOpen"
              class="ti ti-chevron-down text-xs lg:text-sm text-gray-500 dark:text-gray-400 transition-transform duration-200 shrink-0"
              :class="{ 'rotate-180': isGroupExpanded(item.path) }"
            ></i>
          </button>
          <transition name="submenu">
            <div
              v-if="isOpen && isGroupExpanded(item.path)"
              class="mt-0.5 lg:mt-1 ml-3 lg:ml-4 pl-2 space-y-0.5 lg:space-y-1 short:space-y-0.5 border-l border-gray-300 dark:border-gray-600"
            >
              <a
                v-for="child in item.children"
                :key="child.tab"
                :href="`${item.path}?tab=${child.tab}`"
                @click.prevent="handleChildClick(item, child)"
                class="flex items-center px-2 py-1 lg:py-1.5 short:py-1 text-xs lg:text-sm short:text-xs rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors min-w-0"
                :class="isChildActive(item, child)
                  ? 'bg-gray-200 dark:bg-gray-700 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300'"
              >
                <i :class="child.icon" class="mr-1.5 lg:mr-2 text-sm lg:text-base short:text-sm shrink-0"></i>
                <span class="whitespace-nowrap">{{ child.title }}</span>
              </a>
            </div>
          </transition>
        </template>

        <router-link
          v-else
          :to="item.path"
          :title="(hasHover && !isOpen) ? item.title : undefined"
          class="flex items-center px-2 py-1.5 lg:py-2 short:py-1 text-sm lg:text-base short:text-sm font-medium rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors min-w-0"
          :class="{ 'bg-gray-200 dark:bg-gray-700': isActiveRoute(item.path) }"
        >
          <i :class="item.icon" class="w-5 h-5 lg:w-6 lg:h-6 short:w-4 short:h-4 text-lg lg:text-xl short:text-base text-gray-600 dark:text-gray-400 shrink-0"></i>
          <span v-if="isOpen" class="ml-2.5 lg:ml-3 whitespace-nowrap">{{ item.title }}</span>
        </router-link>
      </template>
    </nav>
  </aside>
</template>

<script>
import { state } from '../../state';

export default {
  name: 'Sidebar',
  props: {
    isOpen: { type: Boolean, required: true },
    isMobile: { type: Boolean, default: false },
  },
  emits: ['close-sidebar'],
  data() {
    return {
      expandedGroups: [],
      menuItems: [
        { title: 'Órdenes', path: '/', icon: 'ti ti-ticket' },
        {
          title: 'Catálogos',
          path: '/catalogos',
          icon: 'ti ti-category-2',
          children: [
            { title: 'Tipos de fotografía', tab: 'photo-types', icon: 'ti ti-camera' },
            { title: 'Servicios adicionales', tab: 'additional-services', icon: 'ti ti-tool' },
          ],
        },
        { title: 'Cotizaciones', path: '/cotizaciones', icon: 'ti ti-file-description' },
        { title: 'Paquetes', path: '/paquetes', icon: 'ti ti-package' },
      ],
    };
  },
  computed: {
    hasHover() {
      return state.hasHover;
    },
    activeCatalogTab() {
      return this.$route.query.tab === 'additional-services' ? 'additional-services' : 'photo-types';
    },
  },
  watch: {
    '$route.path': {
      immediate: true,
      handler() {
        if (this.isOnCatalogos() && !this.expandedGroups.includes('/catalogos')) {
          this.expandedGroups.push('/catalogos');
        }
      },
    },
  },
  methods: {
    isActiveRoute(path) {
      const current = this.$route.path.replace(/\/$/, '') || '/';
      const target = path.replace(/\/$/, '') || '/';
      return current === target;
    },
    isOnCatalogos() {
      return this.isActiveRoute('/catalogos');
    },
    isGroupExpanded(path) {
      return this.expandedGroups.includes(path);
    },
    toggleGroup(path) {
      const idx = this.expandedGroups.indexOf(path);
      if (idx === -1) this.expandedGroups.push(path);
      else this.expandedGroups.splice(idx, 1);
    },
    isChildActive(parent, child) {
      if (!this.isActiveRoute(parent.path)) return false;
      return this.activeCatalogTab === child.tab;
    },
    handleParentClick(item) {
      if (!this.isOpen) {
        this.$router.push(item.path);
        return;
      }
      this.toggleGroup(item.path);
    },
    handleChildClick(parent, child) {
      this.$router.push({ path: parent.path, query: { tab: child.tab } });
    },
  },
};
</script>

<style scoped>
.submenu-enter-active, .submenu-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.submenu-enter-from, .submenu-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>