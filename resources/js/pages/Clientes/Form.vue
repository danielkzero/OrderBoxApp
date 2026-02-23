<template>
  <ClientesPageShell :empresa-id="empresaId" active-main-tab="clientes">
  <div class="bg-white rounded-sm border border-gray-200">
    <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
      <h1 class="text-xl font-semibold text-gray-800">{{ isEdit ? 'Alterar cliente' : 'Novo cliente' }}</h1>
      <Link :href="`/${empresaId}/clientes`" class="text-sm text-indigo-600 hover:text-indigo-700">Voltar</Link>
    </div>

    <div class="p-6 space-y-8">
      <div v-if="Object.keys(form.errors).length" class="border border-red-200 bg-red-50 text-red-700 rounded-lg p-3 text-sm">
        <p class="font-semibold mb-1">Corrija os campos obrigatorios:</p>
        <ul class="list-disc list-inside">
          <li v-for="(erro, chave) in form.errors" :key="chave">{{ erro }}</li>
        </ul>
      </div>

      <section class="space-y-4">
        <div class="flex items-center gap-6">
          <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input v-model="form.tipo" type="radio" value="J" class="text-indigo-600" />
            Pessoa juridica
          </label>
          <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input v-model="form.tipo" type="radio" value="F" class="text-indigo-600" />
            Pessoa fisica
          </label>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="field-label">{{ form.tipo === 'J' ? 'CNPJ' : 'CPF' }}</label>
            <input v-model="form.cnpj" type="text" class="field-input" />
          </div>
          <div>
            <label class="field-label">ICMS-ST *</label>
            <select v-model="form.icms_st_id" class="field-input">
              <option :value="null">Selecione...</option>
              <option v-for="item in icmsOptions" :key="item.id" :value="item.id">{{ item.nome }}</option>
            </select>
          </div>
        </div>

        <div>
          <label class="field-label">* {{ form.tipo === 'J' ? 'Razao social' : 'Nome' }}</label>
          <input v-model="form.razao_social" type="text" class="field-input" />
        </div>

        <div>
          <label class="field-label">Nome fantasia</label>
          <input v-model="form.nome_fantasia" type="text" class="field-input" />
        </div>

        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="field-label">Inscricao estadual</label>
            <input v-model="form.inscricao_estadual" type="text" class="field-input" />
          </div>
          <div>
            <label class="field-label">SUFRAMA</label>
            <input v-model="form.suframa" type="text" class="field-input" />
          </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
          <div>
            <label class="field-label">Excecao fiscal</label>
            <select v-model="form.excecao_fiscal_id" class="field-input">
              <option :value="null">Selecione...</option>
              <option v-for="item in excecoesFiscais" :key="item.id" :value="item.id">{{ item.nome }}</option>
            </select>
          </div>
          <div>
            <label class="field-label">Segmento</label>
            <select v-model="form.segmento_id" class="field-input">
              <option :value="null">Selecione...</option>
              <option v-for="item in segmentos" :key="item.id" :value="item.id">{{ item.nome }}</option>
            </select>
          </div>
          <div>
            <label class="field-label">Rede</label>
            <select v-model="form.rede_id" class="field-input">
              <option :value="null">Selecione...</option>
              <option v-for="item in redes" :key="item.id" :value="item.id">{{ item.nome }}</option>
            </select>
          </div>
        </div>

        <div>
          <label class="field-label">Tag</label>
          <select v-model="form.tags_ids" class="field-input" multiple>
            <option v-for="item in tags" :key="item.id" :value="item.id">{{ item.nome }}</option>
          </select>
          <p class="text-xs text-gray-500 mt-1">Segure Ctrl (ou Cmd) para selecionar mais de uma tag.</p>
        </div>

        <div>
          <label class="field-label">Informacoes adicionais</label>
          <textarea v-model="form.observacao" class="field-input min-h-28"></textarea>
        </div>
      </section>

      <section class="space-y-3">
        <h2 class="section-title">Telefones</h2>
        <div v-for="(telefone, idx) in form.telefones" :key="`telefone-${idx}`" class="flex gap-2">
          <input v-model="telefone.numero" type="text" class="field-input" placeholder="Numero" />
          <button type="button" class="btn-outline" @click="removeTelefone(idx)">Remover</button>
        </div>
        <button type="button" class="btn-outline" @click="addTelefone">Adicionar telefone</button>
      </section>

      <section class="space-y-3">
        <h2 class="section-title">E-mails</h2>
        <div v-for="(email, idx) in form.emails" :key="`email-${idx}`" class="flex gap-2">
          <input v-model="email.email" type="email" class="field-input" placeholder="E-mail" />
          <button type="button" class="btn-outline" @click="removeEmail(idx)">Remover</button>
        </div>
        <button type="button" class="btn-outline" @click="addEmail">Adicionar e-mail</button>
      </section>

      <section class="space-y-4">
        <h2 class="section-title">Endereco principal</h2>
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="field-label">CEP</label>
            <input v-model="form.endereco_principal.cep" type="text" class="field-input" />
          </div>
          <div>
            <label class="field-label">Endereco</label>
            <input v-model="form.endereco_principal.rua" type="text" class="field-input" />
          </div>
          <div>
            <label class="field-label">Numero</label>
            <input v-model="form.endereco_principal.numero" type="text" class="field-input" />
          </div>
          <div>
            <label class="field-label">Complemento</label>
            <input v-model="form.endereco_principal.complemento" type="text" class="field-input" />
          </div>
          <div>
            <label class="field-label">Bairro</label>
            <input v-model="form.endereco_principal.bairro" type="text" class="field-input" />
          </div>
          <div>
            <label class="field-label">Estado</label>
            <select v-model="form.endereco_principal.uf_codigo" class="field-input" @change="resetMunicipioPrincipal">
              <option :value="null">Selecione...</option>
              <option v-for="estado in estados" :key="estado.codigo" :value="estado.codigo">{{ estado.nome }}</option>
            </select>
          </div>
          <div class="md:col-span-2">
            <label class="field-label">Cidade</label>
            <select v-model="form.endereco_principal.municipio_codigo" class="field-input">
              <option :value="null">Selecione...</option>
              <option v-for="cidade in municipiosDaUf(form.endereco_principal.uf_codigo)" :key="cidade.codigo" :value="cidade.codigo">{{ cidade.nome }}</option>
            </select>
          </div>
        </div>
      </section>

      <section class="space-y-4">
        <h2 class="section-title">Enderecos adicionais</h2>
        <div v-for="(endereco, idx) in form.enderecos_adicionais" :key="`endereco-${idx}`" class="border border-gray-200 rounded-lg p-4 space-y-3">
          <div class="grid md:grid-cols-2 gap-4">
            <input v-model="endereco.cep" type="text" class="field-input" placeholder="CEP" />
            <input v-model="endereco.rua" type="text" class="field-input" placeholder="Endereco" />
            <input v-model="endereco.numero" type="text" class="field-input" placeholder="Numero" />
            <input v-model="endereco.complemento" type="text" class="field-input" placeholder="Complemento" />
            <input v-model="endereco.bairro" type="text" class="field-input" placeholder="Bairro" />
            <select v-model="endereco.uf_codigo" class="field-input" @change="resetMunicipioAdicional(idx)">
              <option :value="null">Estado</option>
              <option v-for="estado in estados" :key="estado.codigo" :value="estado.codigo">{{ estado.nome }}</option>
            </select>
            <select v-model="endereco.municipio_codigo" class="field-input md:col-span-2">
              <option :value="null">Cidade</option>
              <option v-for="cidade in municipiosDaUf(endereco.uf_codigo)" :key="cidade.codigo" :value="cidade.codigo">{{ cidade.nome }}</option>
            </select>
          </div>
          <button type="button" class="btn-outline" @click="removeEnderecoAdicional(idx)">Remover endereco</button>
        </div>
        <button type="button" class="btn-outline" @click="addEnderecoAdicional">Adicionar endereco</button>
      </section>

      <section class="space-y-4">
        <h2 class="section-title">Contatos</h2>
        <div v-for="(contato, contatoIdx) in form.contatos" :key="`contato-${contatoIdx}`" class="border border-gray-200 rounded-lg p-4 space-y-3">
          <div class="grid md:grid-cols-2 gap-4">
            <input v-model="contato.nome" type="text" class="field-input" placeholder="Nome" />
            <input v-model="contato.cargo" type="text" class="field-input" placeholder="Cargo" />
          </div>

          <div>
            <p class="field-label">Telefones do contato</p>
            <div v-for="(telefone, telIdx) in contato.telefones" :key="`contato-${contatoIdx}-tel-${telIdx}`" class="flex gap-2 mb-2">
              <input v-model="telefone.numero" type="text" class="field-input" placeholder="Numero" />
              <button type="button" class="btn-outline" @click="removeContatoTelefone(contatoIdx, telIdx)">Remover</button>
            </div>
            <button type="button" class="btn-outline" @click="addContatoTelefone(contatoIdx)">Adicionar telefone</button>
          </div>

          <div>
            <p class="field-label">E-mails do contato</p>
            <div v-for="(email, emailIdx) in contato.emails" :key="`contato-${contatoIdx}-mail-${emailIdx}`" class="flex gap-2 mb-2">
              <input v-model="email.email" type="email" class="field-input" placeholder="E-mail" />
              <button type="button" class="btn-outline" @click="removeContatoEmail(contatoIdx, emailIdx)">Remover</button>
            </div>
            <button type="button" class="btn-outline" @click="addContatoEmail(contatoIdx)">Adicionar e-mail</button>
          </div>

          <button type="button" class="btn-outline" @click="removeContato(contatoIdx)">Remover contato</button>
        </div>
        <button type="button" class="btn-outline" @click="addContato">Adicionar contato</button>
      </section>

      <section v-if="camposExtrasConfig.length" class="space-y-4">
        <h2 class="section-title">Campos extras</h2>
        <div class="grid md:grid-cols-2 gap-4">
          <div v-for="campo in camposExtrasConfig" :key="campo.id">
            <label class="field-label">{{ campo.nome }} <span v-if="campo.obrigatorio">*</span></label>
            <input
              v-if="campo.tipo === 'LIVRE'"
              v-model="form.campos_extras[String(campo.id)]"
              type="text"
              class="field-input"
            />
            <input
              v-else-if="campo.tipo === 'NUMERICO'"
              v-model="form.campos_extras[String(campo.id)]"
              type="number"
              step="0.01"
              class="field-input"
            />
            <input
              v-else-if="campo.tipo === 'DATA'"
              v-model="form.campos_extras[String(campo.id)]"
              type="date"
              class="field-input"
            />
            <input
              v-else-if="campo.tipo === 'HORA'"
              v-model="form.campos_extras[String(campo.id)]"
              type="time"
              class="field-input"
            />
            <select
              v-else
              v-model="form.campos_extras[String(campo.id)]"
              class="field-input"
            >
              <option :value="null">Selecione...</option>
              <option v-for="opcao in (campo.opcoes || [])" :key="opcao" :value="opcao">{{ opcao }}</option>
            </select>
          </div>
        </div>
      </section>

      <section class="space-y-4">
        <h2 class="section-title">Bloqueio</h2>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
          <input v-model="form.bloqueado" type="checkbox" class="text-indigo-600" />
          Cliente bloqueado
        </label>
        <div v-if="form.bloqueado">
          <label class="field-label">Motivo do bloqueio</label>
          <select v-model="form.motivo_bloqueio_id" class="field-input">
            <option :value="null">Selecione...</option>
            <option v-for="motivo in motivosBloqueio" :key="motivo.id" :value="motivo.id">{{ motivo.nome }}</option>
          </select>
        </div>
      </section>
    </div>

    <div class="border-t border-gray-200 p-6 flex gap-3">
      <button type="button" class="btn-primary" :disabled="form.processing" @click="salvar">
        {{ form.processing ? 'Salvando...' : 'Salvar' }}
      </button>
      <Link :href="`/${empresaId}/clientes`" class="btn-outline">Cancelar</Link>
    </div>
  </div>
  </ClientesPageShell>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import ClientesPageShell from '@/pages/Clientes/components/ClientesPageShell.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineOptions({ layout: AppLayout });

