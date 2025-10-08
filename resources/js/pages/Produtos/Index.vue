<template>
    <div class="space-y-6 bg-white dark:bg-gray-800 px-4 pb-4 rounded-sm shadow">
        <!-- Tabs principais -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <Link :href="tab.url" v-for="tab in mainTabs" :key="tab.name" @click="activeMainTab = tab.name"
                    class="flex items-center space-x-2 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    :class="activeMainTab === tab.name
                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'">
                <i :class="tab.icon + ' text-lg'"></i>
                <span>{{ tab.name }}</span>
                </Link>
            </nav>
        </div>

        <!-- Conteúdo Produtos -->
        <div v-if="activeMainTab === 'Produtos'">
            <!-- Sub-tabs Produtos -->
            <div class="bg-gray-100 dark:bg-gray-800 -mx-4 -mt-6">
                <nav class="flex space-x-2">
                    <Link :href="sub.url" v-for="sub in produtosTabs" :key="sub.name"
                        @click="activeProdutosTab = sub.name"
                        class="flex items-center space-x-2 px-3 py-1 text-sm font-medium"
                        :class="activeProdutosTab === sub.name
                            ? 'text-indigo-700 dark:text-white border-b-2'
                            : 'text-gray-500 dark:text-white border-b-2 border-gray-100 hover:text-gray-700 hover:border-gray-300'">
                    <i :class="sub.icon + ' text-base'"></i>
                    <span>{{ sub.name }}</span>
                    </Link>
                </nav>
            </div>

            <!-- Conteúdo das sub-tabs Produtos -->
            <div class="mt-4">
                <div v-if="activeProdutosTab === 'Produtos e Tabelas'">
                    <div class="flex space-x-2 mb-3">
                        <ButtonCustom icon="bx-plus" text="Cadastrar produto" url="/produtos/create" :outline="false" />
                        <ButtonCustom icon="bx-import" text="Importar produtos" url="/produtos/import"
                            :outline="true" />
                    </div>
                    <div class="text-gray-800 dark:text-gray-50">
                        <DataTable :columns="columns" :data="produtosNormalizados">
                            <template #cell-fotos="{ row }">
                                <div v-if="row?.imagens[0]">
                                    <img :src="row.imagens[0].imagem_base64"
                                        class="border-2 border-white rounded-xl shadow-sm w-10" />
                                </div>
                            </template>
                            <template v-for="id in allTabelaIds" #[`cell-tabela_${id}`]="{ row }" :key="id">
                                <span class="font-medium text-green-600">
                                    {{ formatCurrency(row[`tabela_${id}`] || 0) }}
                                </span>
                            </template>
                        </DataTable>
                    </div>
                </div>

                <div v-else-if="activeProdutosTab === 'Gerenciar Estoque'">
                    <ButtonCustom icon="bx-plus" text="Habilitar gerência de estoque" url="/produtos/create"
                        :outline="false" />
                </div>

                <div v-else-if="activeProdutosTab === 'Importar Fotos'">
                    <!-- BLOCO UPLOAD DE FOTOS -->
                    <div class="relative w-full border-2 border-dashed border-gray-300 hover:border-indigo-500 rounded-xl bg-gray-50 dark:bg-gray-800 p-6 flex flex-col items-center justify-center text-center cursor-pointer transition"
                        @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop" @click="triggerFileInput">
                        <!-- Estado visual ao arrastar -->
                        <div v-if="isDragging"
                            class="absolute inset-0 bg-indigo-50/70 dark:bg-indigo-500/10 rounded-xl border-2 border-indigo-400 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-medium">
                            Solte os arquivos aqui
                        </div>

                        <!-- Conteúdo padrão -->
                        <div class="pointer-events-none">
                            <p class="text-indigo-600 font-medium mb-1">Arraste e solte suas fotos aqui</p>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                ou clique para selecionar arquivos do computador
                            </p>
                            <p class="text-xs mt-2 text-gray-500 dark:text-gray-500">
                                Caso o nome do arquivo for o código de um produto ele automaticamente será atribuído ao
                                mesmo.<br />
                                Formatos aceitos: JPG, JPEG, PNG, GIF &nbsp;•&nbsp; Máx: 2MB por imagem. <br />
                                Dimensão recomendada: 800 x 800 pixels<br />
                            </p>
                        </div>

                        <!-- Input invisível -->
                        <input ref="fileInput" type="file" multiple accept=".jpg,.jpeg,.png,.gif" class="hidden"
                            @change="handleFiles" />
                    </div>

                </div>
            </div>
        </div>

        <!-- Conteúdo Promoções -->
        <div v-else-if="activeMainTab === 'Promoções'">
            <ButtonCustom icon="bx-plus" text="Nova promoção" url="/produtos/create" :outline="false" />
        </div>

        <!-- Conteúdo Destaques -->
        <div v-else-if="activeMainTab === 'Destaques'">
            <ButtonCustom icon="bx-plus" text="Novo destaque" url="/produtos/create" :outline="false" />
        </div>

        <!-- Conteúdo Configurações -->
        <div v-else-if="activeMainTab === 'Configurações'">
            <!-- Sub-tabs Configurações -->
            <div class="bg-gray-100 dark:bg-gray-800 -mx-4 -mt-6">
                <nav class="flex space-x-2">
                    <Link :href="sub.url" v-for="sub in configTabs" :key="sub.name" @click="activeConfigTab = sub.name"
                        class="flex items-center space-x-2 px-3 py-1 text-sm font-medium"
                        :class="activeConfigTab === sub.name
                            ? 'text-indigo-700 dark:text-white border-b-2'
                            : 'text-gray-500 dark:text-white border-b-2 border-gray-100 hover:text-gray-700 hover:border-gray-300'">
                    <i :class="sub.icon + ' text-base'"></i>
                    <span>{{ sub.name }}</span>
                    </Link>
                </nav>
            </div>

            <!-- Conteúdo das sub-tabs Configurações -->
            <div class="mt-4">
                <div v-if="activeConfigTab === 'Categorias'">
                    <ButtonCustom icon="bx-plus" text="Nova categoria" url="/produtos/create" :outline="false" />
                </div>

                <div v-else-if="activeConfigTab === 'Variações de Produto'">
                    <ButtonCustom icon="bx-plus" text="Nova variação" url="/produtos/create" :outline="false" />
                </div>

                <div v-else-if="activeConfigTab === 'Período de Inatividade'">
                    <ButtonCustom icon="bx-edit" text="Alterar periodo de inativadade" url="/produtos/create"
                        :outline="false" />
                </div>

                <div v-else-if="activeConfigTab === 'Tributações'">
                    <ButtonCustom icon="bx-plus" text="Nova regra de cálculo" url="/produtos/create" :outline="false" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import ButtonCustom from "@/components/ButtonCustom.vue";
