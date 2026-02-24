<template>
  <ProdutosPageShell
    :main-tabs="mainTabs"
    :active-main-tab="activeMainTab"
    :sub-tabs="subTabsAtivas"
    :active-sub-tab="activeSubTab"
  >

    <div v-if="activeMainTab === 'produtos'">
      <div>
        <div v-if="activeProdutosTab === 'produtos_tabelas'">
          <div class="flex space-x-2 mb-3">
            <ButtonCustom icon="bx-plus" text="Cadastrar produto" :url="`/${empresaId}/produtos/create`" :outline="false" />
            <ButtonCustom icon="bx-import" text="Importar produtos" :url="`/${empresaId}/produtos/importar`" :outline="true" />
          </div>

          <div class="text-gray-800 dark:text-gray-50">
            <DataTable :columns="columns" :data="produtosNormalizados">
              <template #cell-nome="{ row }">
                <Link :href="`/${empresaId}/produtos/${row.id}/edit`" class="font-medium text-indigo-700 hover:underline">
                  {{ row.nome }}
                </Link>
              </template>

              <template #cell-fotos="{ row }">
                <div v-if="row?.imagens?.[0]">
                  <img :src="row.imagens[0].imagem_base64" class="border-2 border-white rounded-xl shadow-sm w-10" />
                </div>
              </template>

              <template v-for="tabela in tabelasPrecos" :key="tabela.id" #[`cell-tabela_${tabela.id}`]="{ row }">
                <span class="font-medium text-green-600">
                  {{ formatCurrency(row[`tabela_${tabela.id}`]) }}
                </span>
              </template>
            </DataTable>
          </div>
        </div>

        <div v-else-if="activeProdutosTab === 'gerenciar_estoque'">
          <div class="rounded-lg border border-gray-200 bg-white p-5">
            <h3 class="text-lg font-semibold text-gray-800">Controle de estoque em pedidos</h3>
            <p class="mt-2 text-sm text-gray-600">
              Quando habilitado, o sistema valida saldo antes de salvar o pedido, informa indisponibilidade e baixa/estorna estoque automaticamente.
            </p>

            <label class="mt-4 inline-flex items-center gap-3 text-sm font-medium text-gray-700">
              <input v-model="estoqueGerenciado" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600" />
              Habilitar gerenciamento de estoque
            </label>

            <div class="mt-4">
              <button
                type="button"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-70"
                :disabled="salvandoEstoque"
                @click="salvarConfigEstoque"
              >
                {{ salvandoEstoque ? "Salvando..." : "Salvar configuracao" }}
              </button>
            </div>

            <p v-if="feedbackEstoque" class="mt-3 text-sm text-green-600">{{ feedbackEstoque }}</p>
            <p v-if="erroEstoque" class="mt-3 text-sm text-red-600">{{ erroEstoque }}</p>
          </div>

          <div class="mt-4 rounded-lg border border-gray-200 bg-white p-5">
            <h4 class="text-base font-semibold text-gray-800">Lancar movimentacao</h4>
            <div class="mt-3 grid gap-3 md:grid-cols-4">
              <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Produto</label>
                <input
                  v-model="buscaProdutoMovimento"
                  type="text"
                  placeholder="Digite codigo ou nome do produto"
                  class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                  @focus="mostrarSugestoesProduto = true"
                />
                <div v-if="mostrarSugestoesProduto && produtosSugeridosMovimento.length" class="mt-2 max-h-48 overflow-y-auto rounded-md border border-gray-200 bg-white">
                  <button
                    v-for="produto in produtosSugeridosMovimento"
                    :key="produto.id"
                    type="button"
                    class="block w-full border-b border-gray-100 px-3 py-2 text-left text-sm hover:bg-gray-50"
                    @click="selecionarProdutoMovimento(produto)"
                  >
                    {{ produto.codigo ? `${produto.codigo} - ` : "" }}{{ produto.nome }} (saldo: {{ formatarSaldo(produto.saldo_estoque) }})
                  </button>
                </div>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Tipo</label>
                <select v-model="movimentoForm.tipo" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                  <option value="entrada">Entrada</option>
                  <option value="saida">Saida</option>
                </select>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Quantidade</label>
                <input v-model="movimentoForm.quantidade" type="number" min="0.01" step="0.01" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
              </div>

              <div class="md:col-span-4">
                <label class="mb-1 block text-sm font-medium text-gray-700">Observacoes</label>
                <input v-model="movimentoForm.observacoes" type="text" maxlength="1000" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
              </div>
            </div>

            <div v-if="produtoSelecionadoMovimento" class="mt-3 text-sm text-gray-700">
              Produto selecionado: <strong>{{ produtoSelecionadoMovimento.codigo ? `${produtoSelecionadoMovimento.codigo} - ` : "" }}{{ produtoSelecionadoMovimento.nome }}</strong>
              | Saldo atual: <strong>{{ formatarSaldo(produtoSelecionadoMovimento.saldo_estoque) }}</strong>
            </div>

            <div class="mt-4">
              <button
                type="button"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-70"
                :disabled="salvandoMovimentoEstoque"
                @click="salvarMovimentoEstoque"
              >
                {{ salvandoMovimentoEstoque ? "Lancando..." : "Lancar movimentacao" }}
              </button>
            </div>

            <p v-if="feedbackMovimentoEstoque" class="mt-3 text-sm text-green-600">{{ feedbackMovimentoEstoque }}</p>
            <p v-if="erroMovimentoEstoque" class="mt-3 text-sm text-red-600">{{ erroMovimentoEstoque }}</p>
          </div>

          <div class="mt-4 rounded-lg border border-gray-200 bg-white p-5">
            <h4 class="text-base font-semibold text-gray-800">Historico do produto selecionado</h4>
            <p class="mt-1 text-xs text-gray-500">Exibindo ate {{ limiteHistoricoGeral }} registros mais recentes da empresa.</p>
            <div class="mt-3">
              <DataTable :columns="colunasMovimento" :data="movimentosProdutoSelecionado" :enable-page-size="false">
                <template #cell-data="{ row }">{{ formatarDataHora(row.created_at) }}</template>
                <template #cell-produto="{ row }">{{ row.produto_codigo ? `${row.produto_codigo} - ` : "" }}{{ row.produto_nome || "-" }}</template>
                <template #cell-tipo="{ row }">
                  <span :class="row.tipo === 'entrada' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                    {{ row.tipo === "entrada" ? "Entrada" : "Saida" }}
                  </span>
                </template>
                <template #cell-quantidade="{ row }">{{ formatarSaldo(row.quantidade) }}</template>
                <template #cell-saldo_anterior="{ row }">{{ formatarSaldo(row.saldo_anterior) }}</template>
                <template #cell-saldo_atual="{ row }">{{ formatarSaldo(row.saldo_atual) }}</template>
              </DataTable>
            </div>
          </div>

          <div class="mt-4 rounded-lg border border-gray-200 bg-white p-5">
            <h4 class="text-base font-semibold text-gray-800">Historico geral de movimentacoes</h4>
            <p class="mt-1 text-xs text-gray-500">Exibindo ate {{ limiteHistoricoGeral }} registros mais recentes da empresa.</p>
            <div class="mt-3">
              <DataTable :columns="colunasMovimento" :data="movimentosEstoqueGeral" :enable-page-size="false">
                <template #cell-data="{ row }">{{ formatarDataHora(row.created_at) }}</template>
                <template #cell-produto="{ row }">{{ row.produto_codigo ? `${row.produto_codigo} - ` : "" }}{{ row.produto_nome || "-" }}</template>
                <template #cell-tipo="{ row }">
                  <span :class="row.tipo === 'entrada' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                    {{ row.tipo === "entrada" ? "Entrada" : "Saida" }}
                  </span>
                </template>
                <template #cell-quantidade="{ row }">{{ formatarSaldo(row.quantidade) }}</template>
                <template #cell-saldo_anterior="{ row }">{{ formatarSaldo(row.saldo_anterior) }}</template>
                <template #cell-saldo_atual="{ row }">{{ formatarSaldo(row.saldo_atual) }}</template>
              </DataTable>
            </div>
          </div>
        </div>

        <div v-else-if="activeProdutosTab === 'importar_fotos'">
          <div
            class="relative w-full border-2 border-dashed border-gray-300 hover:border-indigo-500 rounded-xl bg-gray-50 dark:bg-gray-800 p-6 flex flex-col items-center justify-center text-center cursor-pointer transition"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            @click="!importandoFotos && triggerFileInput()"
          >
            <div
              v-if="isDragging"
              class="absolute inset-0 bg-indigo-50/70 dark:bg-indigo-500/10 rounded-xl border-2 border-indigo-400 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-medium"
            >
              Solte os arquivos aqui
            </div>

            <div class="pointer-events-none">
              <p class="text-indigo-600 font-medium mb-1">Arraste e solte suas fotos aqui</p>
              <p class="text-gray-600 dark:text-gray-400 text-sm">ou clique para selecionar arquivos do computador</p>
              <p class="text-xs mt-2 text-gray-500 dark:text-gray-500">
                Caso o nome do arquivo seja o codigo do produto, ele sera atribuido automaticamente.<br />
                Formatos aceitos: JPG, JPEG, PNG, GIF. Maximo: 2MB por imagem.<br />
                Dimensao recomendada: 800 x 800 pixels.
              </p>
            </div>

            <input ref="fileInput" type="file" multiple accept=".jpg,.jpeg,.png,.gif,.webp" class="hidden" @change="handleFiles" />
          </div>
          <p v-if="importandoFotos" class="mt-3 text-sm text-indigo-600">Importando fotos...</p>
          <p v-if="importFeedback" class="mt-3 text-sm text-green-600">{{ importFeedback }}</p>
          <p v-if="importErro" class="mt-3 text-sm text-red-600">{{ importErro }}</p>
        </div>
      </div>
    </div>

    <div v-else-if="activeMainTab === 'promocoes'">
      <div class="space-y-4">
        <div class="flex items-center justify-between gap-3">
          <h2 class="text-xl font-semibold text-gray-800">Promocoes</h2>
          <ButtonCustom icon="bx-plus" text="Nova promocao" :url="`/${empresaId}/produtos/promocoes/nova`" :outline="false" />
        </div>

        <div v-if="promocoes.length" class="rounded-lg border border-gray-200 bg-white p-4">
          <DataTable :columns="colunasPromocoes" :data="promocoes">
            <template #cell-nome="{ row }">
              <Link :href="`/${empresaId}/produtos/promocoes/${row.id}/editar`" class="font-medium text-indigo-700 hover:underline">
                {{ row.nome }}
              </Link>
            </template>
            <template #cell-data_inicio="{ row }">{{ formatarData(row.data_inicio) }}</template>
            <template #cell-data_fim="{ row }">{{ formatarData(row.data_fim) }}</template>
          </DataTable>
        </div>

        <div v-else class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
          Nenhuma promocao cadastrada ainda.
        </div>
      </div>
    </div>

    <div v-else-if="activeMainTab === 'destaques'">
      <div class="space-y-4">
        <div class="flex items-center justify-between gap-3">
          <h2 class="text-xl font-semibold text-gray-800">Destaques</h2>
          <ButtonCustom icon="bx-plus" text="Novo destaque" :url="`/${empresaId}/produtos/destaques/novo`" :outline="false" />
        </div>

        <div v-if="destaques.length" class="rounded-lg border border-gray-200 bg-white p-4">
          <DataTable :columns="colunasDestaques" :data="destaques">
            <template #cell-nome="{ row }">
              <Link :href="`/${empresaId}/produtos/destaques/${row.id}/editar`" class="font-medium text-indigo-700 hover:underline">
                {{ row.nome }}
              </Link>
            </template>
            <template #cell-data_inicio="{ row }">{{ formatarData(row.data_inicio) }}</template>
            <template #cell-data_fim="{ row }">{{ formatarData(row.data_fim) }}</template>
          </DataTable>
        </div>

        <div v-else class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
          Nenhum destaque cadastrado ainda.
        </div>
      </div>
    </div>

    <div v-else-if="activeMainTab === 'configuracoes'">
      <div>
        <div v-if="activeConfigTab === 'categorias'">
          <ButtonCustom icon="bx-plus" text="Nova categoria" :url="`/${empresaId}/produtos/create`" :outline="false" />
        </div>

        <div v-else-if="activeConfigTab === 'variacoes'">
          <ButtonCustom icon="bx-plus" text="Nova variacao" :url="`/${empresaId}/produtos/create`" :outline="false" />
        </div>

        <div v-else-if="activeConfigTab === 'inatividade'">
          <ButtonCustom icon="bx-edit" text="Alterar periodo de inatividade" :url="`/${empresaId}/produtos/create`" :outline="false" />
        </div>

        <div v-else-if="activeConfigTab === 'tributacoes'">
          <ButtonCustom icon="bx-plus" text="Nova regra de calculo" :url="`/${empresaId}/produtos/create`" :outline="false" />
        </div>
      </div>
    </div>
  </ProdutosPageShell>
