<template>
  <div class="bg-white rounded-sm shadow text-gray-800 overflow-hidden">
    <div class="border-b border-gray-200 px-4">
      <div class="flex items-center gap-6 text-sm font-semibold">
        <Link
          :href="`/${empresaId}/clientes`"
          class="inline-flex items-center gap-2 py-4 border-b-2"
          :class="activeMainTab === 'clientes' ? 'border-indigo-600 text-gray-900' : 'border-transparent text-gray-500 hover:text-indigo-600'"
        >
          <i class="bx bxs-store-alt"></i>
          Clientes
        </Link>
        <Link
          :href="`/${empresaId}/clientes/configuracoes/campos_extras`"
          class="inline-flex items-center gap-2 py-4 border-b-2"
          :class="activeMainTab === 'configuracoes' ? 'border-indigo-600 text-gray-900' : 'border-transparent text-gray-500 hover:text-indigo-600'"
        >
          <i class="bx bxs-cog"></i>
          Configurações
        </Link>
      </div>
    </div>

    <div v-if="configTabs.length" class="border-b border-gray-200 bg-gray-50 px-4">
      <div class="flex flex-wrap gap-6">
        <Link
          v-for="tab in configTabs"
          :key="tab.key"
          :href="tab.url"
          class="py-3 text-sm font-semibold inline-flex items-center gap-2 border-b-2"
          :class="tab.key === activeConfigTab ? 'border-indigo-600 text-gray-900' : 'border-transparent text-gray-400 hover:text-indigo-600'"
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
import { Link } from '@inertiajs/vue3';

defineProps({
  empresaId: { type: Number, required: true },
  activeMainTab: { type: String, default: 'clientes' },
  configTabs: { type: Array, default: () => [] },
  activeConfigTab: { type: String, default: '' },
});
</script>