const page = usePage();
const empresaId = Number(page.props.empresa_id);
const isEdit = !!page.props.is_edit;
const cliente = page.props.cliente;

const icmsOptions = page.props.icms_options || [];
const tags = page.props.tags || [];
const segmentos = page.props.segmentos || [];
const redes = page.props.redes || [];
const excecoesFiscais = page.props.excecoes_fiscais || [];
const motivosBloqueio = page.props.motivos_bloqueio || [];
const camposExtrasConfig = page.props.campos_extras_config || [];
const estados = page.props.estados || [];
const municipiosPorUf = page.props.municipios_por_uf || {};

const form = useForm({
  tipo: cliente?.tipo || 'J',
  razao_social: cliente?.razao_social || '',
  nome_fantasia: cliente?.nome_fantasia || '',
  cnpj: cliente?.cnpj || '',
  inscricao_estadual: cliente?.inscricao_estadual || '',
  suframa: cliente?.suframa || '',
  icms_st_id: cliente?.icms_st_id || icmsOptions[0]?.id || null,
  tags_ids: cliente?.tags_ids || [],
  segmento_id: cliente?.segmento_id || null,
  rede_id: cliente?.rede_id || null,
  excecao_fiscal_id: cliente?.excecao_fiscal_id || null,
  bloqueado: !!cliente?.bloqueado,
  motivo_bloqueio_id: cliente?.motivo_bloqueio_id || null,
  observacao: cliente?.observacao || '',

  telefones: cliente?.telefones?.length ? cliente.telefones : [{ numero: '', tipo: 'T' }],
  emails: cliente?.emails?.length ? cliente.emails : [{ email: '', tipo: 'T' }],

  endereco_principal: cliente?.endereco_principal || {
    cep: '',
    rua: '',
    numero: '',
    complemento: '',
    bairro: '',
    municipio_codigo: null,
    uf_codigo: null,
  },
  enderecos_adicionais: cliente?.enderecos_adicionais || [],
  contatos: cliente?.contatos || [],

  campos_extras: cliente?.campos_extras || {},
});

