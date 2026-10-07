<template>
  <div :class="stacked ? 'space-y-4' : 'grid grid-cols-1 md:grid-cols-2 gap-4'">
    <div>
      <label class="form-label text-lg font-medium mb-2 block text-gray-700 dark:text-gray-300">Fecha de entrega estimada <span class="text-red-500">*</span></label>
      <div class="relative">
        <input
          ref="dateInput"
          :value="deliveryDate"
          @input="$emit('update:deliveryDate', $event.target.value)"
          @click="onDateClick"
          @blur="onDateBlur"
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
        <i class="ti ti-clock absolute left-4 top-1/2 -translate-y-1/2 text-xl text-gray-400 dark:text-gray-500 pointer-events-none"></i>
        <input
          :value="deliveryTime"
          @input="$emit('update:deliveryTime', $event.target.value)"
          type="time"
          step="300"
          class="form-control form-control-sm w-full text-lg p-4 pl-12 rounded-lg border bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
          :class="showErrors && !deliveryTime ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'"
        >
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