<template>
  <PedidosPageShell :empresa-id="empresaId" active-main-tab="pedidos">
    <div class="space-y-4">
      <div class="flex space-x-2">
        <ButtonCustom icon="bx-plus" text="Criar pedido / orcamento" :url="`/${empresaId}/pedidos/create`" :outline="false" />
        <ButtonCustom icon="bxs-printer" text="Imprimir pedido" :url="`/${empresaId}/pedidos`" :outline="true" />
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
          <option value="manual">Lancamento manual</option>
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
          <option value="pendente">Pendente</option>
          <option value="aprovado">Aprovado</option>
          <option value="cancelado">Cancelado</option>
        </FormField>
      </div>

      <div class="mt-4">
        <DataTable :columns="columns" :data="pedidosFiltrados">
          <template #cell-id="{ row }">
            <Link :href="`/${empresaId}/pedidos/${row.id}/edit`" class="text-indigo-700 hover:underline dark:text-indigo-300">
              #{{ row.id }}
            </Link>
          </template>
          <template #cell-criado_em="{ row }">{{ row.criado_em || '-' }}</template>
          <template #cell-enviado_label="{ row }">
            <span :class="row.foi_enviado ? 'bg-green-100 text-green-800 px-2 py-1 rounded' : 'bg-gray-200 text-gray-700 px-2 py-1 rounded'">
              {{ row.enviado_label }}
            </span>
          </template>
          <template #cell-total="{ row }">{{ formatCurrency(row.total) }}</template>
          <template #cell-status="{ row }">
            <span :class="statusMap[row.status]?.class ?? 'bg-gray-200 text-gray-800 px-2 py-1 rounded'">
              {{ statusMap[row.status]?.label ?? row.status }}
            </span>
          </template>
          <template #cell-acoes="{ row }">
            <Link :href="`/${empresaId}/pedidos/${row.id}/edit`" class="text-indigo-700 hover:underline dark:text-indigo-300">
              Editar
            </Link>
          </template>
        </DataTable>
      </div>
    </div>
  </PedidosPageShell>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import ButtonCustom from '@/components/ButtonCustom.vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import PedidosPageShell from '@/pages/Pedidos/components/PedidosPageShell.vue';
import { usePage, Link } from '@inertiajs/vue3';
import { formatCurrency } from '@/lib/utils';

defineOptions({ layout: AppLayout });

const page = usePage();
const pedidos = ref(page.props.pedidos ?? []);
const empresaId = page.props.empresa_id;

const statusMap = {
  aprovado: { label: 'Aprovado', class: 'bg-green-200 text-green-800 px-2 py-1 rounded' },
  pendente: { label: 'Pendente', class: 'bg-yellow-200 text-yellow-800 px-2 py-1 rounded' },
  cancelado: { label: 'Cancelado', class: 'bg-red-200 text-red-800 px-2 py-1 rounded' },
};

const columns = [
  { label: 'Pedido', key: 'id' },
  { label: 'Criado em', key: 'criado_em' },
  { label: 'Enviado', key: 'enviado_label' },
  { label: 'Cliente', key: 'cliente.razao_social' },
  { label: 'Emitido por', key: 'usuario.name' },
  { label: 'Valor', key: 'total' },
  { label: 'Status', key: 'status' },
  { label: 'Acoes', key: 'acoes', sortable: false },
];

const filtros = ref({
  pedidos: 'ativos',
  vendedores: 'todos',
  plataformas: 'todas',
  envio: 'ignorar',
  status: 'qualquer',
});

const pedidosFiltrados = computed(() => {
  return (pedidos.value || []).filter((pedido) => {
    const status = (pedido.status || '').toLowerCase();
    const valorFrete = Number(pedido.valor_frete ?? 0);
    const temEnvio = valorFrete > 0 || Boolean(pedido.transportadora_id) || Boolean(pedido.transportadora_nome);
    const tipoPedido = (pedido.tipo_pedido?.nome || '').toLowerCase();
    const nomeVendedor = (pedido.usuario?.name || '').toLowerCase();
    const roleVendedor = (pedido.usuario?.roles?.nome || '').toLowerCase();

    if (filtros.value.pedidos === 'ativos' && status === 'cancelado') return false;
    if (filtros.value.pedidos === 'finalizados' && status !== 'aprovado') return false;
    if (filtros.value.pedidos === 'cancelados' && status !== 'cancelado') return false;

    if (filtros.value.vendedores === 'internos') {
      const ehInterno = roleVendedor.includes('intern') || nomeVendedor.includes('intern');
      if (!ehInterno) return false;
    }

    if (filtros.value.vendedores === 'representantes') {
      const ehRepresentante = roleVendedor.includes('represent') || nomeVendedor.includes('represent');
      if (!ehRepresentante) return false;
    }

    if (filtros.value.plataformas === 'b2b' && !tipoPedido.includes('b2b')) return false;
    if (filtros.value.plataformas === 'app' && !tipoPedido.includes('app')) return false;
    if (filtros.value.plataformas === 'manual' && !tipoPedido.includes('manual')) return false;

    if (filtros.value.envio === 'com-envio' && !temEnvio) return false;
    if (filtros.value.envio === 'sem-envio' && temEnvio) return false;

    if (filtros.value.status !== 'qualquer' && status !== filtros.value.status) return false;

    return true;
  });
});
</script>
