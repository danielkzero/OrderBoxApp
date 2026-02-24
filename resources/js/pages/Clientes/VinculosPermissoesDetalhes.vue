<template>
  <ClientesPageShell :empresa-id="empresaId" active-main-tab="clientes">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
      <div class="lg:col-span-7 space-y-4">
        <div class="card p-0 overflow-hidden">
          <div class="px-5 py-5 border-b border-gray-200">
            <h1 class="text-3xl sm:text-5xl font-semibold text-gray-700 mb-4">{{ cliente.razao_social || "Cliente" }}</h1>
            <div class="flex flex-wrap gap-2 items-center">
              <Link :href="`/${empresaId}/clientes/${cliente.id}/edit`" class="btn-primary">
                <i class="bx bx-pencil mr-1"></i>
                Alterar
              </Link>
              <Link :href="`/${empresaId}/clientes/vinculos-permissoes`" class="btn-outline">
                <i class="bx bx-list-check mr-1"></i>
                Vinculos e Permissoes
              </Link>
              <div class="relative">
                <button type="button" class="btn-outline" @click="openMaisAcoes = !openMaisAcoes">
                  Mais opcoes
                  <i class="bx bx-chevron-down ml-1"></i>
                </button>
                <div v-if="openMaisAcoes" class="dropdown-acoes">
                  <button type="button" class="dropdown-item" @click="toggleBloqueioCliente">
                    <i class="bx bx-block mr-2"></i>
                    {{ clienteBloqueado ? "Desbloquear cliente" : "Bloquear cliente" }}
                  </button>
                  <button type="button" class="dropdown-item danger" @click="excluirCliente">
                    <i class="bx bx-trash mr-2"></i>
                    Excluir cliente
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="px-5 py-5 border-b border-gray-200 space-y-3 text-sm text-gray-700">
            <div v-if="cliente.telefone">
              <i class="bx bx-phone mr-2"></i>{{ cliente.telefone }}
            </div>
            <div v-if="cliente.email" class="text-indigo-700">
              <i class="bx bx-envelope mr-2"></i>{{ cliente.email }}
            </div>
            <div>
              <i class="bx bx-map mr-2"></i>{{ montarEndereco(cliente.endereco) || "--" }}
            </div>
          </div>

          <div class="px-5 py-5 border-b border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
              <div class="field-title">Razao social</div>
              <div>{{ cliente.razao_social || "--" }}</div>
            </div>
            <div>
              <div class="field-title">Nome fantasia</div>
              <div>{{ cliente.nome_fantasia || "--" }}</div>
            </div>
            <div>
              <div class="field-title">CNPJ</div>
              <div>{{ cliente.cnpj || "--" }}</div>
            </div>
            <div>
              <div class="field-title">Insc. Estadual</div>
              <div>{{ cliente.inscricao_estadual || "--" }}</div>
            </div>
          </div>

          <div class="px-5 py-5 border-b border-gray-200 text-sm">
            <div class="field-title">Excecoes fiscais</div>
            <div>{{ cliente.excecao_fiscal || "--" }}</div>
          </div>

          <div class="px-5 py-5 border-b border-gray-200 text-sm">
            <div class="field-title">Contatos</div>
            <div v-if="cliente.contatos?.length" class="space-y-2">
              <div v-for="contato in cliente.contatos" :key="`${contato.nome}-${contato.cargo || ''}`">
                <div class="font-semibold">{{ contato.nome }}</div>
                <div class="text-gray-600">{{ contato.cargo || "--" }}</div>
              </div>
            </div>
            <div v-else>Nenhum contato cadastrado</div>
          </div>

          <div class="px-5 py-5 border-b border-gray-200 text-sm">
            <div class="field-title">Vendedores</div>
            <div>{{ formatarLista(cliente.vendedores) }}</div>
          </div>

          <div class="px-5 py-5 border-b border-gray-200 text-sm">
            <div class="field-title">Tabelas de Preco</div>
            <div>{{ formatarLista(cliente.tabelas_preco) }}</div>
          </div>

          <div class="px-5 py-5 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
              <div class="field-title">Data do cadastro</div>
              <div>{{ cliente.cadastro?.data || "--" }}</div>
            </div>
            <div>
              <div class="field-title">Origem do cadastro</div>
              <div>Integracao</div>
            </div>
          </div>
        </div>

        <div class="card p-0 overflow-hidden">
          <div class="card-section-header">
            <span>Agenda</span>
            <button type="button" class="btn-primary" @click="openModalEvento = true">Criar evento</button>
          </div>
          <div class="p-4">
            <div class="flex items-center justify-between mb-3">
              <button type="button" class="month-nav" @click="voltarMes">
                <i class="bx bx-chevron-left"></i>
              </button>
              <div class="font-semibold text-gray-700">{{ mesCalendarioLabel }}</div>
              <button type="button" class="month-nav" @click="avancarMes">
                <i class="bx bx-chevron-right"></i>
              </button>
            </div>

            <div class="calendar-grid calendar-weekdays">
              <div v-for="dia in diasSemana" :key="dia">{{ dia }}</div>
            </div>
            <div class="calendar-grid">
              <div
                v-for="dia in diasCalendario"
                :key="dia.key"
                :class="['calendar-cell', { muted: !dia.noMesAtual }]"
              >
                <div class="calendar-day">{{ dia.numero }}</div>
                <button
                  v-for="evento in dia.eventos"
                  :key="evento.id"
                  type="button"
                  class="evento-pill"
                  @click="abrirEvento(evento)"
                >
                  {{ evento.titulo }}
                </button>
              </div>
            </div>
            <p v-if="!eventos.length" class="text-sm text-gray-400 text-center mt-4">
              Crie um evento na agenda para lembrar de contatar este cliente.
            </p>
          </div>
        </div>

        <div class="card p-0 overflow-hidden">
          <div class="card-section-header">
            <span>Pedidos e atendimentos</span>
            <div class="flex gap-2">
              <Link :href="`/${empresaId}/pedidos/create?cliente_id=${cliente.id}`" class="btn-primary">Criar pedido</Link>
              <button type="button" class="btn-outline" @click="openModalAtendimento = true">Registrar atendimento</button>
            </div>
          </div>
          <div class="p-5">
            <div v-if="atendimentos.length" class="space-y-3">
              <div v-for="item in atendimentos" :key="item.id" class="border border-gray-200 rounded-lg p-3 text-sm">
                <div class="font-semibold text-gray-800">{{ item.resultado }}</div>
                <div class="text-gray-500">{{ item.data }}</div>
                <div class="text-gray-700 mt-1">{{ item.observacao || "--" }}</div>
              </div>
            </div>
            <div v-else class="card-empty">Veja os pedidos criados e registre os atendimentos realizados neste cliente.</div>
          </div>
        </div>

        <div class="card p-0 overflow-hidden">
          <div class="card-section-header">
            <span>Notas fiscais</span>
          </div>
          <div class="card-empty">Nao ha notas fiscais disponiveis para este cliente.</div>
        </div>
      </div>

      <div class="lg:col-span-5 space-y-4">
        <div class="card p-0 overflow-hidden">
          <div class="card-section-header"><span>Resumo</span></div>
          <div class="p-4">
            <div class="rounded-lg bg-gray-50 border border-gray-200 p-4">
              <p class="text-sm text-gray-700 mb-3">Ultimos 6 meses</p>
              <p class="text-xl text-gray-800">
                <strong>{{ cliente.resumo?.pedidos_ultimos_6_meses || 0 }}</strong>
                <span class="text-gray-500"> Pedidos realizados</span>
              </p>
              <p class="text-sm text-gray-500 mt-3">Apenas pedidos do tipo venda</p>
            </div>
          </div>
        </div>

        <div class="card p-0 overflow-hidden">
          <div class="card-section-header"><span>Portal do cliente</span></div>
          <div class="p-4 flex items-center justify-between">
            <div class="text-sm font-semibold" :class="portalLiberado ? 'text-green-600' : 'text-gray-500'">
              {{ portalLiberado ? "Portal liberado" : "Portal bloqueado" }}
            </div>
            <button type="button" class="switch" :class="{ on: portalLiberado }" @click="portalLiberado = !portalLiberado">
              <span class="switch-knob"></span>
            </button>
          </div>
        </div>

        <div class="card p-0 overflow-hidden">
          <div class="card-section-header"><span>Limite de credito</span></div>
          <div class="p-4">
            <div class="font-semibold text-gray-700 mb-3">Globalplastic</div>
            <div class="grid grid-cols-2 gap-4 text-sm mb-4">
              <div>
                <div class="text-gray-500">Limite disponivel</div>
                <div class="text-xl">{{ currency(credito.limite_disponivel) }}</div>
              </div>
              <div>
                <div class="text-gray-500">Limite total</div>
                <div class="text-xl">{{ currency(credito.limite_total) }}</div>
              </div>
            </div>
            <button type="button" class="btn-outline" @click="openModalCredito = true">
              <i class="bx bx-pencil mr-1"></i> Editar credito
            </button>
          </div>
        </div>

        <div class="card p-0 overflow-hidden">
          <div class="card-section-header">
            <span>Titulos</span>
            <button type="button" class="btn-outline" @click="openModalTitulo = true">
              <i class="bx bx-plus mr-1"></i> Adicionar titulo
            </button>
          </div>
          <div class="px-4 pb-4">
            <div class="flex gap-2 mb-3">
              <button type="button" class="tab-btn" :class="{ active: abaTitulos === 'receber' }" @click="abaTitulos = 'receber'">A receber</button>
              <button type="button" class="tab-btn" :class="{ active: abaTitulos === 'recebidos' }" @click="abaTitulos = 'recebidos'">Recebidos</button>
            </div>
            <div v-if="titulosFiltrados.length" class="space-y-3">
              <div v-for="titulo in titulosFiltrados" :key="titulo.id" class="border border-gray-200 rounded-lg p-3 text-sm">
                <div class="font-semibold">{{ currency(titulo.valor) }}</div>
                <div class="text-gray-500">Vencimento: {{ titulo.vencimento }}</div>
                <div class="text-gray-600">{{ titulo.documento || "--" }}</div>
              </div>
            </div>
            <div v-else class="card-empty py-8">Este cliente nao possui titulos cadastrados no sistema.</div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="openModalEvento" class="modal-overlay">
      <div class="modal-card">
        <div class="modal-header">
          <h3>Criar evento</h3>
          <button type="button" class="modal-close" @click="openModalEvento = false">&times;</button>
        </div>
        <div class="modal-body space-y-3">
          <div>
            <label class="field-title">Titulo</label>
            <input v-model="formEvento.titulo" class="field-input" type="text" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="field-title">Data</label>
              <input v-model="formEvento.data" class="field-input" type="date" />
            </div>
            <div>
              <label class="field-title">Hora</label>
              <input v-model="formEvento.hora" class="field-input" type="time" />
            </div>
          </div>
          <div>
            <label class="field-title">Descricao</label>
            <textarea v-model="formEvento.descricao" class="field-input" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-primary" @click="salvarEvento">Salvar</button>
          <button type="button" class="btn-outline" @click="openModalEvento = false">Cancelar</button>
        </div>
      </div>
    </div>

    <div v-if="eventoSelecionado" class="modal-overlay">
      <div class="modal-card">
        <div class="modal-header">
          <h3>Evento agendado</h3>
          <button type="button" class="modal-close" @click="eventoSelecionado = null">&times;</button>
        </div>
        <div class="modal-body">
          <p class="font-semibold text-lg text-gray-800">{{ eventoSelecionado.titulo }}</p>
          <p class="text-gray-500 mt-1">{{ formatarDataBr(eventoSelecionado.data) }} {{ eventoSelecionado.hora }}</p>
          <p class="mt-3 text-gray-700">{{ eventoSelecionado.descricao || "--" }}</p>
        </div>
      </div>
    </div>

    <div v-if="openModalAtendimento" class="modal-overlay">
      <div class="modal-card">
        <div class="modal-header">
          <h3>Registrar atendimento</h3>
          <button type="button" class="modal-close" @click="openModalAtendimento = false">&times;</button>
        </div>
        <div class="modal-body space-y-3">
          <div>
            <label class="field-title">Resultado</label>
            <input v-model="formAtendimento.resultado" class="field-input" type="text" />
          </div>
          <div>
            <label class="field-title">Data</label>
            <input v-model="formAtendimento.data" class="field-input" type="date" />
          </div>
          <div>
            <label class="field-title">Observacao</label>
            <textarea v-model="formAtendimento.observacao" class="field-input" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-primary" @click="salvarAtendimento">Salvar</button>
          <button type="button" class="btn-outline" @click="openModalAtendimento = false">Cancelar</button>
        </div>
      </div>
    </div>

    <div v-if="openModalTitulo" class="modal-overlay">
      <div class="modal-card">
        <div class="modal-header">
          <h3>Adicionar titulo</h3>
          <button type="button" class="modal-close" @click="openModalTitulo = false">&times;</button>
        </div>
        <div class="modal-body space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="field-title">Valor</label>
              <input v-model.number="formTitulo.valor" class="field-input" type="number" step="0.01" min="0" />
            </div>
            <div>
              <label class="field-title">Data de vencimento</label>
              <input v-model="formTitulo.vencimento" class="field-input" type="date" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="field-title">Numero do documento</label>
              <input v-model="formTitulo.documento" class="field-input" type="text" />
            </div>
            <div>
              <label class="field-title">Data de pagamento</label>
              <input v-model="formTitulo.pagamento" class="field-input" type="date" />
            </div>
          </div>
          <div>
            <label class="field-title">Observacao</label>
            <textarea v-model="formTitulo.observacao" class="field-input" rows="3" maxlength="300"></textarea>
            <p class="text-xs text-gray-400 mt-1">{{ 300 - (formTitulo.observacao?.length || 0) }} caracteres restantes</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-primary" @click="salvarTitulo(false)">Salvar</button>
          <button type="button" class="btn-primary" @click="salvarTitulo(true)">Salvar e adicionar outro</button>
          <button type="button" class="btn-outline" @click="openModalTitulo = false">Cancelar</button>
        </div>
      </div>
    </div>

    <div v-if="openModalCredito" class="modal-overlay">
      <div class="modal-card">
        <div class="modal-header">
          <h3>Editar limite de credito</h3>
          <button type="button" class="modal-close" @click="openModalCredito = false">&times;</button>
        </div>
        <div class="modal-body space-y-3">
          <div>
            <label class="field-title">Limite disponivel</label>
            <input v-model.number="formCredito.limite_disponivel" class="field-input" type="number" step="0.01" min="0" />
          </div>
          <div>
            <label class="field-title">Limite total</label>
            <input v-model.number="formCredito.limite_total" class="field-input" type="number" step="0.01" min="0" />
          </div>
          <div class="rounded-lg border border-gray-200 p-3 text-sm text-gray-600 bg-gray-50">
            Para o limite de credito funcionar corretamente, preencha os dois valores.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-primary" @click="salvarCredito">Salvar</button>
          <button type="button" class="btn-outline" @click="openModalCredito = false">Cancelar</button>
        </div>
      </div>
    </div>
  </ClientesPageShell>
