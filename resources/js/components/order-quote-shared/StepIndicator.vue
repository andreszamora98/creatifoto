<template>
  <div class="flex items-start justify-center gap-0 sm:gap-1 short:gap-0 mb-3 sm:mb-6 short:mb-1.5">
    <template v-for="(step, idx) in steps" :key="idx">
      <div class="flex flex-col items-center gap-1.5 sm:gap-2 short:gap-1 w-20 sm:w-32 short:w-36">
        <div
          class="w-7 h-7 sm:w-9 sm:h-9 short:w-6 short:h-6 rounded-full flex items-center justify-center text-sm sm:text-base short:text-xs font-bold transition-colors flex-shrink-0"
          :class="circleClass(idx + 1)"
        >
          <i v-if="idx + 1 < currentStep" class="ti ti-check"></i>
          <span v-else>{{ idx + 1 }}</span>
        </div>
        <span class="text-xs sm:text-base short:text-xs font-semibold text-gray-500 dark:text-gray-400 text-center leading-tight whitespace-normal sm:whitespace-nowrap">
          {{ step.label }}
        </span>
      </div>
      <div
        v-if="idx < steps.length - 1"
        class="w-5 sm:w-32 short:w-16 h-1 rounded mt-3 sm:mt-4 short:mt-2.5 flex-shrink-0 transition-colors mx-0 sm:-mx-4 short:-mx-4"
        :class="idx + 1 < currentStep ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'"
      ></div>
    </template>
  </div>
</template>

<script>
export default {
  name: 'StepIndicator',
  props: {
    steps: { type: Array, required: true },
    currentStep: { type: Number, required: true }
  },
  methods: {
    circleClass(n) {
      if (n === this.currentStep) return 'bg-blue-500 text-white';
      if (n < this.currentStep) return 'bg-green-500 text-white';
      return 'bg-gray-200 dark:bg-gray-600 text-gray-500 dark:text-gray-300';
    }
  }
};
</script>