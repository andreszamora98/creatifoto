<template>
  <div>
    <label v-if="label" class="form-label text-lg font-medium mb-2 block text-gray-700 dark:text-gray-300">{{ label }}</label>
    <textarea
      :value="modelValue"
      @input="onInput"
      v-blur-on-enter
      enterkeyhint="done"
      :rows="rows"
      class="form-control form-control-sm w-full text-lg p-4 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 resize-none"
    ></textarea>
    <div class="flex justify-end mt-1">
      <span class="text-sm" :class="wordCount >= maxWords ? 'text-red-500 dark:text-red-400 font-medium' : 'text-gray-400 dark:text-gray-500'">
        {{ wordCount }}/{{ maxWords }} palabras
      </span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotesField',
  props: {
    modelValue: { type: String, default: '' },
    label: { type: String, default: 'Observaciones' },
    maxWords: { type: Number, default: 500 },
    rows: { type: Number, default: 4 }
  },
  emits: ['update:modelValue'],
  computed: {
    wordCount() {
      const trimmed = (this.modelValue || '').trim();
      return trimmed === '' ? 0 : trimmed.split(/\s+/).filter(Boolean).length;
    }
  },
  methods: {
    onInput(event) {
      const previous = this.modelValue || '';
      const previousWordCount = previous.trim() === '' ? 0 : previous.trim().split(/\s+/).filter(Boolean).length;
      const raw = event.target.value;

      if (previousWordCount >= this.maxWords && raw.length > previous.length) {
        const addedChars = raw.length - previous.length;
        const selStart = event.target.selectionStart ?? previous.length;
        event.target.value = previous;
        const restoredPos = Math.max(0, selStart - addedChars);
        event.target.setSelectionRange(restoredPos, restoredPos);
        return;
      }

      const words = raw.trim().split(/\s+/).filter(Boolean);
      if (words.length > this.maxWords) {
        const truncated = words.slice(0, this.maxWords).join(' ');
        event.target.value = truncated;
        event.target.setSelectionRange(truncated.length, truncated.length);
        this.$emit('update:modelValue', truncated);
        return;
      }

      this.$emit('update:modelValue', raw);
    }
  }
};
</script>