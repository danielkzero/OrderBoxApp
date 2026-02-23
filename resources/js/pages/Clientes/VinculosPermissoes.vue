<template>
  <ClientesPageShell :empresa-id="empresaId" active-main-tab="clientes">
    <div class="border-b border-gray-200 py-4 flex items-center justify-between">
      <h1 class="text-xl font-semibold text-gray-800">Vinculos e permissoes</h1>
    </div>

    <div class="py-6">
      <p class="text-sm text-gray-600 mb-6">
        Utilize vinculos e permissoes para configurar quais clientes tem acesso as tabelas de precos, categorias,
        tags, condicoes de pagamento e representadas.
      </p>

      <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
        <h2 class="text-3xl leading-8 text-gray-400">CLIENTES</h2>
        <button type="button" class="btn-outline" @click="openFilter = true">
          <i class="bx bx-slider-alt mr-1"></i>
          Filtrar clientes
        </button>
      </div>

      <div class="overflow-auto border border-gray-200 rounded-lg">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-700">
            <tr>
              <th class="text-left px-3 py-2">Razao social</th>
              <th class="text-left px-3 py-2">Cidade</th>
              <th class="text-left px-3 py-2">Estado</th>
              <th class="text-left px-3 py-2">Tags</th>
              <th class="text-left px-3 py-2">Tabelas de preco</th>
              <th class="text-left px-3 py-2">Condicoes de pagamento</th>
              <th class="text-left px-3 py-2">Categorias</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="cliente in clientes" :key="cliente.id" class="border-t border-gray-200">
              <td class="px-3 py-2 text-indigo-700">{{ cliente.razao_social }}</td>
              <td class="px-3 py-2">{{ cliente.cidade || '--' }}</td>
              <td class="px-3 py-2">{{ cliente.estado || '--' }}</td>
              <td class="px-3 py-2">{{ cliente.tags?.length ? cliente.tags.join(', ') : '--' }}</td>
              <td class="px-3 py-2">{{ cliente.tabelas_preco }}</td>
              <td class="px-3 py-2">{{ cliente.condicoes_pagamento }}</td>
              <td class="px-3 py-2">{{ cliente.categorias }}</td>
            </tr>
            <tr v-if="!clientes.length">
              <td colspan="7" class="text-center text-gray-500 py-10">Nenhum cliente encontrado para os filtros informados.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="openFilter" class="fixed inset-0 z-50 flex justify-end">
      <div class="absolute inset-0 bg-black/30" @click="openFilter = false"></div>
      <aside class="relative w-full max-w-xs bg-white h-full shadow-xl border-l border-gray-200 p-4 overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
          <h3 class="text-3xl leading-8 text-gray-800">Filtrar Clientes</h3>
          <button type="button" class="text-indigo-600 text-2xl" @click="openFilter = false">&times;</button>
        </div>

        <div class="space-y-3">
          <label class="field-label">Nome ou CNPJ</label>
          <input v-model="form.nome_cnpj" type="text" class="field-input" />

          <label class="field-label">Cidade</label>
          <input v-model="form.cidade" type="text" class="field-input" />

          <label class="field-label">Email</label>
          <input v-model="form.email" type="text" class="field-input" />

          <hr class="my-3" />

          <select v-model="form.estado" class="field-input">
            <option value="">Todos os estados</option>
            <option v-for="estado in estados" :key="estado.codigo" :value="estado.codigo">{{ estado.nome }}</option>
          </select>

          <select v-model="form.vendedor_id" class="field-input">
            <option value="">Todos os vendedores</option>
            <option v-for="vendedor in vendedores" :key="vendedor.id" :value="vendedor.id">{{ vendedor.nome }}</option>
          </select>

          <select v-model="form.representada_id" class="field-input">
            <option value="">Todas as representadas</option>
            <option v-for="representada in representadas" :key="representada.id" :value="representada.id">{{ representada.nome }}</option>
          </select>

          <select v-model="form.segmento_id" class="field-input">
            <option value="">Todos os segmentos de cliente</option>
            <option v-for="segmento in segmentos" :key="segmento.id" :value="segmento.id">{{ segmento.nome }}</option>
          </select>

          <select v-model="form.rede_id" class="field-input">
            <option value="">Todas as redes de clientes</option>
            <option v-for="rede in redes" :key="rede.id" :value="rede.id">{{ rede.nome }}</option>
          </select>

          <select v-model="form.tag_id" class="field-input">
            <option value="">Todas as tags de cliente</option>
            <option v-for="tag in tags" :key="tag.id" :value="tag.id">{{ tag.nome }}</option>
          </select>
        </div>

        <div class="sticky bottom-0 bg-white pt-4 mt-4 border-t border-gray-200">
          <button type="button" class="btn-primary w-full" @click="aplicarFiltro">Filtrar</button>
        </div>
      </aside>
    </div>
  </ClientesPageShell>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import ClientesPageShell from '@/pages/Clientes/components/ClientesPageShell.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

defineOptions({ layout: AppLayout });

const page = usePage();
const empresaId = Number(page.props.empresa_id);

const clientes = page.props.clientes || [];
const estados = page.props.estados || [];
const vendedores = page.props.vendedores || [];
const representadas = page.props.representadas || [];
const segmentos = page.props.segmentos || [];
const redes = page.props.redes || [];
const tags = page.props.tags || [];

const openFilter = ref(false);

const form = reactive({
  nome_cnpj: page.props.filtros?.nome_cnpj || '',
  cidade: page.props.filtros?.cidade || '',
  email: page.props.filtros?.email || '',
  estado: page.props.filtros?.estado || '',
  vendedor_id: page.props.filtros?.vendedor_id || '',
  representada_id: page.props.filtros?.representada_id || '',
  segmento_id: page.props.filtros?.segmento_id || '',
  rede_id: page.props.filtros?.rede_id || '',
  tag_id: page.props.filtros?.tag_id || '',
});

function aplicarFiltro() {
  router.get(`/${empresaId}/clientes/vinculos-permissoes`, form, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    onSuccess: () => {
      openFilter.value = false;
    },
  });
}
</script>

<style scoped>
.field-label {
  display: block;
  font-size: 13px;
  color: #374151;
  margin-bottom: 4px;
}

.field-input {
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  padding: 9px 10px;
  font-size: 14px;
  color: #111827;
  background: #fff;
}

.field-input:focus {
  outline: none;
  border-color: var(--color-indigo-600);
  box-shadow: 0 0 0 2px rgb(79 70 229 / 0.15);
}

.btn-primary {
  background: var(--color-indigo-600);
  border: 1px solid var(--color-indigo-600);
  color: white;
  padding: 9px 16px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 14px;
}

.btn-outline {
  border: 1px solid #d1d5db;
  color: var(--color-indigo-600);
  padding: 9px 14px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  background: #fff;
}
</style>
