<template>
  <div v-if="open" class="preview-backdrop" @click.self="$emit('close')">
    <div class="preview-modal">
      <div class="preview-header">
        <h3>Visualizar pedido</h3>
        <button type="button" class="icon-btn" @click="$emit('close')">
          <i class="bx bx-x"></i>
        </button>
      </div>

      <div class="preview-toolbar">
        <div class="preview-toolbar-left">
          <button type="button" class="btn-outline" @click="printPedido">
            <i class="bx bx-printer"></i>
            Imprimir
          </button>
          <button type="button" class="btn-outline" @click="downloadPdf">
            <i class="bx bx-download"></i>
            Download PDF
          </button>
        </div>

        <button type="button" class="btn-outline" @click="abrirConfiguracoes">
          <i class="bx bx-cog"></i>
          Configurar modelo do pedido
        </button>
      </div>

      <div class="preview-body" id="pedido-printable">
        <div class="print-sheet">
          <div class="sheet-header" v-if="config.cabecalhoTexto">
            {{ config.cabecalhoTexto }}
          </div>

          <div class="sheet-title">
            <strong>{{ representadaNome }}</strong>
            <div>Pedido N&ordm; {{ pedido?.id || '-' }}</div>
          </div>

          <table class="sheet-info-table">
            <tr>
              <td colspan="2"><b>Representada:</b> {{ representadaNome }}</td>
            </tr>
            <tr>
              <td><b>Cliente:</b> {{ pedido?.cliente?.razao_social || '-' }}</td>
              <td><b>Nome Fantasia:</b> {{ pedido?.cliente?.nome_fantasia || pedido?.cliente?.razao_social || '-' }}</td>
            </tr>
            <tr>
              <td><b>CNPJ:</b> {{ pedido?.cliente?.cnpj || '-' }}</td>
              <td><b>Inscricao Estadual:</b> {{ pedido?.cliente?.inscricao_estadual || '-' }}</td>
            </tr>
            <tr>
              <td><b>Endereco:</b> {{ pedido?.cliente?.endereco || '-' }}</td>
              <td><b>CEP:</b> {{ pedido?.cliente?.cep || '-' }}</td>
            </tr>
            <tr>
              <td><b>Cidade:</b> {{ pedido?.cliente?.cidade || '-' }}</td>
              <td><b>Estado:</b> {{ pedido?.cliente?.uf || '-' }}</td>
            </tr>
            <tr>
              <td><b>Telefone:</b> {{ pedido?.cliente?.telefone || '-' }}</td>
              <td><b>E-mail:</b> {{ pedido?.cliente?.email || '-' }}</td>
            </tr>
          </table>

          <table class="sheet-items-table">
            <thead>
              <tr>
                <th v-if="config.mostrarOrdemProduto" class="col-mini">#</th>
                <th v-if="config.mostrarCodigoProduto" class="col-mini">Codigo</th>
                <th class="col-produto">Produto</th>
                <th v-if="config.mostrarNcm" class="col-mini">NCM</th>
                <th v-if="config.mostrarQuantidade" class="col-mini">Qtde.</th>
                <th v-if="config.mostrarUnidadeDeMedida" class="col-mini">Unidade</th>
                <th v-if="config.mostrarDescontos" class="col-small">Desc.</th>
                <th v-if="config.mostrarPrecoLiquido" class="col-money">Preco Liquido</th>
                <th v-if="config.mostrarSt" class="col-small">ST</th>
                <th v-if="config.mostrarPrecoLiquidoComImpostos" class="col-money">Preco Liq. c/ Impostos</th>
                <th v-if="config.mostrarSubtotal" class="col-money">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in itensOrdenados" :key="`${item.produto_id || item.codigo || 'item'}-${index}`">
                <td v-if="config.mostrarOrdemProduto" class="text-center">{{ index + 1 }}</td>
                <td v-if="config.mostrarCodigoProduto" class="text-center">{{ item.codigo || '-' }}</td>
                <td>
                  <div class="produto-cell">
                    <img
                      v-if="config.mostrarImagemProduto && item.imagem_base64"
                      :src="item.imagem_base64"
                      alt="Produto"
                      :class="['produto-imagem', `size-${config.tamanhoImagemProduto}`]"
                    />
                    <div>
                      <div v-if="config.mostrarDescricaoProduto" class="produto-nome">{{ item.nome || '-' }}</div>
                      <div v-if="config.mostrarInformacoesAdicionaisProduto" class="produto-extra">
                        {{ item.observacoes || '-' }}
                      </div>
                    </div>
                  </div>
                </td>
                <td v-if="config.mostrarNcm" class="text-center">{{ item.ncm || '-' }}</td>
                <td v-if="config.mostrarQuantidade" class="text-center">{{ toNumber(item.quantidade) }}</td>
                <td v-if="config.mostrarUnidadeDeMedida" class="text-center">{{ item.unidade || 'UN' }}</td>
                <td v-if="config.mostrarDescontos" class="text-center">{{ formatDiscounts(item) }}</td>
                <td v-if="config.mostrarPrecoLiquido" class="text-right">{{ moneyDec(item.preco_liquido) }}</td>
                <td v-if="config.mostrarSt" class="text-center">{{ percent(item.st) }}</td>
                <td v-if="config.mostrarPrecoLiquidoComImpostos" class="text-right">
                  {{ moneyDec(precoLiquidoComImpostos(item)) }}
                </td>
                <td v-if="config.mostrarSubtotal" class="text-right">{{ money(item.subtotal) }}</td>
              </tr>
            </tbody>
          </table>

          <table class="sheet-totals-table">
            <tr v-if="config.mostrarQuantidadeTotal">
              <td>Quantidade total:</td>
              <td>{{ quantidadeTotal }}</td>
            </tr>
            <tr v-if="config.mostrarPesoBrutoTotal">
              <td>Peso bruto total:</td>
              <td>{{ pesoTotal.toFixed(3).replace('.', ',') }} kg</td>
            </tr>
            <tr v-if="config.mostrarValorTotalSemIpi">
              <td>Valor total em produtos:</td>
              <td>{{ money(totalProdutos) }}</td>
            </tr>
            <tr v-if="config.mostrarValorFrete">
              <td>Valor do frete:</td>
              <td>{{ money(pedido?.valor_frete || 0) }}</td>
            </tr>
            <tr v-if="config.mostrarValorTotal">
              <td><strong>Valor total:</strong></td>
              <td><strong>{{ money(totalPedido) }}</strong></td>
            </tr>
          </table>

          <table class="sheet-info-table details-bottom">
            <tr>
              <td><b>Condicao de Pagamento:</b> {{ pedido?.condicao_pagamento_nome || '-' }}</td>
              <td><b>Data de Emissao:</b> {{ formatDate(pedido?.data_emissao) }}</td>
            </tr>
            <tr><td colspan="2"><b>CODIGO:</b> {{ pedido?.codigo || '-' }}</td></tr>
            <tr><td colspan="2"><b>ESPECIAL:</b> {{ pedido?.especial || '-' }}</td></tr>
            <tr><td colspan="2"><b>NOME.REP:</b> {{ pedido?.nome_rep || pedido?.vendedor || '-' }}</td></tr>
            <tr><td colspan="2"><b>SUBSTITUICAO TRIBUTARIA:</b> {{ pedido?.substituicao_tributaria || '-' }}</td></tr>
          </table>

          <div class="sheet-footer" v-if="config.rodapeTexto">
            {{ config.rodapeTexto }}
          </div>
        </div>
      </div>
    </div>

    <div v-if="openConfigModal" class="config-backdrop" @click.self="openConfigModal = false">
      <div class="config-modal">
        <div class="config-header">
          <h4>Configurar impressao do pedido</h4>
          <button type="button" class="icon-btn" @click="openConfigModal = false">
            <i class="bx bx-x"></i>
          </button>
        </div>

        <div class="config-body">
          <section class="config-section">
            <h5>Itens do Pedido</h5>
            <p>Selecione as informacoes e defina ordenacao dos itens no pedido.</p>

            <div class="config-grid">
              <div class="config-col">
                <p class="config-col-title">Detalhes do produto</p>
                <label><input type="checkbox" v-model="draft.mostrarOrdemProduto" /> Numero de ordem do item</label>
                <label><input type="checkbox" v-model="draft.mostrarImagemProduto" /> Foto do produto</label>
                <label class="nested" v-if="draft.mostrarImagemProduto">
                  Tamanho da imagem
                  <select v-model.number="draft.tamanhoImagemProduto">
                    <option :value="1">Minima</option>
                    <option :value="2">Pequena</option>
                    <option :value="3">Media</option>
                    <option :value="4">Grande</option>
                  </select>
                </label>
                <label><input type="checkbox" v-model="draft.mostrarCodigoProduto" /> Codigo do produto</label>
                <label><input type="checkbox" v-model="draft.mostrarDescricaoProduto" /> Nome do produto</label>
                <label><input type="checkbox" v-model="draft.mostrarNcm" /> NCM</label>
                <label><input type="checkbox" v-model="draft.mostrarQuantidade" /> Quantidade</label>
                <label><input type="checkbox" v-model="draft.mostrarUnidadeDeMedida" /> Unidade de medida</label>
                <label><input type="checkbox" v-model="draft.mostrarInformacoesAdicionaisProduto" /> Info. adicionais do produto</label>
              </div>

              <div class="config-col">
                <p class="config-col-title">Precos, impostos e subtotais</p>
                <label><input type="checkbox" v-model="draft.mostrarDescontos" /> Descontos aplicados</label>
                <label><input type="checkbox" v-model="draft.mostrarPrecoLiquido" /> Preco liquido</label>
                <label><input type="checkbox" v-model="draft.mostrarPrecoLiquidoComImpostos" /> Preco liq. c/ impostos</label>
                <label><input type="checkbox" v-model="draft.mostrarSt" /> Aliquota ICMS-ST</label>
                <label><input type="checkbox" v-model="draft.mostrarSubtotal" /> Subtotal</label>
              </div>

              <div class="config-col">
                <p class="config-col-title">Totais do pedido</p>
                <label><input type="checkbox" v-model="draft.mostrarQuantidadeTotal" /> Quantidade total</label>
                <label><input type="checkbox" v-model="draft.mostrarPesoBrutoTotal" /> Peso bruto total</label>
                <label><input type="checkbox" v-model="draft.mostrarValorTotalSemIpi" /> Valor total em produtos</label>
                <label><input type="checkbox" v-model="draft.mostrarValorFrete" /> Valor do frete</label>
                <label><input type="checkbox" v-model="draft.mostrarValorTotal" /> Valor total</label>
              </div>
            </div>

            <div class="config-inline-grid">
              <label>
                Ordem dos itens
                <select v-model="draft.ordenacaoItem">
                  <option value="insercao_desc">Ordem de insercao (decrescente)</option>
                  <option value="insercao_asc">Ordem de insercao (crescente)</option>
                  <option value="nome">Nome do produto</option>
                  <option value="codigo">Codigo do produto</option>
                </select>
              </label>

              <label>
                Casas decimais do preco liquido
                <select v-model.number="draft.casasDecimaisPrecoLiquido">
                  <option v-for="n in [2,3,4,5,6,7,8,9,10]" :key="n" :value="n">{{ String(n).padStart(2, '0') }}</option>
                </select>
              </label>
            </div>
          </section>

          <section class="config-section">
            <h5>Cabecalho e Rodape</h5>
            <label>
              Cabecalho do pedido
              <textarea v-model="draft.cabecalhoTexto" rows="2" placeholder="Texto exibido no topo da impressao"></textarea>
            </label>
            <label>
              Rodape do pedido
              <textarea v-model="draft.rodapeTexto" rows="2" placeholder="Texto exibido no final da impressao"></textarea>
            </label>
          </section>

          <div class="config-alert">
            Esta configuracao sera aplicada em todos os pedidos neste navegador.
          </div>
        </div>

        <div class="config-footer">
          <button type="button" class="btn-outline" @click="restaurarPadrao">Restaurar padrao</button>
          <div class="config-footer-right">
            <button type="button" class="btn-outline" @click="openConfigModal = false">Cancelar</button>
            <button type="button" class="btn-primary" @click="salvarConfiguracoes">Salvar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { formatCurrency } from '@/lib/utils';

