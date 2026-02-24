<template>
  <ClientesPageShell :empresa-id="empresaId" active-main-tab="clientes">
    <div class="border-b border-gray-200 py-4 flex items-center justify-between">
      <h1 class="text-xl font-semibold text-gray-800">Vinculos e permissoes</h1>
    </div>

    <div class="py-6">
      <p class="text-sm text-gray-600 mb-6">
        Utilize vinculos e permissoes para configurar quais clientes tem acesso as tabelas de precos, categorias,
        tags e condicoes de pagamento.
      </p>

      <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
        <h2 class="text-3xl leading-8 text-gray-400">CLIENTES</h2>
        <div class="flex items-center gap-2">
          <button type="button" class="btn-outline" @click="openFilter = true">
            <i class="bx bx-slider-alt mr-1"></i>
            Filtrar clientes
          </button>
          <button type="button" class="btn-primary" :disabled="selectedClienteIds.length === 0" @click="openEditor = true">
            Editar Vinculos e permissoes
          </button>
        </div>
      </div>

      <div class="overflow-auto border border-gray-200 rounded-lg">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-700">
            <tr>
              <th class="text-left px-3 py-2 w-10">
                <input type="checkbox" :checked="allClientesSelecionados" @change="toggleSelecionarTodosClientes" />
              </th>
              <th class="text-left px-3 py-2">Razao social</th>
              <th class="text-left px-3 py-2">Cidade</th>
              <th class="text-left px-3 py-2">Estado</th>
              <th class="text-left px-3 py-2">Tags</th>
              <th class="text-left px-3 py-2">Tabelas de preco</th>
              <th class="text-left px-3 py-2">Condicoes de pagamento</th>
              <th class="text-left px-3 py-2">Categorias</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="cliente in clientes" :key="cliente.id" class="border-t border-gray-200">
              <td class="px-3 py-2">
                <input type="checkbox" :checked="selectedClienteIds.includes(cliente.id)" @change="toggleCliente(cliente.id)" />
              </td>
              <td class="px-3 py-2">
                <Link :href="`/${empresaId}/clientes/${cliente.id}/show`" class="link-cliente">
                  {{ cliente.razao_social }}
                </Link>
              </td>
              <td class="px-3 py-2">{{ cliente.cidade || "--" }}</td>
              <td class="px-3 py-2">{{ cliente.estado || "--" }}</td>
              <td class="px-3 py-2">{{ formatarLista(cliente.tags) }}</td>
              <td class="px-3 py-2">{{ formatarLista(cliente.tabelas_preco) }}</td>
              <td class="px-3 py-2">{{ formatarLista(cliente.condicoes_pagamento) }}</td>
              <td class="px-3 py-2">{{ formatarLista(cliente.categorias) }}</td>
            </tr>
            <tr v-if="!clientes.length">
              <td colspan="8" class="text-center text-gray-500 py-10">Nenhum cliente encontrado para os filtros informados.</td>
            </tr>
          </tbody>
        </table>
      </div>
      <p class="text-sm text-gray-600 mt-2">{{ selectedClienteIds.length }} de {{ clientes.length }} clientes selecionados</p>
    </div>

    <div v-if="openEditor" class="fixed inset-0 z-50 flex justify-end">
      <div class="absolute inset-0 bg-black/30" @click="openEditor = false"></div>
      <aside class="relative w-full max-w-md bg-white h-full shadow-xl border-l border-gray-200 p-4 overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
          <h3 class="text-2xl leading-8 text-gray-800">Editar vinculos e permissoes</h3>
          <button type="button" class="text-indigo-600 text-2xl" @click="openEditor = false">&times;</button>
        </div>

        <label class="field-label">Tipo do vinculo</label>
        <select v-model="editor.tipo_vinculo" class="field-input">
          <option value="categorias">Categorias</option>
          <option value="condicoes_pagamento">Condicoes de pagamento</option>
          <option value="tabelas_preco">Tabelas de preco</option>
          <option value="tags">Tags</option>
        </select>

        <div class="mt-4">
          <label class="field-label">{{ tituloTipoSelecionado }}</label>
          <input v-model="editor.busca" type="text" class="field-input" placeholder="Buscar..." />
        </div>

        <div class="mt-4 border border-gray-200 rounded-lg max-h-[55vh] overflow-auto">
          <label class="option-row border-b border-gray-200">
            <input type="checkbox" :checked="allOpcoesSelecionadas" @change="toggleSelecionarTodasOpcoes" />
            <span class="font-semibold">Marcar Todos</span>
          </label>
          <label v-for="opcao in opcoesFiltradas" :key="opcao.id" class="option-row border-b border-gray-100">
            <input type="checkbox" :checked="editor.item_ids.includes(opcao.id)" @change="toggleOpcao(opcao.id)" />
            <span>{{ opcao.nome }}</span>
          </label>
          <p v-if="!opcoesFiltradas.length" class="p-4 text-sm text-gray-500">Nao foram encontrados registros para sua busca.</p>
        </div>

        <div class="sticky bottom-0 bg-white pt-4 mt-4 border-t border-gray-200 grid grid-cols-3 gap-2">
          <button type="button" class="btn-primary" :disabled="processingVinculos" @click="salvarVinculos('adicionar')">Adicionar</button>
          <button type="button" class="btn-primary" :disabled="processingVinculos" @click="salvarVinculos('substituir')">Substituir</button>
          <button type="button" class="btn-danger-soft" :disabled="processingVinculos" @click="salvarVinculos('remover')">Remover</button>
        </div>
      </aside>
    </div>

    <div v-if="openFilter" class="fixed inset-0 z-50 flex justify-end">
      <div class="absolute inset-0 bg-black/30" @click="openFilter = false"></div>
      <aside class="relative w-full max-w-xs bg-white h-full shadow-xl border-l border-gray-200 p-4 overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
          <h3 class="text-3xl leading-8 text-gray-800">Filtrar Clientes</h3>
          <button type="button" class="text-indigo-600 text-2xl" @click="openFilter = false">&times;</button>
        </div>

        <div class="space-y-3">
          <label class="field-label">Nome ou CNPJ</label>
          <input v-model="form.nome_cnpj" type="text" class="field-input" />

          <label class="field-label">Cidade</label>
          <input v-model="form.cidade" type="text" class="field-input" />

          <label class="field-label">Email</label>
          <input v-model="form.email" type="text" class="field-input" />

          <hr class="my-3" />

          <select v-model="form.estado" class="field-input">
            <option value="">Todos os estados</option>
            <option v-for="estado in estados" :key="estado.codigo" :value="estado.codigo">{{ estado.nome }}</option>
          </select>

          <select v-model="form.vendedor_id" class="field-input">
            <option value="">Todos os vendedores</option>
            <option v-for="vendedor in vendedores" :key="vendedor.id" :value="vendedor.id">{{ vendedor.nome }}</option>
          </select>

          <select v-model="form.representada_id" class="field-input">
            <option value="">Todas as representadas</option>
            <option v-for="representada in representadas" :key="representada.id" :value="representada.id">{{ representada.nome }}</option>
          </select>

          <select v-model="form.segmento_id" class="field-input">
            <option value="">Todos os segmentos de cliente</option>
            <option v-for="segmento in segmentos" :key="segmento.id" :value="segmento.id">{{ segmento.nome }}</option>
          </select>

          <select v-model="form.rede_id" class="field-input">
            <option value="">Todas as redes de clientes</option>
            <option v-for="rede in redes" :key="rede.id" :value="rede.id">{{ rede.nome }}</option>
          </select>

          <select v-model="form.tag_id" class="field-input">
            <option value="">Todas as tags de cliente</option>
            <option v-for="tag in tags" :key="tag.id" :value="tag.id">{{ tag.nome }}</option>
          </select>
        </div>

        <div class="sticky bottom-0 bg-white pt-4 mt-4 border-t border-gray-200">
          <button type="button" class="btn-primary w-full" @click="aplicarFiltro">Filtrar</button>
        </div>
      </aside>
    </div>

  </ClientesPageShell>
