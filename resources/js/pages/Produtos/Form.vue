<template>
  <ProdutosPageShell
    :main-tabs="mainTabs"
    active-main-tab="produtos"
    :sub-tabs="subTabs"
    active-sub-tab="produtos_tabelas"
  >
  <div class="bg-white rounded-sm border border-gray-200">
    <div class="border-b border-gray-200 px-6 py-4">
      <h1 class="text-2xl font-semibold text-gray-700 uppercase">
        {{ isEdit ? 'Alterar produto' : 'Novo produto' }}
      </h1>
    </div>

    <div class="p-6">
      <div v-if="Object.keys(form.errors).length" class="border border-red-200 bg-red-50 text-red-700 rounded p-3 text-sm mb-5">
        <p class="font-semibold mb-1">Corrija os campos obrigatorios:</p>
        <ul class="list-disc list-inside">
          <li v-for="(erro, chave) in form.errors" :key="chave">{{ erro }}</li>
        </ul>
      </div>

      <div class="flex gap-4">
        <button
          type="button"
          class="w-14 h-14 rounded border border-gray-300 bg-gray-100 flex items-center justify-center text-gray-400 text-xl overflow-hidden"
          @click="abrirModalImagens"
        >
          <img v-if="imagemPreview" :src="imagemPreview" alt="Imagem do produto" class="w-full h-full object-cover" />
          <i v-else class="bx bx-image"></i>
        </button>

        <div class="flex-1 space-y-4">
          <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 md:col-span-9">
              <label class="field-label">* Nome</label>
              <input v-model="form.nome" type="text" class="field-input" />
            </div>
            <div class="col-span-12 md:col-span-3">
              <label class="field-label">Codigo</label>
              <input v-model="form.codigo" type="text" class="field-input" placeholder="SKU ou referencia" />
            </div>
          </div>

          <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 md:col-span-2">
              <label class="field-label">Unidade de medida</label>
              <input v-model="form.unidade" type="text" class="field-input" placeholder="Kg, Cx, Un..." />
            </div>

            <div class="col-span-12 md:col-span-2">
              <label class="field-label">Venda em multiplos de</label>
              <input v-model="form.multiplo" type="number" min="1" step="1" class="field-input text-right" />
            </div>

            <div class="col-span-12 md:col-span-8">
              <label class="field-label">Categoria</label>
              <select v-model="form.categoria_id" class="field-input">
                <option :value="null">Sem categoria</option>
                <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">{{ categoria.nome }}</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-7 border-b border-gray-200">
        <nav class="-mb-px flex gap-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            type="button"
            class="tab-btn"
            :class="activeTab === tab.id ? 'tab-btn-active' : 'tab-btn-inactive'"
            @click="activeTab = tab.id"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>

      <div class="pt-6 min-h-72">
        <section v-if="activeTab === 'tabelas'" class="space-y-5">
          <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 md:col-span-2">
              <label class="field-label">Moeda</label>
              <select v-model="form.moeda" class="field-input">
                <option value="R$">R$</option>
                <option value="$">$</option>
                <option value="EUR">EUR</option>
              </select>
            </div>
            <div class="col-span-12 md:col-span-2">
              <label class="field-label">Preco minimo</label>
              <input v-model="form.preco_minimo" type="number" min="0" step="0.01" class="field-input text-right" />
            </div>
          </div>

          <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 md:col-span-3">
              <label class="field-label">* Preco de tabela</label>
              <input v-model="form.preco_tabela" type="number" min="0" step="0.01" class="field-input text-right" />
            </div>
          </div>

          <div class="grid grid-cols-12 gap-4">
            <div v-for="tabela in tabelasPrecos" :key="tabela.id" class="col-span-12 sm:col-span-6 lg:col-span-3">
              <label class="field-label">{{ tabela.nome }}</label>
              <input
                v-model="form.precos_tabelas[tabela.id]"
                type="number"
                min="0"
                step="0.01"
                class="field-input text-right"
              />
            </div>
          </div>
        </section>

        <section v-if="activeTab === 'gerais'" class="space-y-6">
          <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 md:col-span-2">
              <label class="field-label">IPI</label>
              <input v-model="form.ipi" type="number" min="0" step="0.01" class="field-input text-right" />
            </div>

            <div class="col-span-12 md:col-span-1">
              <label class="field-label">Tipo</label>
              <select v-model="form.tipo_ipi" class="field-input">
                <option value="%">%</option>
                <option value="R$">R$</option>
              </select>
            </div>

            <div class="col-span-12 md:col-span-3">
              <label class="field-label">NCM</label>
              <input v-model="form.codigo_ncm" type="text" class="field-input" />
            </div>

            <div class="col-span-12 md:col-span-2">
              <label class="field-label">Comissao (%)</label>
              <input v-model="form.comissao" type="number" min="0" step="0.01" class="field-input text-right" />
            </div>
          </div>

          <div>
            <label class="field-label">Informacoes adicionais</label>
            <textarea v-model="form.observacoes" rows="7" class="field-input"></textarea>
          </div>
        </section>

        <section v-if="activeTab === 'variacoes'" class="space-y-4">
          <p class="text-sm text-gray-500">
            As variacoes sao gerenciadas em Configuracoes. Aqui voce define os dados basicos e de precos do produto.
          </p>

          <div class="flex flex-wrap gap-2">
            <span v-for="variacao in variacoes" :key="variacao.id" class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-700 border border-gray-200">
              {{ variacao.nome }}
            </span>
            <span v-if="!variacoes.length" class="text-sm text-gray-400">Nenhuma variacao cadastrada.</span>
          </div>

          <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input v-model="form.exibir_no_b2b" type="checkbox" class="text-indigo-600" />
            Exibir no B2B
          </label>
        </section>

        <section v-if="activeTab === 'peso'" class="space-y-5">
          <p class="text-sm text-gray-500">
            Selecione a melhor forma de cadastro do peso e dimensoes deste produto.
          </p>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button type="button" class="card-radio" :class="form.peso_dimensoes_unitario ? 'card-radio-active' : 'card-radio-inactive'" @click="form.peso_dimensoes_unitario = true">
              <div class="font-semibold text-gray-700">Peso e dimensoes unitarias</div>
              <div class="text-sm text-gray-500 mt-2">Considera o produto unitario.</div>
            </button>
            <button type="button" class="card-radio" :class="!form.peso_dimensoes_unitario ? 'card-radio-active' : 'card-radio-inactive'" @click="form.peso_dimensoes_unitario = false">
              <div class="font-semibold text-gray-700">Peso e dimensoes da caixa master</div>
              <div class="text-sm text-gray-500 mt-2">Considera os multiplos de venda.</div>
            </button>
          </div>

          <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 md:col-span-3">
              <label class="field-label">Peso bruto (kg)</label>
              <input v-model="form.peso_bruto" type="number" min="0" step="0.001" class="field-input text-right" />
            </div>
            <div class="col-span-12 md:col-span-3">
              <label class="field-label">Largura (cm)</label>
              <input v-model="form.largura" type="number" min="0" step="0.001" class="field-input text-right" />
            </div>
            <div class="col-span-12 md:col-span-3">
              <label class="field-label">Altura (cm)</label>
              <input v-model="form.altura" type="number" min="0" step="0.001" class="field-input text-right" />
            </div>
            <div class="col-span-12 md:col-span-3">
              <label class="field-label">Comprimento (cm)</label>
              <input v-model="form.comprimento" type="number" min="0" step="0.001" class="field-input text-right" />
            </div>
          </div>
        </section>
      </div>

      <div class="mt-6 flex items-center gap-3">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
          <input v-model="form.ativo" type="checkbox" class="text-indigo-600" />
          Produto ativo
        </label>
      </div>
    </div>

    <div class="border-t border-gray-200 px-6 py-4 flex gap-3">
      <button type="button" class="btn-primary" :disabled="form.processing" @click="salvar(false)">
        <i class="bx bx-check mr-1"></i> Salvar
      </button>
      <button type="button" class="btn-primary" :disabled="form.processing" @click="salvar(true)">
        Salvar e cadastrar outro
      </button>
      <Link :href="`/${empresaId}/produtos`" class="btn-outline">Cancelar</Link>
    </div>

    <div v-if="openImagensModal" class="modal-backdrop" @click.self="openImagensModal = false">
      <div class="modal-card modal-card-imagens">
        <div class="modal-header">
          <h3>Imagens do produto</h3>
          <button type="button" class="icon-btn" @click="openImagensModal = false"><i class="bx bx-x"></i></button>
        </div>

        <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
          <button type="button" class="btn-primary" :disabled="uploadingImagens || salvandoOrdenacao" @click="abrirSeletorGaleria">
            <i class="bx bx-plus mr-1"></i> Adicionar imagens
          </button>
          <div class="text-xs text-gray-500 flex items-center gap-3">
            <span>{{ imagensGaleria.length }} imagem(ns)</span>
            <span>JPG, JPEG, PNG, GIF ate 2MB</span>
          </div>
        </div>

        <div v-if="modalErro" class="mb-3 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
          {{ modalErro }}
        </div>

        <input
          ref="inputGaleriaRef"
          type="file"
          multiple
          accept=".jpg,.jpeg,.png,.gif"
          class="hidden"
          @change="onSelecionarImagens"
        />

        <div v-if="!imagensGaleria.length" class="text-sm text-gray-500 border border-dashed border-gray-300 rounded p-6 text-center">
          Nenhuma imagem adicionada.
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[60vh] overflow-y-auto pr-1">
          <div
            v-for="(img, index) in imagensGaleria"
            :key="img.uid"
            class="relative border rounded-lg bg-gray-50 overflow-hidden shadow-sm"
          >
            <div class="relative">
              <img :src="img.imagem_base64" alt="Imagem produto" class="w-full h-44 object-cover" />
              <span class="image-number">#{{ index + 1 }}</span>
              <span v-if="img.uploading" class="image-uploading">Enviando...</span>
            </div>
            <div class="p-2 border-t border-gray-200 bg-white">
              <div class="text-xs text-gray-600">Ordem: {{ index }}</div>
              <div class="text-xs text-gray-500">
                Criada em: {{ formatarDataImagem(img.created_at) }}
              </div>
              <div class="mt-2 flex items-center gap-2">
                <button
                  type="button"
                  class="icon-btn-sm"
                  :disabled="index === 0 || uploadingImagens || salvandoOrdenacao || !!img.pendente"
                  @click="moverImagem(index, -1)"
                  title="Mover para cima"
                >
                  <i class="bx bx-chevron-up"></i>
                </button>
                <button
                  type="button"
                  class="icon-btn-sm"
                  :disabled="index === imagensGaleria.length - 1 || uploadingImagens || salvandoOrdenacao || !!img.pendente"
                  @click="moverImagem(index, 1)"
                  title="Mover para baixo"
                >
                  <i class="bx bx-chevron-down"></i>
                </button>
              </div>
            </div>
            <button
              type="button"
              class="absolute top-1 right-1 w-7 h-7 rounded-full bg-white/90 border border-red-200 text-red-600 hover:bg-red-50"
              :disabled="uploadingImagens || removendoImagemId === img.id || salvandoOrdenacao"
              @click="removerImagem(img)"
            >
              <i class="bx bx-trash"></i>
            </button>
          </div>
        </div>

        <div class="modal-footer">
          <span v-if="isEdit" class="text-xs text-gray-500 mr-auto">A ordem e salva automaticamente.</span>
          <span v-if="!isEdit" class="text-xs text-gray-500 mr-auto">As imagens serao salvas ao clicar em Salvar.</span>
          <button type="button" class="btn-outline" @click="openImagensModal = false">Fechar</button>
        </div>
      </div>
    </div>
  </div>
  </ProdutosPageShell>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ProdutosPageShell from '@/pages/Produtos/components/ProdutosPageShell.vue';