const props = defineProps({
  open: { type: Boolean, default: false },
  pedido: { type: Object, default: null },
  empresaNome: { type: String, default: '' },
});

const STORAGE_KEY = 'orderbox.pedido.print-config';

const defaultConfig = {
  mostrarOrdemProduto: true,
  mostrarImagemProduto: true,
  tamanhoImagemProduto: 3,
  mostrarCodigoProduto: true,
  mostrarDescricaoProduto: true,
  mostrarNcm: false,
  mostrarQuantidade: true,
  mostrarUnidadeDeMedida: true,
  mostrarInformacoesAdicionaisProduto: false,
  mostrarDescontos: true,
  mostrarPrecoLiquido: true,
  mostrarPrecoLiquidoComImpostos: true,
  mostrarSt: true,
  mostrarSubtotal: true,
  mostrarQuantidadeTotal: false,
  mostrarPesoBrutoTotal: false,
  mostrarValorTotalSemIpi: true,
  mostrarValorFrete: false,
  mostrarValorTotal: true,
  ordenacaoItem: 'insercao_asc',
  casasDecimaisPrecoLiquido: 2,
  cabecalhoTexto: '',
  rodapeTexto: '',
};

const openConfigModal = ref(false);
const config = reactive(loadConfig());
const draft = reactive({ ...config });