</template>

<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import ClientesPageShell from "@/pages/Clientes/components/ClientesPageShell.vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed, reactive, ref, watch } from "vue";

defineOptions({ layout: AppLayout });

const page = usePage();
const empresaId = Number(page.props.empresa_id);

const clientes = page.props.clientes || [];
const estados = page.props.estados || [];
const vendedores = page.props.vendedores || [];
const representadas = page.props.representadas || [];
const segmentos = page.props.segmentos || [];
const redes = page.props.redes || [];
const tags = page.props.tags || [];
const categorias = page.props.categorias || [];
const condicoesPagamentos = page.props.condicoes_pagamentos || [];
const tabelasPrecos = page.props.tabelas_precos || [];

const openFilter = ref(false);
const openEditor = ref(false);
const processingVinculos = ref(false);
const selectedClienteIds = ref([]);

const form = reactive({
  nome_cnpj: page.props.filtros?.nome_cnpj || "",
  cidade: page.props.filtros?.cidade || "",
  email: page.props.filtros?.email || "",
  estado: page.props.filtros?.estado || "",
  vendedor_id: page.props.filtros?.vendedor_id || "",
  representada_id: page.props.filtros?.representada_id || "",
  segmento_id: page.props.filtros?.segmento_id || "",
  rede_id: page.props.filtros?.rede_id || "",
  tag_id: page.props.filtros?.tag_id || "",
});

