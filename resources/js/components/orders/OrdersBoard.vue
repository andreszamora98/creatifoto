<template>
  <div class="h-full flex flex-col overflow-hidden">
    <div class="flex-shrink-0 mb-3">
      <div class="flex flex-col gap-3">
        <div class="flex-shrink-0 mb-4 mt-2 ml-4 flex flex-col md:flex-row items-start md:items-center gap-3">
          <div class="flex-1 max-w-xl w-full md:w-auto">
            <div class="relative">
              <i class="ti ti-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
              <input v-model="searchQuery" type="text"
                    placeholder="Buscar por cliente o código..."
                    class="w-full pl-9 pr-8 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
          </div>

          <div class="flex items-center gap-2 flex-wrap">
            <button class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-2.5 rounded-lg text-sm flex items-center gap-2 transition-colors">
              <i class="ti ti-filter text-base"></i>
              <span>Filtros avanzados</span>
            </button>
            <button @click="refreshOrders" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-2.5 rounded-lg text-sm flex items-center gap-2 transition-colors">
              <i class="ti ti-refresh text-base" :class="{ 'animate-spin': loading }"></i>
              Actualizar
            </button>
            <button @click="showChoiceModal = true" class="bg-blue-500 dark:bg-blue-600 hover:bg-blue-600 dark:hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm flex items-center gap-2 transition-colors">
              <i class="ti ti-plus text-base"></i>
              <span>Nuevo</span>
            </button>
            <button @click="showPhotoTypeManager = true" class="bg-green-500 dark:bg-green-600 hover:bg-green-600 dark:hover:bg-green-700 text-white px-4 py-2.5 rounded-lg text-sm flex items-center gap-2 transition-colors">
              <i class="ti ti-photo text-base"></i>
              <span>Tipos de fotografía</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="flex-shrink-0 mb-4">
      <div class="relative mb-2">
        <div class="absolute h-1 rounded-full bg-gray-200 dark:bg-gray-500 bottom-0 left-5 right-5"></div>
        <div class="absolute h-1 rounded-full transition-all duration-300 ease-in-out bottom-0"
            :style="{
              width: `${100 / columns.length}%`,
              transform: `translateX(${activeColumn * 100}%) scaleX(0.9)`,
              backgroundColor: columns[activeColumn]?.color.outline,
              boxShadow: '0 4px 6px rgba(0,0,0,0.1)'
            }">
        </div>
      </div>

      <div class="relative">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-1.5 px-1">
          <div v-for="(column, index) in columns" :key="index"
              @click="selectColumn(index)"
              :class="[
                'category-card relative p-3 rounded-lg cursor-pointer transition-all duration-200',
                'flex flex-row items-center justify-center gap-4',
                activeColumn === index
                  ? 'shadow-xl z-10'
                  : 'opacity-60 hover:opacity-80 hover:shadow-sm'
              ]"
              :style="{
                backgroundColor: column.color.inactive,
                outline: activeColumn === index ? `2px solid ${column.color.outline}` : 'none',
                outlineOffset: '0px',
                boxShadow: activeColumn === index
                  ? `inset 0 0 0 2px ${column.color.outline}, 0 10px 15px -3px rgba(0, 0, 0, 0.1)`
                  : 'none'
              }">
            <div class="w-12 h-12 rounded-md flex items-center justify-center shadow-md flex-shrink-0"
                :style="{
                  backgroundColor: column.color.active,
                  boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.2)'
                }">
              <i :class="column.icon + ' text-white text-xl'"></i>
            </div>

            <div class="flex flex-col items-center justify-center min-w-0">
              <h3 class="font-semibold text-base leading-tight" :style="{ color: column.textColor.active }">
                {{ column.title }}
              </h3>
              <div class="text-xl font-bold" :style="{ color: column.textColor.active }">
                {{ columnCounts[column.type] }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden min-h-0">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow flex-1 flex flex-col overflow-hidden min-h-0">
        <div class="flex-shrink-0 p-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3 flex-wrap">
          <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
            <span class="font-medium px-2 py-1 rounded-full text-xs"
                  :style="{
                    backgroundColor: columns[activeColumn]?.color.inactive,
                    color: columns[activeColumn]?.textColor.active
                  }">
              {{ columns[activeColumn]?.title }}
            </span>
            <span class="mx-1">•</span>
            <span>{{ filteredOrders.length }} órdenes</span>
            <span v-if="searchQuery" class="text-gray-400 ml-2">
              (filtrado)
            </span>
          </div>

          <div class="flex items-center gap-2">
            <button @click="toggleDateSort" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-2.5 rounded-lg text-sm flex items-center gap-2 transition-colors">
              <i class="ti ti-sort-descending text-base"></i>
              <span>{{ dateSort === 'desc' ? 'Mostrando recientes' : 'Mostrando antiguas' }}</span>
            </button>
            <button
              @click="toggleViewMode"
              class="flex items-center gap-2 px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-800 dark:text-white text-sm transition-colors"
            >
              <i :class="viewMode === 'cards' ? 'ti ti-table' : 'ti ti-layout-grid'" class="text-base"></i>
              <span>{{ viewMode === 'cards' ? 'Tabla' : 'Tarjetas' }}</span>
            </button>
          </div>
        </div>

        <div class="flex-1 overflow-hidden min-h-0 relative">
          <div v-if="loading" class="absolute inset-0 flex items-center justify-center p-4">
            <div class="text-center">
              <i class="ti ti-refresh animate-spin text-xl text-blue-500 mb-2"></i>
              <p class="text-gray-600 dark:text-gray-400 text-sm">Cargando órdenes...</p>
            </div>
          </div>

          <div v-else-if="filteredOrders.length === 0" class="absolute inset-0 flex items-center justify-center p-4">
            <div class="text-center max-w-xs">
              <div class="w-12 h-12 mx-auto mb-3 rounded-full flex items-center justify-center"
                   :style="{ backgroundColor: columns[activeColumn]?.color.inactive }">
                <i class="ti ti-inbox text-lg" :style="{ color: columns[activeColumn]?.textColor.active }"></i>
              </div>
              <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                {{ searchQuery ? 'No se encontraron resultados' : 'No hay órdenes' }}
              </h3>
              <p class="text-gray-500 dark:text-gray-400 text-xs">
                {{ searchQuery ? 'Intenta con otros términos de búsqueda.' : 'No hay órdenes en esta categoría.' }}
              </p>
            </div>
          </div>

          <div v-else class="h-full overflow-y-auto p-3">
            <div v-if="viewMode === 'cards'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
              <div v-for="order in paginatedOrders" :key="order.id"
                   :id="'order-card-' + order.id"
                   @click="openOrderDetail(order)"
                   :class="[
                     'bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 transition-all duration-200 cursor-pointer overflow-hidden group',
                     `hover-bg-${activeColumn}`,
                     isBlinking(order.id) ? 'animate-blink' : ''
                   ]">
                <div class="h-1.5" :style="{ backgroundColor: columns[activeColumn]?.color.active }"></div>
                <div class="p-3">
                  <div class="flex justify-between items-start mb-2">
                    <div>
                      <h3 class="font-bold text-gray-800 dark:text-gray-200 text-sm">
                        #{{ orderCode(order) }}
                      </h3>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <i class="ti ti-calendar mr-1"></i>
                        {{ formatDate(order.created_at) }}
                      </p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                          :class="statusClass(order.status)">
                      {{ statusLabel(order.status) }}
                    </span>
                  </div>

                  <div class="mb-2">
                    <p class="text-gray-600 dark:text-gray-400 text-xs line-clamp-2">
                      {{ order.customer?.name || 'Sin cliente' }}
                    </p>
                  </div>

                  <div class="flex justify-between items-center text-xs mb-2">
                    <div class="flex items-center">
                      <i class="ti ti-package mr-1 text-gray-500"></i>
                      <span class="text-gray-600 dark:text-gray-400">{{ packagesSummary(order) }}</span>
                    </div>
                    <div class="flex items-center">
                      <i class="ti ti-currency-dollar mr-1 text-gray-500"></i>
                      <span class="text-gray-600 dark:text-gray-400">{{ formatCurrency(order.total) }}</span>
                    </div>
                  </div>

                  <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center">
                      <i class="ti ti-calendar-event mr-1 text-gray-500"></i>
                      <span class="text-gray-600 dark:text-gray-400">Entrega: {{ formatDeliveryDate(order.estimated_delivery_date) }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                      <i class="ti ti-check-circle text-green-500" v-if="order.status === 'delivered'"></i>
                      <i class="ti ti-clock text-yellow-500" v-else></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0 z-10">
                  <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Código</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Cliente</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Paquetes/Tipos</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Total</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Estado</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">F. Entrega</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Entregado</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="order in paginatedOrders" :key="order.id"
                      :id="'order-card-' + order.id"
                      @click="openOrderDetail(order)"
                      class="transition-colors cursor-pointer"
                      :class="[
                        `hover-bg-row-${activeColumn}`,
                        isBlinking(order.id) ? 'animate-blink-row' : ''
                      ]">
                    <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">#{{ orderCode(order) }}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ order.customer?.name || 'Sin cliente' }}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ packagesSummary(order) }}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ formatCurrency(order.total) }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">
                      <span class="px-2 py-0.5 text-xs rounded-full" :class="statusClass(order.status)">
                        {{ statusLabel(order.status) }}
                      </span>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ formatDeliveryDate(order.estimated_delivery_date) }}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      <i :class="order.status === 'delivered' ? 'ti ti-check-circle text-green-500' : 'ti ti-clock text-yellow-500'"></i>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="flex-shrink-0 mt-3 flex items-center justify-center gap-2 pb-3 border-t border-gray-200 dark:border-gray-700 pt-3">
          <button
            @click="prevPage"
            :disabled="currentPage === 1 || totalPages === 0"
            class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
            :class="currentPage === 1 || totalPages === 0
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600'
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
          >
            <i class="ti ti-chevron-left text-sm"></i> Anterior
          </button>

          <div class="flex items-center gap-1">
            <button
              v-for="page in totalPages"
              :key="page"
              @click="goToPage(page)"
              class="w-8 h-8 rounded-md text-xs font-medium transition-colors"
              :class="currentPage === page
                ? 'bg-blue-500 text-white dark:bg-blue-600'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
            >
              {{ page }}
            </button>
            <span v-if="totalPages === 0" class="w-8 h-8 rounded-md bg-gray-100 text-gray-400 flex items-center justify-center text-xs dark:bg-gray-800 dark:text-gray-600">1</span>
          </div>

          <button
            @click="nextPage"
            :disabled="currentPage === totalPages || totalPages === 0"
            class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
            :class="currentPage === totalPages || totalPages === 0
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600'
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
          >
            Siguiente <i class="ti ti-chevron-right text-sm"></i>
          </button>
        </div>

        <div class="flex-shrink-0 flex items-center justify-center gap-4 text-xs text-gray-500 dark:text-gray-400 pb-2">
          <span>Mostrando {{ paginatedOrders.length }}/{{ filteredOrders.length }} {{ filteredOrders.length === 1 ? 'orden' : 'órdenes' }}</span>
          <span v-if="totalPages > 0">Página {{ currentPage }} de {{ totalPages }}</span>
          <span v-else>Página —</span>
        </div>
      </div>
    </div>

    <NewRecordChoiceModal
      :show="showChoiceModal"
      @close="showChoiceModal = false"
      @choose="handleChoiceSelected"
    />

    <OrderModal
      :show="showOrderModal"
      @close="handleOrderModalClosed"
      @created="handleOrderCreated"
      @notify="handleNotify"
    />

    <QuoteModal
      :show="showQuoteModal"
      @close="handleQuoteModalClosed"
      @created="handleQuoteCreated"
      @notify="handleNotify"
    />

    <PhotoTypeManager
      :show="showPhotoTypeManager"
      @close="showPhotoTypeManager = false"
      @updated="handlePhotoTypesUpdated"
    />

    <OrderDetailModal
      :show="showOrderDetailModal"
      :order="selectedOrderForDetail"
      :columns="columns"
      :active-column="activeColumn"
      @close="handleOrderDetailClosed"
      @updated="handleOrderDetailUpdated"
      @notify="handleNotify"
    />

    <transition name="fade-slide">
      <div v-if="showNotification" class="fixed top-3 right-3 z-50 max-w-xs w-full">
        <div class="flex items-center p-3 rounded-lg shadow-lg"
             :class="{
               'bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800': notificationType === 'success',
               'bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800': notificationType === 'error',
               'bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800': notificationType === 'info'
             }">
          <div class="flex-shrink-0 mr-2">
            <div class="w-8 h-8 rounded-full flex items-center justify-center"
                 :class="{
                   'bg-green-100 dark:bg-green-800': notificationType === 'success',
                   'bg-red-100 dark:bg-red-800': notificationType === 'error',
                   'bg-blue-100 dark:bg-blue-800': notificationType === 'info'
                 }">
              <i class="text-sm"
                 :class="{
                   'ti ti-check text-green-600 dark:text-green-300': notificationType === 'success',
                   'ti ti-x text-red-600 dark:text-red-300': notificationType === 'error',
                   'ti ti-info-circle text-blue-600 dark:text-blue-300': notificationType === 'info'
                 }"></i>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-0.5 text-xs">{{ notificationTitle }}</h4>
            <p class="text-xs text-gray-600 dark:text-gray-400">{{ notificationMessage }}</p>
          </div>
          <button @click="closeNotification" class="ml-2 flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <i class="ti ti-x text-sm"></i>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from 'axios';