import DataTable from "@/components/DataTable.vue";
import { usePage, Link } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { formatCurrency } from "@/lib/utils";

const fileInput = ref(null)
const isDragging = ref(false)

defineOptions({ layout: AppLayout });

const page = usePage();
const produtos = ref(page.props.produtos || { data: [], meta: {} });

const mainTabs = [
    { name: "Produtos", icon: "bx bx-box", url: "./produtos" },
    { name: "Promoções", icon: "bx bx-badge", url: "./produtos/promocoes" },
    { name: "Destaques", icon: "bx bx-star", url: "./produtos/destaques" },
    { name: "Configurações", icon: "bx bx-cog", url: "./produtos/configuracoes" },
];
const activeMainTab = ref("Produtos");

// Sub-tabs Produtos
const produtosTabs = [
    { name: "Produtos e Tabelas", icon: "bx bx-list-ul", url: "./produtos/tabelas" },
    { name: "Gerenciar Estoque", icon: "bx bx-store", url: "./produtos/gerenciar_estoque" },
    { name: "Importar Fotos", icon: "bx bx-image-add", url: "./produtos/importar_fotos" },
];
const activeProdutosTab = ref("Produtos e Tabelas");

// Sub-tabs Configurações
const configTabs = [
    { name: "Categorias", icon: "bx bx-category", url: "./produtos/configuracoes/categorias" },
    { name: "Variações de Produto", icon: "bx bx-transfer", url: "./produtos/configuracoes/variacoes_produto" },
    { name: "Período de Inatividade", icon: "bx bx-time", url: "./produtos/configuracoes/periodo_inatividade" },
    { name: "Tributações", icon: "bx bx-receipt", url: "./produtos/configuracoes/tributacoes" },
];
const activeConfigTab = ref("Categorias");

const allTabelaIds = [
    ...new Set(
        produtos.value.flatMap(produto =>
            (produto.precos || []).map(p => p.tabela_id)
        )
    ),
];

// Criar um mapeamento id → nome para poder ordenar alfabeticamente
const tabelaNamesMap = {};
produtos.value.forEach(produto => {
  (produto.precos || []).forEach(p => {
    if (p.tabelas?.nome) tabelaNamesMap[p.tabela_id] = p.tabelas.nome;
  });
});

// Ordena os IDs pelo nome da tabela
allTabelaIds.sort((a, b) => {
  const nameA = tabelaNamesMap[a] || `Tabela ${a}`;
  const nameB = tabelaNamesMap[b] || `Tabela ${b}`;
  return nameA.localeCompare(nameB);
});


// Colunas do DataTable
const columns = [
    { label: "Fotos", key: "fotos" },
    { label: "Código", key: "codigo" },
    { label: "Nome", key: "nome" },
    { label: "Variações", key: "variacoes" },
    { label: "IPI", key: "ipi" },
    { label: "Unidade", key: "unidade" },
    { label: "Comissão", key: "comissao" },
    { label: "Preço Mínimo", key: "preco_minimo" },
    { label: "Preço Tabela", key: "preco_tabela" },
    ...allTabelaIds.map(id => ({
        label: produtos.value
            .flatMap(p => p.precos || [])
            .find(p => p.tabela_id === id)?.tabelas?.nome || `Tabela ${id}`,
        key: `tabela_${id}`,
    })),
];


// Normalizar produtos garantindo todas as tabelas
const produtosNormalizados = produtos.value.map(produto => {
    const precosObj = {};

    // Preenche as tabelas do produto
    (produto.precos || []).forEach(p => {
        precosObj[`tabela_${p.tabela_id}`] = p.preco;
    });

    // Garante que todas as tabelas existam (com valor 0 se ausente)
    allTabelaIds.forEach(id => {
        if (precosObj[`tabela_${id}`] === undefined) {
            precosObj[`tabela_${id}`] = 0;
        }
    });

    return { ...produto, ...precosObj };
});
function triggerFileInput() {
    fileInput.value?.click()
}

function handleFiles(event) {
    const files = event.target.files
    if (!files.length) return
    console.log('Arquivos selecionados:', files)
    // aqui você pode enviar para o servidor ou exibir previews
}

function handleDrop(event) {
    isDragging.value = false
    const files = event.dataTransfer.files
    if (!files.length) return
    console.log('Arquivos arrastados:', files)
    // também pode tratar o upload aqui
}
</script>
