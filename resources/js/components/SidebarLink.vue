<template>
  <Link :href="href" class="flex items-center space-x-3 p-2 rounded cursor-pointer" :class="[
    isActive
      ? 'bg-indigo-600 text-indigo-50 dark:bg-indigo-600'
      : 'hover:bg-indigo-600 hover:text-indigo-50 dark:hover:bg-indigo-600 hover:scale-105 transition-all'
  ]">
  <i :class="icon + ' text-xl'"></i>
  <span v-if="!collapsed" class="text-sm font-medium truncate">
    {{ label }}
  </span>
  </Link>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const props = defineProps({
  href: String,
  icon: String,
  label: String,
  collapsed: Boolean,
});

// Página atual
const page = usePage();

// Verifica se a rota atual é igual ao href do menu
const isActive = computed(() => {
  const url = page.url.split(/[/?]/).filter(Boolean);
  return './' + url[1] === props.href
});
</script>
