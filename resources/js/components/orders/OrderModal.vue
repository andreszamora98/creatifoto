<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center z-50 p-3">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-5xl max-h-[92vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
              <i class="ti ti-plus mr-2"></i>Nueva orden
            </h3>
            <button @click="handleClose" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <i class="ti ti-x text-2xl"></i>
            </button>
          </div>

          <div class="space-y-8" @input="markDirty" @change="markDirty" @click="markDirty">

            <section>
              <h4 class="text-sm font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-500 text-white text-xs flex items-center justify-center font-bold">1</span>
                Datos del cliente
              </h4>

              <div>
                <label class="form-label text-base font-medium block mb-2 text-gray-700 dark:text-gray-300">Cliente <span class="text-red-500">*</span></label>
                <div class="relative">
                  <i class="ti ti-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-base"></i>
                  <input
                    type="text"
                    v-model="customerSearch"
                    @focus="showCustomerDropdown = true"
                    @blur="hideCustomerDropdown"
                    placeholder="Escribe el nombre completo del cliente..."
                    class="form-control form-control-sm w-full text-base p-3 pl-10 pr-9 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    :class="attemptedSubmit && fieldErrors.customer
                      ? 'border-red-500 dark:border-red-500'
                      : 'border-gray-300 dark:border-gray-600'"
                  />
                  <button v-if="customerSearch" @click="clearCustomerSearch" type="button"
                          class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="ti ti-x text-lg"></i>
                  </button>
                  <div v-if="showCustomerDropdown && filteredCustomers.length"
                      class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-48 overflow-y-auto">
                    <div v-for="c in filteredCustomers" :key="c.id"
                        @mousedown.prevent="selectCustomer(c)"
                        class="px-4 py-3 text-base hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer flex items-center gap-3 text-gray-800 dark:text-gray-200">
                      <i class="ti ti-user text-gray-400 dark:text-gray-500 flex-shrink-0 text-base"></i>
                      <span>{{ c.name }}</span>
                      <span class="text-gray-400 dark:text-gray-500 text-sm flex items-center gap-1" v-if="c.phone">
                        <i class="ti ti-phone text-xs"></i>{{ c.phone }}
                      </span>
                      <span class="text-gray-400 dark:text-gray-500 text-sm flex items-center gap-1" v-if="c.email">
                        <i class="ti ti-mail text-xs"></i>{{ c.email }}
                      </span>
                    </div>
                  </div>
                </div>
                <p v-if="selectedCustomer" class="text-base text-green-600 dark:text-green-400 mt-2">
                  <i class="ti ti-check"></i> Cliente ya existe en el registro: {{ selectedCustomer.name }}
                </p>
                <p v-else-if="customerSearch.trim() && !exactCustomerMatch" class="text-base text-blue-600 dark:text-blue-400 mt-2">
                  <i class="ti ti-user-plus"></i> Se creará un nuevo cliente: {{ customerSearch }}
                </p>
                <p v-if="attemptedSubmit && fieldErrors.customer" class="text-sm text-red-600 dark:text-red-400 mt-1 font-medium">
                  Campo obligatorio*
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-4">
                  <div>
                    <div class="flex items-center justify-between mb-2">
                      <label class="form-label text-base font-medium block text-gray-700 dark:text-gray-300">Teléfono</label>
                      <div class="flex items-center gap-2">
                        <button v-if="!editingPhone && selectedCustomer" @click="toggleEditPhone" type="button"
                                class="text-gray-400 hover:text-blue-500 transition-colors">
                          <i class="ti ti-pencil text-base"></i>
                        </button>
                        <template v-if="editingPhone">
                          <button v-if="canConfirmPhone" @click="confirmPhoneChange" type="button"
                                  class="text-green-500 hover:text-green-600 transition-colors" title="Guardar cambio">
                            <i class="ti ti-check text-base"></i>
                          </button>
                          <button @click="cancelPhoneEdit" type="button"
                                  class="text-red-500 hover:text-red-600 transition-colors" title="Dejar igual">
                            <i class="ti ti-x text-base"></i>
                          </button>
                        </template>
                      </div>
                    </div>
                    <div class="relative">
                      <i class="ti ti-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-base"></i>
                      <input
                        type="text"
                        v-model="customerPhone"
                        maxlength="10"
                        inputmode="numeric"
                        :disabled="!!selectedCustomer && !editingPhone"
                        placeholder="Ej. 4421234567"
                        class="form-control form-control-sm w-full text-base p-3 pl-10 pr-9 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-60 disabled:cursor-not-allowed"
                        :class="phoneError ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                      />
                    </div>
                    <p v-if="phoneError" class="text-xs text-red-600 dark:text-red-400 mt-1 font-medium">
                      {{ phoneError }}
                    </p>
                    <p v-if="phoneExists" class="text-xs text-red-600 dark:text-red-400 mt-1 font-medium">
                      <i class="ti ti-alert-triangle mr-1"></i>
                      Teléfono ya registrado a nombre de: {{ phoneExists.name }}
                    </p>
                    <p v-else-if="selectedCustomer && !editingPhone" class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                      Dato del cliente registrado. Pulsa <i class="ti ti-pencil"></i> para editarlo.
                    </p>
                    <p v-else-if="selectedCustomer && editingPhone" class="text-xs text-amber-500 dark:text-amber-400 mt-1">
                      <i class="ti ti-alert-triangle"></i> Confirma con <i class="ti ti-check"></i> o descarta con <i class="ti ti-x"></i>.
                    </p>
                  </div>
                  <div>
                    <div class="flex items-center justify-between mb-2">
                      <label class="form-label text-base font-medium block text-gray-700 dark:text-gray-300">Correo</label>
                      <div class="flex items-center gap-2">
                        <button v-if="!editingEmail && selectedCustomer" @click="toggleEditEmail" type="button"
                                class="text-gray-400 hover:text-blue-500 transition-colors">
                          <i class="ti ti-pencil text-base"></i>
                        </button>
                        <template v-if="editingEmail">
                          <button v-if="emailChanged" @click="confirmEmailChange" type="button"
                                  class="text-green-500 hover:text-green-600 transition-colors" title="Guardar cambio">
                            <i class="ti ti-check text-base"></i>
                          </button>
                          <button @click="cancelEmailEdit" type="button"
                                  class="text-red-500 hover:text-red-600 transition-colors" title="Dejar igual">
                            <i class="ti ti-x text-base"></i>
                          </button>
                        </template>
                      </div>
                    </div>
                    <div class="relative">
                      <i class="ti ti-mail absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-base"></i>
                      <input
                        type="email"
                        v-model="customerEmail"
                        maxlength="255"
                        :disabled="!!selectedCustomer && !editingEmail"
                        placeholder="Ej. cliente@correo.com"
                        class="form-control form-control-sm w-full text-base p-3 pl-10 pr-9 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-60 disabled:cursor-not-allowed"
                        :class="emailError ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
                      />
                    </div>
                    <p v-if="emailError" class="text-xs text-red-600 dark:text-red-400 mt-1 font-medium">
                      {{ emailError }}
                    </p>
                    <p v-if="emailExists" class="text-xs text-red-600 dark:text-red-400 mt-1 font-medium">
                      <i class="ti ti-alert-triangle mr-1"></i>
                      Correo ya registrado a nombre de: {{ emailExists.name }}
                    </p>
                    <p v-else-if="selectedCustomer && !editingEmail" class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                      Dato del cliente registrado. Pulsa <i class="ti ti-pencil"></i> para editarlo.
                    </p>
                    <p v-else-if="selectedCustomer && editingEmail" class="text-xs text-amber-500 dark:text-amber-400 mt-1">
                      <i class="ti ti-alert-triangle"></i> Confirma con <i class="ti ti-check"></i> o descarta con <i class="ti ti-x"></i>.
                    </p>
                  </div>
                </div>
              </div>
            </section>

            <div class="my-8 border-t-4 border-dashed border-gray-300 dark:border-gray-600"></div>

            <section class="space-y-6">
              <h4 class="text-sm font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-500 text-white text-xs flex items-center justify-center font-bold">2</span>
                Datos de la orden
              </h4>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                  <label class="form-label text-base font-medium mb-2 block text-gray-700 dark:text-gray-300">Fecha de entrega estimada <span class="text-red-500">*</span></label>
                  <div class="relative">
                    <input
                      ref="deliveryDateInput"
                      v-model="form.estimated_delivery_date"
                      type="date"
                      @click.prevent="toggleDatePicker"
                      @change="dateInputOpen = false"
                      @blur="dateInputOpen = false"
                      class="form-control form-control-sm w-full text-base p-3 pr-10 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 [&::-webkit-calendar-picker-indicator]:opacity-0"
                      :class="attemptedSubmit && fieldErrors.delivery_date
                        ? 'border-red-500 dark:border-red-500'
                        : 'border-gray-300 dark:border-gray-600'"
                    >
                    <i class="ti ti-calendar absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 pointer-events-none"></i>
                  </div>
                  <p v-if="attemptedSubmit && fieldErrors.delivery_date" class="text-sm text-red-600 dark:text-red-400 mt-1 font-medium">
                    Campo obligatorio*
                  </p>
                </div>
                <div>
                  <label class="form-label text-base font-medium mb-2 block text-gray-700 dark:text-gray-300">Toma seleccionada <span class="text-red-500">*</span></label>
                  <input v-model.number="form.pose_number" type="number" min="1"
                         class="form-control form-control-sm w-full text-base p-3 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                         :class="attemptedSubmit && fieldErrors.pose_number
                           ? 'border-red-500 dark:border-red-500'
                           : 'border-gray-300 dark:border-gray-600'"
                         placeholder="Número de toma">
                  <p v-if="attemptedSubmit && fieldErrors.pose_number" class="text-sm text-red-600 dark:text-red-400 mt-1 font-medium">
                    Campo obligatorio*
                  </p>
                </div>
              </div>

              <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5 border-2"
                   :class="attemptedSubmit && fieldErrors.photo_types ? 'border-red-500' : 'border-transparent'">
                <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-1 flex items-center">
                  <i class="ti ti-photo mr-2"></i>Tipos de fotografía <span class="text-red-500 ml-1">*</span>
                </h4>
                <p v-if="attemptedSubmit && fieldErrors.photo_types" class="text-sm text-red-600 dark:text-red-400 mb-3 font-medium">
                  Campo obligatorio*
                </p>
                <div v-if="loadingCatalogs" class="text-center py-4 text-base text-gray-500 dark:text-gray-400 mt-3">
                  <i class="ti ti-refresh animate-spin"></i> Cargando catálogo...
                </div>
                <div v-else
                     class="space-y-3 pr-1 mt-3"
                     :class="photoTypes.length > 4 ? 'max-h-72 overflow-y-auto' : ''">
                  <div v-for="pt in photoTypes" :key="pt.id"
                      class="p-4 rounded border transition-all cursor-pointer"
                      :class="lines[pt.id] && lines[pt.id].selected
                        ? 'bg-white dark:bg-gray-800 border-blue-500 dark:border-blue-400'
                        : 'bg-gray-100 dark:bg-gray-800/50 border-gray-200 dark:border-gray-600 opacity-60'"
                      @click="togglePhotoType(pt.id)">
                    <div class="flex items-center gap-4">
                      <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                          :class="lines[pt.id] && lines[pt.id].selected
                            ? 'border-blue-500 bg-blue-500'
                            : 'border-gray-400 dark:border-gray-500'">
                        <i v-if="lines[pt.id] && lines[pt.id].selected" class="ti ti-check text-white text-sm"></i>
                      </div>
                      <span class="flex-1 text-base font-medium text-gray-700 dark:text-gray-300">{{ pt.name }}</span>
                      <span class="text-sm text-gray-500 dark:text-gray-400">${{ Number(pt.base_price).toFixed(2) }} c/u</span>
                      <input v-if="lines[pt.id] && lines[pt.id].selected"
                            type="number" min="1" v-model.number="lines[pt.id].quantity"
                            @click.stop
                            @blur="onQuantityBlur(pt.id)"
                            class="form-control form-control-sm text-base p-2 w-24 text-center rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                    </div>
                    <div v-if="lines[pt.id] && lines[pt.id].selected" class="grid grid-cols-2 gap-4 mt-3 pl-10" @click.stop>
                      <div class="relative">
                        <button
                          type="button"
                          @click="toggleFormatDropdown(pt.id)"
                          @blur="closeFormatDropdown(pt.id)"
                          class="w-full text-base p-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 flex items-center justify-between"
                        >
                          <span class="flex items-center gap-2">
                            <i v-if="lines[pt.id].format === 'color'" class="ti ti-palette text-base text-pink-500"></i>
                            <span v-else class="inline-flex -space-x-1">
                              <span class="w-3 h-3 rounded-full bg-white border border-gray-400 dark:border-gray-500"></span>
                              <span class="w-3 h-3 rounded-full bg-black border border-gray-300 dark:border-gray-400"></span>
                            </span>
                            <span>{{ lines[pt.id].format === 'color' ? 'Color' : 'Blanco y negro' }}</span>
                          </span>
                          <i class="ti ti-chevron-down text-sm"></i>
                        </button>

                        <div v-if="openFormatDropdownId === pt.id"
                             class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
                          <button
                            @mousedown.prevent="selectFormat(pt.id, 'color')"
                            class="w-full px-3 py-2 text-base hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-800 dark:text-gray-200"
                          >
                            <i class="ti ti-palette text-base text-pink-500"></i> Color
                          </button>
                          <button
                            @mousedown.prevent="selectFormat(pt.id, 'bn')"
                            class="w-full px-3 py-2 text-base hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-800 dark:text-gray-200"
                          >
                            <span class="inline-flex -space-x-1">
                              <span class="w-3 h-3 rounded-full bg-white border border-gray-400 dark:border-gray-500"></span>
                              <span class="w-3 h-3 rounded-full bg-black border border-gray-300 dark:border-gray-400"></span>
                            </span>
                            Blanco y negro
                          </button>
                        </div>
                      </div>

                      <select v-model="lines[pt.id].paper_type_id" class="form-control form-control-sm text-base p-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                        <option :value="null">Papel (opcional)</option>
                        <option v-for="p in paperTypes" :key="p.id" :value="p.id" class="bg-white dark:bg-gray-800">{{ p.name }}</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5">
                <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center">
                  <i class="ti ti-package mr-2"></i>Paquetes escolares (opcional)
                </h4>
                <div v-if="!loadingCatalogs && packages.length === 0" class="text-base text-gray-500 dark:text-gray-400 italic">
                  No hay paquetes disponibles.
                </div>
                <div v-else class="space-y-3 max-h-56 overflow-y-auto pr-1">
                  <label v-for="pkg in packages" :key="pkg.id"
                         class="flex items-center justify-between p-4 rounded bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 cursor-pointer text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-4 text-base">
                      <input type="checkbox" :value="pkg.id" v-model="selectedPackageIds" class="w-5 h-5 text-blue-600 dark:text-blue-500">
                      {{ pkg.name || ('Paquete ' + pkg.package_number) }}
                      <span class="text-gray-400 dark:text-gray-500 text-sm" v-if="pkg.school">— {{ pkg.school.name }}</span>
                    </span>
                    <span class="text-base font-medium text-gray-600 dark:text-gray-400">${{ Number(pkg.total_price).toFixed(2) }}</span>
                  </label>
                </div>
              </div>

              <div class="flex items-center justify-between bg-blue-50 dark:bg-blue-900/20 rounded-lg p-5">
                <span class="text-base font-semibold text-blue-800 dark:text-blue-300">Total estimado</span>
                <span class="text-xl font-bold text-blue-800 dark:text-blue-300">${{ estimatedTotal.toFixed(2) }}</span>
              </div>

              <div>
                <label class="form-label text-base font-medium mb-2 block text-gray-700 dark:text-gray-300">Observaciones</label>
                <textarea v-model="form.notes" rows="4" class="form-control form-control-sm w-full text-base p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 resize-none"></textarea>
                <div class="flex justify-end mt-1">
                  <span class="text-xs" :class="notesWordCount >= 1000 ? 'text-red-500 dark:text-red-400 font-medium' : 'text-gray-400 dark:text-gray-500'">
                    {{ notesWordCount }}/1000 palabras
                  </span>
                </div>
              </div>
            </section>

            <div class="my-8 border-t-4 border-dashed border-gray-300 dark:border-gray-600"></div>

            <section>
              <h4 class="text-sm font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-500 text-white text-xs flex items-center justify-center font-bold">3</span>
                Pago
              </h4>

              <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5">
                <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                  <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                    <i class="ti ti-cash-banknote mr-2"></i>{{ paymentConcept }} <span class="text-red-500 ml-1">*</span>
                  </h4>
                  <span v-if="showBalanceBadge" class="text-sm font-semibold"
                        :class="remainingBalance > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400'">
                    <i :class="remainingBalance > 0 ? 'ti ti-progress-alert' : 'ti ti-circle-check'"></i>
                    {{ remainingBalance > 0 ? ('Restante: $' + remainingBalance.toFixed(2)) : 'Liquidado' }}
                  </span>
                </div>

                <label class="flex items-center gap-2 mb-4 text-sm text-gray-600 dark:text-gray-400 cursor-pointer select-none">
                  <input type="checkbox" :checked="liquidateAll" @change="toggleLiquidateAll" class="w-4 h-4 text-blue-600 dark:text-blue-500">
                  Liquidar todo con un solo pago
                </label>

                <div class="space-y-3">
                  <div v-for="(payment, idx) in payments" :key="idx"
                       class="flex flex-col md:flex-row gap-3 md:items-start bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-3">
                    <span class="text-sm font-semibold text-gray-500 dark:text-gray-400 w-16 flex-shrink-0 pt-2.5">Pago {{ idx + 1 }}</span>

                    <div class="flex-1 w-full md:w-auto">
                      <input v-model.number="payment.amount" type="number" min="0" step="0.01"
                            @input="onPaymentAmountInput(idx)"
                            placeholder="0.00"
                            class="form-control form-control-sm text-base p-2.5 rounded-lg border w-full text-gray-800 dark:text-gray-200"
                            :class="attemptedSubmit && (!payment.amount || payment.amount <= 0)
                              ? 'border-red-500 dark:border-red-500'
                              : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700'">

                      <p v-if="attemptedSubmit && (!payment.amount || payment.amount <= 0)" class="text-xs text-red-600 dark:text-red-400 mt-1 font-medium">
                        Campo obligatorio*
                      </p>
                    </div>

                    <div class="relative w-full md:w-44 flex-shrink-0">
                      <button
                        type="button"
                        @click="togglePaymentMethodDropdown(idx)"
                        @blur="closePaymentMethodDropdown(idx)"
                        class="w-full text-base p-2.5 rounded-lg border text-gray-800 dark:text-gray-200 flex items-center justify-between"
                        :class="attemptedSubmit && !payment.payment_method
                          ? 'border-red-500 dark:border-red-500'
                          : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700'"
                      >
                        <span class="flex items-center gap-2">
                          <i v-if="payment.payment_method === 'efectivo'" class="ti ti-cash text-lg text-green-600 dark:text-green-400"></i>
                          <i v-else-if="payment.payment_method === 'tarjeta'" class="ti ti-credit-card text-lg text-blue-600 dark:text-blue-400"></i>
                          <span v-if="payment.payment_method === 'efectivo'">Efectivo</span>
                          <span v-else-if="payment.payment_method === 'tarjeta'">Tarjeta</span>
                          <span v-else class="text-gray-400 dark:text-gray-500">Método</span>
                        </span>
                        <i class="ti ti-chevron-down text-sm"></i>
                      </button>

                      <div v-if="openPaymentMethodDropdownIdx === idx"
                           class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
                        <button @mousedown.prevent="selectLinePaymentMethod(idx, 'efectivo')"
                                class="w-full px-3 py-2 text-base hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-800 dark:text-gray-200">
                          <i class="ti ti-cash text-lg text-green-600 dark:text-green-400"></i> Efectivo
                        </button>
                        <button @mousedown.prevent="selectLinePaymentMethod(idx, 'tarjeta')"
                                class="w-full px-3 py-2 text-base hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-800 dark:text-gray-200">
                          <i class="ti ti-credit-card text-lg text-blue-600 dark:text-blue-400"></i> Tarjeta
                        </button>
                      </div>
                      <p v-if="attemptedSubmit && !payment.payment_method" class="text-xs text-red-600 dark:text-red-400 mt-1 font-medium">
                        Campo obligatorio*
                      </p>
                    </div>

                    <button v-if="payments.length > 1" @click="removePayment(idx)" type="button"
                            class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors flex-shrink-0 self-end md:self-start md:pt-2.5">
                      <i class="ti ti-trash text-lg"></i>
                    </button>
                  </div>
                </div>

                <button @click="addPayment" type="button" :disabled="remainingBalance <= 0 || liquidateAll"
                        class="mt-3 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1">
                  <i class="ti ti-plus"></i> Agregar otro pago
                </button>

                <p v-if="remainingBalance <= 0 && estimatedTotal > 0" class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                  El pago cubre el total de la orden. No se pueden agregar más pagos.
                </p>
              </div>
            </section>

          </div>

          <div class="flex gap-4 mt-8">
            <button @click="handleClose" class="flex-1 btn btn-light py-3 text-base rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition-colors">
              Cancelar
            </button>
            <button @click="submit" :disabled="submitting" class="flex-1 btn btn-primary py-3 text-base rounded-lg bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
              <span v-if="submitting"><i class="ti ti-refresh animate-spin mr-2"></i>Creando...</span>
              <span v-else><i class="ti ti-check mr-2"></i>Crear orden</span>
            </button>
          </div>

        </div>
      </div>
    </div>
  </transition>

  <ConfirmModal
    :show="showConfirmModal"
    title="¿Cerrar sin guardar?"
    message="Hay cambios sin guardar. ¿Estás seguro de que quieres cerrar esta ventana?"
    confirm-text="Cerrar de todos modos"
    cancel-text="Seguir editando"
    @confirm="closeConfirmed"
    @cancel="showConfirmModal = false"
  />
  <SuccessModal
    :show="showSuccessModal"
    title="¡Orden creada!"
    message="La orden se registró correctamente."
    @closed="onSuccessClosed"
  />
  <AlertModal
    :show="showAlertModal"
    title="Atención"
    :message="alertMessage"
    @closed="showAlertModal = false"
  />