import { setPageTitle } from '../../store';
import OrderModal from './OrderModal.vue';
import OrderDetailModal from './OrderDetailModal.vue';
import QuoteModal from '../quotes/QuoteModal.vue';
import NewRecordChoiceModal from '../order-quote-shared/NewRecordChoiceModal.vue';
import PhotoTypeManager from '../catalog/PhotoTypeManager.vue';

export default {
  name: 'OrdersBoard',
  components: { OrderModal, QuoteModal, NewRecordChoiceModal, PhotoTypeManager, OrderDetailModal },
  data() {
    return {
      isDarkMode: false,
      darkObserver: null,
      showOrderDetailModal: false,
      selectedOrderForDetail: null,
      pendingBlinkOrderId: null,
      showPhotoTypeManager: false,
      activeColumn: 0,
      viewMode: 'cards',
      loading: false,
      orders: [],
      searchQuery: '',
      dateSort: 'desc',
      showOrderModal: false,
      showQuoteModal: false,
      showChoiceModal: false,
      pendingNavigateToQuotes: false,

      currentPage: 1,
      itemsPerPage: 15,

      newOrderId: null,
      blinkInterval: null,

      showNotification: false,
      notificationTitle: '',
      notificationMessage: '',
      notificationType: 'info',
      notificationTimeout: null,

      columnsBase: [
        {
          title: 'Pendientes',
          type: 'pending',
          icon: 'ti ti-clock',
          color: {
            light: { active: '#FFEB3B', inactive: '#FFFDE7', outline: '#FFEB3B' },
            dark:  { active: '#B8A03D', inactive: '#4A3F1F', outline: '#E0C860' }
          },
          textColor: {
            light: { active: '#D97706', inactive: '#F59E0B' },
            dark:  { active: '#E5D58A', inactive: '#C4A547' }
          }
        },
        {
          title: 'En proceso',
          type: 'processing',
          icon: 'ti ti-progress',
          color: {
            light: { active: '#FFD8B1', inactive: '#FFF5EB', outline: '#FBBF24' },
            dark:  { active: '#8B5A2B', inactive: '#2A1F14', outline: '#C4854C' }
          },
          textColor: {
            light: { active: '#DD6B20', inactive: '#ED8936' },
            dark:  { active: '#E5C19A', inactive: '#C4854C' }
          }
        },
        {
          title: 'Listas',
          type: 'ready',
          icon: 'ti ti-check',
          color: {
            light: { active: '#B3E5FC', inactive: '#F0F9FF', outline: '#38BDF8' },
            dark:  { active: '#2C5A78', inactive: '#16232E', outline: '#5BA3CC' }
          },
          textColor: {
            light: { active: '#2B6CB0', inactive: '#4299E1' },
            dark:  { active: '#A8D0EB', inactive: '#5BA3CC' }
          }
        },
        {
          title: 'Entregadas',
          type: 'delivered',
          icon: 'ti ti-checkbox',
          color: {
            light: { active: '#C6F6D5', inactive: '#F0FFF4', outline: '#34D399' },
            dark:  { active: '#2F6B4A', inactive: '#152519', outline: '#5EB88A' }
          },
          textColor: {
            light: { active: '#276749', inactive: '#38A169' },
            dark:  { active: '#A8D9BF', inactive: '#5EB88A' }
          }
        }
      ]
    };
  },
  computed: {
    columns() {
      const theme = this.isDarkMode ? 'dark' : 'light';
      return this.columnsBase.map(col => ({
        title: col.title,
        type: col.type,
        icon: col.icon,
        color: col.color[theme],
        textColor: col.textColor[theme]
      }));
    },
    columnCounts() {
      const counts = {};
      this.columns.forEach(col => {
        counts[col.type] = this.orders.filter(o => o.status === col.type).length;
      });
      return counts;
    },
    filteredOrders() {
      const columnType = this.columns[this.activeColumn]?.type;
      let list = this.orders.filter(o => o.status === columnType);

      if (this.searchQuery.trim()) {
        const q = this.searchQuery.toLowerCase().trim();
        list = list.filter(o =>
          this.orderCode(o).toLowerCase().includes(q) ||
          (o.customer?.name || '').toLowerCase().includes(q)
        );
      }

      return [...list].sort((a, b) => {
        const dateA = new Date(a.created_at);
        const dateB = new Date(b.created_at);
        return this.dateSort === 'desc' ? dateB - dateA : dateA - dateB;
      });
    },
    paginatedOrders() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredOrders.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredOrders.length / this.itemsPerPage);
    }
  },
  watch: {
    filteredOrders() {
      if (this.currentPage > this.totalPages && this.totalPages > 0) {
        this.currentPage = this.totalPages;
      }
    },
    searchQuery() {
      this.currentPage = 1;
    },
    activeColumn() {
      this.currentPage = 1;
    },
    dateSort() {
      this.currentPage = 1;
    }
  },
  mounted() {
    setPageTitle('Tablero de órdenes');
    this.isDarkMode = document.documentElement.classList.contains('dark');
    this.darkObserver = new MutationObserver(() => {
      this.isDarkMode = document.documentElement.classList.contains('dark');
    });
    this.darkObserver.observe(document.documentElement, {
      attributes: true,
      attributeFilter: ['class']
    });
    this.loadOrders();
  },
  beforeUnmount() {
    if (this.blinkInterval) {
      clearInterval(this.blinkInterval);
      this.blinkInterval = null;
    }
    if (this.darkObserver) {
      this.darkObserver.disconnect();
      this.darkObserver = null;
    }
  },
  methods: {
    handleChoiceSelected(type) {
      this.showChoiceModal = false;
      if (type === 'order') {
        this.showOrderModal = true;
      } else {
        this.showQuoteModal = true;
      }
    },
    handleQuoteCreated(quote) {
      if (quote && quote.id) {
        sessionStorage.setItem('pendingBlinkQuoteId', String(quote.id));
        sessionStorage.setItem('pendingQuoteNotification', JSON.stringify({
          title: 'Cotización creada',
          message: `La cotización C-${String(quote.id).padStart(3, '0')} se registró correctamente.`,
          type: 'success'
        }));
      }
      this.pendingNavigateToQuotes = true;
    },
    handleQuoteModalClosed() {
      this.showQuoteModal = false;
      if (this.pendingNavigateToQuotes) {
        this.pendingNavigateToQuotes = false;
        window.location.href = '/cotizaciones';
      }
    },
    openOrderDetail(order) {
      this.selectedOrderForDetail = order;
      this.showOrderDetailModal = true;
    },
    handleOrderDetailClosed() {
      this.showOrderDetailModal = false;
      this.selectedOrderForDetail = null;
    },
    async handleOrderDetailUpdated() {
      await this.loadOrders();
    },
    formatDeliveryDate(dateString) {
      if (!dateString) return '';
      const datePart = String(dateString).substring(0, 10);
      const [y, m, d] = datePart.split('-');
      if (!y || !m || !d) return dateString;
      return `${d}/${m}/${y}`;
    },
    handlePhotoTypesUpdated() {
    },
    async loadOrders() {
      this.loading = true;
      try {
        const res = await axios.get('/orders-table');
        this.orders = res.data;
      } catch (error) {
        console.error('Error cargando órdenes:', error);
        this.showNotificationMessage('Error', 'No se pudieron cargar las órdenes', 'error');
        this.orders = [];
      } finally {
        this.loading = false;
      }
    },
    refreshOrders() {
      this.loadOrders();
    },
    goToPage(page) {
      if (this.totalPages > 0 && page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },
    nextPage() {
      if (this.totalPages > 0 && this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },
    prevPage() {
      if (this.totalPages > 0 && this.currentPage > 1) {
        this.currentPage--;
      }
    },
    selectColumn(index) {
      this.activeColumn = index;
      this.currentPage = 1;
    },
    async handleOrderCreated(order) {
      if (order && order.status) {
        const columnIndex = this.columns.findIndex(col => col.type === order.status);
        if (columnIndex !== -1) this.activeColumn = columnIndex;
      }
      await this.loadOrders();
      if (order && order.id) {
        this.pendingBlinkOrderId = order.id;
      }
    },
    handleOrderModalClosed() {
      this.showOrderModal = false;
      if (this.pendingBlinkOrderId) {
        const idToBlink = this.pendingBlinkOrderId;
        this.pendingBlinkOrderId = null;

        this.$nextTick(() => {
          this.startBlink(idToBlink);

          setTimeout(() => {
            const el = document.getElementById('order-card-' + idToBlink);
            if (el) {
              el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
          }, 50);
        });
      }
    },
    handleNotify({ title, message, type }) {
      this.showNotificationMessage(title, message, type);
    },
    toggleViewMode() {
      this.viewMode = this.viewMode === 'cards' ? 'table' : 'cards';
    },
    toggleDateSort() {
      this.dateSort = this.dateSort === 'desc' ? 'asc' : 'desc';
    },
    orderCode(order) {
      return 'ORD-' + String(order.id).padStart(3, '0');
    },
    packagesSummary(order) {
      if (order.packages && order.packages.length) {
        return order.packages.map(p => p.name || ('Paquete ' + p.package_number)).join(', ');
      }
      const itemCount = (order.order_photo_types || []).length;
      return itemCount ? `Personalizado (${itemCount} tipos)` : 'Personalizado';
    },
    statusLabel(status) {
      const labels = {
        pending: 'Pendiente',
        processing: 'En proceso',
        ready: 'Lista',
        delivered: 'Entregada'
      };
      return labels[status] || status;
    },
    statusClass(status) {
      const colors = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        processing: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
        ready: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        delivered: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
      };
      return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    },
    formatCurrency(value) {
      const num = Number(value || 0);
      return '$' + num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' });
    },
    showNotificationMessage(title, message, type = 'info') {
      this.notificationTitle = title;
      this.notificationMessage = message;
      this.notificationType = type;
      this.showNotification = true;
      if (this.notificationTimeout) clearTimeout(this.notificationTimeout);
      this.notificationTimeout = setTimeout(() => { this.closeNotification(); }, 3000);
    },
    closeNotification() {
      this.showNotification = false;
      if (this.notificationTimeout) {
        clearTimeout(this.notificationTimeout);
        this.notificationTimeout = null;
      }
    },
    startBlink(orderId) {
      if (this.blinkInterval) {
        clearInterval(this.blinkInterval);
        this.blinkInterval = null;
      }
      this.newOrderId = orderId;
      let count = 0;
      const maxCount = 8;
      this.blinkInterval = setInterval(() => {
        count++;
        if (count >= maxCount) {
          clearInterval(this.blinkInterval);
          this.blinkInterval = null;
          this.newOrderId = null;
        }
      }, 400);
    },
    isBlinking(orderId) {
      return this.newOrderId === orderId && this.blinkInterval !== null;
    }
  }
};
</script>

<style scoped>
.overflow-hidden { overflow: hidden; }
.flex-1 { flex: 1 1 0%; }
.flex-shrink-0 { flex-shrink: 0; }
.min-h-0 { min-height: 0; }
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 #f1f1f1;
}
.overflow-y-auto::-webkit-scrollbar { width: 6px; }
.overflow-y-auto::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
.overflow-y-auto::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 3px; }
.overflow-y-auto::-webkit-scrollbar-thumb:hover { background: #a0aec0; }
.dark .overflow-y-auto::-webkit-scrollbar-track { background: #374151; }
.dark .overflow-y-auto::-webkit-scrollbar-thumb { background: #4b5563; }
.dark .overflow-y-auto::-webkit-scrollbar-thumb:hover { background: #6b7280; }

.category-card {
  transition: all 0.2s ease;
  min-height: 120px;
}
.category-card:hover { transform: translateY(-1px); }

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.3s ease; }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateX(20px); }

.animate-blink {
  animation: blinkOrder 0.8s ease-in-out 4;
}
.animate-blink-row {
  animation: blinkRow 0.8s ease-in-out 4;
}
.dark .animate-blink {
  animation: blinkOrderDark 0.8s ease-in-out 4;
}
.dark .animate-blink-row {
  animation: blinkRowDark 0.8s ease-in-out 4;
}

@keyframes blinkOrder {
  0%, 100% { background-color: transparent; }
  50% { background-color: #FFEB3B; }
}
@keyframes blinkRow {
  0%, 100% { background-color: transparent; }
  50% { background-color: #FFF9C4; }
}
@keyframes blinkOrderDark {
  0%, 100% { background-color: transparent; }
  50% { background-color: #FFD700; }
}
@keyframes blinkRowDark {
  0%, 100% { background-color: transparent; }
  50% { background-color: #FFD700; }
}

.hover-bg-0:hover {
  background-color: rgba(255, 235, 59, 0.15) !important;
  box-shadow: 0 4px 10px -2px rgba(255, 235, 59, 0.75);
}
.hover-bg-1:hover {
  background-color: rgba(251, 191, 36, 0.15) !important;
  box-shadow: 0 4px 10px -2px rgba(251, 191, 36, 0.75);
}
.hover-bg-2:hover {
  background-color: rgba(56, 189, 248, 0.15) !important;
  box-shadow: 0 4px 10px -2px rgba(56, 189, 248, 0.75);
}
.hover-bg-3:hover {
  background-color: rgba(52, 211, 153, 0.15) !important;
  box-shadow: 0 4px 10px -2px rgba(52, 211, 153, 0.75);
}

.hover-bg-row-0:hover {
  background-color: rgba(255, 235, 59, 0.15) !important;
}
.hover-bg-row-1:hover {
  background-color: rgba(251, 191, 36, 0.15) !important;
}
.hover-bg-row-2:hover {
  background-color: rgba(56, 189, 248, 0.15) !important;
}
.hover-bg-row-3:hover {
  background-color: rgba(52, 211, 153, 0.15) !important;
}

.dark .hover-bg-0:hover,
.dark .hover-bg-row-0:hover {
  background-color: rgba(255, 235, 59, 0.08) !important;
}
.dark .hover-bg-1:hover,
.dark .hover-bg-row-1:hover {
  background-color: rgba(251, 191, 36, 0.08) !important;
}
.dark .hover-bg-2:hover,
.dark .hover-bg-row-2:hover {
  background-color: rgba(56, 189, 248, 0.08) !important;
}
.dark .hover-bg-3:hover,
.dark .hover-bg-row-3:hover {
  background-color: rgba(52, 211, 153, 0.08) !important;
}
</style>