function municipiosDaUf(ufCodigo) {
  if (!ufCodigo) return [];
  return municipiosPorUf[ufCodigo] || [];
}

function resetMunicipioPrincipal() {
  form.endereco_principal.municipio_codigo = null;
}

function resetMunicipioAdicional(index) {
  form.enderecos_adicionais[index].municipio_codigo = null;
}

function addTelefone() {
  form.telefones.push({ numero: '', tipo: 'T' });
}

function removeTelefone(index) {
  form.telefones.splice(index, 1);
  if (!form.telefones.length) addTelefone();
}

function addEmail() {
  form.emails.push({ email: '', tipo: 'T' });
}

function removeEmail(index) {
  form.emails.splice(index, 1);
  if (!form.emails.length) addEmail();
}

function addEnderecoAdicional() {
  form.enderecos_adicionais.push({
    cep: '',
    rua: '',
    numero: '',
    complemento: '',
    bairro: '',
    municipio_codigo: null,
    uf_codigo: null,
  });
}

function removeEnderecoAdicional(index) {
  form.enderecos_adicionais.splice(index, 1);
}

function addContato() {
  form.contatos.push({
    nome: '',
    cargo: '',
    telefones: [{ numero: '', tipo: 'T' }],
    emails: [{ email: '', tipo: 'T' }],
  });
}

