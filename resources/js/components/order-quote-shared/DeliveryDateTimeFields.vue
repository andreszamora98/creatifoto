<template>
  <div :class="stacked ? 'space-y-4' : 'grid grid-cols-1 md:grid-cols-2 gap-4'">
    <div>
      <label class="form-label text-lg font-medium mb-2 block text-gray-700 dark:text-gray-300">Fecha de entrega estimada <span class="text-red-500">*</span></label>
      <div class="relative">
        <input
          ref="dateInput"
          :value="deliveryDate"
          @input="$emit('update:deliveryDate', $event.target.value)"
          @click="openDatePicker"
          type="date"
          :min="minDateStr"
          class="form-control form-control-sm w-full text-lg p-4 pr-11 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 cursor-pointer [&::-webkit-calendar-picker-indicator]:hidden"
          :class="showErrors && !deliveryDate ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
        >
        <i class="ti ti-calendar absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 pointer-events-none text-xl"></i>
      </div>
      <p v-if="showErrors && !deliveryDate" class="text-base text-red-600 dark:text-red-400 mt-1 font-medium">Campo obligatorio*</p>
    </div>

    <div>
      <label class="form-label text-lg font-medium mb-2 block text-gray-700 dark:text-gray-300">Hora de entrega estimada <span class="text-red-500">*</span></label>
      <div class="relative">
        <button
          type="button"
          @click="toggleTimeDropdown"
          @blur="closeTimeDropdown"
          class="form-control form-control-sm w-full text-lg p-4 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 flex items-center justify-between text-left"
          :class="showErrors && !deliveryTime ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
        >
          <span class="flex items-center gap-2 truncate">
            <i class="ti ti-clock text-xl text-gray-400 dark:text-gray-500 flex-shrink-0"></i>
            <span v-if="deliveryTime" class="truncate">{{ deliveryTime }} <span class="text-gray-500 dark:text-gray-400">hrs</span></span>
            <span v-else class="text-gray-400 dark:text-gray-500 truncate">Seleccionar hora</span>
          </span>
          <i class="ti ti-chevron-down text-sm flex-shrink-0"></i>
        </button>

        <div v-if="openTimeDropdown" class="absolute z-30 mt-1 left-0 right-0 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg overflow-hidden">
          <div class="flex divide-x divide-gray-200 dark:divide-gray-700">
            <div class="flex-1 max-h-56 overflow-y-auto">
              <div class="sticky top-0 px-3 py-1.5 text-xs font-bold uppercase tracking-wide bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Hora</div>
              <button
                v-for="h in hourOptions"
                :key="h"
                type="button"
                @mousedown.prevent="selectHour(h)"
                class="w-full px-3 py-2 text-base text-center hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200"
                :class="{ 'bg-blue-50 dark:bg-blue-900/30 font-semibold text-blue-700 dark:text-blue-300': selectedHour === h }"
              >
                {{ h }}
              </button>
            </div>
            <div class="flex-1 max-h-56 overflow-y-auto">
              <div class="sticky top-0 px-3 py-1.5 text-xs font-bold uppercase tracking-wide bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Min</div>
              <button
                v-for="m in minuteOptions"
                :key="m"
                type="button"
                @mousedown.prevent="selectMinute(m)"
                class="w-full px-3 py-2 text-base text-center hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200"
                :class="{ 'bg-blue-50 dark:bg-blue-900/30 font-semibold text-blue-700 dark:text-blue-300': selectedMinute === m }"
              >
                {{ m }}
              </button>
            </div>
          </div>
        </div>
      </div>
      <p v-if="showErrors && !deliveryTime" class="text-base text-red-600 dark:text-red-400 mt-1 font-medium">Campo obligatorio*</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DeliveryDateTimeFields',
  props: {
    deliveryDate: { type: String, default: '' },
    deliveryTime: { type: String, default: '' },
    showErrors: { type: Boolean, default: false },
    stacked: { type: Boolean, default: false }
  },
  emits: ['update:deliveryDate', 'update:deliveryTime'],
  data() {
    return { openTimeDropdown: false };
  },
  computed: {
    minDateStr() {
      const today = new Date();
      const y = today.getFullYear();
      const m = String(today.getMonth() + 1).padStart(2, '0');
      const d = String(today.getDate()).padStart(2, '0');
      return `${y}-${m}-${d}`;
    },
    hourOptions() {
      const arr = [];
      for (let h = 0; h <= 23; h++) arr.push(String(h).padStart(2, '0'));
      return arr;
    },
    minuteOptions() {
      const arr = [];
      for (let m = 0; m <= 59; m++) arr.push(String(m).padStart(2, '0'));
      return arr;
    },
    selectedHour() {
      return this.deliveryTime ? this.deliveryTime.split(':')[0] : null;
    },
    selectedMinute() {
      return this.deliveryTime ? this.deliveryTime.split(':')[1] : null;
    }
  },
  methods: {
    openDatePicker() {
      const el = this.$refs.dateInput;
      if (el && typeof el.showPicker === 'function') {
        try {
          el.showPicker();
        } catch (e) { /* el navegador puede rechazarlo si no hay gesto de usuario */ }
      }
    },
    toggleTimeDropdown() {
      this.openTimeDropdown = !this.openTimeDropdown;
    },
    closeTimeDropdown() {
      setTimeout(() => { this.openTimeDropdown = false; }, 200);
    },
    selectHour(h) {
      const minute = this.selectedMinute || '00';
      this.$emit('update:deliveryTime', `${h}:${minute}`);
    },
    selectMinute(m) {
      const hour = this.selectedHour || '16';
      this.$emit('update:deliveryTime', `${hour}:${m}`);
      this.openTimeDropdown = false;
    }
  }
};
</script>