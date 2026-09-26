<template>
  <div class="flex items-start justify-center gap-0 sm:gap-1 mb-6">
    <template v-for="(step, idx) in steps" :key="idx">
      <div class="flex flex-col items-center gap-2 w-32">
        <div
          class="w-9 h-9 rounded-full flex items-center justify-center text-base font-bold transition-colors flex-shrink-0"
          :class="circleClass(idx + 1)"
        >
          <i v-if="idx + 1 < currentStep" class="ti ti-check"></i>
          <span v-else>{{ idx + 1 }}</span>
        </div>
        <span class="text-base font-semibold text-gray-500 dark:text-gray-400 text-center leading-tight whitespace-nowrap">
          {{ step.label }}
        </span>
      </div>
      <div
        v-if="idx < steps.length - 1"
        class="w-32 h-1 rounded mt-4 flex-shrink-0 transition-colors -mx-4"
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