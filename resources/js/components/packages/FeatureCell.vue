<template>
  <div v-if="!text" class="text-xs sm:text-sm short:text-xs text-gray-400 dark:text-gray-500 italic">—</div>
  <div v-else>
    <p class="text-xs sm:text-sm short:text-xs whitespace-pre-wrap break-words" :class="expanded ? '' : 'line-clamp-3'">{{ text }}</p>
    <button v-if="isLong" @click="expanded = !expanded" type="button" class="text-[11px] sm:text-xs short:text-[10px] text-blue-600 dark:text-blue-400 font-medium mt-1">
      {{ expanded ? 'Ver menos' : 'Ver más' }}
    </button>
  </div>
</template>

<script>
export default {
  name: 'FeatureCell',
  props: {
    text: { type: String, default: '' }
  },
  data() {
    return { expanded: false };
  },
  computed: {
    isLong() {
      return (this.text || '').length > 140;
    }
  }
};
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>