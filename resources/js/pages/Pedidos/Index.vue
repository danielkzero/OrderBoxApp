<template>
  <div class="space-y-6 bg-white px-4 pb-4 rounded-sm shadow">
    <!-- Tabs principais -->
    <div class="border-b border-gray-200 dark:border-gray-700">
      <nav class="-mb-px flex space-x-8">
        <button v-for="tab in mainTabs" :key="tab.name" @click="activeMainTab = tab.name"
          class="flex items-center space-x-2 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
          :class="activeMainTab === tab.name
            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'">
          <i :class="tab.icon + ' text-lg'"></i>
          <span>{{ tab.name }}</span>
        </button>
      </nav>
    </div>

    <!-- Conteúdo Pedidos -->
    <div v-if="activeMainTab === 'Pedidos'">
      <div class="flex space-x-2">
        <ButtonCustom icon="bx-plus" text="Criar pedido / orçamento" url="/pedidos/create" :outline="false" />
        <ButtonCustom icon="bxs-printer" text="Imprimir pedido" url="/pedidos/create" :outline="true" />
      </div>
      <div class="flex flex-wrap items-center gap-4 text-sm my-3">
        Mostrando
        <FormField tag="select" v-model="filtros.pedidos" :removeClass="true">
          <option value="ativos">Pedidos ativos</option>
          <option value="finalizados">Pedidos finalizados</option>
          <option value="cancelados">Pedidos cancelados</option>
          <option value="todos">Todos os pedidos</option>
        </FormField>
        Feitos por
        <FormField tag="select" v-model="filtros.vendedores" :removeClass="true">
          <option value="todos">Todos os vendedores</option>
          <option value="internos">Equipe interna</option>
          <option value="representantes">Representantes</option>
        </FormField>
        Via
        <FormField tag="select" v-model="filtros.plataformas" :removeClass="true">
          <option value="todas">Todas as plataformas</option>
          <option value="b2b">E-commerce B2B</option>
          <option value="app">Aplicativo</option>
          <option value="manual">Lançamento manual</option>
        </FormField>
        Envio
        <FormField tag="select" v-model="filtros.envio" :removeClass="true">
          <option value="ignorar">Sem considerar o envio</option>
          <option value="com-envio">Com envio</option>
          <option value="sem-envio">Sem envio</option>
        </FormField>
        Com
        <FormField tag="select" v-model="filtros.status" :removeClass="true">
          <option value="qualquer">Qualquer status</option>
          <option value="aguardando">Aguardando pagamento</option>
          <option value="processando">Processando</option>
          <option value="enviado">Enviado</option>
        </FormField>
      </div>
      <div class="mt-4">
        <DataTable />
      </div>
    </div>

    <!-- Conteúdo Configurações -->
    <div v-else>
      <!-- Sub-tabs -->
      <div class="bg-gray-100 dark:bg-gray-800 -mx-4 -mt-6">
        <nav class="flex space-x-2">
          <button v-for="sub in configTabs" :key="sub.name" @click="activeConfigTab = sub.name"
            class="flex items-center space-x-2 px-3 py-1 text-sm font-medium" :class="activeConfigTab === sub.name
              ? 'text-indigo-700 dark:text-white border-b-2'
              : 'text-gray-500 dark:text-white border-b-2 border-gray-100 hover:text-gray-700 hover:border-gray-300'">
            <i :class="sub.icon + ' text-base'"></i>
            <span>{{ sub.name }}</span>
          </button>
        </nav>
      </div>

      <!-- Conteúdo das sub-tabs -->
      <div class="mt-4">
        <div v-if="activeConfigTab === 'Campos extras'">
          <h3 class="text-lg font-semibold">Campos extras</h3>
          <p class="text-gray-600 dark:text-gray-400">Configuração de campos adicionais do pedido.</p>
        </div>

        <div v-else-if="activeConfigTab === 'Status de pedido'">
          <h3 class="text-lg font-semibold">Status de pedido</h3>
          <p class="text-gray-600 dark:text-gray-400">Aqui você gerencia os status de pedidos.</p>
        </div>

        <div v-else-if="activeConfigTab === 'Tipo de pedido'">
          <h3 class="text-lg font-semibold">Tipo de pedido</h3>
          <p class="text-gray-600 dark:text-gray-400">Defina os tipos de pedido disponíveis.</p>
        </div>

        <div v-else>
          <h3 class="text-lg font-semibold">Geral</h3>
          <p class="text-gray-600 dark:text-gray-400">Configurações gerais dos pedidos.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { ref } from "vue";
import ButtonCustom from "@/components/ButtonCustom.vue";
import DataTable from "@/components/DataTable.vue";
import FormField from "@/components/FormField.vue";

defineOptions({
  layout: AppLayout,
});

// Tabs principais com ícones
const mainTabs = [
  { name: "Pedidos", icon: "bx bx-cart" },
  { name: "Configurações", icon: "bx bx-cog" },
];
const activeMainTab = ref("Pedidos");

// Sub-tabs com ícones
const configTabs = [
  { name: "Campos extras", icon: "bx bx-list-plus" },
  { name: "Status de pedido", icon: "bx bx-check-circle" },
  { name: "Tipo de pedido", icon: "bx bx-category" },
  { name: "Geral", icon: "bx bx-slider" },
];
const activeConfigTab = ref("Campos extras");

const filtros = ref({
  pedidos: "ativos",
  vendedores: "todos",
  plataformas: "todas",
  envio: "ignorar",
  status: "qualquer",
});
</script>
