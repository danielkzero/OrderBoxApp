<template>
  <ClientesPageShell
    :empresa-id="empresaId"
    active-main-tab="configuracoes"
    :config-tabs="tabs"
    :active-config-tab="activeTab"
  >
    <slot />
  </ClientesPageShell>
</template>

<script setup>
import ClientesPageShell from '@/pages/Clientes/components/ClientesPageShell.vue';
defineProps({
  empresaId: { type: Number, required: true },
  tabs: { type: Array, default: () => [] },
  activeTab: { type: String, required: true },
});
</script>