</template>

<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import ClientesPageShell from "@/pages/Clientes/components/ClientesPageShell.vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed, reactive, ref } from "vue";

defineOptions({ layout: AppLayout });

const page = usePage();
const empresaId = Number(page.props.empresa_id);
const cliente = page.props.cliente || {};

const openMaisAcoes = ref(false);
const clienteBloqueado = ref(Boolean(cliente.bloqueado));
const portalLiberado = ref(true);

const openModalEvento = ref(false);
const openModalAtendimento = ref(false);
const openModalTitulo = ref(false);
const openModalCredito = ref(false);
const eventoSelecionado = ref(null);

const hoje = new Date();
const mesAtual = ref(new Date(hoje.getFullYear(), hoje.getMonth(), 1));
const diasSemana = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sab"];

const eventos = ref([]);
const atendimentos = ref([]);
const titulos = ref([]);
const abaTitulos = ref("receber");
const credito = reactive({
  limite_disponivel: 0,
  limite_total: 0,
});

const formEvento = reactive({
  titulo: "",
  data: "",
  hora: "",
  descricao: "",
});

const formAtendimento = reactive({
  resultado: "",
  data: "",
  observacao: "",
});

const formTitulo = reactive({
  valor: 0,
  vencimento: "",
  documento: "",
  pagamento: "",
  observacao: "",
});

