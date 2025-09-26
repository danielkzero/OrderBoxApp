
<template>
  <div :class="props.class">
    <label class="block mb-1 text-sm font-medium text-gray-600 dark:text-gray-400" v-if="props.label">
      {{ props.label }}
    </label>
    <component
      :is="props.tag"
      v-bind="$attrs"
      :value="props.modelValue"
      @input="updateValue"
      :class="[classField, removeClass ? '' : 'w-full border border-indigo-200 dark:border-gray-600 rounded px-3 py-2 text-sm dark:text-gray-400 dark:bg-gray-900']"
    >
      <slot />
    </component>
  </div>
</template>

<script setup>
const props = defineProps({
  label: String,
  modelValue: [String, Number],
  tag: { type: String, default: 'input' }, // 'input', 'select', 'textarea'
  class: { type: String, default: '' },
  classField: { type: String, default: '' },
  removeClass: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue'])

function updateValue(e) {
  emit('update:modelValue', e.target.value)
}
</script>
