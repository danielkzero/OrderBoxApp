<template>
  <PedidosConfigShell :empresa-id="empresaId" :tabs="tabs" :active-tab="activeTab">
    <div class="relative inline-block mb-6 ">
      <button ref="btnRef" type="button" class="btn-indigo" @click="toggleMenu">
        Novo campo extra
      </button>
      <Teleport to="body">
        <div v-if="menuTipoAberto" class="fixed w-56 bg-white border border-gray-200 rounded shadow-xl z-[9999]"
          :style="menuStyle">
          <div class="menu-title">Tipo do campo</div>
          <button v-for="tipo in tiposCampos" :key="tipo" type="button" class="menu-item cursor-pointer" @click="abrirCriacao(tipo)">
            {{ tipoLabel(tipo) }}
          </button>
        </div>
      </Teleport>
    </div>

    <div v-if="tiposAgrupados.length === 0" class="empty-state">
      <h3>Nenhum campo extra cadastrado</h3>
      <p>Crie campos para complementar os dados dos pedidos.</p>
    </div>

    <div v-for="grupo in tiposAgrupados" :key="grupo.tipo" class="mb-10">
      <h3 class="section-title">{{ tipoLabel(grupo.tipo) }}</h3>
      <div v-for="campo in grupo.items" :key="campo.id" class="item-row">
        <div>
          <div class="item-name">{{ campo.nome }}</div>
          <div v-if="campo.tipo === 'LISTA' && campo.opcoes?.length" class="item-meta">
            Opcoes: {{ campo.opcoes.join(', ') }}
          </div>
        </div>
        <div class="item-actions">
          <span v-if="campo.obrigatorio" class="badge">Obrigatorio</span>
          <button type="button" class="btn-link" @click="abrirEdicao(campo)">Editar</button>
          <button type="button" class="btn-link danger" @click="excluirCampo(campo.id)">Excluir</button>
        </div>
      </div>
    </div>

    <div v-if="openModal" class="modal-backdrop" @click.self="openModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>{{ isEditing ? 'Editar campo extra' : 'Novo campo extra' }}</h3>
          <button type="button" class="icon-btn" @click="openModal = false"><i class="bx bx-x"></i></button>
        </div>

        <div class="space-y-3">
          <FormField label="Nome do campo" v-model="form.nome" />
          <FormField label="Tipo" tag="select" v-model="form.tipo">
            <option v-for="tipo in tiposCampos" :key="tipo" :value="tipo">{{ tipoLabel(tipo) }}</option>
          </FormField>
          <label class="checkbox-line">
            <input v-model="form.obrigatorio" type="checkbox" />
            Campo obrigatorio
          </label>

          <div v-if="form.tipo === 'LISTA'">
            <FormField label="Opcoes (uma por linha)" tag="textarea" :model-value="opcoesText"
              @update:model-value="opcoesText = $event" />
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-outline" @click="openModal = false">Cancelar</button>
          <button type="button" class="btn-indigo" :disabled="form.processing" @click="salvar">
            {{ form.processing ? 'Salvando...' : 'Salvar' }}
          </button>
        </div>
      </div>
    </div>
  </PedidosConfigShell>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import FormField from '@/components/FormField.vue';
