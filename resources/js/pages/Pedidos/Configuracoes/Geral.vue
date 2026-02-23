<template>
  <PedidosConfigShell :empresa-id="empresaId" :tabs="tabs" :active-tab="activeTab">
    <h3 class="section-title">ITENS DO PEDIDO</h3>

    <div class="grid md:grid-cols-2 gap-4 mb-4">
      <label class="radio-card" :class="{ active: form.permitir_itens_duplicados }">
        <input v-model="form.permitir_itens_duplicados" type="radio" :value="true" />
        <div>
          <h4>Permitir itens duplicados</h4>
          <p>Permite adicionar o mesmo item no pedido mais de uma vez.</p>
        </div>
      </label>

      <label class="radio-card" :class="{ active: !form.permitir_itens_duplicados }">
        <input v-model="form.permitir_itens_duplicados" type="radio" :value="false" />
        <div>
          <h4>Nao permitir itens duplicados</h4>
          <p>Nao permite adicionar o mesmo item mais de uma vez no pedido.</p>
        </div>
      </label>
    </div>

    <label class="checkbox-row mb-8">
      <input v-model="form.nao_permitir_preco_zerado" type="checkbox" />
      <div>
        <h4>Nao permitir venda de produtos com valor zerado na tabela de precos</h4>
        <p>Se marcado, o sistema nao aceita produtos com preco zerado no pedido.</p>
      </div>
    </label>

    <h3 class="section-title">CAMPOS OBRIGATORIOS</h3>
    <p class="text-gray-600 mb-3">Selecione os campos que devem ser obrigatorios:</p>
    <div class="space-y-2 mb-10">
      <label class="checkbox-line">
        <input v-model="form.obrigar_transportadora" type="checkbox" />
        Transportadora
      </label>
      <label class="checkbox-line">
        <input v-model="form.obrigar_valor_frete" type="checkbox" />
        Valor de frete
      </label>
    </div>

    <button type="button" class="btn-indigo" :disabled="form.processing" @click="salvar">
      {{ form.processing ? 'Salvando...' : 'Salvar' }}
    </button>
  </PedidosConfigShell>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import PedidosConfigShell from './components/PedidosConfigShell.vue';

defineOptions({ layout: AppLayout });

const page = usePage();
const empresaId = Number(page.props.empresa_id);
const tabs = page.props.tabs || [];
const activeTab = page.props.active_tab || 'geral';
const geral = page.props.geral || {};

const form = useForm({
  permitir_itens_duplicados: !!geral.permitir_itens_duplicados,
  nao_permitir_preco_zerado: !!geral.nao_permitir_preco_zerado,
  obrigar_transportadora: !!geral.obrigar_transportadora,
  obrigar_valor_frete: !!geral.obrigar_valor_frete,
});

function salvar() {
  form.post(`/${empresaId}/pedidos/configuracoes/geral`, { preserveScroll: true });
}
</script>

<style scoped>
.section-title {
  font-size: 31px;
  color: #9ca3af;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 6px;
  margin-bottom: 14px;
}

.radio-card {
  border: 1px solid #d1d5db;
  border-radius: 8px;
  padding: 14px;
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.radio-card.active {
  border-color: var(--color-indigo-600);
}

.radio-card h4,
.checkbox-row h4 {
  margin: 0;
  font-weight: 700;
  color: #111827;
}

.radio-card p,
.checkbox-row p {
  margin: 6px 0 0;
  color: #6b7280;
}

.checkbox-row {
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.checkbox-line {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #111827;
  font-weight: 600;
}

.btn-indigo {
  background: var(--color-indigo-600);
  border: 1px solid var(--color-indigo-600);
  color: #fff;
  border-radius: 8px;
  padding: 10px 18px;
  font-weight: 700;
}
</style>

