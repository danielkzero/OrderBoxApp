<template>
  <PedidosConfigShell :empresa-id="empresaId" :tabs="tabs" :active-tab="activeTab">
    <div class="flex items-center gap-2 mb-8">
      <input v-model="novoStatus" type="text" class="input-text" placeholder="Criar novo status de pedido" />
      <button type="button" class="btn-indigo" :disabled="creating" @click="criar">
        {{ creating ? '...' : 'OK' }}
      </button>
    </div>

    <div v-if="statusPedido.length === 0" class="empty-state">
      <i class="bx bx-transfer-alt text-6xl text-indigo-600"></i>
      <h3>Nenhum status cadastrado</h3>
      <p>Cadastre diferentes status para organizar seus pedidos.</p>
    </div>

    <div v-for="status in statusPedido" :key="status.id" class="row-item">
      <input v-model="statusEdicao[status.id]" type="text" class="input-text flex-1" />
      <button type="button" class="btn-outline" @click="atualizar(status.id)">Salvar</button>
      <button type="button" class="btn-danger" @click="excluir(status.id)">Excluir</button>
    </div>
  </PedidosConfigShell>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import PedidosConfigShell from './components/PedidosConfigShell.vue';

defineOptions({ layout: AppLayout });

const page = usePage();
const empresaId = Number(page.props.empresa_id);
const tabs = page.props.tabs || [];
const activeTab = page.props.active_tab || 'status_pedido';
const statusPedido = computed(() => page.props.status_pedido || []);

const novoStatus = ref('');
const creating = ref(false);
const statusEdicao = ref(
  (statusPedido.value || []).reduce((acc, item) => {
    acc[item.id] = item.nome;
    return acc;
  }, {}),
);

function criar() {
  if (!novoStatus.value.trim()) return;

  creating.value = true;
  router.post(`/${empresaId}/pedidos/configuracoes/status_pedido`, { nome: novoStatus.value.trim() }, {
    preserveScroll: true,
    onFinish: () => {
      creating.value = false;
      novoStatus.value = '';
    },
  });
}

function atualizar(id) {
  const nome = (statusEdicao.value[id] || '').trim();
  if (!nome) return;

  router.put(`/${empresaId}/pedidos/configuracoes/status_pedido/${id}`, { nome }, { preserveScroll: true });
}

function excluir(id) {
  if (!window.confirm('Excluir este status?')) return;
  router.delete(`/${empresaId}/pedidos/configuracoes/status_pedido/${id}`, { preserveScroll: true });
}
</script>

<style scoped>
.btn-indigo {
  background: var(--color-indigo-600);
  border: 1px solid var(--color-indigo-600);
  color: #fff;
  border-radius: 8px;
  padding: 10px 18px;
  font-weight: 700;
}

.btn-outline {
  border: 1px solid #d1d5db;
  color: var(--color-indigo-600);
  border-radius: 8px;
  padding: 9px 14px;
  font-weight: 700;
  background: #fff;
}

.btn-danger {
  border: 1px solid #ef4444;
  color: #b91c1c;
  border-radius: 8px;
  padding: 9px 14px;
  font-weight: 700;
  background: #fff;
}

.input-text {
  border: 1px solid #d1d5db;
  border-radius: 8px;
  padding: 9px 10px;
  min-width: 280px;
}

.empty-state {
  text-align: center;
  border: 1px dashed #d1d5db;
  border-radius: 12px;
  padding: 30px 16px;
}

.empty-state h3 {
  font-size: 30px;
  margin: 8px 0 4px;
}

.empty-state p {
  color: #6b7280;
}

.row-item {
  display: flex;
  align-items: center;
  gap: 8px;
  border-bottom: 1px solid #ececec;
  padding: 10px 0;
}
</style>

