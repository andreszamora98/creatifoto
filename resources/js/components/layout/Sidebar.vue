<template>
  <aside
    :class="[
      'flex flex-col h-full bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 transition-all duration-300 overflow-y-auto overflow-x-hidden border-r border-gray-200 dark:border-gray-700',
      isOpen ? 'w-64' : 'w-16',
    ]"
  >
    <div class="flex items-center justify-center h-20 border-b border-gray-200 dark:border-gray-700 shrink-0">
      <img v-if="isOpen" :src="'/images/creatifoto_2.png'" alt="creatifoto" class="h-14 w-auto" />
      <img v-else :src="'/images/creatifoto_1.png'" alt="creatifoto" class="h-14 w-auto" />
    </div>

    <nav class="flex-1 px-2 py-4 space-y-1 min-w-0">
      <template v-for="item in menuItems" :key="item.path">
        <template v-if="item.children && item.children.length">
          <button
            type="button"
            @click="handleParentClick(item)"
            :title="!isOpen ? item.title : ''"
            class="w-full flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors min-w-0"
            :class="{ 'bg-gray-200 dark:bg-gray-700': isActiveRoute(item.path) }"
          >
            <i :class="item.icon" class="w-6 h-6 text-xl text-gray-600 dark:text-gray-400 shrink-0"></i>
            <span v-if="isOpen" class="ml-3 flex-1 text-left whitespace-nowrap">{{ item.title }}</span>
            <i
              v-if="isOpen"
              class="ti ti-chevron-down text-sm text-gray-500 dark:text-gray-400 transition-transform duration-200 shrink-0"
              :class="{ 'rotate-180': isGroupExpanded(item.path) }"
            ></i>
          </button>
          <transition name="submenu">
            <div
              v-if="isOpen && isGroupExpanded(item.path)"
              class="mt-1 ml-4 pl-2 space-y-1 border-l border-gray-300 dark:border-gray-600"
            >
              <a
                v-for="child in item.children"
                :key="child.tab"
                :href="`${item.path}?tab=${child.tab}`"
                @click.prevent="handleChildClick(item, child)"
                class="flex items-center px-2 py-1.5 text-sm rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors min-w-0"
                :class="isChildActive(item, child)
                  ? 'bg-gray-200 dark:bg-gray-700 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300'"
              >
                <i :class="child.icon" class="mr-2 text-base shrink-0"></i>
                <span class="whitespace-nowrap">{{ child.title }}</span>
              </a>
            </div>
          </transition>
        </template>

        <router-link
          v-else
          :to="item.path"
          :title="!isOpen ? item.title : ''"
          class="flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors min-w-0"
          :class="{ 'bg-gray-200 dark:bg-gray-700': isActiveRoute(item.path) }"
        >
          <i :class="item.icon" class="w-6 h-6 text-xl text-gray-600 dark:text-gray-400 shrink-0"></i>
          <span v-if="isOpen" class="ml-3 whitespace-nowrap">{{ item.title }}</span>
        </router-link>
      </template>
    </nav>
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
      expandedGroups: [],
      menuItems: [
        { title: 'Órdenes', path: '/', icon: 'ti ti-ticket' },
        {
          title: 'Catálogos',
          path: '/catalogos',
          icon: 'ti ti-category-2',
          children: [
            { title: 'Tipos de fotografía', tab: 'photo-types', icon: 'ti ti-photo' },
            { title: 'Servicios adicionales', tab: 'additional-services', icon: 'ti ti-tool' },
          ],
        },
        { title: 'Cotizaciones', path: '/cotizaciones', icon: 'ti ti-file-description' },
        { title: 'Paquetes', path: '/paquetes', icon: 'ti ti-package' },
      ],
    };
  },
  computed: {
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