const representadaNome = computed(() => {
  return props.empresaNome || props.pedido?.representada?.[0]?.nome || props.pedido?.representada?.nome || '-';
});

const itensOrdenados = computed(() => {
  const itens = [...(props.pedido?.itens || [])];

  switch (config.ordenacaoItem) {
    case 'insercao_desc':
      return itens.reverse();
    case 'nome':
      return itens.sort((a, b) => String(a.nome || '').localeCompare(String(b.nome || '')));
    case 'codigo':
      return itens.sort((a, b) => String(a.codigo || '').localeCompare(String(b.codigo || '')));
    case 'insercao_asc':
    default:
      return itens;
  }
});

const totalProdutos = computed(() => {
  return itensOrdenados.value.reduce((acc, item) => acc + toNumber(item.subtotal), 0);
});

const totalPedido = computed(() => {
  return totalProdutos.value + toNumber(props.pedido?.valor_frete);
});

const quantidadeTotal = computed(() => {
  return itensOrdenados.value.reduce((acc, item) => acc + toNumber(item.quantidade), 0);
});

const pesoTotal = computed(() => {
  return itensOrdenados.value.reduce((acc, item) => acc + (toNumber(item.peso_bruto) * toNumber(item.quantidade)), 0);
});

function toNumber(value) {
  const parsed = Number(value);
  return Number.isFinite(parsed) ? parsed : 0;
}