const formCredito = reactive({
  limite_disponivel: 0,
  limite_total: 0,
});

const mesCalendarioLabel = computed(() =>
  mesAtual.value.toLocaleDateString("pt-BR", { month: "long", year: "numeric" }).toUpperCase()
);

const diasCalendario = computed(() => {
  const inicio = new Date(mesAtual.value.getFullYear(), mesAtual.value.getMonth(), 1);
  const fim = new Date(mesAtual.value.getFullYear(), mesAtual.value.getMonth() + 1, 0);
  const inicioGrid = new Date(inicio);
  inicioGrid.setDate(inicio.getDate() - inicio.getDay());
  const fimGrid = new Date(fim);
  fimGrid.setDate(fim.getDate() + (6 - fim.getDay()));

  const dias = [];
  const cursor = new Date(inicioGrid);
  while (cursor <= fimGrid) {
    const dataIso = dateToIso(cursor);
    dias.push({
      key: dataIso,
      numero: cursor.getDate(),
      noMesAtual: cursor.getMonth() === mesAtual.value.getMonth(),
      eventos: eventos.value.filter((evento) => evento.data === dataIso),
    });
    cursor.setDate(cursor.getDate() + 1);
  }
  return dias;
});

const titulosFiltrados = computed(() =>
  titulos.value.filter((item) => (abaTitulos.value === "receber" ? !item.pagamento : !!item.pagamento))
);

