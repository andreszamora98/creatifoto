<template>
  <div class="h-full flex flex-col overflow-hidden">
    <!-- HEADER -->
    <div class="flex-shrink-0 mb-4">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
          <i class="ti ti-ticket mr-2 text-lg"></i>Tablero de órdenes
        </h2>
        <div class="flex items-center gap-2 flex-wrap">
          <button class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-3 py-1.5 rounded-lg text-xs flex items-center gap-1 transition-colors">
            <i class="ti ti-filter text-sm"></i>
            <span>Filtros</span>
          </button>
          <button class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-3 py-1.5 rounded-lg text-xs flex items-center gap-1 transition-colors">
            <i class="ti ti-sort-descending text-sm"></i>
            <span>Recientes</span>
          </button>
          <button class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-3 py-1.5 rounded-lg text-xs flex items-center gap-1 transition-colors">
            <i class="ti ti-refresh text-sm mr-1"></i>
            Actualizar
          </button>
          <button class="bg-blue-500 dark:bg-blue-600 hover:bg-blue-600 dark:hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs flex items-center gap-1 transition-colors">
            <i class="ti ti-plus text-sm"></i>
            <span>Nueva orden</span>
          </button>
        </div>
      </div>
    </div>

    <!-- PANEL SUPERIOR: Columnas -->
    <div class="flex-shrink-0 mb-4">
      <div class="relative mb-2">
        <div class="absolute h-1 rounded-full bg-gray-200 dark:bg-gray-500 bottom-0 left-5 right-5"></div>
        <div class="absolute h-1 rounded-full transition-all duration-300 ease-in-out bottom-0"
             :style="{
               width: `${100 / columns.length}%`,
               transform: `translateX(${activeColumn * 100}%)`,
               backgroundColor: columns[activeColumn]?.color.outline,
               boxShadow: '0 4px 6px rgba(0,0,0,0.1)'
             }">
        </div>
      </div>

      <div class="relative">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
          <div v-for="(column, index) in columns" :key="index"
               @click="activeColumn = index"
               :class="[
                 'category-card relative p-3 pt-4 rounded-lg cursor-pointer transition-all duration-200',
                 'flex flex-col items-center justify-center text-center',
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
            <div class="mb-3">
              <div class="w-10 h-10 rounded-md flex items-center justify-center shadow-lg"
                   :style="{
                     backgroundColor: column.color.active,
                     boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.2)'
                   }">
                <i :class="column.icon + ' text-white text-lg'"></i>
              </div>
            </div>
            <h3 class="font-semibold text-xs mb-1" :style="{ color: column.textColor.active }">
              {{ column.title }}
            </h3>
            <div class="text-base font-bold" :style="{ color: column.textColor.active }">
              {{ columnCounts[column.type] }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- PANEL INFERIOR: Grid de órdenes -->
    <div class="flex-1 flex flex-col overflow-hidden min-h-0">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow flex-1 flex flex-col overflow-hidden min-h-0">
        <!-- Barra de búsqueda y controles -->
        <div class="flex-shrink-0 p-3 border-b border-gray-200 dark:border-gray-700">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
            <div class="flex-1">
              <div class="relative">
                <i class="ti ti-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text"
                       placeholder="Buscar órdenes..."
                       class="w-full pl-9 pr-8 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              </div>
            </div>
            <div class="flex items-center gap-3 mt-2 md:mt-0">
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
              </div>
              <button 
                @click="toggleViewMode" 
                class="flex items-center gap-1 px-2 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-800 dark:text-white text-xs"
              >
                <i :class="viewMode === 'cards' ? 'ti ti-table' : 'ti ti-layout-grid'" class="text-xs"></i>
                <span>{{ viewMode === 'cards' ? 'Tabla' : 'Tarjetas' }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Contenido de órdenes -->
        <div class="flex-1 overflow-hidden min-h-0 relative">
          <div class="h-full overflow-y-auto p-3">
            <!-- Vista tarjetas -->
            <div v-if="viewMode === 'cards'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
              <div v-for="order in filteredOrders" :key="order.id"
                   class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all duration-200 cursor-pointer overflow-hidden group">
                <div class="h-1.5" :style="{ backgroundColor: columns[activeColumn]?.color.active }"></div>
                <div class="p-3">
                  <div class="flex justify-between items-start mb-2">
                    <div>
                      <h3 class="font-bold text-gray-800 dark:text-gray-200 text-sm">
                        #{{ order.code }}
                      </h3>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <i class="ti ti-calendar mr-1"></i>
                        {{ formatDate(order.created_at) }}
                      </p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                          :class="statusClass(order.status)">
                      {{ order.status }}
                    </span>
                  </div>

                  <div class="mb-2">
                    <p class="text-gray-600 dark:text-gray-400 text-xs line-clamp-2">
                      {{ order.customer_name }}
                    </p>
                  </div>

                  <div class="flex justify-between items-center text-xs mb-2">
                    <div class="flex items-center">
                      <i class="ti ti-package mr-1 text-gray-500"></i>
                      <span class="text-gray-600 dark:text-gray-400">{{ order.package_name }}</span>
                    </div>
                    <div class="flex items-center">
                      <i class="ti ti-currency-dollar mr-1 text-gray-500"></i>
                      <span class="text-gray-600 dark:text-gray-400">{{ order.price }}</span>
                    </div>
                  </div>

                  <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center">
                      <i class="ti ti-calendar-event mr-1 text-gray-500"></i>
                      <span class="text-gray-600 dark:text-gray-400">Entrega: {{ order.delivery_date }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                      <i class="ti ti-check-circle text-green-500" v-if="order.delivered"></i>
                      <i class="ti ti-clock text-yellow-500" v-else></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Vista tabla -->
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0 z-10">
                  <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Código</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Cliente</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Paquete</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Precio</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Estado</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">F. Entrega</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Entregado</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="order in filteredOrders" :key="order.id" 
                      class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                    <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">#{{ order.code }}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ order.customer_name }}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ order.package_name }}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ order.price }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">
                      <span class="px-2 py-0.5 text-xs rounded-full" :class="statusClass(order.status)">
                        {{ order.status }}
                      </span>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ order.delivery_date }}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      <i :class="order.delivered ? 'ti ti-check-circle text-green-500' : 'ti ti-clock text-yellow-500'"></i>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OrdersBoard',
  data() {
    return {
      activeColumn: 0,
      viewMode: 'cards', // 'cards' | 'table'
      columns: [
        {
          title: 'Pendientes',
          type: 'pending',
          icon: 'ti ti-clock',
          color: {
            active: '#FFEB3B',
            inactive: '#FFFDE7',
            outline: '#FFEB3B'
          },
          textColor: {
            active: '#D97706',
            inactive: '#F59E0B'
          }
        },
        {
          title: 'En proceso',
          type: 'in_progress',
          icon: 'ti ti-progress',
          color: {
            active: '#FFD8B1',
            inactive: '#FFF5EB',
            outline: '#FBBF24'
          },
          textColor: {
            active: '#DD6B20',
            inactive: '#ED8936'
          }
        },
        {
          title: 'Listas',
          type: 'ready',
          icon: 'ti ti-check',
          color: {
            active: '#B3E5FC',
            inactive: '#F0F9FF',
            outline: '#38BDF8'
          },
          textColor: {
            active: '#2B6CB0',
            inactive: '#4299E1'
          }
        },
        {
          title: 'Entregadas',
          type: 'delivered',
          icon: 'ti ti-checkbox',
          color: {
            active: '#C6F6D5',
            inactive: '#F0FFF4',
            outline: '#34D399'
          },
          textColor: {
            active: '#276749',
            inactive: '#38A169'
          }
        }
      ],
      orders: [
        {
          id: 1,
          code: 'ORD-001',
          customer_name: 'María González',
          package_name: 'Paquete Básico',
          price: '$150.00',
          status: 'Pendiente',
          delivered: false,
          delivery_date: '2026-09-01',
          created_at: '2026-08-24T10:00:00'
        },
        {
          id: 2,
          code: 'ORD-002',
          customer_name: 'Juan Pérez',
          package_name: 'Paquete Premium',
          price: '$350.00',
          status: 'En proceso',
          delivered: false,
          delivery_date: '2026-08-28',
          created_at: '2026-08-23T14:30:00'
        },
        {
          id: 3,
          code: 'ORD-003',
          customer_name: 'Ana López',
          package_name: 'Paquete Familiar',
          price: '$250.00',
          status: 'Listas',
          delivered: false,
          delivery_date: '2026-08-26',
          created_at: '2026-08-22T09:15:00'
        },
        {
          id: 4,
          code: 'ORD-004',
          customer_name: 'Carlos Ruiz',
          package_name: 'Paquete Básico',
          price: '$150.00',
          status: 'Entregadas',
          delivered: true,
          delivery_date: '2026-08-20',
          created_at: '2026-08-18T11:45:00'
        },
        {
          id: 5,
          code: 'ORD-005',
          customer_name: 'Laura Martínez',
          package_name: 'Paquete Premium',
          price: '$350.00',
          status: 'Pendiente',
          delivered: false,
          delivery_date: '2026-09-05',
          created_at: '2026-08-24T08:20:00'
        }
      ]
    };
  },
  computed: {
    columnCounts() {
      const counts = {};
      this.columns.forEach(col => {
        counts[col.type] = this.orders.filter(o => this.getColumnType(o.status) === col.type).length;
      });
      return counts;
    },
    filteredOrders() {
      const columnType = this.columns[this.activeColumn]?.type;
      return this.orders.filter(o => this.getColumnType(o.status) === columnType);
    }
  },
  methods: {
    toggleViewMode() {
      this.viewMode = this.viewMode === 'cards' ? 'table' : 'cards';
    },
    getColumnType(status) {
      const map = {
        'Pendiente': 'pending',
        'En proceso': 'in_progress',
        'Listas': 'ready',
        'Entregadas': 'delivered'
      };
      return map[status] || 'pending';
    },
    statusClass(status) {
      const colors = {
        'Pendiente': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'En proceso': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
        'Listas': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'Entregadas': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
      };
      return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
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
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 3px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}
.dark .overflow-y-auto::-webkit-scrollbar-track {
  background: #374151;
}
.dark .overflow-y-auto::-webkit-scrollbar-thumb {
  background: #4b5563;
}
.dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}
.category-card {
  transition: all 0.2s ease;
  min-height: 90px;
}
.category-card:hover {
  transform: translateY(-1px);
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>