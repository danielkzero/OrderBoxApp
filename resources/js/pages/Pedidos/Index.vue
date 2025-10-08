<template>
  <div class="space-y-6 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-50 px-4 pb-4 rounded-sm shadow">
    <!-- Tabs principais -->
    <div class="border-b border-gray-200 dark:border-gray-700">
      <nav class="-mb-px flex space-x-8">
        <Link :href="tab.url" v-for="tab in mainTabs" :key="tab.name" @click="activeMainTab = tab.name"
          class="flex items-center space-x-2 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
          :class="activeMainTab === tab.name
            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'">
          <i :class="tab.icon + ' text-lg'"></i>
          <span>{{ tab.name }}</span>
        </Link>
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
        <DataTable :columns="columns" :data="pedidos">
          <template #cell-total="{ row }">
            {{ formatCurrency(row.total) }}
          </template>
          <template #cell-status="{ row }">
            <span :class="statusMap[row.status].class">
              {{ statusMap[row.status].label }}
            </span>
          </template>
        </DataTable>
      </div>
    </div>

    <!-- Conteúdo Configurações -->
    <div v-else>
      <!-- Sub-tabs -->
      <div class="bg-gray-100 dark:bg-gray-800 -mx-4 -mt-6">
        <nav class="flex space-x-2">
          <Link :href="sub.url" v-for="sub in configTabs" :key="sub.name" @click="activeConfigTab = sub.name"
            class="flex items-center space-x-2 px-3 py-1 text-sm font-medium" :class="activeConfigTab === sub.name
              ? 'text-indigo-700 dark:text-white border-b-2'
              : 'text-gray-500 dark:text-white border-b-2 border-gray-100 hover:text-gray-700 hover:border-gray-300'">
            <i :class="sub.icon + ' text-base'"></i>
            <span>{{ sub.name }}</span>
          </Link>
        </nav>
      </div>

      <!-- Conteúdo das sub-tabs -->
      <div class="mt-4">
        <div v-if="activeConfigTab === 'Campos extras'">
          <ButtonCustom icon="bx-plus" text="Novo campo extra" url="/produtos/create" :outline="false" />
        </div>

        <div v-else-if="activeConfigTab === 'Status de pedido'">
          <ButtonCustom icon="bx-plus" text="Novo status de pedido" url="/produtos/create" :outline="false" />
        </div>

        <div v-else-if="activeConfigTab === 'Tipo de pedido'">
          <ButtonCustom icon="bx-plus" text="Novo tipo de pedido" url="/produtos/create" :outline="false" />
        </div>

        <div v-else>
          <!-- ITENS DO PEDIDO -->
          <h3 class="text-gray-700 dark:text-gray-200 font-semibold my-3">ITENS DO PEDIDO</h3>
          <div class="space-y-3">
            <!-- Radio buttons -->
            <div class="grid grid-cols-1 md:grid-cols-2 space-y-2 space-x-2">
              <label class="flex items-start space-x-2 bg-white border rounded-xl p-4 h-full" :class="pedidoConfig.itensDuplicados == 'permitir' ? 'border-indigo-500' : 'border-gray-300'">
                <input type="radio" value="permitir" v-model="pedidoConfig.itensDuplicados" class="mt-1">
                <div>
                  <span class="font-medium text-gray-800 dark:text-gray-50">Permitir itens duplicados</span>
                  <p class="text-gray-500 dark:text-gray-400 text-sm">
                    Permite que o mesmo item seja adicionado ao pedido mais de uma vez. Selecione essa opção se você
                    precisa
                    adicionar o mesmo produto com diferentes preços no pedido.
                  </p>
                </div>
              </label>

              <label class="flex items-start space-x-2 bg-white border rounded-xl border-gray-300 p-4 h-full" :class="pedidoConfig.itensDuplicados == 'nao-permitir' ? 'border-indigo-500' : 'border-gray-300'">
                <input type="radio" value="nao-permitir" v-model="pedidoConfig.itensDuplicados" class="mt-1">
                <div>
                  <span class="font-medium text-gray-800 dark:text-gray-50">Não permitir itens duplicados</span>
                  <p class="text-gray-500 dark:text-gray-400 text-sm">
                    Não permite que o mesmo item seja adicionado mais de uma vez no pedido.
                  </p>
                </div>
              </label>
            </div>

            <!-- Checkbox venda produtos com valor zero -->
            <label class="flex items-start space-x-2 my-4">
              <input type="checkbox" v-model="pedidoConfig.naoVenderZero" class="mt-1">
              <div>
                <span class="text-gray-800 dark:text-gray-50 font-medium">Não permitir a venda de produtos que tem valor
                  zero na tabela de preços</span>
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                  Se essa opção estiver marcada, o sistema não permitirá que produtos com valor zerado na tabela de
                  preços
                  sejam adicionados aos orçamentos/pedidos. No e-commerce B2B estes produtos nem serão exibidos no
                  catálogo.
                </p>
              </div>
            </label>
          </div>

          <!-- CAMPOS OBRIGATÓRIOS -->
          <h3 class="text-gray-700 dark:text-gray-200 font-semibold my-3">CAMPOS OBRIGATÓRIOS</h3>
          <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded space-y-3">
            <p class="text-gray-500 dark:text-gray-400 text-sm mb-2">Selecione os campos que devem ser obrigatórios:</p>

            <label class="flex items-center space-x-2">
              <input type="checkbox" v-model="pedidoConfig.camposObrigatorios.transportadora">
              <span class="text-gray-800 dark:text-gray-50">Transportadora</span>
            </label>

            <label class="flex items-center space-x-2">
              <input type="checkbox" v-model="pedidoConfig.camposObrigatorios.valorFrete">
              <span class="text-gray-800 dark:text-gray-50">Valor de frete</span>
            </label>
          </div>
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
import { usePage, Link } from "@inertiajs/vue3";
import { formatCurrency } from "@/lib/utils";