defineOptions({ layout: AppLayout });

const page = usePage();
const empresaId = Number(page.props.empresa_id);
const isEdit = !!page.props.is_edit;
const produto = page.props.produto;
const categorias = page.props.categorias || [];
const tabelasPrecos = page.props.tabelas_precos || [];
const variacoes = page.props.variacoes || [];
const mainTabs = [
  { key: 'produtos', label: 'Produtos', icon: 'bx bx-box', url: `/${empresaId}/produtos/tabelas` },
  { key: 'promocoes', label: 'Promocoes', icon: 'bx bx-badge', url: `/${empresaId}/produtos/promocoes` },
  { key: 'destaques', label: 'Destaques', icon: 'bx bx-star', url: `/${empresaId}/produtos/destaques` },
  { key: 'configuracoes', label: 'Configuracoes', icon: 'bx bx-cog', url: `/${empresaId}/produtos/configuracoes/categorias` },
];
const subTabs = [
  { key: 'produtos_tabelas', label: 'Produtos e Tabelas', icon: 'bx bx-list-ul', url: `/${empresaId}/produtos/tabelas` },
  { key: 'gerenciar_estoque', label: 'Gerenciar Estoque', icon: 'bx bx-store', url: `/${empresaId}/produtos/gerenciar_estoque` },
  { key: 'importar_fotos', label: 'Importar Fotos', icon: 'bx bx-image-add', url: `/${empresaId}/produtos/importar_fotos` },
];
const inputGaleriaRef = ref(null);
const openImagensModal = ref(false);
const uploadingImagens = ref(false);
const salvandoOrdenacao = ref(false);
const removendoImagemId = ref(null);
const modalErro = ref('');
const imagensPendentes = ref([]);
const galeriaImagens = ref(Array.isArray(produto?.imagens) ? produto.imagens : []);