function removeContato(index) {
  form.contatos.splice(index, 1);
}

function addContatoTelefone(contatoIdx) {
  form.contatos[contatoIdx].telefones.push({ numero: '', tipo: 'T' });
}

function removeContatoTelefone(contatoIdx, telefoneIdx) {
  form.contatos[contatoIdx].telefones.splice(telefoneIdx, 1);
  if (!form.contatos[contatoIdx].telefones.length) {
    form.contatos[contatoIdx].telefones.push({ numero: '', tipo: 'T' });
  }
}

function addContatoEmail(contatoIdx) {
  form.contatos[contatoIdx].emails.push({ email: '', tipo: 'T' });
}

function removeContatoEmail(contatoIdx, emailIdx) {
  form.contatos[contatoIdx].emails.splice(emailIdx, 1);
  if (!form.contatos[contatoIdx].emails.length) {
    form.contatos[contatoIdx].emails.push({ email: '', tipo: 'T' });
  }
}

function salvar() {
  if (isEdit && cliente?.id) {
    form.put(`/${empresaId}/clientes/${cliente.id}`, { preserveScroll: true });
    return;
  }

  form.post(`/${empresaId}/clientes`, { preserveScroll: true });
}
</script>

<style scoped>
.section-title {
  font-size: 30px;
  line-height: 36px;
  color: #9ca3af;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 8px;
}

.field-label {
  display: block;
  font-size: 13px;
  color: #374151;
  margin-bottom: 6px;
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
