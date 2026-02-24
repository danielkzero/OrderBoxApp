<template>
  <PedidosPageShell :empresa-id="empresaId" active-main-tab="pedidos">
  <div class="space-y-4">
    <div class="pedido-header box">
      <div class="flex items-center justify-between gap-3">
        <div>
          <p class="text-lg font-semibold">#{{ pedidoId || 'Novo' }}</p>
          <Link :href="`/${empresaId}/pedidos`" class="text-sm text-indigo-700 hover:underline">Voltar para pedidos</Link>
        </div>
        <span class="pedido-badge" :class="statusBadgeClass">{{ statusBadgeLabel }}</span>
      </div>
    </div>

    <div class="box">
      <div class="pedido-actions">
        <button type="button" class="btn-primary" :disabled="form.processing" @click="gerarOuAlterarPedido">
          <i class="bx bx-check"></i>
          {{ isEdit ? 'Alterar pedido' : 'Gerar pedido' }}
        </button>
        <button type="button" class="btn-outline" @click="openPrintModal = true">
          <i class="bx bx-file"></i>
          Visualizar
        </button>
        <button type="button" class="btn-outline" @click="enviarPorEmail">
          <i class="bx bx-envelope"></i>
          Enviar por e-mail
        </button>
        <button type="button" class="btn-outline" @click="enviarPorWhatsapp">
          <i class="bx bxl-whatsapp"></i>
          Enviar por whatsapp
        </button>
        <button v-if="isEdit" type="button" class="btn-outline" @click="openExportModal = true">
          <i class="bx bx-export"></i>
          Exportar pedido
        </button>
      </div>
    </div>

    <div v-if="Object.keys(form.errors).length" class="box error-box">
      Verifique os campos obrigatorios antes de salvar.
    </div>
    <div v-if="alertasEstoque.length" class="box error-box">
      <p class="font-semibold mb-2">Aviso de estoque:</p>
      <ul class="list-disc list-inside">
        <li v-for="alerta in alertasEstoque" :key="alerta.produto_id">
          {{ alerta.nome }} - solicitado {{ alerta.solicitado }}, disponivel {{ alerta.disponivel }}.
          Sugestao: {{ alerta.sugerido }}.
        </li>
      </ul>
    </div>

    <section class="box etapa">
      <div class="etapa-title"><i class="bx bx-store"></i> Cliente</div>

      <div class="grid gap-4">
        <div v-if="!clienteSelecionado || trocarCliente">
          <input
            v-model="clienteBusca"
            type="text"
            class="input"
            placeholder="Digite nome ou CNPJ para buscar cliente"
          />

          <div v-if="clientesFiltrados.length" class="dropdown-list mt-2">
            <button
              v-for="cliente in clientesFiltrados"
              :key="cliente.id"
              type="button"
              class="dropdown-item"
              @click="selecionarCliente(cliente)"
            >
              <div class="font-medium">{{ cliente.razao_social }}</div>
              <div class="text-xs text-gray-500">{{ cliente.cnpj || 'Sem CNPJ' }}</div>
            </button>
          </div>
        </div>

        <div class="cliente-card" v-if="clienteSelecionado && !trocarCliente">
          <template v-if="clienteSelecionado">
            <p class="cliente-name">{{ clienteSelecionado.razao_social }}</p>
            <p class="cliente-info">{{ clienteSelecionado.cnpj || 'Sem CNPJ' }}</p>
            <p class="cliente-info"><i class="bx bx-phone"></i> {{ contatoTelefonePrincipal || 'Sem telefone' }}</p>
            <p class="cliente-info"><i class="bx bx-envelope"></i> {{ contatoEmailPrincipal || 'Sem email' }}</p>
            <p v-if="telefonesCliente.length > 1" class="cliente-info"><b>Telefones:</b> {{ telefonesCliente.join(' | ') }}</p>
            <p v-if="emailsCliente.length > 1" class="cliente-info"><b>E-mails:</b> {{ emailsCliente.join(' | ') }}</p>
            <div v-if="contatosCliente.length" class="cliente-info">
              <b>Contatos:</b>
              <div v-for="contato in contatosCliente" :key="contato.id" class="cliente-contato-item">
                <span>{{ contato.nome }}<span v-if="contato.cargo"> ({{ contato.cargo }})</span></span>
                <span v-if="contato.telefones?.length"> - {{ contato.telefones.join(', ') }}</span>
                <span v-if="contato.emails?.length"> - {{ contato.emails.join(', ') }}</span>
              </div>
            </div>
            <p class="cliente-info">Tabela de preco: {{ clienteSelecionado.tabela_preco_nivel || 'Nao definida' }}</p>
            <button type="button" class="btn-outline btn-mini mt-2" @click="limparCliente">
              <i class="bx bx-refresh"></i>
              Trocar cliente
            </button>
          </template>
          <template v-else>
            <p class="text-gray-500">Nenhum cliente selecionado.</p>
          </template>
        </div>
      </div>
      <p v-if="form.errors.cliente_id" class="text-sm text-red-600 mt-2">{{ form.errors.cliente_id }}</p>
    </section>

    <section class="box etapa">
      <div class="etapa-title"><i class="bx bx-building-house"></i> Representada</div>
      <div class="cliente-card">
        <p class="cliente-name">{{ empresaAtual?.nome || 'Representada nao informada' }}</p>
        <p class="cliente-info">Empresa vinculada ao pedido</p>
      </div>
    </section>

    <section class="box etapa">
      <div class="etapa-title"><i class="bx bx-package"></i> Produtos</div>

      <input
        v-model="produtoBusca"
        type="text"
        class="input"
        placeholder="Digite o codigo ou nome do produto para adicionar ao pedido"
      />

      <div v-if="produtosFiltrados.length" class="dropdown-list mb-3">
        <button
          v-for="produto in produtosFiltrados"
          :key="produto.id"
          type="button"
          class="dropdown-item"
          @click="adicionarProduto(produto)"
        >
          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
              <img v-if="produto.imagem_base64" :src="produto.imagem_base64" alt="Foto" class="w-8 h-8 rounded object-cover border border-gray-200" />
              <div>
                <div class="font-medium">{{ produto.nome }}</div>
                <div class="text-xs text-gray-500">{{ produto.codigo || 'Sem codigo' }}</div>
              </div>
            </div>
            <div class="text-sm text-green-700">{{ formatCurrency(toNumber(produto.preco_tabela)) }}</div>
            <div v-if="gerenciarEstoqueAtivo" class="text-xs text-gray-500">Estoque: {{ formatarEstoque(produto.saldo_estoque) }}</div>
          </div>
        </button>
      </div>

      <div class="table-wrap">
        <table class="itens-table">
          <thead>
            <tr>
              <th>Foto</th>
              <th>Codigo</th>
              <th>Descricao</th>
              <th>Qtde.</th>
              <th>Preco Tab.</th>
              <th>
                <button type="button" class="th-link" @click="openGlobalAdjustModal = true">Desc. Acres.</button>
              </th>
              <th>Preco Liq.</th>
              <th>Subtotal</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in itensExibidos" :key="`${item.produto_id}-${index}`">
              <td>
                <img v-if="item.imagem_base64" :src="item.imagem_base64" alt="Foto" class="foto-item" />
              </td>
              <td>{{ item.codigo }}</td>
              <td>{{ item.nome }}</td>
              <td>
                <div class="qtd-cell">
                  <input
                    v-model.number="item.quantidade"
                    type="number"
                    min="1"
                    :max="gerenciarEstoqueAtivo ? estoqueDisponivelParaItem(item) : null"
                    class="input-mini"
                    @input="recalcularItem(item)"
                  />
                  <small>
                    {{ item.unidade || 'UN' }}
                    <span v-if="gerenciarEstoqueAtivo"> | Disp: {{ formatarEstoque(estoqueDisponivelParaItem(item)) }}</span>
                  </small>
                </div>
              </td>
              <td>{{ formatCurrency(toNumber(item.preco_tabela)) }}</td>
              <td>
                <button type="button" class="icon-btn" @click="abrirModalItem(item, index)"><i class="bx bx-pencil"></i></button>
                <span>{{ formatarPercentual(item.item_desconto, 'desconto') }} {{ formatarPercentual(item.item_acrescimo, 'acrescimo') }}</span>
              </td>
              <td>
                <input
                  :value="item.preco_liquido"
                  type="number"
                  step="0.01"
                  min="0"
                  class="input-mini"
                  @input="atualizarPreco(item, $event.target.value)"
                />
              </td>
              <td>{{ formatCurrency(toNumber(item.subtotal)) }}</td>
              <td>
                <button type="button" class="icon-btn" @click="removerItem(index)"><i class="bx bx-trash"></i></button>
              </td>
            </tr>
            <tr v-if="!form.itens.length">
              <td colspan="9" class="text-center py-4 text-gray-500">Nenhum item adicionado.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <button v-if="mostrarToggleItens" type="button" class="show-more" @click="showAllItems = !showAllItems">
        {{ showAllItems ? 'Mostrar menos itens' : `Mostrar todos os ${form.itens.length} produtos do pedido` }}
      </button>

      <div class="rodape-resumo">
        <div class="resumo-item">
          <label>Itens no pedido</label>
          <strong>{{ totalItens }}</strong>
        </div>
        <div class="resumo-item">
          <label>Quantidade total</label>
          <strong>{{ quantidadeTotal }}</strong>
        </div>
        <div class="resumo-item">
          <label>Peso bruto total</label>
          <strong>{{ pesoTotal.toFixed(3) }} kg</strong>
        </div>
        <div class="resumo-item">
          <label>Desconto medio</label>
          <strong>{{ descontoMedio }}</strong>
        </div>
        <div class="resumo-item total">
          <label>Valor total</label>
          <strong>{{ formatCurrency(valorTotalComFrete) }}</strong>
        </div>
      </div>
    </section>

    <section class="box etapa">
      <div class="etapa-title"><i class="bx bx-info-circle"></i> Detalhes do pedido</div>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <ItemDetalhe display="No do pedido:" :value="String(pedidoId || 'novo')" />
        <ItemDetalhe display="Data da emissao:" :value="formatDate(form.data_emissao)" />
        <ItemDetalhe display="Tipo de pedido:" :value="tipoPedidoNome" />
        <ItemDetalhe display="Vendedor:" :value="vendedorNome" />
        <ItemDetalhe display="Contato do cliente:" :value="form.contato_cliente" />
        <ItemDetalhe display="Tabela de preco do cliente:" :value="clienteSelecionado?.tabela_preco_nivel || '-'" />
        <ItemDetalhe display="Cond. de pagamento:" :value="condicaoPagamentoNome" />
        <ItemDetalhe display="Informacoes adicionais:" :value="form.observacoes" />
      </div>
      <div class="mt-3">
        <button type="button" class="btn-outline btn-mini" @click="openDetalhesModal = true">
          <i class="bx bx-pencil"></i>
          Alterar detalhes do pedido
        </button>
      </div>
    </section>

    <div class="box">
      <div class="pedido-actions">
        <button type="button" class="btn-primary" :disabled="form.processing" @click="gerarOuAlterarPedido">
          <i class="bx bx-check"></i>
          {{ isEdit ? 'Alterar pedido' : 'Gerar pedido' }}
        </button>
        <button type="button" class="btn-outline" @click="openPrintModal = true">
          <i class="bx bx-file"></i>
          Visualizar
        </button>
        <button type="button" class="btn-outline" @click="enviarPorEmail">
          <i class="bx bx-envelope"></i>
          Enviar por e-mail
        </button>
        <button type="button" class="btn-outline" @click="enviarPorWhatsapp">
          <i class="bx bxl-whatsapp"></i>
          Enviar por whatsapp
        </button>
        <button v-if="isEdit" type="button" class="btn-outline" @click="openExportModal = true">
          <i class="bx bx-export"></i>
          Exportar pedido
        </button>
      </div>
    </div>

    <ModalDescontosAcrescimosTotal
      :open="openGlobalAdjustModal"
      :discounts="globalDiscounts"
      :increases="globalIncreases"
      @close="openGlobalAdjustModal = false"
      @replace="substituirDescontos"
      @append="adicionarDescontos"
    />

    <ModalDescontoItem
      :open="openItemAdjustModal"
      :item="itemSelecionado"
      @close="fecharModalItem"
      @save="atualizarDescontoItem"
    />

    <ModalVisualizarPedido
      :open="openPrintModal"
      :pedido="pedidoPreview"
      :empresa-nome="empresaAtual?.nome || ''"
      @close="openPrintModal = false"
    />

    <div v-if="openDetalhesModal" class="modal-backdrop" @click.self="openDetalhesModal = false">
      <div class="details-modal">
        <div class="details-modal-header">
          <h3>Alterar detalhes do pedido</h3>
          <button type="button" class="icon-btn" @click="openDetalhesModal = false">
            <i class="bx bx-x"></i>
          </button>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
          <FormField label="Data da emissao" tag="input" type="date" v-model="form.data_emissao" />
          <FormField label="Tipo de pedido" tag="select" v-model="form.tipo_pedido_id">
            <option value="">Selecione</option>
            <option v-for="tipo in tiposPedidos" :key="tipo.id" :value="tipo.id">{{ tipo.nome }}</option>
          </FormField>
          <FormField label="Contato do cliente" tag="input" type="text" v-model="form.contato_cliente" />
          <FormField label="Forma de pagamento" tag="select" v-model="form.forma_pagamento_id">
            <option value="">Selecione</option>
            <option v-for="forma in formasPagamentos" :key="forma.id" :value="forma.id">{{ forma.nome }}</option>
          </FormField>
          <FormField label="Condicao de pagamento" tag="select" v-model="form.condicao_pagamento_id">
            <option value="">Selecione</option>
            <option v-for="condicao in condicoesPagamentos" :key="condicao.id" :value="condicao.id">{{ condicao.nome }}</option>
          </FormField>
          <FormField label="Valor do frete" tag="input" type="number" step="0.01" min="0" v-model="form.valor_frete" />
          <FormField label="Informacoes adicionais" tag="textarea" v-model="form.observacoes" class="md:col-span-2 lg:col-span-3" />
        </div>

        <div class="details-modal-footer">
          <button type="button" class="btn-outline" @click="openDetalhesModal = false">Fechar</button>
        </div>
      </div>
    </div>

    <div v-if="openExportModal" class="modal-backdrop" @click.self="openExportModal = false">
      <div class="details-modal">
        <div class="details-modal-header">
          <h3>Exportar pedido</h3>
          <button type="button" class="icon-btn" @click="openExportModal = false">
            <i class="bx bx-x"></i>
          </button>
        </div>

        <div class="grid md:grid-cols-2 gap-3">
          <FormField label="Formato" tag="select" v-model="exportConfig.formato">
            <option value="xls">XLS</option>
            <option value="csv">CSV</option>
            <option value="txt">TXT</option>
          </FormField>
        </div>

        <div class="export-options">
          <label><input v-model="exportConfig.incluir_cabecalho_colunas" type="checkbox" /> Incluir cabecalho de colunas</label>
          <label><input v-model="exportConfig.incluir_dados_cabecalho" type="checkbox" /> Incluir dados de cabecalho do pedido</label>
          <label><input v-model="exportConfig.incluir_itens" type="checkbox" /> Incluir itens</label>
          <label><input v-model="exportConfig.incluir_informacoes_extras" type="checkbox" /> Incluir informacoes extras</label>
        </div>

        <div class="mt-3">
          <h4 class="text-base font-semibold text-gray-700 mb-2">Colunas dos itens</h4>
          <div class="columns-grid">
            <label v-for="column in exportColumnOptions" :key="column.key">
              <input type="checkbox" :checked="exportConfig.colunas.includes(column.key)" @change="toggleExportColumn(column.key)" />
              {{ column.label }}
            </label>
          </div>
        </div>

        <div class="details-modal-footer gap-2">
          <button type="button" class="btn-outline" :disabled="savingExportConfig" @click="salvarConfiguracaoExportacao">
            {{ savingExportConfig ? 'Salvando...' : 'Salvar padrao' }}
          </button>
          <button type="button" class="btn-primary" :disabled="savingExportConfig" @click="exportarPedido">
            Exportar agora
          </button>
        </div>
      </div>
    </div>
  </div>
  </PedidosPageShell>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import PedidosPageShell from '@/pages/Pedidos/components/PedidosPageShell.vue';
