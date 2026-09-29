<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
    <div class="bg-gray-50 dark:bg-gray-700/40 rounded-lg p-5 border border-gray-200 dark:border-gray-700">
      <slot name="summary" />
    </div>

    <div class="bg-gray-50 dark:bg-gray-700/40 rounded-lg p-5 border border-gray-200 dark:border-gray-700">
      <slot name="payment" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'ChargePaymentLayout'
};
</script>