</template>

<script setup>
import { computed, ref } from "vue";
import ButtonCustom from "@/components/ButtonCustom.vue";
import DataTable from "@/components/DataTable.vue";
import { usePage, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { formatCurrency } from "@/lib/utils";
import ProdutosPageShell from "@/pages/Produtos/components/ProdutosPageShell.vue";

const page = usePage();
const fileInput = ref(null);
const isDragging = ref(false);
const importandoFotos = ref(false);
const importFeedback = ref("");
const importErro = ref("");
const estoqueGerenciado = ref(Boolean(page.props.configuracoes_gerais?.gerenciar_estoque));
const salvandoEstoque = ref(false);
const feedbackEstoque = ref("");
const erroEstoque = ref("");
const salvandoMovimentoEstoque = ref(false);
const feedbackMovimentoEstoque = ref("");
const erroMovimentoEstoque = ref("");
const buscaProdutoMovimento = ref("");
const mostrarSugestoesProduto = ref(false);
const movimentoForm = ref({
  produto_id: "",
  tipo: "entrada",
  quantidade: "",
  observacoes: "",
});

defineOptions({ layout: AppLayout });

const empresaId = Number(page.props.empresa_selecionada || 0);
const initialMainTab = String(page.props.active_tab || "produtos");
const initialSubTab = String(page.props.active_sub_tab || "produtos_tabelas");

const produtos = computed(() => (Array.isArray(page.props.produtos) ? page.props.produtos : []));
const promocoes = computed(() => (Array.isArray(page.props.promocoes) ? page.props.promocoes : []));
const destaques = computed(() => (Array.isArray(page.props.destaques) ? page.props.destaques : []));
const tabelasPrecos = computed(() => (Array.isArray(page.props.tabelas_precos) ? page.props.tabelas_precos : []));
const movimentosEstoqueGeral = computed(() => (Array.isArray(page.props.movimentos_estoque_geral) ? page.props.movimentos_estoque_geral : []));
const limiteHistoricoGeral = computed(() => Number(page.props.estoque_limites?.historico_geral || 300));
const produtosEstoque = computed(() =>
  [...produtos.value]
    .map((produto) => ({
      id: Number(produto.id),
      nome: produto.nome,
      codigo: produto.codigo,
      saldo_estoque: Number(produto.saldo_estoque || 0),
    }))
    .sort((a, b) => String(a.nome || "").localeCompare(String(b.nome || ""), "pt-BR"))
);
const produtoSelecionadoMovimento = computed(() =>
  produtosEstoque.value.find((produto) => produto.id === Number(movimentoForm.value.produto_id)) || null
);
const produtosSugeridosMovimento = computed(() => {
  const termo = buscaProdutoMovimento.value.trim().toLowerCase();
  if (!termo) return [];

  return produtosEstoque.value
    .filter((produto) => {
      const nome = String(produto.nome || "").toLowerCase();
      const codigo = String(produto.codigo || "").toLowerCase();
      return nome.includes(termo) || codigo.includes(termo);
    })
    .slice(0, 20);
});

const colunasMovimento = [
  { label: "Data", key: "data", sortable: false },
  { label: "Produto", key: "produto", sortable: false },
  { label: "Tipo", key: "tipo", sortable: false },
  { label: "Qtde", key: "quantidade", sortable: false },
  { label: "Saldo Antes", key: "saldo_anterior", sortable: false },
  { label: "Saldo Depois", key: "saldo_atual", sortable: false },
  { label: "Usuario", key: "usuario_nome", sortable: false },
  { label: "Obs", key: "observacoes", sortable: false },
];
const colunasPromocoes = [
  { label: "Nome da promocao", key: "nome" },
  { label: "Inicio", key: "data_inicio" },
  { label: "Termino", key: "data_fim" },
  { label: "Situacao", key: "situacao" },
];
const colunasDestaques = [
  { label: "Destaque no catalogo", key: "nome" },
  { label: "Inicio", key: "data_inicio" },
  { label: "Termino", key: "data_fim" },
  { label: "Situacao", key: "situacao" },
];

const movimentosProdutoSelecionado = computed(() => {
  if (!movimentoForm.value.produto_id) return [];
  const produtoId = Number(movimentoForm.value.produto_id);
  return movimentosEstoqueGeral.value.filter((mov) => Number(mov.produto_id) === produtoId);
});

const mainTabs = [
  { id: "produtos", label: "Produtos", icon: "bx bx-box", url: `/${empresaId}/produtos/tabelas` },
  { id: "promocoes", label: "Promoções", icon: "bx bx-badge", url: `/${empresaId}/produtos/promocoes` },
  { id: "destaques", label: "Destaques", icon: "bx bx-star", url: `/${empresaId}/produtos/destaques` },
  { id: "configuracoes", label: "Configurações", icon: "bx bx-cog", url: `/${empresaId}/produtos/configuracoes/categorias` },
];
const activeMainTab = initialMainTab;

const produtosTabs = [
  { id: "produtos_tabelas", label: "Produtos e Tabelas", icon: "bx bx-list-ul", url: `/${empresaId}/produtos/tabelas` },
  { id: "gerenciar_estoque", label: "Gerenciar Estoque", icon: "bx bx-store", url: `/${empresaId}/produtos/gerenciar_estoque` },
  { id: "importar_fotos", label: "Importar Fotos", icon: "bx bx-image-add", url: `/${empresaId}/produtos/importar_fotos` },
];
const activeProdutosTab = initialMainTab === "produtos" ? initialSubTab : "produtos_tabelas";

const configTabs = [
  { id: "categorias", label: "Categorias", icon: "bx bx-category", url: `/${empresaId}/produtos/configuracoes/categorias` },
  { id: "variacoes", label: "Variações de Produto", icon: "bx bx-transfer", url: `/${empresaId}/produtos/configuracoes/variacoes` },
  { id: "inatividade", label: "Período de Inatividade", icon: "bx bx-time", url: `/${empresaId}/produtos/configuracoes/inatividade` },
  { id: "tributacoes", label: "Tributações", icon: "bx bx-receipt", url: `/${empresaId}/produtos/configuracoes/tributacoes` },
];
const activeConfigTab = initialMainTab === "configuracoes" ? initialSubTab : "categorias";
const subTabsAtivas = computed(() => {
  if (activeMainTab === "produtos") return produtosTabs;
  if (activeMainTab === "configuracoes") return configTabs;
  return [];
});
const activeSubTab = computed(() => {
  if (activeMainTab === "produtos") return activeProdutosTab;
  if (activeMainTab === "configuracoes") return activeConfigTab;
  return "";
});

const columns = computed(() => [
  { label: "Fotos", key: "fotos" },
  { label: "Código", key: "codigo" },
  { label: "Nome", key: "nome" },
  { label: "Variações", key: "variacoes" },
  { label: "IPI", key: "ipi" },
  { label: "Unidade", key: "unidade" },
  { label: "Comissão", key: "comissao" },
  { label: "Preço Mínimo", key: "preco_minimo" },
  { label: "Preço Tabela", key: "preco_tabela" },
  ...tabelasPrecos.value.map((tabela) => ({
    label: tabela.nome || `Tabela ${tabela.id}`,
    key: `tabela_${tabela.id}`,
  })),
]);

const produtosNormalizados = computed(() =>
  produtos.value.map((produto) => {
    const precosObj = {};

    tabelasPrecos.value.forEach((tabela) => {
      precosObj[`tabela_${tabela.id}`] = null;
    });

    (produto.precos || []).forEach((p) => {
      precosObj[`tabela_${p.tabela_id}`] = p.preco;
    });

    return { ...produto, ...precosObj };
  })
);

function triggerFileInput() {
  fileInput.value?.click();
}

function handleFiles(event) {
  const files = Array.from(event.target.files || []);
  event.target.value = "";
  enviarFotos(files);
}

function handleDrop(event) {
  isDragging.value = false;
  const files = Array.from(event.dataTransfer.files || []);
  enviarFotos(files);
}

function enviarLoteFotos(filesLote) {
  return new Promise((resolve) => {
    router.post(`/${empresaId}/produtos/importar-fotos`, {
      files: filesLote,
    }, {
      preserveScroll: true,
      preserveState: true,
      forceFormData: true,
      onSuccess: () => resolve({ ok: true }),
      onError: (errors) => {
        const mensagens = Object.values(errors || {}).flat().filter(Boolean);
        resolve({ ok: false, error: mensagens[0] || "Nao foi possivel importar este lote de fotos." });
      },
    });
  });
}

async function enviarFotos(files) {
  if (!files.length || importandoFotos.value) return;

  importFeedback.value = "";
  importErro.value = "";
  importandoFotos.value = true;

  const maxArquivosPorLote = 3;
  const maxBytesPorLote = 8 * 1024 * 1024;
  const lotes = [];
  let loteAtual = [];
  let bytesLoteAtual = 0;

  files.forEach((file) => {
    const fileSize = Number(file?.size || 0);
    const excedeQuantidade = loteAtual.length >= maxArquivosPorLote;
    const excedeTamanho = loteAtual.length > 0 && (bytesLoteAtual + fileSize) > maxBytesPorLote;

    if (excedeQuantidade || excedeTamanho) {
      lotes.push(loteAtual);
      loteAtual = [];
      bytesLoteAtual = 0;
    }

    loteAtual.push(file);
    bytesLoteAtual += fileSize;
  });

  if (loteAtual.length > 0) {
    lotes.push(loteAtual);
  }

  let lotesComErro = 0;
  let primeiraMensagemErro = "";

  for (let i = 0; i < lotes.length; i += 1) {
    importFeedback.value = `Importando lote ${i + 1} de ${lotes.length}...`;
    const resultado = await enviarLoteFotos(lotes[i]);
    if (!resultado.ok) {
      lotesComErro += 1;
      if (!primeiraMensagemErro) primeiraMensagemErro = resultado.error || "";
    }
  }

  if (lotesComErro > 0) {
    importErro.value = primeiraMensagemErro || `Falha em ${lotesComErro} lote(s).`;
    importFeedback.value = `Importacao concluida com ${lotesComErro} lote(s) com erro.`;
  } else {
    importFeedback.value = "Importacao de fotos concluida.";
  }

  importandoFotos.value = false;
}

function salvarConfigEstoque() {
  feedbackEstoque.value = "";
  erroEstoque.value = "";

  router.post(`/${empresaId}/produtos/configuracoes/gerenciar_estoque`, {
    gerenciar_estoque: estoqueGerenciado.value,
  }, {
    preserveScroll: true,
    preserveState: true,
    onStart: () => {
      salvandoEstoque.value = true;
    },
    onSuccess: () => {
      feedbackEstoque.value = "Configuracao de estoque salva com sucesso.";
    },
    onError: (errors) => {
      const mensagens = Object.values(errors || {}).flat().filter(Boolean);
      erroEstoque.value = mensagens[0] || "Nao foi possivel salvar a configuracao.";
    },
    onFinish: () => {
      salvandoEstoque.value = false;
    },
  });
}

function formatarSaldo(valor) {
  const numero = Number(valor || 0);
  return Number.isFinite(numero)
    ? numero.toLocaleString("pt-BR", { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    : "0,00";
}

function formatarDataHora(valor) {
  if (!valor) return "-";
  try {
    return new Intl.DateTimeFormat("pt-BR", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    }).format(new Date(valor));
  } catch {
    return String(valor);
  }
}

function formatarData(valor) {
  if (!valor) return "-";
  try {
    return new Intl.DateTimeFormat("pt-BR", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    }).format(new Date(`${valor}T00:00:00`));
  } catch {
    return String(valor);
  }
}

function salvarMovimentoEstoque() {
  feedbackMovimentoEstoque.value = "";
  erroMovimentoEstoque.value = "";

  router.post(`/${empresaId}/produtos/estoque/movimentos`, {
    produto_id: movimentoForm.value.produto_id,
    tipo: movimentoForm.value.tipo,
    quantidade: movimentoForm.value.quantidade,
    observacoes: movimentoForm.value.observacoes,
  }, {
    preserveScroll: true,
    preserveState: true,
    onStart: () => {
      salvandoMovimentoEstoque.value = true;
    },
    onSuccess: () => {
      feedbackMovimentoEstoque.value = "Movimentacao registrada com sucesso.";
      movimentoForm.value.quantidade = "";
      movimentoForm.value.observacoes = "";
    },
    onError: (errors) => {
      const mensagens = Object.values(errors || {}).flat().filter(Boolean);
      erroMovimentoEstoque.value = mensagens[0] || "Nao foi possivel registrar a movimentacao.";
    },
    onFinish: () => {
      salvandoMovimentoEstoque.value = false;
    },
  });
}

function selecionarProdutoMovimento(produto) {
  movimentoForm.value.produto_id = produto.id;
  buscaProdutoMovimento.value = `${produto.codigo ? `${produto.codigo} - ` : ""}${produto.nome}`;
  mostrarSugestoesProduto.value = false;
}
</script>