const tabs = [
  { id: 'tabelas', label: 'Tabelas de preco' },
  { id: 'gerais', label: 'Informacoes gerais' },
  { id: 'variacoes', label: 'Variacoes' },
  { id: 'peso', label: 'Peso e dimensoes' },
];

const activeTab = ref('tabelas');

const precosTabelasInicial = {};
for (const tabela of tabelasPrecos) {
  precosTabelasInicial[tabela.id] = produto?.precos_tabelas?.[tabela.id] ?? '';
}

const form = useForm({
  codigo: produto?.codigo || '',
  nome: produto?.nome || '',
  unidade: produto?.unidade || '',
  multiplo: produto?.multiplo || 1,
  categoria_id: produto?.categoria_id ?? null,
  moeda: produto?.moeda || 'R$',
  preco_tabela: produto?.preco_tabela ?? 0,
  preco_minimo: produto?.preco_minimo ?? 0,
  precos_tabelas: precosTabelasInicial,
  ipi: produto?.ipi ?? 0,
  tipo_ipi: produto?.tipo_ipi || '%',
  comissao: produto?.comissao ?? 0,
  codigo_ncm: produto?.codigo_ncm || '',
  observacoes: produto?.observacoes || '',
  peso_dimensoes_unitario: produto?.peso_dimensoes_unitario ?? true,
  peso_bruto: produto?.peso_bruto ?? '',
  largura: produto?.largura ?? '',
  altura: produto?.altura ?? '',
  comprimento: produto?.comprimento ?? '',
  ativo: produto?.ativo ?? true,
  exibir_no_b2b: produto?.exibir_no_b2b ?? false,
  imagem: null,
  imagens: [],
});