import FormField from '@/components/FormField.vue';
import { formatCurrency } from '@/lib/utils';
import { computed, ref, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import ItemDetalhe from './components/ItemDetalhe.vue';
import ModalDescontosAcrescimosTotal from './components/ModalDescontosAcrescimosTotal.vue';
import ModalDescontoItem from './components/ModalDescontoItem.vue';
import ModalVisualizarPedido from './components/ModalVisualizarPedido.vue';

defineOptions({ layout: AppLayout });

const page = usePage();

const empresaId = page.props.empresa_id;
const pedidoInicial = page.props.pedido || null;
const isEdit = Boolean(page.props.is_edit);
const pedidoId = pedidoInicial?.id || null;
const clientes = page.props.clientes || [];
const produtos = page.props.produtos || [];
const formasPagamentos = page.props.formas_pagamentos || [];
const condicoesPagamentos = page.props.condicoes_pagamentos || [];
const tiposPedidos = page.props.tipos_pedidos || [];
const estoqueConfig = page.props.estoque_config || {};
const gerenciarEstoqueAtivo = Boolean(estoqueConfig.gerenciar_estoque);
const authUser = page.props.auth?.user || null;
const empresas = page.props.empresas || [];
const exportConfigInicial = page.props.export_config || null;

const clienteBusca = ref('');
const produtoBusca = ref('');

const openGlobalAdjustModal = ref(false);
const openItemAdjustModal = ref(false);
const openPrintModal = ref(false);
const openDetalhesModal = ref(false);
const openExportModal = ref(false);
const itemSelecionado = ref(null);
const itemSelecionadoIndex = ref(null);
const globalDiscounts = ref([]);
const globalIncreases = ref([]);
const showAllItems = ref(false);
const savingExportConfig = ref(false);

const exportColumnOptions = [
  { key: 'ordem', label: 'Ordem' },
  { key: 'codigo', label: 'Codigo' },
  { key: 'descricao', label: 'Descricao' },
  { key: 'quantidade', label: 'Quantidade' },
  { key: 'unidade', label: 'Unidade' },
  { key: 'desconto', label: 'Desconto' },
  { key: 'acrescimo', label: 'Acrescimo' },
  { key: 'preco_tabela', label: 'Preco tabela' },
  { key: 'preco_liquido', label: 'Preco liquido' },
  { key: 'st', label: 'ST' },
  { key: 'subtotal', label: 'Subtotal' },
  { key: 'observacoes', label: 'Observacoes' },
];

const trocarCliente = ref(false);

const exportConfig = ref({
  formato: exportConfigInicial?.formato || 'csv',
  incluir_cabecalho_colunas: exportConfigInicial?.incluir_cabecalho_colunas ?? true,
  incluir_dados_cabecalho: exportConfigInicial?.incluir_dados_cabecalho ?? true,
  incluir_itens: exportConfigInicial?.incluir_itens ?? true,
  incluir_informacoes_extras: exportConfigInicial?.incluir_informacoes_extras ?? true,
  colunas: Array.isArray(exportConfigInicial?.colunas) && exportConfigInicial.colunas.length
    ? [...exportConfigInicial.colunas]
    : ['ordem', 'codigo', 'descricao', 'quantidade', 'unidade', 'preco_liquido', 'st', 'subtotal'],
});

const form = useForm({
  cliente_id: pedidoInicial?.cliente_id || '',
  tipo_pedido_id: pedidoInicial?.tipo_pedido_id || '',
  forma_pagamento_id: pedidoInicial?.forma_pagamento_id || '',
  condicao_pagamento_id: pedidoInicial?.condicao_pagamento_id || '',
  valor_frete: pedidoInicial?.valor_frete || 0,
  data_emissao: pedidoInicial?.data_emissao || new Date().toISOString().slice(0, 10),
  contato_cliente: pedidoInicial?.contato_cliente || '',
  observacoes: pedidoInicial?.observacoes || '',
  status: pedidoInicial?.status || 'pendente',
  itens: (pedidoInicial?.itens || []).map((item) => ({
    ...item,
    quantidade: Number(item.quantidade || 1),
    multiplo: Number(item.multiplo || 1),
    qtd_unitaria: Number(item.multiplo || 1) * Number(item.quantidade || 1),
    item_desconto: Array.isArray(item.item_desconto) ? item.item_desconto : [],
    item_acrescimo: Array.isArray(item.item_acrescimo) ? item.item_acrescimo : [],
    preco_editado_manual: false,
  })),
});

const statusBadgeLabel = computed(() => {
  if (form.status === 'aprovado') return 'Gerado';
  if (form.status === 'cancelado') return 'Cancelado';
  return 'Em orcamento';
});

const statusBadgeClass = computed(() => {
  if (form.status === 'aprovado') return 'badge-success';
  if (form.status === 'cancelado') return 'badge-danger';
  return 'badge-warning';
});

const empresaAtual = computed(() => {
  return empresas.find((item) => Number(item.id) === Number(empresaId)) || null;
});

const clienteSelecionado = computed(() => {
  return clientes.find((cliente) => Number(cliente.id) === Number(form.cliente_id))
    || pedidoInicial?.cliente
    || null;
});

const telefonesCliente = computed(() => {
  if (!clienteSelecionado.value) return [];
  const telefonesDiretos = Array.isArray(clienteSelecionado.value.telefones) ? clienteSelecionado.value.telefones : [];
  const telefonesContatos = (clienteSelecionado.value.contatos || []).flatMap((contato) => contato.telefones || []);
  return [...new Set([...telefonesDiretos, ...telefonesContatos].filter(Boolean))];
});

const emailsCliente = computed(() => {
  if (!clienteSelecionado.value) return [];
  const emailsDiretos = Array.isArray(clienteSelecionado.value.emails) ? clienteSelecionado.value.emails : [];
  const emailsContatos = (clienteSelecionado.value.contatos || []).flatMap((contato) => contato.emails || []);
  return [...new Set([...emailsDiretos, ...emailsContatos].filter(Boolean))];
});

const contatoTelefonePrincipal = computed(() => {
  return clienteSelecionado.value?.telefone || telefonesCliente.value[0] || null;
});

const contatoEmailPrincipal = computed(() => {
  return clienteSelecionado.value?.email || emailsCliente.value[0] || null;
});

const contatosCliente = computed(() => {
  return Array.isArray(clienteSelecionado.value?.contatos) ? clienteSelecionado.value.contatos : [];
});

const condicaoPagamentoNome = computed(() => {
  return condicoesPagamentos.find((item) => Number(item.id) === Number(form.condicao_pagamento_id))?.nome || '-';
});

const tipoPedidoNome = computed(() => {
  return tiposPedidos.find((item) => Number(item.id) === Number(form.tipo_pedido_id))?.nome || '-';
});

const vendedorNome = computed(() => {
  return pedidoInicial?.vendedor || authUser?.name || '-';
});

const clientesFiltrados = computed(() => {
  const termo = clienteBusca.value.trim().toLowerCase();
  if (!termo) return clientes.slice(0, 8);

  return clientes
    .filter((cliente) => {
      const nome = String(cliente.razao_social || '').toLowerCase();
      const cnpj = String(cliente.cnpj || '').toLowerCase();
      return nome.includes(termo) || cnpj.includes(termo);
    })
    .slice(0, 8);
});

const produtosFiltrados = computed(() => {
  const termo = produtoBusca.value.trim().toLowerCase();

  if (!termo) return [];

  const base = produtos.filter((produto) => {
    if (gerenciarEstoqueAtivo && toNumber(produto.saldo_estoque) <= 0) {
      return false;
    }

    const nome = String(produto.nome || '').toLowerCase();
    const codigo = String(produto.codigo || '').toLowerCase();
    return nome.includes(termo) || codigo.includes(termo);
  });

  return base.slice(0, 20);
});

const totalItens = computed(() => form.itens.length);
const quantidadeTotal = computed(() => form.itens.reduce((acc, item) => acc + toNumber(item.qtd_unitaria), 0));
const pesoTotal = computed(() => form.itens.reduce((acc, item) => acc + (toNumber(item.peso_bruto) * toNumber(item.quantidade)), 0));
const valorTotalItens = computed(() => form.itens.reduce((acc, item) => acc + toNumber(item.subtotal), 0));
const valorTotalComFrete = computed(() => valorTotalItens.value + toNumber(form.valor_frete));

const descontoMedio = computed(() => {
  if (!form.itens.length) return '0,0000%';

  const soma = form.itens.reduce((acc, item) => {
    const totalItem = (item.item_desconto || []).reduce((a, v) => a + toNumber(v), 0);
    return acc + totalItem;
  }, 0);

  const media = soma / form.itens.length;
  return `${media.toFixed(4).replace('.', ',')}%`;
});

const mostrarToggleItens = computed(() => form.itens.length > 5);

const itensExibidos = computed(() => {
  if (showAllItems.value) return form.itens;
  return form.itens.slice(0, 5);
});

const pedidoPreview = computed(() => ({
  id: pedidoId,
  cliente: clienteSelecionado.value,
  contato_cliente: form.contato_cliente,
  condicao_pagamento_nome: condicaoPagamentoNome.value,
  data_emissao: form.data_emissao,
  vendedor: vendedorNome.value,
  itens: form.itens,
  valor_frete: form.valor_frete,
}));

const produtoPorId = computed(() => {
  const mapa = new Map();
  produtos.forEach((produto) => {
    mapa.set(Number(produto.id), produto);
  });
  return mapa;
});

const quantidadeOriginalPorProduto = computed(() => {
  const mapa = new Map();
  (pedidoInicial?.itens || []).forEach((item) => {
    const produtoId = Number(item.produto_id);
    const quantidade = toNumber(item.quantidade);
    mapa.set(produtoId, (mapa.get(produtoId) || 0) + quantidade);
  });
  return mapa;
});

const alertasEstoque = computed(() => {
  if (!gerenciarEstoqueAtivo) return [];

  return form.itens
    .map((item) => {
      const disponivel = toNumber(estoqueDisponivelParaItem(item));
      const solicitado = toNumber(item.quantidade);
      if (solicitado <= disponivel) return null;
      return {
        produto_id: item.produto_id,
        nome: item.nome || `Produto #${item.produto_id}`,
        solicitado,
        disponivel,
        sugerido: Math.max(0, Math.floor(disponivel)),
      };
    })
    .filter(Boolean);
});

watch(
  () => form.cliente_id,
  (clienteId) => {
    if (!clienteId) {
      clienteBusca.value = '';
      return;
    }

    const cliente = clientes.find((item) => Number(item.id) === Number(clienteId));
    if (cliente) clienteBusca.value = cliente.razao_social || '';
  },
  { immediate: true },
);

function toNumber(value) {
  const parsed = Number(value);
  return Number.isFinite(parsed) ? parsed : 0;
}

function formatDate(value) {
  if (!value) return '-';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return String(value);
  return date.toLocaleDateString('pt-BR');
}

function formatarPercentual(values, type) {
  if (!Array.isArray(values) || values.length === 0) return '';
  const symbol = type === 'desconto' ? '-' : '+';
  return values.map((v) => `${symbol}${toNumber(v).toFixed(2)}%`).join(' ');
}

function formatarEstoque(value) {
  return Math.max(0, Math.floor(toNumber(value)));
}

function estoqueDisponivelParaItem(item) {
  if (!gerenciarEstoqueAtivo) return Number.MAX_SAFE_INTEGER;

  const produtoId = Number(item.produto_id);
  const produtoAtual = produtoPorId.value.get(produtoId);
  const saldoAtual = toNumber(produtoAtual?.saldo_estoque);
  const reservadoOriginal = toNumber(quantidadeOriginalPorProduto.value.get(produtoId) || 0);
  return Math.max(0, Math.floor(saldoAtual + reservadoOriginal));
}

function selecionarCliente(cliente) {
  form.cliente_id = cliente.id;
  clienteBusca.value = cliente.razao_social || '';
  if (!form.contato_cliente && Array.isArray(cliente.contatos) && cliente.contatos.length) {
    form.contato_cliente = cliente.contatos[0]?.nome || '';
  }
  trocarCliente.value = false;
}

function limparCliente() {
  form.cliente_id = '';
  clienteBusca.value = '';

  trocarCliente.value = true;
}

function calcularPrecoComAjustes(item) {
  const base = Math.max(0, toNumber(item.preco_tabela));
  const desconto = (item.item_desconto || []).reduce((acc, p) => acc * (1 - (toNumber(p) / 100)), 1);
  const acrescimo = (item.item_acrescimo || []).reduce((acc, p) => acc * (1 + (toNumber(p) / 100)), 1);
  return base * desconto * acrescimo;
}

function adicionarProduto(produto) {
  if (gerenciarEstoqueAtivo && toNumber(produto.saldo_estoque) <= 0) {
    window.alert(`Sem estoque para ${produto.nome}.`);
    return;
  }

  const itemExistente = form.itens.find((item) => Number(item.produto_id) === Number(produto.id));

  if (itemExistente) {
    itemExistente.quantidade = toNumber(itemExistente.quantidade) + 1;
    recalcularItem(itemExistente);
    produtoBusca.value = '';
    return;
  }

  const preco = toNumber(produto.preco_tabela);
  const multiplo = Math.max(1, toNumber(produto.multiplo || 1));

  form.itens.push({
    produto_id: produto.id,
    nome: produto.nome,
    codigo: produto.codigo,
    imagem_base64: produto.imagem_base64 || null,
    unidade: produto.unidade || 'UN',
    multiplo,
    qtd_unitaria: multiplo,
    peso_bruto: toNumber(produto.peso_bruto),
    quantidade: 1,
    preco_tabela: preco,
    preco_liquido: preco,
    st: toNumber(produto.st),
    subtotal: preco,
    item_desconto: [],
    item_acrescimo: [],
    preco_editado_manual: false,
    observacoes: '',
  });

  produtoBusca.value = '';
}

function atualizarPreco(item, rawValue) {
  item.preco_liquido = Math.max(0, toNumber(rawValue));
  item.preco_editado_manual = true;
  item.subtotal = toNumber(item.preco_liquido) * toNumber(item.quantidade);
}

function recalcularItem(item) {
  const quantidade = Math.max(1, toNumber(item.quantidade));
  const multiplo = Math.max(1, toNumber(item.multiplo || 1));

  item.quantidade = quantidade;
  item.qtd_unitaria = quantidade * multiplo;

  if (!item.preco_editado_manual) {
    item.preco_liquido = calcularPrecoComAjustes(item);
  }

  item.subtotal = toNumber(item.preco_liquido) * quantidade;
}

function removerItem(index) {
  form.itens.splice(index, 1);
}

function substituirDescontos({ discounts, increases }) {
  globalDiscounts.value = discounts;
  globalIncreases.value = increases;

  form.itens.forEach((item) => {
    if (item.preco_editado_manual) return;
    item.item_desconto = [...discounts];
    item.item_acrescimo = [...increases];
    recalcularItem(item);
  });

  openGlobalAdjustModal.value = false;
}

function adicionarDescontos({ discounts, increases }) {
  globalDiscounts.value = [...globalDiscounts.value, ...discounts];
  globalIncreases.value = [...globalIncreases.value, ...increases];

  form.itens.forEach((item) => {
    if (item.preco_editado_manual) return;
    item.item_desconto = [...(item.item_desconto || []), ...discounts];
    item.item_acrescimo = [...(item.item_acrescimo || []), ...increases];
    recalcularItem(item);
  });

  openGlobalAdjustModal.value = false;
}

function abrirModalItem(item, index) {
  itemSelecionado.value = item;
  itemSelecionadoIndex.value = index;
  openItemAdjustModal.value = true;
}

function fecharModalItem() {
  openItemAdjustModal.value = false;
  itemSelecionado.value = null;
  itemSelecionadoIndex.value = null;
}

function atualizarDescontoItem({ discounts, increases }) {
  if (itemSelecionadoIndex.value === null) return;

  const item = form.itens[itemSelecionadoIndex.value];
  if (!item) return;

  item.item_desconto = [...discounts];
  item.item_acrescimo = [...increases];
  item.preco_editado_manual = false;
  recalcularItem(item);
  fecharModalItem();
}

function salvar(status = null) {
  if (gerenciarEstoqueAtivo && alertasEstoque.value.length) {
    const lista = alertasEstoque.value
      .map((item) => `${item.nome} (disp: ${item.disponivel}, solicitado: ${item.solicitado})`)
      .join('; ');
    window.alert(`Estoque insuficiente para: ${lista}`);
    return;
  }

  if (status) form.status = status;

  form
    .transform((data) => ({
      ...data,
      valor_frete: toNumber(data.valor_frete),
      itens: data.itens.map((item) => ({
        produto_id: item.produto_id,
        quantidade: Math.max(1, toNumber(item.quantidade)),
        preco_tabela: toNumber(item.preco_tabela),
        preco_liquido: Math.max(0, toNumber(item.preco_liquido)),
        subtotal: Math.max(0, toNumber(item.subtotal)),
        item_desconto: Array.isArray(item.item_desconto) ? item.item_desconto.map((n) => toNumber(n)) : [],
        item_acrescimo: Array.isArray(item.item_acrescimo) ? item.item_acrescimo.map((n) => toNumber(n)) : [],
        observacoes: item.observacoes || null,
      })),
    }))
    [isEdit ? 'put' : 'post'](isEdit ? `/${empresaId}/pedidos/${pedidoId}` : `/${empresaId}/pedidos`);
}

function gerarOuAlterarPedido() {
  salvar('aprovado');
}

function enviarPorEmail() {
  const email = contatoEmailPrincipal.value;
  if (!email) {
    window.alert('Cliente sem e-mail cadastrado.');
    return;
  }

  const assunto = isEdit ? `Pedido #${pedidoId}` : 'Novo pedido';
  window.location.href = `mailto:${email}?subject=${encodeURIComponent(assunto)}`;
}

function enviarPorWhatsapp() {
  const telefoneRaw = contatoTelefonePrincipal.value;
  if (!telefoneRaw) {
    window.alert('Cliente sem telefone cadastrado.');
    return;
  }

  const telefone = normalizarTelefoneWhatsapp(telefoneRaw);
  if (!telefone) {
    window.alert('Telefone do cliente invalido.');
    return;
  }

  const mensagem = isEdit ? `Pedido #${pedidoId} atualizado.` : 'Novo pedido gerado.';
  window.open(`https://wa.me/${telefone}?text=${encodeURIComponent(mensagem)}`, '_blank');
}

function normalizarTelefoneWhatsapp(value) {
  const digits = String(value || '').replace(/\D/g, '');
  if (!digits) return '';

  // Assume Brasil (55) when the number has local length.
  if (digits.length <= 11) return `55${digits}`;
  return digits;
}

function toggleExportColumn(column) {
  const selected = exportConfig.value.colunas;
  if (selected.includes(column)) {
    if (selected.length === 1) return;
    exportConfig.value.colunas = selected.filter((item) => item !== column);
    return;
  }

  exportConfig.value.colunas = [...selected, column];
}

async function salvarConfiguracaoExportacao() {
  savingExportConfig.value = true;

  try {
    const xsrfToken = decodeURIComponent(
      document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1] || '',
    );

    const response = await fetch(`/${empresaId}/pedidos/export-config`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': xsrfToken,
      },
      credentials: 'same-origin',
      body: JSON.stringify(exportConfig.value),
    });

    if (!response.ok) {
      throw new Error('Falha ao salvar configuracao de exportacao.');
    }

    window.alert('Padrao de exportacao salvo com sucesso.');
    return true;
  } catch (error) {
    window.alert(error?.message || 'Nao foi possivel salvar a configuracao.');
    return false;
  } finally {
    savingExportConfig.value = false;
  }
}