function money(value) {
  return formatCurrency(toNumber(value));
}

function moneyDec(value) {
  const formatted = toNumber(value).toLocaleString('pt-BR', {
    minimumFractionDigits: config.casasDecimaisPrecoLiquido,
    maximumFractionDigits: config.casasDecimaisPrecoLiquido,
  });
  return `R$ ${formatted}`;
}

function percent(value) {
  const n = toNumber(value);
  return n ? `${n.toFixed(2).replace('.', ',')}%` : '---';
}

function formatDate(value) {
  if (!value) return '-';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return String(value);
  return date.toLocaleDateString('pt-BR');
}

function precoLiquidoComImpostos(item) {
  const preco = toNumber(item.preco_liquido);
  const st = toNumber(item.st) / 100;
  return preco * (1 + st);
}

function formatDiscounts(item) {
  const list = Array.isArray(item.item_desconto) ? item.item_desconto : [];
  if (!list.length) return '---';
  return list.map((value) => `${toNumber(value).toFixed(2).replace('.', ',')}%`).join(' + ');
}

function abrirConfiguracoes() {
  Object.assign(draft, config);
  openConfigModal.value = true;
}

function salvarConfiguracoes() {
  Object.assign(config, draft);
  window.localStorage.setItem(STORAGE_KEY, JSON.stringify(config));
  openConfigModal.value = false;
}

