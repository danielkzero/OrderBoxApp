<template>
  <ClientesPageShell :empresa-id="empresaId" active-main-tab="clientes">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-4">
        <div class="flex items-center justify-between gap-4 flex-wrap">
          <div class="flex items-center gap-2">
            <Link :href="`/${empresaId}/clientes/vinculos-permissoes`" class="px-3 py-1 text-sm bg-indigo-50 border border-indigo-200 rounded text-indigo-600">
              <i class="bx bx-link"></i> Vinculos e permissoes
            </Link>
            <Link :href="`/${empresaId}/clientes/create`" class="px-3 py-1 text-sm bg-indigo-600 border border-indigo-600 rounded text-white">
              <i class="bx bx-plus"></i> Novo cliente
            </Link>
          </div>

          <div class="relative">
            <input
              v-model="search"
              type="text"
              placeholder="Pesquise por nome ou CNPJ"
              class="pl-8 pr-3 py-1 text-sm border rounded w-64"
            />
            <i class="bx bx-search absolute left-2 top-1.5 text-gray-400"></i>
          </div>
        </div>

        <div v-for="cliente in filteredClientes" :key="cliente.id" class="border-b border-gray-200 pb-4 pt-3">
          <div class="font-semibold text-indigo-600">
            <Link :href="`/${empresaId}/clientes/${cliente.id}/show`">
              {{ cliente.nome_fantasia || cliente.razao_social }}
            </Link>            
          </div>
          <div class="text-sm text-gray-600">{{ cliente.razao_social }} - {{ cliente.cnpj }}</div>

          <div v-for="telefone in (cliente.telefones || [])" :key="`telefone-${telefone.id}`" class="flex items-center gap-2 mt-1 text-sm text-gray-600">
            <i class="bx bx-phone"></i> <span>{{ telefone.numero }}</span>
          </div>
          <div v-for="email in (cliente.emails || [])" :key="`email-${email.id}`" class="flex items-center gap-2 mt-1 text-sm text-gray-600">
            <i class="bx bx-envelope"></i> <span>{{ email.email }}</span>
          </div>
          <div v-if="cliente.rua || cliente.bairro" class="flex items-center gap-2 mt-1 text-sm text-gray-600">
            <i class="bx bx-map"></i>
            <span>{{ [cliente.rua, cliente.bairro].filter(Boolean).join(' - ') }}</span>
          </div>

          <div class="mt-3 space-x-2">
            <Link :href="`/${empresaId}/clientes/${cliente.id}/edit`" class="px-3 py-1 text-sm border rounded text-indigo-600 border-indigo-400 inline-block">
              <i class="bx bx-edit"></i> Alterar
            </Link>
            <button class="px-3 py-1 text-sm border rounded text-red-600 border-red-400" @click="excluirCliente(cliente.id)">
              <i class="bx bx-trash"></i> Excluir
            </button>
          </div>
        </div>
      </div>

      <div class="bg-white rounded shadow p-6">
        <div class="flex justify-between items-center mb-2">
          <h3 class="text-sm font-semibold text-gray-700">CARTEIRA DE CLIENTES</h3>
          <span class="text-xs text-gray-500">{{ mesAtual }}</span>
        </div>

        <apexchart type="pie" height="220" :options="chartOptions" :series="series"></apexchart>

        <div class="text-xs text-gray-600 space-y-1 mt-2">
          <div><span class="text-green-500">&bull;</span> {{ counts.ativos }} ativos</div>
          <div><span class="text-yellow-500">&bull;</span> {{ counts.inativos_recentes }} inativos recentes</div>
          <div><span class="text-red-500">&bull;</span> {{ counts.inativos_antigos }} inativos antigos</div>
          <div><span class="text-gray-400">&bull;</span> {{ counts.prospects }} prospects</div>
        </div>
      </div>
    </div>
  </ClientesPageShell>
</template>

<script setup>
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ClientesPageShell from '@/pages/Clientes/components/ClientesPageShell.vue';
import { usePage, Link, router } from '@inertiajs/vue3';

defineOptions({
  layout: AppLayout,
});

const page = usePage();
const empresaId = Number(page.props.empresa);
const search = ref('');

const clientes = page.props.clientes || [];
const series = page.props.chartData?.series || [0, 0, 0, 0];
const counts = page.props.chartData?.counts || {
  ativos: 0,
  inativos_recentes: 0,
  inativos_antigos: 0,
  prospects: 0,
};

const chartOptions = {
  labels: ['Ativos', 'Inativos recentes', 'Inativos antigos', 'Prospects'],
  colors: ['#22c55e', '#eab308', '#ef4444', '#9ca3af'],
  legend: { show: false },
  dataLabels: { enabled: false },
};

const mesAtual = computed(() => {
  return new Date().toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' }).toUpperCase();
});

const filteredClientes = computed(() => {
  const termo = (search.value || '').toLowerCase().trim();
  if (!termo) return clientes;

  return clientes.filter((cliente) => {
    const razao = (cliente.razao_social || '').toLowerCase();
    const fantasia = (cliente.nome_fantasia || '').toLowerCase();
    const cnpj = (cliente.cnpj || '').toLowerCase();
    return razao.includes(termo) || fantasia.includes(termo) || cnpj.includes(termo);
  });
});

function excluirCliente(clienteId) {
  if (!confirm('Deseja realmente excluir este cliente?')) return;
  router.delete(`/${empresaId}/clientes/${clienteId}`, { preserveScroll: true });
}
</script>