function formatarLista(lista) {
  return Array.isArray(lista) && lista.length ? lista.join(", ") : "--";
}

function montarEndereco(endereco) {
  if (!endereco) return "";
  const linha1 = [endereco.rua, endereco.numero].filter(Boolean).join(", ");
  const linha2 = [endereco.bairro, endereco.cep].filter(Boolean).join(" - ");
  const linha3 = [endereco.cidade, endereco.estado].filter(Boolean).join(" - ");
  return [linha1, linha2, linha3, endereco.complemento].filter(Boolean).join(" | ");
}

function dateToIso(date) {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, "0");
  const d = String(date.getDate()).padStart(2, "0");
  return `${y}-${m}-${d}`;
}

function currency(valor) {
  return new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" }).format(Number(valor || 0));
}

function formatarDataBr(dataIso) {
  if (!dataIso) return "--";
  const [ano, mes, dia] = dataIso.split("-");
  return `${dia}/${mes}/${ano}`;
}

function voltarMes() {
  mesAtual.value = new Date(mesAtual.value.getFullYear(), mesAtual.value.getMonth() - 1, 1);
}

function avancarMes() {
  mesAtual.value = new Date(mesAtual.value.getFullYear(), mesAtual.value.getMonth() + 1, 1);
}

function salvarEvento() {
  if (!formEvento.titulo || !formEvento.data) return;
  eventos.value.push({
    id: Date.now(),
    titulo: formEvento.titulo,
    data: formEvento.data,
    hora: formEvento.hora || "00:00",
    descricao: formEvento.descricao,
  });
  formEvento.titulo = "";
  formEvento.data = "";
  formEvento.hora = "";
  formEvento.descricao = "";
  openModalEvento.value = false;
}