function restaurarPadrao() {
  Object.assign(draft, defaultConfig);
}

function loadConfig() {
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY);
    if (!raw) return { ...defaultConfig };

    const parsed = JSON.parse(raw);
    return { ...defaultConfig, ...parsed };
  } catch {
    return { ...defaultConfig };
  }
}

function buildPrintDocument() {
  const content = document.getElementById('pedido-printable')?.innerHTML;
  if (!content) return null;

  return `
    <html>
      <head>
        <title>Pedido #${props.pedido?.id || ''}</title>
        <style>
          body { font-family: Arial, sans-serif; padding: 18px; color: #111827; }
          .print-sheet { border: 1px solid #1f2937; }
          .sheet-header, .sheet-footer { text-align: center; padding: 8px; border-bottom: 1px solid #1f2937; }
          .sheet-footer { border-top: 1px solid #1f2937; border-bottom: 0; }
          .sheet-title { text-align: center; font-size: 28px; line-height: 1.1; padding: 8px; border-bottom: 1px solid #1f2937; }
          table { width: 100%; border-collapse: collapse; }
          th, td { border: 1px solid #888; padding: 6px; }
          th { background: #f3f4f6; font-weight: 700; }
          .text-right { text-align: right; }
          .text-center { text-align: center; }
          .sheet-totals-table td { text-align: right; }
          .sheet-totals-table td:last-child { width: 180px; }
          .produto-cell { display: flex; align-items: center; gap: 8px; }
          .produto-imagem { object-fit: cover; border: 1px solid #d1d5db; }
          .size-1 { width: 32px; height: 32px; }
          .size-2 { width: 48px; height: 48px; }
          .size-3 { width: 64px; height: 64px; }
          .size-4 { width: 88px; height: 88px; }
          @page { size: A4 portrait; margin: 12mm; }
        </style>
      </head>
      <body>${content}</body>
    </html>
  `;
}

function printPedido() {
  const html = buildPrintDocument();
  if (!html) return;

  const printWindow = window.open('', '_blank');
  if (!printWindow) return;

  printWindow.document.write(html);
  printWindow.document.close();
  printWindow.focus();
  setTimeout(() => {
    printWindow.print();
    printWindow.close();
  }, 120);
}

function downloadPdf() {
  // Browser native flow: user can choose "Salvar como PDF" in the print dialog.
  printPedido();
}
</script>

