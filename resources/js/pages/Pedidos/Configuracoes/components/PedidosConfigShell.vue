<template>
  <PedidosPageShell
    :empresa-id="empresaId"
    active-main-tab="configuracoes"
    :config-tabs="tabs"
    :active-config-tab="activeTab"
  >
    <slot />
  </PedidosPageShell>
</template>

<script setup>
import PedidosPageShell from '@/pages/Pedidos/components/PedidosPageShell.vue';
defineProps({
  empresaId: { type: Number, required: true },
  tabs: { type: Array, default: () => [] },
  activeTab: { type: String, required: true },
});
</script>