function abrirEvento(evento) {
  eventoSelecionado.value = evento;
}

function salvarAtendimento() {
  if (!formAtendimento.resultado || !formAtendimento.data) return;
  atendimentos.value.unshift({
    id: Date.now(),
    resultado: formAtendimento.resultado,
    data: formatarDataBr(formAtendimento.data),
    observacao: formAtendimento.observacao,
  });
  formAtendimento.resultado = "";
  formAtendimento.data = "";
  formAtendimento.observacao = "";
  openModalAtendimento.value = false;
}

function limparFormTitulo() {
  formTitulo.valor = 0;
  formTitulo.vencimento = "";
  formTitulo.documento = "";
  formTitulo.pagamento = "";
  formTitulo.observacao = "";
}

function salvarTitulo(adicionarOutro) {
  if (!formTitulo.valor || !formTitulo.vencimento) return;
  titulos.value.unshift({
    id: Date.now(),
    valor: Number(formTitulo.valor),
    vencimento: formatarDataBr(formTitulo.vencimento),
    documento: formTitulo.documento,
    pagamento: formTitulo.pagamento ? formatarDataBr(formTitulo.pagamento) : "",
    observacao: formTitulo.observacao,
  });
  if (adicionarOutro) {
    limparFormTitulo();
    return;
  }
  limparFormTitulo();
  openModalTitulo.value = false;
}

function salvarCredito() {
  credito.limite_disponivel = Number(formCredito.limite_disponivel || 0);
  credito.limite_total = Number(formCredito.limite_total || 0);
  openModalCredito.value = false;
}

