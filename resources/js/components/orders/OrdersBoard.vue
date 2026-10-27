<template>
  <div class="h-full flex flex-col overflow-hidden">
    <div class="flex-shrink-0 mb-3 short:mb-1 lg:mb-7">
      <div class="flex flex-col md:flex-row md:items-center short:flex-row short:items-center gap-2 short:gap-1 lg:mt-2 lg:ml-4">
        <div class="flex-1 w-full md:max-w-md short:w-auto short:min-w-0">
          <div class="relative">
            <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input
              ref="searchInput"
              v-model="searchQuery"
              type="text"
              placeholder="Buscar por cliente o código..."
              class="w-full pl-9 pr-10 py-2.5 short:py-1.5 text-sm short:text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
            <button
              v-if="searchQuery"
              type="button"
              @mousedown.prevent
              @click="clearSearch"
              aria-label="Borrar búsqueda"
              class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full flex items-center justify-center bg-gray-300 hover:bg-gray-400 text-gray-700 dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-gray-100 transition-colors"
            >
              <i class="ti ti-x text-sm"></i>
            </button>
          </div>
        </div>

        <div class="flex items-center gap-2 short:gap-1 short:flex-shrink-0">
          <button
            class="hidden md:flex md:flex-initial flex-1 justify-center bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-3.5 py-2.5 short:py-1.5 rounded-lg text-sm flex items-center gap-2 transition-colors"
          >
            <i class="ti ti-filter text-base"></i>
            <span class="hidden sm:inline">Filtros</span>
          </button>
          <button
            @click="refreshOrders"
            class="flex-1 md:flex-initial short:flex-initial justify-center bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-3.5 py-2.5 short:py-1.5 rounded-lg text-sm flex items-center gap-2 transition-colors"
          >
            <i class="ti ti-refresh text-base" :class="{ 'animate-spin': loading }"></i>
            <span class="hidden sm:inline short:hidden">Actualizar</span>
          </button>
          <button
            @click="showChoiceModal = true"
            class="flex-1 md:flex-initial short:flex-initial justify-center bg-blue-500 dark:bg-blue-600 hover:bg-blue-600 dark:hover:bg-blue-700 text-white px-3.5 py-2.5 short:py-1.5 rounded-lg text-sm flex items-center gap-2 transition-colors"
          >
            <i class="ti ti-plus text-base"></i>
            <span>Nuevo</span>
          </button>
        </div>
      </div>
    </div>

    <div class="flex-shrink-0 mb-3 short:mb-1 lg:mb-4">
      <div class="hidden lg:block">
        <div class="relative h-1 mb-2">
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
                    : (hasColumnMatches(column.type)
                        ? 'opacity-95 hover:opacity-100 hover:shadow-md'
                        : 'opacity-60 hover:opacity-80 hover:shadow-sm')
                ]"
                :style="{
                  backgroundColor: column.color.inactive,
                  outline: activeColumn === index ? `2px solid ${column.color.outline}` : 'none',
                  outlineOffset: '0px',
                  boxShadow: activeColumn === index
                    ? `inset 0 0 0 2px ${column.color.outline}, 0 10px 15px -3px rgba(0, 0, 0, 0.1)`
                    : (hasColumnMatches(column.type)
                        ? `inset 0 0 0 1.5px ${column.color.outline}80, 0 0 12px -2px ${column.color.outline}99`
                        : 'none')
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
                  {{ formatColumnCount(column.type) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="lg:hidden short:hidden">
        <div class="grid grid-cols-2 gap-2">
          <div v-for="(column, index) in columns" :key="index"
              @click="selectColumn(index)"
              :class="[
                'relative rounded-lg cursor-pointer transition-all duration-200',
                'flex flex-row items-center justify-center gap-2.5 p-2',
                activeColumn === index
                  ? 'shadow-lg'
                  : (hasColumnMatches(column.type)
                      ? 'opacity-95 hover:opacity-100'
                      : 'opacity-60 hover:opacity-90')
              ]"
              :style="{
                backgroundColor: column.color.inactive,
                border: activeColumn === index ? `2px solid ${column.color.outline}` : '2px solid transparent',
                boxShadow: activeColumn === index
                  ? `inset 0 0 0 1px ${column.color.outline}`
                  : (hasColumnMatches(column.type)
                      ? `inset 0 0 0 1.5px ${column.color.outline}80, 0 0 10px -2px ${column.color.outline}99`
                      : 'none')
              }">
            <div class="w-9 h-9 rounded-md flex items-center justify-center flex-shrink-0"
                :style="{ backgroundColor: column.color.active }">
              <i :class="column.icon + ' text-white text-base'"></i>
            </div>
            <div class="flex flex-col items-center justify-center min-w-0">
              <span class="text-xs font-semibold leading-tight text-center" :style="{ color: column.textColor.active }">
                {{ column.title }}
              </span>
              <span class="text-lg font-bold leading-tight" :style="{ color: column.textColor.active }">
                {{ formatColumnCount(column.type) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="hidden short:block">
        <div class="relative">
          <button
            type="button"
            @click="shortColumnDropdownOpen = !shortColumnDropdownOpen"
            @blur="closeShortColumnDropdown"
            class="w-full text-xs font-medium px-2 py-1.5 rounded-lg border-2 flex items-center justify-between transition-colors"
            :style="{
              backgroundColor: columns[activeColumn]?.color.inactive,
              borderColor: columns[activeColumn]?.color.outline,
              color: columns[activeColumn]?.textColor.active
            }"
          >
            <span class="flex items-center gap-2 min-w-0">
              <i :class="columns[activeColumn]?.icon + ' text-base flex-shrink-0'"
                 :style="{ color: columns[activeColumn]?.textColor.active }"></i>
              <span class="font-semibold truncate">{{ columns[activeColumn]?.title }}</span>
              <span class="font-bold">{{ formatColumnCount(columns[activeColumn]?.type) }}</span>
            </span>
            <i class="ti ti-chevron-down text-sm flex-shrink-0 transition-transform duration-200"
               :class="{ 'rotate-180': shortColumnDropdownOpen }"></i>
          </button>

          <transition name="dropdown">
            <div v-if="shortColumnDropdownOpen"
                 @mousedown.prevent
                 class="absolute z-20 w-full mt-1 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-32 overflow-y-auto">
              <button
                v-for="(col, i) in columns"
                :key="i"
                @mousedown.prevent="onSelectShortColumn(i)"
                class="w-full px-3 py-2 text-xs flex items-center gap-2 transition-[filter] duration-150 hover:brightness-95 active:brightness-90"
                :style="{
                  backgroundColor: col.color.inactive,
                  color: col.textColor.active,
                  boxShadow: hasColumnMatches(col.type) && i !== activeColumn
                    ? `inset 0 0 0 1.5px ${col.color.outline}80`
                    : 'none'
                }"
              >
                <i :class="col.icon + ' text-base flex-shrink-0'" :style="{ color: col.textColor.active }"></i>
                <span class="flex-1 text-left font-medium">{{ col.title }}</span>
                <span class="font-bold">{{ formatColumnCount(col.type) }}</span>
                <i v-if="i === activeColumn"
                   class="ti ti-check text-sm flex-shrink-0"
                   :style="{ color: col.textColor.active }"></i>
              </button>
            </div>
          </transition>
        </div>
      </div>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden min-h-0">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow flex-1 flex flex-col overflow-hidden min-h-0">
        <div class="flex-shrink-0 p-2 sm:p-3 short:p-1.5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-2 flex-wrap">
          <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
            <span class="hidden lg:inline-flex short:inline-flex font-medium px-2 py-1 short:py-0.5 rounded-full text-xs"
                  :style="{
                    backgroundColor: columns[activeColumn]?.color.inactive,
                    color: columns[activeColumn]?.textColor.active
                  }">
              {{ columns[activeColumn]?.title }}
            </span>
            <span class="hidden lg:inline short:inline mx-1">•</span>
            <span>{{ filteredOrders.length }} órdenes</span>
            <span v-if="searchQuery" class="text-gray-400 ml-2 short:hidden">
              (filtrado)
            </span>
          </div>

          <div class="flex items-center gap-1.5 sm:gap-2 short:gap-1">
            <button
              @click="toggleDateSort"
              class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-2 sm:px-3 py-2 short:py-1 rounded-lg text-xs sm:text-sm short:text-xs flex items-center gap-1 sm:gap-2 short:gap-1 transition-colors"
            >
              <i
                class="ti ti-sort-descending text-sm sm:text-base short:text-xs transition-transform duration-300 ease-out"
                :class="{ 'rotate-180': dateSort === 'asc' }"
              ></i>
              <span class="relative whitespace-nowrap">
                <span class="invisible" aria-hidden="true">Recientes</span>
                <Transition name="text-swap" mode="out-in">
                  <span :key="dateSort" class="absolute inset-0 flex items-center">
                    {{ dateSort === 'desc' ? 'Recientes' : 'Antiguas' }}
                  </span>
                </Transition>
              </span>
            </button>
            <button
              @click="toggleViewMode"
              class="flex items-center gap-1 sm:gap-2 short:gap-1 px-2 sm:px-3 py-2 short:py-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-800 dark:text-white text-xs sm:text-sm short:text-xs transition-colors"
            >
              <Transition name="icon-swap" mode="out-in">
                <i
                  :key="viewMode"
                  :class="viewMode === 'cards' ? 'ti ti-table' : 'ti ti-layout-grid'"
                  class="text-sm sm:text-base short:text-xs"
                ></i>
              </Transition>
              <span class="relative whitespace-nowrap">
                <span class="invisible" aria-hidden="true">Tarjetas</span>
                <Transition name="text-swap" mode="out-in">
                  <span :key="viewMode" class="absolute inset-0 flex items-center">
                    {{ viewMode === 'cards' ? 'Tabla' : 'Tarjetas' }}
                  </span>
                </Transition>
              </span>
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

          <transition v-else :name="transitionName" mode="out-in">
            <div
              v-if="filteredOrders.length === 0"
              :key="'empty-' + activeColumn"
              class="absolute inset-0 flex items-center justify-center p-4"
            >
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

            <div
              v-else
              :key="'content-' + activeColumn"
              class="h-full overflow-y-auto overflow-x-hidden p-2 sm:p-3 short:p-1.5"
            >
              <transition name="view-fade" mode="out-in">
                <div v-if="viewMode === 'cards'" key="cards">
                  <TransitionGroup
                    name="list"
                    tag="div"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 short:gap-2"
                  >
                    <div v-for="order in paginatedOrders" :key="order.id"
                         :id="'order-card-' + order.id"
                         @click="openOrderDetail(order)"
                         :class="[
                           'bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 transition-all duration-200 cursor-pointer overflow-hidden group',
                           `hover-bg-${activeColumn}`,
                           isBlinking(order.id) ? 'animate-blink' : ''
                         ]">
                      <div class="h-1.5" :style="{ backgroundColor: columns[activeColumn]?.color.active }"></div>
                      <div class="p-3 short:p-2">
                        <div class="flex justify-between items-start mb-2 short:mb-1 gap-2">
                          <div class="min-w-0">
                            <h3 class="font-bold text-gray-800 dark:text-gray-200 text-sm">
                              <span
                                v-for="(seg, i) in getSegments('#' + orderCode(order), orderMatchesCode(order))"
                                :key="'c-code-' + order.id + '-' + i"
                                :style="seg.match ? cellMatchStyle : null"
                              >{{ seg.text }}</span>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 short:mt-0">
                              <i class="ti ti-calendar mr-1"></i>
                              {{ formatDate(order.created_at) }}
                            </p>
                          </div>
                          <span class="px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0"
                                :class="statusClass(order.status)">
                            {{ statusLabel(order.status) }}
                          </span>
                        </div>

                        <div class="mb-2 short:mb-1">
                          <p class="text-gray-600 dark:text-gray-400 text-xs line-clamp-2">
                            <span
                              v-for="(seg, i) in getSegments(order.customer?.name || 'Sin cliente', orderMatchesName(order))"
                              :key="'c-name-' + order.id + '-' + i"
                              :style="seg.match ? cellMatchStyle : null"
                            >{{ seg.text }}</span>
                          </p>
                        </div>

                        <div class="flex justify-between items-center text-xs mb-2 short:mb-1 gap-2">
                          <div class="flex items-center min-w-0">
                            <i class="ti ti-package mr-1 text-gray-500 flex-shrink-0"></i>
                            <span class="text-gray-600 dark:text-gray-400 truncate">{{ packagesSummary(order) }}</span>
                          </div>
                          <div class="flex items-center flex-shrink-0">
                            <i class="ti ti-currency-dollar mr-1 text-gray-500"></i>
                            <span class="text-gray-600 dark:text-gray-400">{{ formatCurrency(order.total) }}</span>
                          </div>
                        </div>

                        <div class="flex items-center justify-between text-xs gap-2">
                          <div class="flex items-center min-w-0">
                            <i class="ti ti-calendar-event mr-1 text-gray-500 flex-shrink-0"></i>
                            <span class="text-gray-600 dark:text-gray-400 truncate">Entrega: {{ formatDeliveryDate(order.estimated_delivery_date) }}</span>
                          </div>
                          <div class="flex items-center gap-1 flex-shrink-0">
                            <i class="ti ti-check-circle text-green-500" v-if="order.status === 'delivered'"></i>
                            <i class="ti ti-clock text-yellow-500" v-else></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </TransitionGroup>
                </div>

                <div v-else key="table" class="overflow-x-auto overflow-y-hidden">
                  <table class="min-w-[720px] w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
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
                    <TransitionGroup
                      name="list"
                      tag="tbody"
                      class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                    >
                      <tr v-for="order in paginatedOrders" :key="order.id"
                          :id="'order-card-' + order.id"
                          @click="openOrderDetail(order)"
                          class="transition-colors cursor-pointer"
                          :class="[
                            `hover-bg-row-${activeColumn}`,
                            isBlinking(order.id) ? 'animate-blink-row' : ''
                          ]">
                        <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">
                          <span
                            v-for="(seg, i) in getSegments('#' + orderCode(order), orderMatchesCode(order))"
                            :key="'t-code-' + order.id + '-' + i"
                            :style="seg.match ? cellMatchStyle : null"
                          >{{ seg.text }}</span>
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                          <span
                            v-for="(seg, i) in getSegments(order.customer?.name || 'Sin cliente', orderMatchesName(order))"
                            :key="'t-name-' + order.id + '-' + i"
                            :style="seg.match ? cellMatchStyle : null"
                          >{{ seg.text }}</span>
                        </td>
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
                    </TransitionGroup>
                  </table>
                </div>
              </transition>
            </div>
          </transition>
        </div>

        <div class="flex-shrink-0 mt-2 sm:mt-3 short:mt-1 pb-2 sm:pb-3 short:pb-1 pt-2 sm:pt-3 short:pt-1 border-t border-gray-200 dark:border-gray-700 px-3">
          <ResponsivePagination v-model:current="currentPage" :total="totalPages" />
        </div>

        <div class="flex-shrink-0 hidden sm:flex short:hidden flex-wrap items-center justify-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400 pb-2">
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

    <OrderDetailModal
      :show="showOrderDetailModal"
      :order="selectedOrderForDetail"
      :columns="columns"
      :active-column="activeColumn"
      @edit="handleOrderDetailEdit"
      @close="handleOrderDetailClosed"
      @updated="handleOrderDetailUpdated"
      @notify="handleNotify"
    />

    <OrderEditModal
      :show="showOrderEditModal"
      :order="selectedOrderForEdit"
      @close="handleOrderEditClosed"
      @updated="handleOrderEditUpdated"
      @notify="handleNotify"
    />

    <transition name="fade-slide">
      <div v-if="showNotification" class="fixed top-3 right-3 left-3 sm:left-auto z-50 max-w-xs sm:w-full mx-auto sm:mx-0">
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
import OrderEditModal from './OrderEditModal.vue';
import ResponsivePagination from '../common/ResponsivePagination.vue';