const imagemPreview = computed(() => {
  if (galeriaImagens.value.length) return galeriaImagens.value[0].imagem_base64;
  if (imagensPendentes.value.length) return imagensPendentes.value[0].imagem_base64;
  return '';
});

const imagensGaleria = computed(() => {
  const persistidas = [...galeriaImagens.value]
    .sort((a, b) => (Number(a.ordem) - Number(b.ordem)) || (Number(a.id) - Number(b.id)))
    .map((img) => ({
    ...img,
    uid: `persistida-${img.id}`,
  }));

  const pendentes = imagensPendentes.value.map((img, idx) => ({
    ...img,
    uid: `pendente-${idx}`,
    pendenteIndex: idx,
    id: null,
    pendente: true,
  }));

  return [...persistidas, ...pendentes];
});

function salvar(cadastrarOutro = false) {
  const onSuccess = () => {
    if (!cadastrarOutro) return;
    router.visit(`/${empresaId}/produtos/create`);
  };

  if (isEdit && produto?.id) {
    form
      .transform((data) => ({
        ...data,
        _method: 'put',
      }))
      .post(`/${empresaId}/produtos/${produto.id}`, {
      preserveScroll: true,
      forceFormData: true,
      onSuccess,
    });
    return;
  }

  form.post(`/${empresaId}/produtos`, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess,
  });
}

function abrirModalImagens() {
  modalErro.value = '';
  openImagensModal.value = true;
}

function abrirSeletorGaleria() {
  inputGaleriaRef.value?.click();
}