import { computed, ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import PedidosConfigShell from './components/PedidosConfigShell.vue';

defineOptions({ layout: AppLayout });

const page = usePage();
const empresaId = Number(page.props.empresa_id);
const tabs = page.props.tabs || [];
const activeTab = page.props.active_tab || 'campos_extras';
const tiposCampos = page.props.tipos_campos || [];
const campos = computed(() => page.props.campos || []);

const menuTipoAberto = ref(false);
const openModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const opcoesText = ref('');

const menuStyle = ref({})
const btnRef = ref(null)

function toggleMenu() {
  menuTipoAberto.value = !menuTipoAberto.value

  if (menuTipoAberto.value) {
    const rect = btnRef.value.getBoundingClientRect()

    menuStyle.value = {
      top: rect.top + 'px',
      left: 10 +  rect.right + 'px'
    }
  }
}

const form = useForm({
  nome: '',
  tipo: 'LIVRE',
  obrigatorio: false,
  opcoes: [],
});

const tiposAgrupados = computed(() => {
  return tiposCampos
    .map((tipo) => ({
      tipo,
      items: (campos.value || []).filter((campo) => campo.tipo === tipo),
    }))
    .filter((grupo) => grupo.items.length > 0);
});

function tipoLabel(tipo) {
  const map = { LIVRE: 'LIVRE', NUMERICO: 'NUMERICO', LISTA: 'LISTA', DATA: 'DATA', HORA: 'HORA' };
  return map[tipo] || tipo;
}

function abrirCriacao(tipo) {
  isEditing.value = false;
  editingId.value = null;
  menuTipoAberto.value = false;
  form.reset();
  form.clearErrors();
  form.tipo = tipo || 'LIVRE';
  opcoesText.value = '';
  openModal.value = true;
}

function abrirEdicao(campo) {
  isEditing.value = true;
  editingId.value = campo.id;
  menuTipoAberto.value = false;
  form.clearErrors();
  form.nome = campo.nome;
  form.tipo = campo.tipo;
  form.obrigatorio = !!campo.obrigatorio;
  opcoesText.value = Array.isArray(campo.opcoes) ? campo.opcoes.join('\n') : '';
  openModal.value = true;
}

function salvar() {
  form.opcoes = form.tipo === 'LISTA'
    ? opcoesText.value.split('\n').map((item) => item.trim()).filter(Boolean)
    : [];

  const url = isEditing.value
    ? `/${empresaId}/pedidos/configuracoes/campos_extras/${editingId.value}`
    : `/${empresaId}/pedidos/configuracoes/campos_extras`;

  form[isEditing.value ? 'put' : 'post'](url, {
    preserveScroll: true,
    onSuccess: () => {
      openModal.value = false;
    },
  });
}

function excluirCampo(id) {
  if (!window.confirm('Excluir este campo extra?')) return;

  router.delete(`/${empresaId}/pedidos/configuracoes/campos_extras/${id}`, {
    preserveScroll: true,
  });
}
</script>

<style scoped>
.btn-indigo {
  background: var(--color-indigo-600);
  border: 1px solid var(--color-indigo-600);
  color: #fff;
  border-radius: 8px;
  padding: 10px 18px;
  font-weight: 600;
}

.btn-outline {
  border: 1px solid #d1d5db;
  color: var(--color-indigo-600);
  border-radius: 8px;
  padding: 10px 18px;
  font-weight: 600;
  background: #fff;
}

.menu-tipo {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  z-index: 20;
  width: 240px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
}

.menu-title {
  padding: 12px 14px;
  border-bottom: 1px solid #e5e7eb;
  font-weight: 700;
}

.menu-item {
  width: 100%;
  text-align: left;
  padding: 10px 14px;
  font-weight: 600;
  color: var(--color-indigo-600);
  border-bottom: 1px solid #f3f4f6;
}

.menu-item:last-child {
  border-bottom: 0;
}

.section-title {
  font-size: 31px;
  color: #9ca3af;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 6px;
  margin-bottom: 6px;
}

.item-row {
  border-bottom: 1px solid #ececec;
  padding: 12px 8px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.item-name {
  font-weight: 700;
  color: #111827;
}

.item-meta {
  margin-top: 4px;
  color: #6b7280;
  font-size: 13px;
}

.item-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.badge {
  background: #f3f4f6;
  border-radius: 999px;
  padding: 6px 10px;
  font-size: 12px;
}

.btn-link {
  color: var(--color-indigo-600);
  font-weight: 600;
}

.btn-link.danger {
  color: #b91c1c;
}

.empty-state {
  border: 1px dashed #d1d5db;
  border-radius: 10px;
  padding: 24px;
  text-align: center;
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
  width: min(640px, 100%);
  background: #fff;
  border: 1px solid #c7d2fe;
  border-radius: 10px;
  padding: 16px;
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
  font-size: 24px;
}

.icon-btn {
  border: 1px solid #d1d5db;
  border-radius: 6px;
  padding: 4px 8px;
  color: #4b5563;
}

.checkbox-line {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #374151;
}

.modal-footer {
  margin-top: 14px;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}
</style>
