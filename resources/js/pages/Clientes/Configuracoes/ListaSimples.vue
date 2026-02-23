<template>
  <ClientesConfigShell :empresa-id="empresaId" :tabs="tabs" :active-tab="activeTab">
    <div class="flex items-center gap-2 mb-8">
      <input v-model="novoNome" type="text" class="input-text" :placeholder="placeholder" />
      <button type="button" class="btn-indigo" :disabled="creating" @click="criar">
        {{ creating ? '...' : 'OK' }}
      </button>
    </div>

    <div v-if="items.length === 0" class="empty-state">
      <h3>{{ emptyTitle }}</h3>
      <p>{{ emptyDescription }}</p>
    </div>

    <div v-for="item in items" :key="item.id" class="row-item">
      <input v-model="edicao[item.id]" type="text" class="input-text flex-1" />
      <button type="button" class="btn-outline" @click="atualizar(item.id)">Editar</button>
      <button type="button" class="btn-danger" @click="excluir(item.id)">Excluir</button>
    </div>
  </ClientesConfigShell>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import ClientesConfigShell from './components/ClientesConfigShell.vue';

defineOptions({ layout: AppLayout });

const page = usePage();
const empresaId = Number(page.props.empresa_id);
const tabs = page.props.tabs || [];
const activeTab = page.props.active_tab || '';
const listKey = page.props.list_key || '';
const placeholder = page.props.placeholder || 'Criar novo item';
const emptyTitle = page.props.empty_title || 'Nenhum item cadastrado';
const emptyDescription = page.props.empty_description || '';
const items = computed(() => page.props.items || []);

const novoNome = ref('');
const creating = ref(false);
const edicao = ref(
  (items.value || []).reduce((acc, item) => {
    acc[item.id] = item.nome;
    return acc;
  }, {}),
);

const endpointMap = {
  tags: '/clientes/configuracoes/tags',
  segmentos: '/clientes/configuracoes/segmentos',
  redes: '/clientes/configuracoes/redes',
  excecoes_fiscais: '/clientes/configuracoes/excecoes_fiscais',
  resultados_atendimentos: '/clientes/configuracoes/resultados_atendimentos',
  motivos_bloqueio: '/clientes/configuracoes/motivos_bloqueio',
};

function endpointBase() {
  return `/${empresaId}${endpointMap[listKey] || ''}`;
}

function criar() {
  if (!novoNome.value.trim()) return;

  creating.value = true;
  router.post(endpointBase(), { nome: novoNome.value.trim() }, {
    preserveScroll: true,
    onFinish: () => {
      creating.value = false;
      novoNome.value = '';
    },
  });
}

function atualizar(id) {
  const nome = (edicao.value[id] || '').trim();
  if (!nome) return;
  router.put(`${endpointBase()}/${id}`, { nome }, { preserveScroll: true });
}

function excluir(id) {
  if (!window.confirm('Excluir este item?')) return;
  router.delete(`${endpointBase()}/${id}`, { preserveScroll: true });
}
</script>

<style scoped>
.btn-indigo { background: var(--color-indigo-600); border: 1px solid var(--color-indigo-600); color: #fff; border-radius: 8px; padding: 10px 18px; font-weight: 700; }
.btn-outline { border: 1px solid #d1d5db; color: var(--color-indigo-600); border-radius: 8px; padding: 9px 14px; font-weight: 700; background: #fff; }
.btn-danger { border: 1px solid #ef4444; color: #b91c1c; border-radius: 8px; padding: 9px 14px; font-weight: 700; background: #fff; }
.input-text { border: 1px solid #d1d5db; border-radius: 8px; padding: 9px 10px; min-width: 280px; }
.empty-state { text-align: center; border: 1px dashed #d1d5db; border-radius: 12px; padding: 30px 16px; }
.empty-state h3 { font-size: 30px; margin: 8px 0 4px; }
.empty-state p { color: #6b7280; }
.row-item { display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #ececec; padding: 10px 0; }
</style>