async function onSelecionarImagens(event) {
  const arquivos = Array.from(event.target.files || []);
  event.target.value = '';

  if (!arquivos.length) return;

  if (isEdit && produto?.id) {
    await uploadImagensEdicao(arquivos);
    return;
  }

  for (const arquivo of arquivos) {
    const preview = await arquivoParaBase64(arquivo);
    imagensPendentes.value.push({
      imagem_base64: preview,
      nome: arquivo.name,
    });
    form.imagens.push(arquivo);
  }
}

async function uploadImagensEdicao(arquivos) {
  modalErro.value = '';
  const previewsTemporarios = await Promise.all(
    arquivos.map(async (arquivo, idx) => ({
      uid: `uploading-${Date.now()}-${idx}`,
      imagem_base64: await arquivoParaBase64(arquivo),
      ordem: (galeriaImagens.value.length || 0) + idx + 1,
      created_at: null,
      uploading: true,
    }))
  );

  galeriaImagens.value = [...galeriaImagens.value, ...previewsTemporarios];
  uploadingImagens.value = true;

  try {
    const formData = new FormData();
    formData.append('_token', obterCsrfToken());
    arquivos.forEach((arquivo) => formData.append('imagens[]', arquivo));

    const resposta = await fetch(`/${empresaId}/produtos/${produto.id}/imagens`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': obterCsrfToken(),
        'X-XSRF-TOKEN': obterXsrfTokenCookie(),
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
      },
      credentials: 'same-origin',
      body: formData,
    });

    if (!resposta.ok) {
      throw new Error(await obterMensagemErroResposta(resposta, 'Falha ao enviar imagens'));
    }

    const json = await resposta.json();
    galeriaImagens.value = Array.isArray(json.imagens) ? json.imagens : galeriaImagens.value;
  } catch (e) {
    galeriaImagens.value = galeriaImagens.value.filter((img) => !img.uploading);
    modalErro.value = e?.message || 'Nao foi possivel enviar as imagens.';
  } finally {
    uploadingImagens.value = false;
  }
}

