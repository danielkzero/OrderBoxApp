<template>
  <ProdutosPageShell :main-tabs="mainTabs" active-main-tab="produtos">
    <div class="rounded-sm border border-gray-200 bg-white">
      <div class="border-b border-gray-200 px-6 py-4">
        <h1 class="text-2xl font-semibold text-gray-700 uppercase">Importar produtos</h1>
      </div>

      <div class="p-6">
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
          <div class="mb-4 flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold uppercase text-gray-500">Passo 1</p>
              <h2 class="text-lg font-semibold text-gray-800">Envie as informacoes dos produtos</h2>
            </div>
            <div class="flex items-center gap-2 text-xs">
              <span class="step-badge step-badge-active">1</span>
              <span class="step-badge">2</span>
              <span class="step-badge">3</span>
              <span class="step-badge"><i class="bx bx-check"></i></span>
            </div>
          </div>

          <p class="mb-3 text-sm text-gray-700">Indique a finalidade desta importacao</p>
          <div class="grid gap-3 md:grid-cols-2">
            <button type="button" class="option-card" :class="{ active: form.modo === 'atualizar' }" @click="form.modo = 'atualizar'">
              <div class="option-title">Atualizar produtos</div>
              <p class="option-desc">Adiciona novos e atualiza os produtos que ja estao no sistema.</p>
            </button>
            <button type="button" class="option-card" :class="{ active: form.modo === 'substituir' }" @click="form.modo = 'substituir'">
              <div class="option-title">Substituir todos os produtos</div>
              <p class="option-desc">Substitui todos os produtos do sistema pelos da planilha.</p>
            </button>
          </div>

          <p class="mt-6 mb-3 text-sm text-gray-700">Envie a planilha modelo preenchida</p>
          <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-lg border border-green-500 bg-white p-5 text-center">
              <div class="mx-auto mb-3 inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">NOVO</div>
              <p class="text-sm text-gray-700">Use o modelo com colunas de NCM e precos de tabela.</p>
              <p class="mt-3 text-xs text-gray-500">
                Campos obrigatorios: Nome do produto e Preco de Tabela.
              </p>
            </div>

            <div
              class="rounded-lg border-2 border-dashed border-gray-300 bg-white p-6 text-center transition"
              :class="{ 'border-indigo-500 bg-indigo-50/40': isDragging }"
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="handleDrop"
            >
              <p class="mb-2 text-sm font-semibold text-indigo-700">Arraste e solte a planilha preenchida aqui</p>
              <p class="text-xs text-gray-500">ou</p>
              <button type="button" class="btn-primary mt-2" :disabled="form.processing" @click="abrirSeletorArquivo">
                Escolha um arquivo do computador
              </button>
              <input ref="inputArquivoRef" type="file" accept=".xlsx" class="hidden" @change="onSelecionarArquivo" />
              <p v-if="nomeArquivo" class="mt-3 text-xs text-gray-600">Arquivo selecionado: {{ nomeArquivo }}</p>
            </div>
          </div>

          <div class="mt-5 rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-600">
            <p class="font-semibold text-gray-700">Sobre produtos com variacoes</p>
            <p>
              O importador atualiza produtos simples com base na planilha.
              Para produtos com variacoes, mantenha o cadastro de variacoes no modulo de configuracoes.
            </p>
          </div>
        </div>
      </div>

      <div class="border-t border-gray-200 px-6 py-4 flex items-center justify-between gap-3">
        <div>
          <p v-if="flashSuccess" class="text-sm text-green-600">{{ flashSuccess }}</p>
          <p v-if="flashWarning" class="text-sm text-amber-600">{{ flashWarning }}</p>
          <div v-if="importErrors.length" class="mt-2 text-sm text-red-600">
            <p class="font-semibold">Erros encontrados:</p>
            <ul class="list-disc pl-5">
              <li v-for="(erro, idx) in importErrors" :key="`erro-${idx}`">{{ erro }}</li>
            </ul>
          </div>
          <p v-if="form.errors.arquivo" class="text-sm text-red-600">{{ form.errors.arquivo }}</p>
          <p v-if="form.errors.modo" class="text-sm text-red-600">{{ form.errors.modo }}</p>
        </div>
        <div class="flex gap-2">
          <button type="button" class="btn-primary" :disabled="form.processing || !form.arquivo" @click="enviar">
            {{ form.processing ? "Importando..." : "Importar planilha" }}
          </button>
          <Link :href="`/${empresaId}/produtos/tabelas`" class="btn-outline">Cancelar importacao</Link>
        </div>
      </div>
    </div>
  </ProdutosPageShell>
</template>

<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import ProdutosPageShell from "@/pages/Produtos/components/ProdutosPageShell.vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

defineOptions({ layout: AppLayout });

const page = usePage();
const empresaId = Number(page.props.empresa_id);
const inputArquivoRef = ref(null);
const isDragging = ref(false);
const nomeArquivo = ref("");

const form = useForm({
  modo: "atualizar",
  arquivo: null,
});

const flashSuccess = computed(() => page.props.flash?.success || "");
const flashWarning = computed(() => page.props.flash?.warning || "");
const importErrors = computed(() => (Array.isArray(page.props.flash?.import_errors) ? page.props.flash.import_errors : []));

const mainTabs = [
  { key: "produtos", label: "Produtos", icon: "bx bx-box", url: `/${empresaId}/produtos/tabelas` },
  { key: "promocoes", label: "Promocoes", icon: "bx bx-badge", url: `/${empresaId}/produtos/promocoes` },
  { key: "destaques", label: "Destaques", icon: "bx bx-star", url: `/${empresaId}/produtos/destaques` },
  { key: "configuracoes", label: "Configuracoes", icon: "bx bx-cog", url: `/${empresaId}/produtos/configuracoes/categorias` },
];

function abrirSeletorArquivo() {
  inputArquivoRef.value?.click();
}

function onSelecionarArquivo(event) {
  const arquivo = event.target.files?.[0] || null;
  setArquivo(arquivo);
  event.target.value = "";
}

function handleDrop(event) {
  isDragging.value = false;
  const arquivo = event.dataTransfer?.files?.[0] || null;
  setArquivo(arquivo);
}

function setArquivo(arquivo) {
  if (!arquivo) return;
  form.arquivo = arquivo;
  nomeArquivo.value = arquivo.name || "";
}

function enviar() {
  form.post(`/${empresaId}/produtos/importar`, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      form.arquivo = null;
      nomeArquivo.value = "";
    },
  });
}
</script>

<style scoped>
.step-badge {
  width: 30px;
  height: 30px;
  border-radius: 999px;
  border: 1px solid #d1d5db;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
  background: #fff;
}

.step-badge-active {
  border-color: var(--color-indigo-600);
  background: var(--color-indigo-600);
  color: #fff;
}

.option-card {
  border: 1px solid #d1d5db;
  border-radius: 8px;
  padding: 14px;
  text-align: left;
  background: #fff;
}

.option-card.active {
  border-color: var(--color-indigo-600);
  box-shadow: 0 0 0 1px var(--color-indigo-600) inset;
}

.option-title {
  font-weight: 700;
  color: #1f2937;
}

.option-desc {
  margin-top: 6px;
  font-size: 13px;
  color: #6b7280;
}

.btn-primary {
  background: var(--color-indigo-600);
  border: 1px solid var(--color-indigo-600);
  color: #fff;
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
</style>
