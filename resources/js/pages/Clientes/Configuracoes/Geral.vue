<template>
  <ClientesConfigShell :empresa-id="empresaId" :tabs="tabs" :active-tab="activeTab">
    <h3 class="section-title">DUPLICIDADE DE CPF / CNPJ</h3>
    <label class="checkbox-row mb-8">
      <input v-model="form.bloquear_duplicidade_cpf_cnpj" type="checkbox" />
      <div>
        <h4>Bloquear o cadastro de mais de um cliente com o mesmo CPF / CNPJ</h4>
      </div>
    </label>

    <h3 class="section-title">CAMPOS OBRIGATORIOS</h3>
    <p class="text-gray-600 mb-4">Selecione os campos do cadastro de clientes que devem ser obrigatorios:</p>

    <div class="grid md:grid-cols-2 gap-8 mb-8">
      <div class="space-y-2">
        <h4 class="font-semibold text-gray-700">Dados basicos</h4>
        <label class="checkbox-line"><input v-model="form.obrigar_cpf_cnpj" type="checkbox" /> CPF / CNPJ</label>
        <label class="checkbox-line"><input v-model="form.obrigar_nome_fantasia" type="checkbox" /> Nome Fantasia / Apelido</label>
        <label class="checkbox-line"><input v-model="form.obrigar_telefone" type="checkbox" /> Telefone</label>
        <label class="checkbox-line"><input v-model="form.obrigar_email" type="checkbox" /> E-mail</label>
        <label class="checkbox-line"><input v-model="form.obrigar_inscricao_estadual" type="checkbox" /> Inscricao estadual</label>
        <label class="checkbox-line"><input v-model="form.obrigar_info_adicional" type="checkbox" /> Informacao adicional</label>
        <label class="checkbox-line"><input v-model="form.obrigar_segmento" type="checkbox" /> Segmento</label>
      </div>
      <div class="space-y-2">
        <h4 class="font-semibold text-gray-700">Endereco</h4>
        <label class="checkbox-line"><input v-model="form.obrigar_cep" type="checkbox" /> CEP</label>
        <label class="checkbox-line"><input v-model="form.obrigar_endereco" type="checkbox" /> Endereco</label>
        <label class="checkbox-line"><input v-model="form.obrigar_numero" type="checkbox" /> Numero</label>
        <label class="checkbox-line"><input v-model="form.obrigar_complemento" type="checkbox" /> Complemento</label>
        <label class="checkbox-line"><input v-model="form.obrigar_bairro" type="checkbox" /> Bairro</label>
        <label class="checkbox-line"><input v-model="form.obrigar_cidade" type="checkbox" /> Cidade</label>
        <label class="checkbox-line"><input v-model="form.obrigar_estado" type="checkbox" /> Estado</label>
      </div>
    </div>

    <div class="hint-box mb-6">
      A Razao social (pessoa juridica) e o Nome (pessoa fisica) sao campos obrigatorios por padrao.
    </div>

    <button type="button" class="btn-indigo" :disabled="form.processing" @click="salvar">
      {{ form.processing ? 'Salvando...' : 'Salvar' }}
    </button>
  </ClientesConfigShell>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import ClientesConfigShell from './components/ClientesConfigShell.vue';

defineOptions({ layout: AppLayout });

const page = usePage();
const empresaId = Number(page.props.empresa_id);
const tabs = page.props.tabs || [];
const activeTab = page.props.active_tab || 'geral';
const geral = page.props.geral || {};

const form = useForm({
  bloquear_duplicidade_cpf_cnpj: !!geral.bloquear_duplicidade_cpf_cnpj,
  obrigar_cpf_cnpj: !!geral.obrigar_cpf_cnpj,
  obrigar_nome_fantasia: !!geral.obrigar_nome_fantasia,
  obrigar_telefone: !!geral.obrigar_telefone,
  obrigar_email: !!geral.obrigar_email,
  obrigar_inscricao_estadual: !!geral.obrigar_inscricao_estadual,
  obrigar_info_adicional: !!geral.obrigar_info_adicional,
  obrigar_segmento: !!geral.obrigar_segmento,
  obrigar_cep: !!geral.obrigar_cep,
  obrigar_endereco: !!geral.obrigar_endereco,
  obrigar_numero: !!geral.obrigar_numero,
  obrigar_complemento: !!geral.obrigar_complemento,
  obrigar_bairro: !!geral.obrigar_bairro,
  obrigar_cidade: !!geral.obrigar_cidade,
  obrigar_estado: !!geral.obrigar_estado,
});

function salvar() {
  form.post(`/${empresaId}/clientes/configuracoes/geral`, { preserveScroll: true });
}
</script>

<style scoped>
.section-title { font-size: 31px; color: #9ca3af; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; margin-bottom: 14px; }
.checkbox-row { display: flex; align-items: flex-start; gap: 10px; }
.checkbox-row h4 { margin: 0; font-weight: 700; color: #111827; }
.checkbox-line { display: flex; align-items: center; gap: 8px; color: #111827; font-weight: 500; }
.hint-box { border: 1px solid #d1d5db; border-radius: 10px; padding: 12px; color: #4b5563; background: #f9fafb; }
.btn-indigo { background: var(--color-indigo-600); border: 1px solid var(--color-indigo-600); color: #fff; border-radius: 8px; padding: 10px 18px; font-weight: 700; }
</style>