function toggleBloqueioCliente() {
  clienteBloqueado.value = !clienteBloqueado.value;
  openMaisAcoes.value = false;
}

function excluirCliente() {
  openMaisAcoes.value = false;
  if (!confirm("Deseja realmente excluir este cliente?")) return;
  router.delete(`/${empresaId}/clientes/${cliente.id}`, {
    onSuccess: () => {
      router.visit(`/${empresaId}/clientes/vinculos-permissoes`);
    },
  });
}
</script>

<style scoped>
.card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
}

.card-section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 14px 16px;
  border-bottom: 1px solid #e5e7eb;
  font-size: 30px;
  line-height: 1.2;
  text-transform: uppercase;
  font-weight: 500;
  color: #1f2937;
}

.card-section-header span {
  font-size: 30px;
}

.card-empty {
  text-align: center;
  color: #9ca3af;
  border-top: 1px solid #e5e7eb;
  padding: 30px 0 20px;
}

.field-title {
  color: #6b7280;
  margin-bottom: 4px;
}

.field-input {
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  padding: 10px;
  font-size: 14px;
}

.btn-primary {
  background: var(--color-indigo-600);
  border: 1px solid var(--color-indigo-600);
  color: #fff;
  border-radius: 8px;
  padding: 8px 14px;
  font-weight: 600;
  font-size: 14px;
  white-space: nowrap;
}

.btn-outline {
  border: 1px solid #d1d5db;
  color: var(--color-indigo-600);
  background: #fff;
  border-radius: 8px;
  padding: 8px 14px;
  font-weight: 600;
  font-size: 14px;
  white-space: nowrap;
}

.dropdown-acoes {
  position: absolute;
  right: 0;
  top: calc(100% + 8px);
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  min-width: 220px;
  box-shadow: 0 12px 24px rgb(0 0 0 / 0.14);
  z-index: 20;
  padding: 6px;
}

.dropdown-item {
  width: 100%;
  text-align: left;
  padding: 10px 12px;
  border-radius: 6px;
  color: #374151;
}

.dropdown-item:hover {
  background: #f3f4f6;
}

.dropdown-item.danger {
  color: #dc2626;
}

.switch {
  width: 52px;
  height: 30px;
  border-radius: 999px;
  background: #d1d5db;
  padding: 3px;
  transition: 0.2s;
}

.switch.on {
  background: #4b2d83;
}

.switch-knob {
  display: block;
  width: 24px;
  height: 24px;
  background: #fff;
  border-radius: 999px;
  transform: translateX(0);
  transition: 0.2s;
}

.switch.on .switch-knob {
  transform: translateX(22px);
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, minmax(0, 1fr));
  gap: 6px;
}

.calendar-weekdays div {
  font-size: 12px;
  color: #6b7280;
  text-align: center;
  padding-bottom: 4px;
}

.calendar-cell {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  min-height: 86px;
  padding: 6px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.calendar-cell.muted {
  opacity: 0.45;
}

.calendar-day {
  font-size: 12px;
  color: #374151;
}

.evento-pill {
  border-radius: 999px;
  padding: 2px 8px;
  background: #ede9fe;
  color: var(--color-indigo-600);
  font-size: 11px;
  text-align: left;
  width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.month-nav {
  width: 32px;
  height: 32px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
}

.tab-btn {
  border: 1px solid #d1d5db;
  color: #6b7280;
  border-radius: 999px;
  padding: 6px 12px;
  font-size: 13px;
}

.tab-btn.active {
  background: var(--color-indigo-600);
  border-color: var(--color-indigo-600);
  color: #fff;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgb(0 0 0 / 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 80;
  padding: 16px;
}

.modal-card {
  width: 100%;
  max-width: 640px;
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
  font-size: 32px;
  line-height: 1.2;
  color: #1f2937;
}

.modal-close {
  font-size: 36px;
  line-height: 1;
  color: #374151;
}

.modal-body {
  padding: 16px 20px;
}

.modal-footer {
  padding: 16px 20px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  gap: 8px;
  justify-content: flex-start;
}

@media (max-width: 768px) {
  .card-section-header span,
  .card-section-header {
    font-size: 22px;
  }

  .modal-header h3 {
    font-size: 24px;
  }
}
</style>
