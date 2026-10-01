<template>
  <aside
    :class="[
      'flex flex-col h-full bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 transition-all duration-300 overflow-y-auto border-r border-gray-200 dark:border-gray-700',
      isOpen ? 'w-64' : 'w-16',
    ]"
  >
    <div class="flex items-center justify-center h-20 border-b border-gray-200 dark:border-gray-700">
      <img v-if="isOpen" :src="'/images/creatifoto_2.png'" alt="creatifoto" class="h-14 w-auto" />
      <img v-else :src="'/images/creatifoto_1.png'" alt="creatifoto" class="h-14 w-auto" />
    </div>

    <nav class="flex-1 px-2 py-4 space-y-1">
      <template v-for="item in menuItems" :key="item.path">
        <template v-if="item.children && item.children.length">
          <button
            type="button"
            @click="handleParentClick(item)"
            :title="!isOpen ? item.title : ''"
            class="w-full flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
            :class="{ 'bg-gray-200 dark:bg-gray-700': isActiveRoute(item.path) }"
          >
            <i :class="item.icon" class="w-6 h-6 text-xl text-gray-600 dark:text-gray-400"></i>
            <span v-if="isOpen" class="ml-3 flex-1 text-left">{{ item.title }}</span>
            <i
              v-if="isOpen"
              class="ti ti-chevron-down text-sm text-gray-500 dark:text-gray-400 transition-transform duration-200"
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
                class="flex items-center px-2 py-1.5 text-sm rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                :class="isChildActive(item, child)
                  ? 'bg-gray-200 dark:bg-gray-700 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300'"
              >
                <i :class="child.icon" class="mr-2 text-base"></i>
                <span>{{ child.title }}</span>
              </a>
            </div>
          </transition>
        </template>

        <a
          v-else
          :href="item.path"
          :title="!isOpen ? item.title : ''"
          @click="handleItemClick(item.path)"
          class="flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
          :class="{ 'bg-gray-200 dark:bg-gray-700': isActiveRoute(item.path) }"
        >
          <i :class="item.icon" class="w-6 h-6 text-xl text-gray-600 dark:text-gray-400"></i>
          <span v-if="isOpen" class="ml-3">{{ item.title }}</span>
        </a>
      </template>
    </nav>

    <div class="p-2 border-t border-gray-200 dark:border-gray-700">
      <a
        href="/settings"
        :title="!isOpen ? 'Ajustes' : ''"
        @click="handleItemClick('/settings')"
        class="flex items-center px-2 py-2 text-base font-medium rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        :class="{ 'bg-gray-200 dark:bg-gray-700': isActiveRoute('/settings') }"
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
      expandedGroups: [],
      activeCatalogTab: 'photo-types',
      menuItems: [
        { title: 'Órdenes', path: '/', icon: 'ti ti-ticket' },
        {
          title: 'Catálogos',
          path: '/catalogos',
          icon: 'ti ti-category-2',
          children: [
            { title: 'Tipos de fotografía', tab: 'photo-types', icon: 'ti ti-photo' },
            { title: 'Servicios adicionales', tab: 'additional-services', icon: 'ti ti-stars' },
          ],
        },
        { title: 'Cotizaciones', path: '/cotizaciones', icon: 'ti ti-file-description' },
        { title: 'Paquetes', path: '/paquetes', icon: 'ti ti-package' },
      ],
    };
  },
  mounted() {
    this.syncFromUrl();
    window.addEventListener('catalog-tab-changed', this.onCatalogTabChanged);
    window.addEventListener('popstate', this.syncFromUrl);
  },
  beforeUnmount() {
    window.removeEventListener('catalog-tab-changed', this.onCatalogTabChanged);
    window.removeEventListener('popstate', this.syncFromUrl);
  },
  methods: {
    isActiveRoute(path) {
      const current = window.location.pathname.replace(/\/$/, '') || '/';
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
        window.location.href = item.path;
        return;
      }
      this.toggleGroup(item.path);
    },
    handleChildClick(parent, child) {
      const current = window.location.pathname.replace(/\/$/, '') || '/';
      const target = parent.path.replace(/\/$/, '') || '/';
      if (current === target) {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', child.tab);
        window.history.replaceState({}, '', url);
        this.activeCatalogTab = child.tab;
        window.dispatchEvent(new CustomEvent('catalog-tab-request', { detail: { tab: child.tab } }));
      } else {
        window.location.href = `${parent.path}?tab=${child.tab}`;
      }
    },
    handleItemClick(path) {},
    syncFromUrl() {
      const params = new URLSearchParams(window.location.search);
      const tab = params.get('tab');
      this.activeCatalogTab = (tab === 'photo-types' || tab === 'additional-services') ? tab : 'photo-types';
      if (this.isOnCatalogos() && !this.expandedGroups.includes('/catalogos')) {
        this.expandedGroups.push('/catalogos');
      }
    },
    onCatalogTabChanged(e) {
      const tab = e.detail?.tab;
      if (tab === 'photo-types' || tab === 'additional-services') {
        this.activeCatalogTab = tab;
      }
      if (this.isOnCatalogos() && !this.expandedGroups.includes('/catalogos')) {
        this.expandedGroups.push('/catalogos');
      }
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