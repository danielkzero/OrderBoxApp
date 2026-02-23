<template>
  <div class="sm:col-span-1">
    <p class="text-xs text-gray-500 dark:text-gray-400">{{ display }}</p>
    <p class="text-sm font-medium break-words">{{ value || '-' }}</p>
  </div>
</template>

<script setup>
defineProps({
  display: { type: String, default: '' },
  value: { type: String, default: '' },
});
</script>