defineOptions({
  layout: AppLayout,
});

const page = usePage();
const pedidos = ref(page.props.pedidos);

const pedidoConfig = ref({
  itensDuplicados: 'nao-permitir',
  naoVenderZero: false,
  camposObrigatorios: {
    transportadora: false,
    valorFrete: false
  }
});

const statusMap = {
  aprovado: { label: "Aprovado", class: "bg-green-200 text-green-800 px-2 py-1 rounded" },
  pendente: { label: "Pendente", class: "bg-yellow-200 text-yellow-800 px-2 py-1 rounded" },
  cancelado: { label: "Cancelado", class: "bg-red-200 text-red-800 px-2 py-1 rounded" },
};

const columns = [
  { label: "Pedido", key: "id" },
  { label: "Cliente", key: "cliente.razao_social" },
  { label: "Emitido por", key: "usuario.name" },
  { label: "Valor", key: "total" },
  { label: "Status", key: "status" },
];

// Tabs principais com ícones
const mainTabs = [
  { name: "Pedidos", icon: "bx bx-cart", url: "./pedidos" },
  { name: "Configurações", icon: "bx bx-cog", url: "./pedidos/configuracoes" },
];
const activeMainTab = ref("Pedidos");

// Sub-tabs com ícones
const configTabs = [
  { name: "Campos extras", icon: "bx bx-list-plus", url: `./pedidos/configuracoes/campos_extras` },
  { name: "Status de pedido", icon: "bx bx-check-circle", url: "./pedidos/configuracoes/status_pedido" },
  { name: "Tipo de pedido", icon: "bx bx-category", url: "./pedidos/configuracoes/tipo_pedido" },
  { name: "Geral", icon: "bx bx-slider", url: "./pedidos/configuracoes/geral" },
];
const activeConfigTab = ref("Campos extras");

const filtros = ref({
  pedidos: "ativos",
  vendedores: "todos",
  plataformas: "todas",
  envio: "ignorar",
  status: "qualquer",
});

function total(row) {
  return row.itens.reduce((acc, item) => acc + (item.subtotal * 1), 0);
}

</script>