async function removerImagem(img) {
  modalErro.value = '';
  if (img.pendente) {
    const idx = Number(img.pendenteIndex);
    if (idx >= 0) imagensPendentes.value.splice(idx, 1);
    if (idx >= 0) form.imagens.splice(idx, 1);
    return;
  }

  if (!isEdit || !produto?.id || !img.id) return;

  removendoImagemId.value = img.id;

  try {
    const resposta = await fetch(`/${empresaId}/produtos/${produto.id}/imagens/${img.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': obterCsrfToken(),
        'X-XSRF-TOKEN': obterXsrfTokenCookie(),
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        _token: obterCsrfToken(),
      }),
    });

    if (!resposta.ok) {
      throw new Error(await obterMensagemErroResposta(resposta, 'Falha ao remover imagem'));
    }

    const json = await resposta.json();
    galeriaImagens.value = Array.isArray(json.imagens) ? json.imagens : galeriaImagens.value;
  } catch (e) {
    modalErro.value = e?.message || 'Nao foi possivel remover a imagem.';
  } finally {
    removendoImagemId.value = null;
  }
}

async function moverImagem(index, deslocamento) {
  const novoIndex = index + deslocamento;
  if (novoIndex < 0 || novoIndex >= imagensGaleria.value.length) return;

  if (imagensGaleria.value[index]?.pendente || imagensGaleria.value[novoIndex]?.pendente) {
    return;
  }

  const atual = [...galeriaImagens.value].sort((a, b) => (Number(a.ordem) - Number(b.ordem)) || (Number(a.id) - Number(b.id)));
  const [item] = atual.splice(index, 1);
  atual.splice(novoIndex, 0, item);

  galeriaImagens.value = atual.map((img, idx) => ({
    ...img,
    ordem: idx,
  }));

  if (isEdit && produto?.id) {
    await salvarOrdenacaoImagens();
  }
}

async function salvarOrdenacaoImagens() {
  modalErro.value = '';
  const payload = galeriaImagens.value
    .filter((img) => img.id)
    .map((img, idx) => ({ id: img.id, ordem: idx }));

  if (!payload.length) return;

  salvandoOrdenacao.value = true;

  try {
    const resposta = await fetch(`/${empresaId}/produtos/${produto.id}/imagens/ordenacao`, {
      method: 'PUT',
      headers: {
        'X-CSRF-TOKEN': obterCsrfToken(),
        'X-XSRF-TOKEN': obterXsrfTokenCookie(),
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        _token: obterCsrfToken(),
        imagens: payload,
      }),
    });

    if (!resposta.ok) {
      throw new Error(await obterMensagemErroResposta(resposta, 'Falha ao ordenar imagens'));
    }

    const json = await resposta.json();
    galeriaImagens.value = Array.isArray(json.imagens) ? json.imagens : galeriaImagens.value;
  } catch (e) {
    modalErro.value = e?.message || 'Nao foi possivel salvar a ordenacao.';
  } finally {
    salvandoOrdenacao.value = false;
  }
}

function arquivoParaBase64(arquivo) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = reject;
    reader.readAsDataURL(arquivo);
  });
}

function obterCsrfToken() {
  const meta = document.querySelector('meta[name=\"csrf-token\"]');
  return meta?.getAttribute('content') || '';
}

function obterXsrfTokenCookie() {
  const cookie = document.cookie
    .split('; ')
    .find((row) => row.startsWith('XSRF-TOKEN='));

  if (!cookie) return '';
  return decodeURIComponent(cookie.split('=')[1] || '');
}

function formatarDataImagem(valor) {
  if (!valor) return 'Aguardando upload';
  try {
    return new Intl.DateTimeFormat('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }).format(new Date(valor));
  } catch {
    return valor;
  }
}

async function obterMensagemErroResposta(resposta, prefixo) {
  try {
    const contentType = resposta.headers.get('content-type') || '';
    if (contentType.includes('application/json')) {
      const json = await resposta.json();
      const erroValidacao = json?.errors ? Object.values(json.errors).flat().join(' ') : null;
      const mensagem = erroValidacao || json?.message;
      if (mensagem) return `${mensagem} (${resposta.status})`;
    } else {
      const texto = (await resposta.text())?.trim();
      if (texto) return `${texto.slice(0, 180)} (${resposta.status})`;
    }
  } catch (_) {
    // ignora falha de parse e aplica fallback
  }

  return `${prefixo} (${resposta.status})`;
}
</script>

<style scoped>
.field-label {
  display: block;
  font-size: 13px;
  color: #4b5563;
  margin-bottom: 6px;
}

.field-input {
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 4px;
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

.tab-btn {
  padding: 8px 0;
  font-size: 14px;
  font-weight: 700;
  text-transform: uppercase;
  border-bottom: 2px solid transparent;
}

.tab-btn-active {
  color: var(--color-indigo-600);
  border-bottom-color: var(--color-indigo-600);
}

.tab-btn-inactive {
  color: #4b5563;
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

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
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

.card-radio {
  border-radius: 6px;
  border: 1px solid #d1d5db;
  text-align: left;
  padding: 14px;
}

.card-radio-active {
  border-color: var(--color-indigo-600);
  box-shadow: 0 0 0 1px var(--color-indigo-600) inset;
}

.card-radio-inactive {
  border-color: #d1d5db;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 60;
  background: rgba(15, 23, 42, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.modal-card {
  width: min(980px, 100%);
  background: #fff;
  border: 1px solid #c7d2fe;
  border-radius: 10px;
  padding: 16px;
}

.modal-card-imagens {
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 8px;
  margin-bottom: 12px;
}

.modal-header h3 {
  margin: 0;
  color: #111827;
  font-size: 20px;
  font-weight: 700;
}

.modal-footer {
  margin-top: 14px;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.icon-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #4b5563;
  background: #fff;
}

.icon-btn-sm {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #4b5563;
  background: #fff;
}

.icon-btn-sm:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.image-number {
  position: absolute;
  left: 8px;
  top: 8px;
  background: rgba(79, 70, 229, 0.9);
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  border-radius: 999px;
  padding: 2px 8px;
}

.image-uploading {
  position: absolute;
  right: 8px;
  bottom: 8px;
  background: rgba(17, 24, 39, 0.8);
  color: #fff;
  font-size: 10px;
  font-weight: 600;
  border-radius: 4px;
  padding: 2px 6px;
}
</style>