async function exportarPedido() {
  if (!isEdit || !pedidoId) {
    window.alert('Somente pedidos salvos podem ser exportados.');
    return;
  }

  const salvo = await salvarConfiguracaoExportacao();
  if (!salvo) return;

  const formato = encodeURIComponent(exportConfig.value.formato || 'csv');
  window.location.href = `/${empresaId}/pedidos/${pedidoId}/export?formato=${formato}`;
}
</script>

<style scoped>
.pedido-page {
  background: #f3f3f5;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 12px;
}

.box {
  background: var(--color-white);
  border: 1px solid #ddd;
  border-radius: 6px;
  padding: 14px;
}

.pedido-badge {
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}

.badge-warning {
  background: #f5d66f;
  color: #6c5600;
}

.badge-success {
  background: #b7e4c7;
  color: #155724;
}

.badge-danger {
  background: #f8c1c1;
  color: #721c24;
}

.pedido-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.btn-primary,
.btn-outline,
.btn-mini,
.icon-btn,
.th-link {
  font-size: 14px;
}

.btn-primary {
  border: 1px solid var(--color-indigo-600);
  background: var(--color-indigo-600);
  color: #fff;
  border-radius: 6px;
  padding: 7px 12px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn-outline {
  border: 1px solid #cfcfd5;
  background: #fff;
  color: var(--color-indigo-600);
  border-radius: 6px;
  padding: 7px 12px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn-mini {
  padding: 5px 10px;
}

.input {
  width: 100%;
  border: 1px solid #ccc;
  border-radius: 4px;
  padding: 8px 10px;
  background: #fff;
}

.dropdown-list {
  border: 1px solid #d8d8dd;
  border-radius: 4px;
  overflow: auto;
  max-height: 220px;
  background: #fff;
}

.dropdown-item {
  width: 100%;
  text-align: left;
  padding: 8px 10px;
  border-bottom: 1px solid #efeff2;
}

.dropdown-item:last-child {
  border-bottom: 0;
}

.dropdown-item:hover {
  background: #f5f2ff;
}

.etapa-title {
  border-bottom: 1px solid #d7d7dd;
  padding-bottom: 6px;
  margin-bottom: 12px;
  font-size: 30px;
  line-height: 1;
  font-size: 31px;
  color: #72727a;
  display: flex;
  align-items: center;
  gap: 8px;
}

.cliente-card {
  border: 1px solid #ddd;
  background: #fff;
  border-radius: 6px;
  padding: 10px;
}

.cliente-name {
  font-weight: 600;
  color: var(--color-indigo-600);
}

.cliente-info {
  color: #666;
  margin-top: 3px;
}

.cliente-contato-item {
  margin-top: 4px;
  font-size: 13px;
  color: #4b5563;
}

.table-wrap {
  border: 1px solid #d7d7dd;
  border-radius: 4px;
  overflow-x: auto;
  background: #fff;
}

.itens-table {
  width: 100%;
  font-size: 14px;
}

.itens-table thead th {
  text-align: left;
  padding: 10px;
  color: var(--color-indigo-600);
  border-bottom: 1px solid #e3e3e8;
}

.itens-table tbody td {
  padding: 10px;
  border-bottom: 1px solid #ececf0;
}

.foto-item {
  width: 48px;
  height: 48px;
  object-fit: cover;
  border: 1px solid #ddd;
}

.input-mini {
  width: 90px;
  border: 1px solid #ccc;
  border-radius: 4px;
  padding: 6px;
  background: #fff;
}

.qtd-cell {
  display: flex;
  align-items: center;
  gap: 6px;
}

.icon-btn {
  border: 1px solid #d6d6dc;
  background: #fff;
  border-radius: 4px;
  padding: 4px 6px;
  margin-right: 4px;
}

.th-link {
  color: var(--color-indigo-600);
  text-decoration: underline;
  background: transparent;
  border: 0;
  padding: 0;
}

.show-more {
  width: 100%;
  border-top: 1px solid #ddd;
  border-bottom: 1px solid #ddd;
  padding: 8px 0;
  color: var(--color-indigo-600);
  margin-top: 8px;
}

.rodape-resumo {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 10px;
  margin-top: 10px;
  border-top: 1px solid #ddd;
  padding-top: 12px;
}

.resumo-item {
  text-align: center;
}

.resumo-item label {
  display: block;
  font-size: 13px;
  color: #777;
}

.resumo-item strong {
  font-size: 28px;
  line-height: 1.2;
  font-size: 30px;
  color: #1f2937;
}

.resumo-item.total strong {
  color: #111827;
}

.error-box {
  border-color: #f1bcbc;
  color: #8a1c1c;
  background: #fff5f5;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 60;
  padding: 16px;
}

.details-modal {
  width: 100%;
  max-width: 960px;
  max-height: calc(100vh - 32px);
  overflow: auto;
  border: 1px solid #c7d2fe;
  border-radius: 10px;
  background: #fff;
  padding: 16px;
  box-shadow: 0 20px 50px rgba(49, 46, 129, 0.25);
}

.details-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 12px;
  padding-bottom: 10px;
  border-bottom: 1px solid #e0e7ff;
}

.details-modal-header h3 {
  margin: 0;
  color: #3730a3;
  font-size: 18px;
  font-weight: 600;
}

.details-modal-footer {
  margin-top: 12px;
  display: flex;
  justify-content: flex-end;
}

.export-options {
  margin-top: 8px;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
}

.export-options label,
.columns-grid label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #374151;
}

.columns-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
}

@media (max-width: 900px) {
  .rodape-resumo {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .export-options,
  .columns-grid {
    grid-template-columns: 1fr;
  }
}
</style>