export default {
  name: 'OrdersBoard',
  components: { OrderModal, QuoteModal, NewRecordChoiceModal, OrderDetailModal, OrderEditModal, ResponsivePagination },
  data() {
    return {
      showOrderEditModal: false,
      selectedOrderForEdit: null,
      isDarkMode: false,
      darkObserver: null,
      showOrderDetailModal: false,
      selectedOrderForDetail: null,
      pendingBlinkOrderId: null,
      activeColumn: 0,
      viewMode: 'table',
      loading: true,
      orders: [],
      searchQuery: '',
      dateSort: 'desc',
      showOrderModal: false,
      showQuoteModal: false,
      showChoiceModal: false,
      pendingNavigateToQuotes: false,
      shortColumnDropdownOpen: false,
      transitionName: 'view-fade',

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
    cellMatchStyle() {
      const col = this.columns[this.activeColumn];
      if (!col) return {};
      const hex = col.color.outline.replace('#', '');
      const r = parseInt(hex.substring(0, 2), 16);
      const g = parseInt(hex.substring(2, 4), 16);
      const b = parseInt(hex.substring(4, 6), 16);
      const aBg = this.isDarkMode ? 0.22 : 0.28;
      const aBorder = this.isDarkMode ? 0.4 : 0.5;
      return {
        backgroundColor: `rgba(${r}, ${g}, ${b}, ${aBg})`,
        boxShadow: `inset 0 0 0 1px rgba(${r}, ${g}, ${b}, ${aBorder})`,
        borderRadius: '4px',
        padding: '0 3px'
      };
    },
    columnCountsDetailed() {
      const q = this.normalizeText(this.searchQuery);
      const result = {};
      this.columns.forEach(col => {
        const all = this.orders.filter(o => o.status === col.type);
        const matched = q
          ? all.filter(o => this.matchesSearch(o, q)).length
          : all.length;
        result[col.type] = { matched, total: all.length };
      });
      return result;
    },
    filteredOrders() {
      const columnType = this.columns[this.activeColumn]?.type;
      let list = this.orders.filter(o => o.status === columnType);

      const q = this.normalizeText(this.searchQuery);
      if (q) {
        list = list.filter(o => this.matchesSearch(o, q));
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
      this.transitionName = 'view-fade';

      const q = this.normalizeText(this.searchQuery);
      if (!q) return;

      const matchedColumns = this.columns
        .map((col, idx) => ({ idx, matched: this.columnCountsDetailed[col.type]?.matched || 0 }))
        .filter(c => c.matched > 0);

      if (matchedColumns.length === 1 && this.activeColumn !== matchedColumns[0].idx) {
        this.activeColumn = matchedColumns[0].idx;
      }
    },
    activeColumn(newVal, oldVal) {
      this.currentPage = 1;
      this.transitionName = newVal > oldVal ? 'col-slide-right' : 'col-slide-left';
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
    this.stopBlink();
    if (this.notificationTimeout) clearTimeout(this.notificationTimeout);
    if (this.darkObserver) {
      this.darkObserver.disconnect();
      this.darkObserver = null;
    }
  },
  methods: {
    normalizeText(value) {
      return String(value ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
    },
    matchesSearch(order, normalizedQuery) {
      if (!normalizedQuery) return true;
      const q = normalizedQuery;
      const qNumeric = q.replace(/^ord-?/, '');
      const numericCode = String(order.id).padStart(3, '0');
      const name = this.normalizeText(order.customer?.name || '');

      if (qNumeric && numericCode.includes(qNumeric)) return true;
      if (name.includes(q)) return true;

      return false;
    },
    orderMatchesCode(order) {
      const q = this.normalizeText(this.searchQuery);
      if (!q) return false;
      const qNumeric = q.replace(/^ord-?/, '');
      if (!qNumeric) return false;
      return String(order.id).padStart(3, '0').includes(qNumeric);
    },
    orderMatchesName(order) {
      const q = this.normalizeText(this.searchQuery);
      if (!q) return false;
      return this.normalizeText(order.customer?.name || '').includes(q);
    },
    buildNormalizedMap(text) {
      const str = String(text ?? '');
      let normalized = '';
      const map = [];
      for (let i = 0; i < str.length; i++) {
        const ch = str[i];
        let norm = ch.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        if (norm.length === 0) {
          norm = ch;
        }
        for (let j = 0; j < norm.length; j++) {
          normalized += norm[j];
          map.push(i);
        }
      }
      map.push(str.length);
      return { normalized, map };
    },
    getSegments(text, allowMatch = true) {
      const str = String(text ?? '');
      const q = this.normalizeText(this.searchQuery);
      if (!str || !q || !allowMatch) return [{ text: str, match: false }];

      const { normalized, map } = this.buildNormalizedMap(str);
      const segments = [];
      let normCursor = 0;
      let origCursor = 0;
      let found = false;

      while (normCursor <= normalized.length - q.length) {
        const idx = normalized.indexOf(q, normCursor);
        if (idx === -1) break;
        found = true;

        const origStart = map[idx];
        const origEnd = map[idx + q.length];

        if (origStart > origCursor) {
          segments.push({ text: str.slice(origCursor, origStart), match: false });
        }
        segments.push({ text: str.slice(origStart, origEnd), match: true });

        origCursor = origEnd;
        normCursor = idx + q.length;
      }

      if (!found) return [{ text: str, match: false }];

      if (origCursor < str.length) {
        segments.push({ text: str.slice(origCursor), match: false });
      }

      return segments;
    },
    hasColumnMatches(type) {
      if (!this.normalizeText(this.searchQuery)) return false;
      return (this.columnCountsDetailed[type]?.matched || 0) > 0;
    },
    handleOrderDetailEdit(order) {
      this.showOrderDetailModal = false;
      this.selectedOrderForDetail = null;
      this.selectedOrderForEdit = order;
      this.showOrderEditModal = true;
    },
    handleOrderEditClosed() {
      this.showOrderEditModal = false;
      this.selectedOrderForEdit = null;
    },
    async handleOrderEditUpdated() {
      await this.loadOrders();
      if (this.selectedOrderForEdit) {
        const refreshed = this.orders.find(o => o.id === this.selectedOrderForEdit.id);
        this.selectedOrderForEdit = null;
        this.showOrderEditModal = false;
        if (refreshed) {
          this.selectedOrderForDetail = refreshed;
          this.showOrderDetailModal = true;
        }
      }
    },
    goToPhotoTypesCatalog() {
      this.$router.push('/catalogos?tab=photo-types');
    },
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
        this.$router.push('/cotizaciones');
      }
    },
    openOrderDetail(order) {
      if (this.newOrderId === order.id) {
        this.stopBlink();
      }
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
    },
    onSelectShortColumn(index) {
      this.selectColumn(index);
      this.shortColumnDropdownOpen = false;
    },
    closeShortColumnDropdown() {
      this.shortColumnDropdownOpen = false;
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
    clearSearch() {
      this.searchQuery = '';
      this.$nextTick(() => {
        this.$refs.searchInput?.focus();
      });
    },
    formatColumnCount(type) {
      const c = this.columnCountsDetailed[type];
      if (!c) return '0';
      return this.normalizeText(this.searchQuery)
        ? `${c.matched}/${c.total}`
        : String(c.total);
    },
    orderCode(order) {
      return 'ORD-' + String(order.id).padStart(3, '0');
    },
    packagesSummary(order) {
      if (order.order_packages && order.order_packages.length) {
        return order.order_packages
          .map(p => p.package_name_snapshot || ('Paquete #' + p.package_id))
          .join(', ');
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
      this.stopBlink();
      this.newOrderId = orderId;
      let count = 0;
      const maxCount = 8;
      this.blinkInterval = setInterval(() => {
        count++;
        if (count >= maxCount) {
          this.stopBlink();
        }
      }, 400);
    },
    stopBlink() {
      if (this.blinkInterval) {
        clearInterval(this.blinkInterval);
        this.blinkInterval = null;
      }
      this.newOrderId = null;
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

.overflow-y-auto,
.overflow-x-auto,
.overflow-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 #f1f1f1;
  overscroll-behavior: contain;
}
.dark .overflow-y-auto,
.dark .overflow-x-auto,
.dark .overflow-auto {
  scrollbar-color: #4b5563 #374151;
}

.overflow-y-auto::-webkit-scrollbar,
.overflow-x-auto::-webkit-scrollbar,
.overflow-auto::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track,
.overflow-x-auto::-webkit-scrollbar-track,
.overflow-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}
.overflow-y-auto::-webkit-scrollbar-thumb,
.overflow-x-auto::-webkit-scrollbar-thumb,
.overflow-auto::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 3px;
  transition: background-color 0.15s ease;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover,
.overflow-x-auto::-webkit-scrollbar-thumb:hover,
.overflow-auto::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}
.dark .overflow-y-auto::-webkit-scrollbar-track,
.dark .overflow-x-auto::-webkit-scrollbar-track,
.dark .overflow-auto::-webkit-scrollbar-track {
  background: #374151;
}
.dark .overflow-y-auto::-webkit-scrollbar-thumb,
.dark .overflow-x-auto::-webkit-scrollbar-thumb,
.dark .overflow-auto::-webkit-scrollbar-thumb {
  background: #4b5563;
}
.dark .overflow-y-auto::-webkit-scrollbar-thumb:hover,
.dark .overflow-x-auto::-webkit-scrollbar-thumb:hover,
.dark .overflow-auto::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}
.overflow-x-auto::-webkit-scrollbar-corner,
.overflow-auto::-webkit-scrollbar-corner {
  background: #f1f1f1;
}
.dark .overflow-x-auto::-webkit-scrollbar-corner,
.dark .overflow-auto::-webkit-scrollbar-corner {
  background: #374151;
}

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

.dropdown-enter-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
  transform-origin: top center;
}
.dropdown-leave-active {
  transition: opacity 0.14s ease, transform 0.14s ease;
  transform-origin: top center;
}
.dropdown-enter-from {
  opacity: 0;
  transform: scaleY(0.9) translateY(-4px);
}
.dropdown-leave-to {
  opacity: 0;
  transform: scaleY(0.9) translateY(-4px);
}

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

.hover-bg-0:not(.animate-blink):not(.animate-blink-row):hover {
  background-color: rgba(255, 235, 59, 0.15) !important;
  box-shadow: 0 4px 10px -2px rgba(255, 235, 59, 0.75);
}
.hover-bg-1:not(.animate-blink):not(.animate-blink-row):hover {
  background-color: rgba(251, 191, 36, 0.15) !important;
  box-shadow: 0 4px 10px -2px rgba(251, 191, 36, 0.75);
}
.hover-bg-2:not(.animate-blink):not(.animate-blink-row):hover {
  background-color: rgba(56, 189, 248, 0.15) !important;
  box-shadow: 0 4px 10px -2px rgba(56, 189, 248, 0.75);
}
.hover-bg-3:not(.animate-blink):not(.animate-blink-row):hover {
  background-color: rgba(52, 211, 153, 0.15) !important;
  box-shadow: 0 4px 10px -2px rgba(52, 211, 153, 0.75);
}

.hover-bg-row-0:not(.animate-blink):not(.animate-blink-row):hover {
  background-color: rgba(255, 235, 59, 0.15) !important;
}
.hover-bg-row-1:not(.animate-blink):not(.animate-blink-row):hover {
  background-color: rgba(251, 191, 36, 0.15) !important;
}
.hover-bg-row-2:not(.animate-blink):not(.animate-blink-row):hover {
  background-color: rgba(56, 189, 248, 0.15) !important;
}
.hover-bg-row-3:not(.animate-blink):not(.animate-blink-row):hover {
  background-color: rgba(52, 211, 153, 0.15) !important;
}

.dark .hover-bg-0:not(.animate-blink):not(.animate-blink-row):hover,
.dark .hover-bg-row-0:not(.animate-blink):not(.animate-blink-row):hover {
  background-color: rgba(255, 235, 59, 0.08) !important;
}
.dark .hover-bg-1:not(.animate-blink):not(.animate-blink-row):hover,
.dark .hover-bg-row-1:not(.animate-blink):not(.animate-blink-row):hover {
  background-color: rgba(251, 191, 36, 0.08) !important;
}
.dark .hover-bg-2:not(.animate-blink):not(.animate-blink-row):hover,
.dark .hover-bg-row-2:not(.animate-blink):not(.animate-blink-row):hover {
  background-color: rgba(56, 189, 248, 0.08) !important;
}
.dark .hover-bg-3:not(.animate-blink):not(.animate-blink-row):hover,
.dark .hover-bg-row-3:not(.animate-blink):not(.animate-blink-row):hover {
  background-color: rgba(52, 211, 153, 0.08) !important;
}

.text-swap-enter-active,
.text-swap-leave-active {
  transition: opacity 0.12s ease, transform 0.12s ease;
}
.text-swap-enter-from {
  opacity: 0;
  transform: translateY(4px);
}
.text-swap-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
.icon-swap-enter-active,
.icon-swap-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.icon-swap-enter-from {
  opacity: 0;
  transform: scale(0.7);
}
.icon-swap-leave-to {
  opacity: 0;
  transform: scale(0.7);
}

.view-fade-enter-active,
.view-fade-leave-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
}
.view-fade-enter-from {
  opacity: 0;
  transform: translateY(6px) scale(0.99);
}
.view-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px) scale(0.99);
}

.col-slide-right-enter-active,
.col-slide-right-leave-active,
.col-slide-left-enter-active,
.col-slide-left-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.col-slide-right-enter-from {
  opacity: 0;
  transform: translateX(24px);
}
.col-slide-right-leave-to {
  opacity: 0;
  transform: translateX(-24px);
}

.col-slide-left-enter-from {
  opacity: 0;
  transform: translateX(-24px);
}
.col-slide-left-leave-to {
  opacity: 0;
  transform: translateX(24px);
}

.list-move {
  transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.list-move-move {
  transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

@media (prefers-reduced-motion: reduce) {
  .text-swap-enter-active,
  .text-swap-leave-active,
  .icon-swap-enter-active,
  .icon-swap-leave-active,
  .view-fade-enter-active,
  .view-fade-leave-active,
  .col-slide-right-enter-active,
  .col-slide-right-leave-active,
  .col-slide-left-enter-active,
  .col-slide-left-leave-active,
  .list-move,
  .list-move-move {
    transition: none;
  }
}
</style>