</template>

<script>
import axios from 'axios';
import ConfirmModal from '../common/ConfirmModal.vue';
import SuccessModal from '../common/SuccessModal.vue';
import AlertModal from '../common/AlertModal.vue';

export default {
  name: 'OrderModal',
  components: { ConfirmModal, SuccessModal, AlertModal },
  props: {
    show: { type: Boolean, default: false }
  },
  emits: ['close', 'created', 'notify'],
  data() {
    return {
      alertMessage: '',
      showAlertModal: false,
      editingPhone: false,
      editingEmail: false,
      showSuccessModal: false,
      loadingCatalogs: false,
      submitting: false,
      isDirty: false,
      showConfirmModal: false,
      openFormatDropdownId: null,
      openPaymentMethodDropdownIdx: null,
      dateInputOpen: false,
      attemptedSubmit: false,
      initialState: null,
      liquidateAll: false,
      photoTypes: [],
      paperTypes: [],
      packages: [],
      customers: [],
      customerSearch: '',
      customerPhone: '',
      customerEmail: '',
      showCustomerDropdown: false,
      selectedCustomer: null,
      selectedPackageIds: [],
      lines: {},
      payments: [
        { amount: null, payment_method: '' }
      ],
      form: {
        estimated_delivery_date: '',
        pose_number: null,
        notes: ''
      }
    };
  },
  computed: {
    phoneExists() {
      const phone = this.customerPhone.trim();
      if (!phone) return null;
      return this.customers.find(c =>
        c.phone && c.phone.trim() === phone &&
        (!this.selectedCustomer || c.id !== this.selectedCustomer.id)
      ) || null;
    },
    emailExists() {
      const email = this.customerEmail.trim();
      if (!email) return null;
      return this.customers.find(c =>
        c.email && c.email.toLowerCase().trim() === email.toLowerCase() &&
        (!this.selectedCustomer || c.id !== this.selectedCustomer.id)
      ) || null;
    },
    canConfirmPhone() {
      const trimmed = this.customerPhone.trim();
      return this.phoneChanged && trimmed.length === 10 && /^\d{10}$/.test(trimmed);
    },
    phoneError() {
      const trimmed = this.customerPhone.trim();
      if (!trimmed) return null;
      if (!/^\d{10}$/.test(trimmed)) {
        return 'El teléfono debe tener exactamente 10 dígitos.';
      }
      return null;
    },
    emailError() {
      const trimmed = this.customerEmail.trim();
      if (!trimmed) return null;
      if (trimmed.length > 255) {
        return 'El correo no puede superar 255 caracteres.';
      }
      return null;
    },
    exactCustomerMatch() {
      const trimmed = this.customerSearch.trim().toLowerCase();
      if (!trimmed) return null;
      return this.customers.find(c => c.name.trim().toLowerCase() === trimmed) || null;
    },
    filteredCustomers() {
      if (!this.customerSearch.trim()) return [];
      const q = this.customerSearch.toLowerCase().trim();
      return this.customers.filter(c =>
        (c.name || '').toLowerCase().startsWith(q)
      );
    },
    phoneChanged() {
      if (!this.selectedCustomer) return false;
      return this.customerPhone.trim() !== (this.selectedCustomer.phone || '').trim();
    },
    emailChanged() {
      if (!this.selectedCustomer) return false;
      return this.customerEmail.trim() !== (this.selectedCustomer.email || '').trim();
    },
    activeLines() {
      return Object.entries(this.lines)
        .filter(([, l]) => l.selected)
        .map(([photo_type_id, l]) => ({ photo_type_id: Number(photo_type_id), quantity: l.quantity, format: l.format, paper_type_id: l.paper_type_id }));
    },
    estimatedTotal() {
      const linesTotal = this.activeLines.reduce((sum, l) => {
        const pt = this.photoTypes.find(p => p.id === l.photo_type_id);
        return sum + (Number(pt?.base_price || 0) * l.quantity);
      }, 0);
      const packagesTotal = this.packages
        .filter(p => this.selectedPackageIds.includes(p.id))
        .reduce((sum, p) => sum + Number(p.total_price || 0), 0);
      return linesTotal + packagesTotal;
    },
    totalPaid() {
      return this.payments.reduce((sum, p) => sum + (Number(p.amount) || 0), 0);
    },
    remainingBalance() {
      return Math.max(this.estimatedTotal - this.totalPaid, 0);
    },
    paymentConcept() {
      return this.remainingBalance > 0 ? 'Anticipo' : 'De contado';
    },
    showBalanceBadge() {
      return this.estimatedTotal > 0 && this.payments.length > 0;
    },
    notesWordCount() {
      const trimmed = this.form.notes.trim();
      return trimmed === '' ? 0 : trimmed.split(/\s+/).length;
    },
    fieldErrors() {
      const hasCustomer = !!this.selectedCustomer || this.customerSearch.trim().length > 0;
      return {
        customer: !hasCustomer,
        delivery_date: !this.form.estimated_delivery_date,
        pose_number: !this.form.pose_number || this.form.pose_number < 1,
        photo_types: this.activeLines.length === 0,
        initial_payment_amount: this.payments.length === 0 || this.payments.some(p => !p.amount || p.amount <= 0),
        initial_payment_method: this.payments.some(p => !p.payment_method),
      };
    },
    canSubmit() {
      return !Object.values(this.fieldErrors).some(Boolean);
    }
  },
  watch: {
    async show(val) {
      if (val) {
        await this.loadCatalogs();
        this.setDefaultDeliveryDate();
        this.$nextTick(() => {
          this.initialState = this.getCurrentState();
          this.isDirty = false;
        });
      } else {
        this.resetForm();
        this.initialState = null;
      }
    },
    customerSearch(newVal) {
      const trimmed = newVal.trim();

      if (this.selectedCustomer && trimmed.toLowerCase() !== this.selectedCustomer.name.trim().toLowerCase()) {
        this.selectedCustomer = null;
      }

      if (!this.selectedCustomer && trimmed) {
        const exactMatch = this.customers.find(
          c => c.name.trim().toLowerCase() === trimmed.toLowerCase()
        );
        if (exactMatch) {
          this.selectedCustomer = exactMatch;
        }
      }
    },
    selectedCustomer(newVal) {
      if (newVal) {
        this.customerPhone = newVal.phone || '';
        this.customerEmail = newVal.email || '';
      } else {
        this.customerPhone = '';
        this.customerEmail = '';
      }
      this.editingPhone = false;
      this.editingEmail = false;
    },
    'form.notes'(newVal) {
      const words = newVal.trim().split(/\s+/).filter(Boolean);
      if (words.length > 1000) {
        this.form.notes = words.slice(0, 1000).join(' ');
      }
    },
    estimatedTotal() {
      if (this.liquidateAll && this.payments.length) {
        this.payments[0].amount = this.estimatedTotal;
      }
    }
  },
  methods: {
    maxAmountFor(idx) {
      const othersSum = this.payments.reduce(
        (sum, p, i) => (i === idx ? sum : sum + (Number(p.amount) || 0)),
        0
      );
      return Math.max(this.estimatedTotal - othersSum, 0);
    },
    toggleLiquidateAll() {
      this.liquidateAll = !this.liquidateAll;
      if (this.liquidateAll) {
        const method = this.payments[0]?.payment_method || '';
        this.payments = [{ amount: this.estimatedTotal, payment_method: method }];
      } else {
        this.payments[0].amount = 0;
      }
      this.markDirty();
    },
    onPaymentAmountInput(idx) {
      const payment = this.payments[idx];
      const amount = Number(payment.amount) || 0;

      if (this.estimatedTotal > 0 && amount >= this.estimatedTotal) {
        payment.amount = this.estimatedTotal;
        this.payments = [payment];
        this.liquidateAll = true;
        this.markDirty();
        return;
      }

      const max = this.maxAmountFor(idx);
      if (amount > max) {
        payment.amount = max;
      }

      if (this.liquidateAll) {
        this.liquidateAll = false;
      }

      this.markDirty();
    },
    toggleEditPhone() {
      this.editingPhone = true;
    },
    confirmPhoneChange() {
      this.editingPhone = false;
      this.markDirty();
    },
    cancelPhoneEdit() {
      this.customerPhone = this.selectedCustomer ? (this.selectedCustomer.phone || '') : '';
      this.editingPhone = false;
    },
    toggleEditEmail() {
      this.editingEmail = true;
    },
    confirmEmailChange() {
      this.editingEmail = false;
      this.markDirty();
    },
    cancelEmailEdit() {
      this.customerEmail = this.selectedCustomer ? (this.selectedCustomer.email || '') : '';
      this.editingEmail = false;
    },
    markDirty() {
      this.isDirty = true;
    },
    getCurrentState() {
      return {
        customerSearch: this.customerSearch,
        selectedCustomerId: this.selectedCustomer ? this.selectedCustomer.id : null,
        customerPhone: this.customerPhone,
        customerEmail: this.customerEmail,
        selectedPackageIds: [...this.selectedPackageIds],
        lines: JSON.parse(JSON.stringify(this.lines)),
        payments: JSON.parse(JSON.stringify(this.payments)),
        form: {
          estimated_delivery_date: this.form.estimated_delivery_date,
          pose_number: this.form.pose_number,
          notes: this.form.notes,
        }
      };
    },
    formatDateLocal(date) {
      const y = date.getFullYear();
      const m = String(date.getMonth() + 1).padStart(2, '0');
      const d = String(date.getDate()).padStart(2, '0');
      return `${y}-${m}-${d}`;
    },
    setDefaultDeliveryDate() {
      const today = new Date();
      let nextDay = new Date(today);
      nextDay.setDate(nextDay.getDate() + 1);

      if (nextDay.getDay() === 0) {
        nextDay.setDate(nextDay.getDate() + 1);
      }

      this.form.estimated_delivery_date = this.formatDateLocal(nextDay);
    },
    toggleDatePicker() {
      const input = this.$refs.deliveryDateInput;
      if (!input) return;
      if (typeof input.showPicker !== 'function') {
        input.focus();
        return;
      }
      if (this.dateInputOpen) {
        input.blur();
        this.dateInputOpen = false;
      } else {
        try {
          input.showPicker();
          this.dateInputOpen = true;
        } catch (e) {
          input.focus();
        }
      }
    },
    togglePhotoType(id) {
      const line = this.lines[id];
      if (!line) return;
      line.selected = !line.selected;
      if (line.selected) {
        if (!line.quantity || line.quantity < 1) line.quantity = 1;
      } else {
        line.quantity = 0;
      }
      this.markDirty();
    },
    onQuantityBlur(id) {
      const line = this.lines[id];
      if (!line) return;
      if (!line.quantity || line.quantity < 1) {
        line.selected = false;
        line.quantity = 0;
      }
    },
    toggleFormatDropdown(id) {
      this.openFormatDropdownId = this.openFormatDropdownId === id ? null : id;
    },
    closeFormatDropdown(id) {
      setTimeout(() => {
        if (this.openFormatDropdownId === id) {
          this.openFormatDropdownId = null;
        }
      }, 200);
    },
    selectFormat(id, value) {
      this.lines[id].format = value;
      this.openFormatDropdownId = null;
      this.markDirty();
    },
    addPayment() {
      if (this.remainingBalance <= 0 || this.liquidateAll) return;
      this.payments.push({ amount: null, payment_method: '' });
      this.markDirty();
    },
    removePayment(idx) {
      this.payments.splice(idx, 1);
      this.markDirty();
    },
    togglePaymentMethodDropdown(idx) {
      this.openPaymentMethodDropdownIdx = this.openPaymentMethodDropdownIdx === idx ? null : idx;
    },
    closePaymentMethodDropdown(idx) {
      setTimeout(() => {
        if (this.openPaymentMethodDropdownIdx === idx) {
          this.openPaymentMethodDropdownIdx = null;
        }
      }, 200);
    },
    selectLinePaymentMethod(idx, method) {
      this.payments[idx].payment_method = method;
      this.openPaymentMethodDropdownIdx = null;
      this.markDirty();
    },
    async loadCatalogs() {
      this.loadingCatalogs = true;
      try {
        const [ptRes, paperRes, pkgRes, custRes] = await Promise.all([
          axios.get('/photo-types'),
          axios.get('/paper-types'),
          axios.get('/packages'),
          axios.get('/customers')
        ]);
        this.photoTypes = ptRes.data;
        this.paperTypes = paperRes.data;
        this.packages = pkgRes.data;
        this.customers = custRes.data;

        this.lines = {};
        this.photoTypes.forEach(pt => {
          this.lines[pt.id] = { selected: false, quantity: 0, format: 'bn', paper_type_id: null };
        });
      } catch (error) {
        this.$emit('notify', { title: 'Error', message: 'No se pudieron cargar los catálogos', type: 'error' });
      } finally {
        this.loadingCatalogs = false;
      }
    },
    selectCustomer(c) {
      this.selectedCustomer = c;
      this.customerSearch = c.name;
      this.showCustomerDropdown = false;
      this.markDirty();
    },
    clearCustomerSearch() {
      this.customerSearch = '';
      this.selectedCustomer = null;
    },
    hideCustomerDropdown() {
      setTimeout(() => {
        this.showCustomerDropdown = false;
      }, 200);
    },
    resetForm() {
      this.selectedCustomer = null;
      this.customerSearch = '';
      this.customerPhone = '';
      this.customerEmail = '';
      this.selectedPackageIds = [];
      this.attemptedSubmit = false;
      this.liquidateAll = false;
      this.payments = [{ amount: null, payment_method: '' }];
      this.openPaymentMethodDropdownIdx = null;
      this.editingPhone = false;
      this.editingEmail = false;
      this.form = {
        estimated_delivery_date: '',
        pose_number: null,
        notes: ''
      };
      this.photoTypes.forEach(pt => {
        this.lines[pt.id] = { selected: false, quantity: 0, format: 'bn', paper_type_id: null };
      });
      this.isDirty = false;
    },
    handleClose() {
      const currentState = this.getCurrentState();
      const isChanged = JSON.stringify(currentState) !== JSON.stringify(this.initialState);
      if (isChanged) {
        this.showConfirmModal = true;
      } else {
        this.close();
      }
    },
    closeConfirmed() {
      this.showConfirmModal = false;
      this.close();
    },
    close() {
      this.$emit('close');
    },
    async submit() {
      this.attemptedSubmit = true;
      let conflictMessages = [];
      if (this.phoneExists) {
        conflictMessages.push(`El teléfono ${this.customerPhone.trim()} ya está registrado a nombre de "${this.phoneExists.name}".`);
      }
      if (this.emailExists) {
        conflictMessages.push(`El correo ${this.customerEmail.trim()} ya está registrado a nombre de "${this.emailExists.name}".`);
      }
      if (conflictMessages.length > 0) {
        this.alertMessage = conflictMessages.join('\n');
        this.showAlertModal = true;
        return;
      }
      if (!this.canSubmit) {
        this.alertMessage = 'Por favor, completa todos los campos marcados con * antes de continuar.';
        this.showAlertModal = true;
        return;
      }
      if (this.submitting) return;
      this.submitting = true;
      try {
        let customerId = null;
        let newCustomer = null;
        let contactUpdate = null;

        if (this.selectedCustomer) {
          customerId = this.selectedCustomer.id;
          if (this.phoneChanged || this.emailChanged) {
            contactUpdate = {
              phone: this.customerPhone.trim() || null,
              email: this.customerEmail.trim() || null,
            };
          }
        } else if (this.customerSearch.trim()) {
          newCustomer = {
            name: this.customerSearch.trim(),
            phone: this.customerPhone.trim() || null,
            email: this.customerEmail.trim() || null,
          };
        }

        const payload = {
          customer_id: customerId,
          new_customer: newCustomer,
          contact_update: contactUpdate,
          estimated_delivery_date: this.form.estimated_delivery_date,
          pose_number: this.form.pose_number,
          notes: this.form.notes,
          photo_types: this.activeLines,
          package_ids: this.selectedPackageIds,
          payments: this.payments.map(p => ({
            amount: p.amount,
            payment_method: p.payment_method,
            type: 'advance'
          }))
        };
        const res = await axios.post('/orders', payload);
        this._createdOrder = res.data.order;
        this.$emit('created', this._createdOrder);

        this.showSuccessModal = true;
      } catch (error) {
        this.$emit('notify', {
          title: 'Error',
          message: error.response?.data?.message || 'No se pudo crear la orden',
          type: 'error'
        });
        this.submitting = false;
      }
    },
    onSuccessClosed() {
      this.showSuccessModal = false;
      this.isDirty = false;
      this.close();
      this.submitting = false;
      this._createdOrder = null;
    }
  }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>