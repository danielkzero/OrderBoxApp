<template>
  <ProdutosPageShell :main-tabs="mainTabs" active-main-tab="destaques">
    <div class="rounded-sm border border-gray-200 bg-white">
      <div class="border-b border-gray-200 px-6 py-4">
        <h1 class="text-2xl font-semibold text-gray-700 uppercase">
          {{ isEdit ? "Alterar destaque" : "Novo destaque" }}
        </h1>
        <p class="mt-1 text-sm text-gray-500">
          Crie aqui um destaque para evidenciar produtos no catalogo.
        </p>
      </div>

      <div class="p-6 space-y-8">
        <section>
          <h2 class="text-3xl leading-8 text-gray-500 mb-4">CARACTERISTICAS</h2>
          <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 md:col-span-6">
              <label class="field-label">* Nome do destaque</label>
              <input v-model="form.nome" type="text" class="field-input" />
            </div>
            <div class="col-span-12 md:col-span-3">
              <label class="field-label">Inicio da exposicao</label>
              <input v-model="form.data_inicio" type="date" class="field-input" />
            </div>
            <div class="col-span-12 md:col-span-3">
              <label class="field-label">Termino da exposicao</label>
              <input v-model="form.data_fim" type="date" class="field-input" />
            </div>
          </div>
        </section>

        <section>
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-3xl leading-8 text-gray-500">PRODUTOS DO DESTAQUE</h2>
            <span class="rounded-full border border-gray-300 w-9 h-9 inline-flex items-center justify-center text-xl text-gray-700">
              {{ form.itens.length }}
            </span>
          </div>

          <div class="relative mb-4">
            <input
              v-model="buscaProduto"
              type="text"
              placeholder="Digite o codigo ou o nome do produto para adicionar a lista"
              class="field-input"
              @focus="mostrarSugestoes = true"
            />

            <div v-if="mostrarSugestoes && produtosFiltrados.length" class="absolute z-20 mt-1 w-full rounded-md border border-gray-200 bg-white shadow-lg max-h-64 overflow-y-auto">
              <button
                v-for="produto in produtosFiltrados"
                :key="produto.id"
                type="button"
                class="w-full border-b border-gray-100 px-3 py-2 text-left text-sm hover:bg-gray-50"
                @click="adicionarProduto(produto)"
              >
                {{ produto.codigo ? `${produto.codigo} - ` : "" }}{{ produto.nome }}
              </button>
            </div>
          </div>

          <div class="rounded-lg border border-gray-200 overflow-hidden">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-50 text-gray-700">
                <tr>
                  <th class="text-left px-3 py-2 w-40">Codigo</th>
                  <th class="text-left px-3 py-2">Descricao</th>
                  <th class="text-left px-3 py-2 w-16"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in form.itens" :key="item.produto_id" class="border-t border-gray-200">
                  <td class="px-3 py-2">{{ item.codigo || "--" }}</td>
                  <td class="px-3 py-2">{{ item.nome }}</td>
                  <td class="px-3 py-2">
                    <button type="button" class="btn-remove" @click="removerProduto(item.produto_id)">
                      <i class="bx bx-trash"></i>
                    </button>
                  </td>
                </tr>
                <tr v-if="!form.itens.length">
                  <td colspan="3" class="px-3 py-8 text-center text-gray-500">
                    Nenhum produto adicionado no destaque.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>

      <div class="border-t border-gray-200 px-6 py-4 flex items-center gap-3">
        <button type="button" class="btn-primary" :disabled="form.processing" @click="salvar">
          <i class="bx bx-check mr-1"></i>
          {{ form.processing ? "Salvando..." : "Salvar destaque" }}
        </button>
        <Link :href="`/${empresaId}/produtos/destaques`" class="btn-outline">Cancelar</Link>
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
const isEdit = Boolean(page.props.is_edit);
const destaque = page.props.destaque || null;
const produtos = Array.isArray(page.props.produtos) ? page.props.produtos : [];

const buscaProduto = ref("");
const mostrarSugestoes = ref(false);

const form = useForm({
  nome: destaque?.nome || "",
  data_inicio: destaque?.data_inicio || "",
  data_fim: destaque?.data_fim || "",
  ativo: destaque?.ativo ?? true,
  itens: Array.isArray(destaque?.itens)
    ? destaque.itens.map((item) => ({
        produto_id: Number(item.produto_id),
        codigo: item.codigo || "",
        nome: item.nome || "",
      }))
    : [],
});

const mainTabs = [
  { key: "produtos", label: "Produtos", icon: "bx bx-box", url: `/${empresaId}/produtos/tabelas` },
  { key: "promocoes", label: "Promocoes", icon: "bx bx-badge", url: `/${empresaId}/produtos/promocoes` },
  { key: "destaques", label: "Destaques", icon: "bx bx-star", url: `/${empresaId}/produtos/destaques` },
  { key: "configuracoes", label: "Configuracoes", icon: "bx bx-cog", url: `/${empresaId}/produtos/configuracoes/categorias` },
];

const produtosFiltrados = computed(() => {
  const termo = buscaProduto.value.trim().toLowerCase();
  if (!termo) return [];

  const idsJaSelecionados = new Set(form.itens.map((item) => Number(item.produto_id)));
  return produtos
    .filter((produto) => {
      if (idsJaSelecionados.has(Number(produto.id))) return false;
      const nome = String(produto.nome || "").toLowerCase();
      const codigo = String(produto.codigo || "").toLowerCase();
      return nome.includes(termo) || codigo.includes(termo);
    })
    .slice(0, 20);
});

function adicionarProduto(produto) {
  form.itens.push({
    produto_id: Number(produto.id),
    codigo: produto.codigo || "",
    nome: produto.nome || "",
  });
  buscaProduto.value = "";
  mostrarSugestoes.value = false;
}

function removerProduto(produtoId) {
  form.itens = form.itens.filter((item) => Number(item.produto_id) !== Number(produtoId));
}

function salvar() {
  const payload = {
    nome: form.nome,
    data_inicio: form.data_inicio || null,
    data_fim: form.data_fim || null,
    ativo: form.ativo,
    itens: form.itens.map((item) => ({
      produto_id: Number(item.produto_id),
    })),
  };

  if (isEdit && destaque?.id) {
    form.transform(() => payload).put(`/${empresaId}/produtos/destaques/${destaque.id}`);
    return;
  }

  form.transform(() => payload).post(`/${empresaId}/produtos/destaques`);
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
  border-radius: 6px;
  padding: 9px 10px;
  font-size: 14px;
  color: #111827;
  background: #fff;
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

.btn-outline {
  border: 1px solid #d1d5db;
  color: var(--color-indigo-600);
  padding: 9px 14px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  background: #fff;
}

.btn-remove {
  border: 1px solid #fecaca;
  color: #dc2626;
  background: #fff;
  width: 32px;
  height: 32px;
  border-radius: 8px;
}
</style>
