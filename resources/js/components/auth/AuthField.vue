<template>
  <div>
    <label :for="inputId" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
      {{ label }}
    </label>

    <div class="group relative">
      <i
        :class="['ti', icon]"
        class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-lg text-gray-400 transition-colors duration-200 group-focus-within:text-[#e0a400]"
        aria-hidden="true"
      ></i>

      <input
        :id="inputId"
        :name="name"
        :type="inputType"
        :value="modelValue"
        :autocomplete="autocomplete"
        :readonly="readonly"
        :autofocus="autofocus"
        :aria-invalid="error ? 'true' : 'false'"
        :aria-describedby="error ? inputId + '-error' : undefined"
        required
        class="w-full rounded-xl border bg-white py-3 pl-11 text-sm text-gray-900 placeholder-gray-400 outline-none transition duration-200 focus:border-[#ffbe00] focus:ring-4 focus:ring-[#ffbe00]/25 read-only:bg-gray-50 read-only:text-gray-500 dark:bg-gray-800/60 dark:text-gray-100 dark:placeholder-gray-500 dark:read-only:bg-gray-800/30"
        :class="[
          password ? 'pr-11' : 'pr-4',
          error ? 'border-red-400 dark:border-red-500/70' : 'border-gray-300 dark:border-gray-700',
        ]"
        :placeholder="placeholder"
        @input="$emit('update:modelValue', $event.target.value)"
      />

      <button
        v-if="password"
        type="button"
        class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-gray-400 transition-colors hover:text-gray-600 focus-visible:outline-2 focus-visible:outline-[#ffbe00] dark:hover:text-gray-200"
        :aria-label="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
        :aria-pressed="visible ? 'true' : 'false'"
        @click="visible = !visible"
      >
        <i :class="['ti', visible ? 'ti-eye-off' : 'ti-eye']" class="text-lg" aria-hidden="true"></i>
      </button>
    </div>

    <transition name="err">
      <p
        v-if="error"
        :id="inputId + '-error'"
        class="mt-1.5 flex items-start gap-1 text-xs text-red-600 dark:text-red-400"
      >
        <i class="ti ti-alert-circle mt-px shrink-0" aria-hidden="true"></i>
        <span>{{ error }}</span>
      </p>
    </transition>
  </div>
</template>

<script>
export default {
  name: 'AuthField',
  props: {
    modelValue: { type: String, default: '' },
    label: { type: String, required: true },
    name: { type: String, required: true },
    icon: { type: String, default: 'ti-mail' },
    type: { type: String, default: 'text' },
    autocomplete: { type: String, default: 'off' },
    placeholder: { type: String, default: '' },
    error: { type: String, default: '' },
    readonly: { type: Boolean, default: false },
    autofocus: { type: Boolean, default: false },
  },
  emits: ['update:modelValue'],
  data() {
    return { visible: false };
  },
  computed: {
    password() {
      return this.type === 'password';
    },
    inputType() {
      return this.password && this.visible ? 'text' : this.type;
    },
    inputId() {
      return `auth-${this.name}`;
    },
  },
};
</script>

<style scoped>
.err-enter-active,
.err-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.err-enter-from,
.err-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
