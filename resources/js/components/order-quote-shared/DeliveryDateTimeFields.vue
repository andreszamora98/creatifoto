<template>
  <div :class="stacked ? 'space-y-3 sm:space-y-4 short:space-y-2' : 'grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 short:gap-2'">
    <div>
      <label class="form-label text-sm sm:text-lg short:text-sm font-medium mb-1.5 sm:mb-2 short:mb-1 block text-gray-700 dark:text-gray-300">Fecha de entrega estimada <span class="text-red-500">*</span></label>
      <div class="relative">
        <input
          ref="dateInput"
          :value="deliveryDate"
          @input="$emit('update:deliveryDate', $event.target.value)"
          @click="onDateClick"
          @blur="onDateBlur"
          type="date"
          :min="minDateStr"
          class="form-control form-control-sm w-full text-sm sm:text-lg short:text-sm p-2.5 sm:p-4 short:p-2 pr-9 sm:pr-11 short:pr-9 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 cursor-pointer [&::-webkit-calendar-picker-indicator]:hidden"
          :class="showErrors && !deliveryDate ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
        >
        <i class="ti ti-calendar absolute right-2.5 sm:right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 pointer-events-none text-base sm:text-xl short:text-base"></i>
      </div>
      <p v-if="showErrors && !deliveryDate" class="text-xs sm:text-base short:text-xs text-red-600 dark:text-red-400 mt-1 font-medium">Campo obligatorio*</p>
    </div>

    <div>
      <label class="form-label text-sm sm:text-lg short:text-sm font-medium mb-1.5 sm:mb-2 short:mb-1 block text-gray-700 dark:text-gray-300">Hora de entrega estimada <span class="text-red-500">*</span></label>
      <div class="relative">
        <i class="ti ti-clock absolute left-2.5 sm:left-4 top-1/2 -translate-y-1/2 text-base sm:text-xl short:text-base text-gray-400 dark:text-gray-500 pointer-events-none"></i>
        <input
          :value="deliveryTime"
          @input="$emit('update:deliveryTime', $event.target.value)"
          type="time"
          step="300"
          class="form-control form-control-sm w-full text-sm sm:text-lg short:text-sm p-2.5 sm:p-4 short:p-2 pl-9 sm:pl-12 short:pl-9 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
          :class="showErrors && !deliveryTime ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
        >
      </div>
      <p v-if="showErrors && !deliveryTime" class="text-xs sm:text-base short:text-xs text-red-600 dark:text-red-400 mt-1 font-medium">Campo obligatorio*</p>
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
    return { datePickerOpen: false };
  },
  computed: {
    minDateStr() {
      const today = new Date();
      const y = today.getFullYear();
      const m = String(today.getMonth() + 1).padStart(2, '0');
      const d = String(today.getDate()).padStart(2, '0');
      return `${y}-${m}-${d}`;
    }
  },
  methods: {
    onDateClick() {
      if (this.datePickerOpen) {
        this.$refs.dateInput?.blur();
        this.datePickerOpen = false;
        return;
      }
      this.openDatePicker();
    },
    openDatePicker() {
      const el = this.$refs.dateInput;
      if (!el) return;
      if (typeof el.showPicker === 'function') {
        try {
          el.showPicker();
          this.datePickerOpen = true;
          return;
        } catch (e) {
          // fallback: focus
        }
      }
      el.focus();
      this.datePickerOpen = true;
    },
    onDateBlur() {
      this.datePickerOpen = false;
    }
  }
};
</script>