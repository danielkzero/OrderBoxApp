<template>
  <ProdutosPageShell
    :main-tabs="mainTabs"
    active-main-tab="configuracoes"
    :sub-tabs="tabs"
    :active-sub-tab="activeTab"
  >
    <slot />
  </ProdutosPageShell>
</template>

<script setup>
import ProdutosPageShell from "@/pages/Produtos/components/ProdutosPageShell.vue";

const props = defineProps({
  empresaId: { type: Number, required: true },
  tabs: { type: Array, default: () => [] },
  activeTab: { type: String, required: true },
});

const mainTabs = [
  { key: "produtos", label: "Produtos", icon: "bx bx-box", url: `/${props.empresaId}/produtos/tabelas` },
  { key: "promocoes", label: "Promocoes", icon: "bx bx-badge", url: `/${props.empresaId}/produtos/promocoes` },
  { key: "destaques", label: "Destaques", icon: "bx bx-star", url: `/${props.empresaId}/produtos/destaques` },
  { key: "configuracoes", label: "Configuracoes", icon: "bx bx-cog", url: `/${props.empresaId}/produtos/configuracoes/categorias` },
];
</script>