<style scoped>
.preview-backdrop {
  position: fixed;
  inset: 0;
  z-index: 70;
  background: rgba(15, 23, 42, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.preview-modal {
  width: min(1240px, 100%);
  max-height: calc(100vh - 32px);
  background: #fff;
  border: 1px solid #c7d2fe;
  border-radius: 10px;
  box-shadow: 0 20px 50px rgba(49, 46, 129, 0.25);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  border-bottom: 1px solid #e5e7eb;
  padding: 12px 16px;
}

.preview-header h3 {
  margin: 0;
  color: #111827;
  font-size: 30px;
}

.preview-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  border-bottom: 1px solid #e5e7eb;
  padding: 12px 16px;
}

.preview-toolbar-left {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.preview-body {
  overflow: auto;
  padding: 16px;
  background: #f8fafc;
}

.print-sheet {
  background: #fff;
  border: 2px solid #1f2937;
}

.sheet-header,
.sheet-footer {
  text-align: center;
  padding: 8px;
  border-bottom: 1px solid #1f2937;
  color: #374151;
  font-size: 13px;
}

.sheet-footer {
  border-top: 1px solid #1f2937;
  border-bottom: 0;
}

.sheet-title {
  text-align: center;
  font-size: 34px;
  line-height: 1.15;
  padding: 8px;
  border-bottom: 1px solid #1f2937;
}

.sheet-title strong {
  display: block;
}

.sheet-info-table,
.sheet-items-table,
.sheet-totals-table {
  width: 100%;
  border-collapse: collapse;
}

.sheet-info-table td,
.sheet-items-table th,
.sheet-items-table td,
.sheet-totals-table td {
  border: 1px solid #888;
  padding: 6px;
  vertical-align: middle;
}

.sheet-items-table th {
  background: #f3f4f6;
  font-weight: 700;
}

.col-mini { width: 64px; }
.col-small { width: 92px; }
.col-money { width: 132px; }
.col-produto { min-width: 220px; }

.text-center { text-align: center; }
.text-right { text-align: right; }

.produto-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.produto-imagem {
  object-fit: cover;
  border: 1px solid #d1d5db;
  border-radius: 4px;
}

.size-1 { width: 32px; height: 32px; }
.size-2 { width: 48px; height: 48px; }
.size-3 { width: 64px; height: 64px; }
.size-4 { width: 88px; height: 88px; }

.produto-nome {
  font-weight: 600;
  color: #1f2937;
}

.produto-extra {
  margin-top: 2px;
  color: #6b7280;
  font-size: 12px;
}

.sheet-totals-table td {
  text-align: right;
}

.sheet-totals-table td:last-child {
  width: 180px;
}

.details-bottom td {
  font-size: 13px;
}

.btn-primary,
.btn-outline,
.icon-btn {
  font-size: 14px;
}

.btn-primary {
  border: 1px solid #4f46e5;
  background: #4f46e5;
  color: #fff;
  border-radius: 6px;
  padding: 7px 12px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn-outline {
  border: 1px solid #d1d5db;
  background: #fff;
  color: #4338ca;
  border-radius: 6px;
  padding: 7px 12px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.icon-btn {
  border: 1px solid #d6d6dc;
  background: #fff;
  border-radius: 6px;
  padding: 4px 8px;
  color: #4b5563;
}

.config-backdrop {
  position: fixed;
  inset: 0;
  z-index: 80;
  background: rgba(15, 23, 42, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.config-modal {
  width: min(1220px, 100%);
  max-height: calc(100vh - 32px);
  overflow: auto;
  background: #fff;
  border: 1px solid #c7d2fe;
  border-radius: 10px;
  box-shadow: 0 20px 50px rgba(49, 46, 129, 0.25);
}

.config-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #e5e7eb;
  padding: 12px 16px;
}

.config-header h4 {
  margin: 0;
  color: #111827;
  font-size: 25px;
}

.config-body {
  padding: 16px;
}

.config-section {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 12px;
}

.config-section h5 {
  margin: 0;
  color: #3730a3;
  font-size: 22px;
}

.config-section > p {
  color: #6b7280;
  margin: 8px 0 12px;
  font-size: 13px;
}

.config-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.config-col {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.config-col-title {
  margin: 0 0 4px;
  color: #6b7280;
  font-size: 12px;
  font-weight: 600;
}

.config-col label,
.config-inline-grid label,
.config-section label {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #1f2937;
  font-size: 13px;
}

.config-col input[type='checkbox'] {
  accent-color: #4f46e5;
}

.config-col .nested {
  padding-left: 22px;
  display: flex;
  gap: 6px;
  align-items: center;
}

.config-col select,
.config-inline-grid select,
.config-section textarea {
  border: 1px solid #d1d5db;
  border-radius: 6px;
  padding: 6px 8px;
  color: #111827;
}

.config-section textarea {
  width: 100%;
  margin-top: 6px;
}

.config-inline-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
  margin-top: 10px;
}

.config-alert {
  border: 1px solid #c7d2fe;
  background: #eef2ff;
  color: #3730a3;
  padding: 10px;
  border-radius: 8px;
  font-size: 13px;
}

.config-footer {
  border-top: 1px solid #e5e7eb;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.config-footer-right {
  display: flex;
  align-items: center;
  gap: 8px;
}

@media (max-width: 980px) {
  .config-grid,
  .config-inline-grid {
    grid-template-columns: 1fr;
  }

  .preview-toolbar {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
