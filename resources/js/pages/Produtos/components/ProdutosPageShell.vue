<template>
  <div class="bg-white rounded-sm shadow text-gray-800 overflow-hidden">
    <div class="border-b border-gray-200 px-4">
      <div class="flex items-center gap-6 text-sm font-semibold">
        <Link
          v-for="tab in mainTabs"
          :key="tab.key ?? tab.id"
          :href="tab.url"
          class="inline-flex items-center gap-2 py-4 border-b-2"
          :class="(tab.key ?? tab.id) === activeMainTab ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-indigo-600'"
        >
          <i :class="tab.icon"></i>
          {{ tab.label }}
        </Link>
      </div>
    </div>

    <div v-if="subTabs.length" class="border-b border-gray-200 bg-gray-50 px-4">
      <div class="flex flex-wrap gap-6">
        <Link
          v-for="tab in subTabs"
          :key="tab.key ?? tab.id"
          :href="tab.url"
          class="py-3 text-sm font-semibold inline-flex items-center gap-2 border-b-2"
          :class="(tab.key ?? tab.id) === activeSubTab ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-400 hover:text-indigo-600'"
        >
          <i :class="tab.icon"></i>
          {{ tab.label }}
        </Link>
      </div>
    </div>

    <div class="p-6">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
  mainTabs: { type: Array, default: () => [] },
  activeMainTab: { type: String, default: "produtos" },
  subTabs: { type: Array, default: () => [] },
  activeSubTab: { type: String, default: "" },
});
</script>