const editor = reactive({
  tipo_vinculo: "categorias",
  busca: "",
  item_ids: [],
});

const opcoesPorTipo = computed(() => ({
  categorias,
  condicoes_pagamento: condicoesPagamentos,
  tabelas_preco: tabelasPrecos,
  tags,
}));

const opcoesAtuais = computed(() => opcoesPorTipo.value[editor.tipo_vinculo] || []);

const opcoesFiltradas = computed(() => {
  const termo = editor.busca.trim().toLowerCase();
  if (!termo) return opcoesAtuais.value;
  return opcoesAtuais.value.filter((item) => String(item.nome || "").toLowerCase().includes(termo));
});

const tituloTipoSelecionado = computed(() => {
  if (editor.tipo_vinculo === "categorias") return "Categorias";
  if (editor.tipo_vinculo === "condicoes_pagamento") return "Condicoes de pagamento";
  if (editor.tipo_vinculo === "tabelas_preco") return "Tabelas de preco";
  return "Tags";
});

const allClientesSelecionados = computed(() => clientes.length > 0 && selectedClienteIds.value.length === clientes.length);

const allOpcoesSelecionadas = computed(() => {
  if (!opcoesFiltradas.value.length) return false;
  return opcoesFiltradas.value.every((item) => editor.item_ids.includes(item.id));
});

watch(
  () => editor.tipo_vinculo,
  () => {
    editor.busca = "";
    editor.item_ids = [];
  }
);

function aplicarFiltro() {
  router.get(`/${empresaId}/clientes/vinculos-permissoes`, form, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    onSuccess: () => {
      openFilter.value = false;
    },
  });
}

function formatarLista(lista) {
  return Array.isArray(lista) && lista.length ? lista.join(", ") : "--";
}

function toggleCliente(clienteId) {
  const id = Number(clienteId);
  if (selectedClienteIds.value.includes(id)) {
    selectedClienteIds.value = selectedClienteIds.value.filter((item) => item !== id);
    return;
  }
  selectedClienteIds.value = [...selectedClienteIds.value, id];
}

function toggleSelecionarTodosClientes(event) {
  if (event.target.checked) {
    selectedClienteIds.value = clientes.map((cliente) => Number(cliente.id));
    return;
  }
  selectedClienteIds.value = [];
}

function toggleOpcao(opcaoId) {
  const id = Number(opcaoId);
  if (editor.item_ids.includes(id)) {
    editor.item_ids = editor.item_ids.filter((item) => item !== id);
    return;
  }
  editor.item_ids = [...editor.item_ids, id];
}

function toggleSelecionarTodasOpcoes(event) {
  const idsFiltrados = opcoesFiltradas.value.map((item) => Number(item.id));
  if (event.target.checked) {
    editor.item_ids = [...new Set([...editor.item_ids, ...idsFiltrados])];
    return;
  }
  editor.item_ids = editor.item_ids.filter((id) => !idsFiltrados.includes(id));
}

function salvarVinculos(acao) {
  if (!selectedClienteIds.value.length) return;

  router.post(`/${empresaId}/clientes/vinculos-permissoes`, {
    cliente_ids: selectedClienteIds.value,
    tipo_vinculo: editor.tipo_vinculo,
    acao,
    item_ids: editor.item_ids,
  }, {
    preserveScroll: true,
    preserveState: false,
    onStart: () => {
      processingVinculos.value = true;
    },
    onFinish: () => {
      processingVinculos.value = false;
    },
  });
}

</script>

<style scoped>
.field-label {
  display: block;
  font-size: 13px;
  color: #374151;
  margin-bottom: 4px;
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

.btn-primary:disabled {
  opacity: 0.6;
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

.btn-danger-soft {
  border: 1px solid #f3d0d0;
  color: #d46a6a;
  background: #fff;
  border-radius: 8px;
  font-weight: 700;
  font-size: 14px;
  padding: 9px 10px;
}

.option-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  font-size: 14px;
  color: #374151;
}

.link-cliente {
  color: var(--color-indigo-600);
  font-weight: 600;
  text-align: left;
}

.link-cliente:hover {
  text-decoration: underline;
}
</style>
