<template>
    <div class="space-y-6 bg-white dark:bg-gray-800 px-4 pb-4 rounded-sm shadow">
        <!-- Tabs principais -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <button v-for="tab in mainTabs" :key="tab.name" @click="activeMainTab = tab.name"
                    class="flex items-center space-x-2 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    :class="activeMainTab === tab.name
                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'">
                    <i :class="tab.icon + ' text-lg'"></i>
                    <span>{{ tab.name }}</span>
                </button>
            </nav>
        </div>

        <!-- Conteúdo Produtos -->
        <div v-if="activeMainTab === 'Produtos'">
            <!-- Sub-tabs Produtos -->
            <div class="bg-gray-100 dark:bg-gray-800 -mx-4 -mt-6">
                <nav class="flex space-x-2">
                    <button v-for="sub in produtosTabs" :key="sub.name" @click="activeProdutosTab = sub.name"
                        class="flex items-center space-x-2 px-3 py-1 text-sm font-medium" :class="activeProdutosTab === sub.name
                            ? 'text-indigo-700 dark:text-white border-b-2'
                            : 'text-gray-500 dark:text-white border-b-2 border-gray-100 hover:text-gray-700 hover:border-gray-300'">
                        <i :class="sub.icon + ' text-base'"></i>
                        <span>{{ sub.name }}</span>
                    </button>
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
                                        class="border-2 border-white rounded-xl shadow-sm" />
                                </div>
                            </template>
                            <template v-for="p in (produtos[0]?.precos || 0)" #[`cell-tabela_${p.tabela_id}`]="{ row }"
                                :key="p.tabela_id">
                                <span class="font-medium text-green-600">
                                    {{ formatCurrency(row[`tabela_${p.tabela_id}`]) }}
                                </span>
                            </template>
                        </DataTable>
                    </div>
                </div>

                <div v-else-if="activeProdutosTab === 'Gerenciar Estoque'">
                    <h3 class="text-lg font-semibold">Gerenciar Estoque</h3>
                    <p class="text-gray-600 dark:text-gray-400">Controle de estoque dos produtos.</p>
                </div>

                <div v-else-if="activeProdutosTab === 'Importar Fotos'">
                    <h3 class="text-lg font-semibold">Importar Fotos</h3>
                    <p class="text-gray-600 dark:text-gray-400">Upload de imagens para produtos em lote.</p>
                </div>
            </div>
        </div>

        <!-- Conteúdo Promoções -->
        <div v-else-if="activeMainTab === 'Promoções'">
            <h3 class="text-lg font-semibold">Promoções</h3>
            <p class="text-gray-600 dark:text-gray-400">Aqui você gerencia promoções.</p>
        </div>

        <!-- Conteúdo Destaques -->
        <div v-else-if="activeMainTab === 'Destaques'">
            <h3 class="text-lg font-semibold">Destaques</h3>
            <p class="text-gray-600 dark:text-gray-400">Aqui você gerencia produtos em destaque.</p>
        </div>

        <!-- Conteúdo Configurações -->
        <div v-else-if="activeMainTab === 'Configurações'">
            <!-- Sub-tabs Configurações -->
            <div class="bg-gray-100 dark:bg-gray-800 -mx-4 -mt-6">
                <nav class="flex space-x-2">
                    <button v-for="sub in configTabs" :key="sub.name" @click="activeConfigTab = sub.name"
                        class="flex items-center space-x-2 px-3 py-1 text-sm font-medium" :class="activeConfigTab === sub.name
                            ? 'text-indigo-700 dark:text-white border-b-2'
                            : 'text-gray-500 dark:text-white border-b-2 border-gray-100 hover:text-gray-700 hover:border-gray-300'">
                        <i :class="sub.icon + ' text-base'"></i>
                        <span>{{ sub.name }}</span>
                    </button>
                </nav>
            </div>

            <!-- Conteúdo das sub-tabs Configurações -->
            <div class="mt-4">
                <div v-if="activeConfigTab === 'Categorias'">
                    <h3 class="text-lg font-semibold">Categorias</h3>
                    <p class="text-gray-600 dark:text-gray-400">Gerencie categorias de produtos.</p>
                </div>

                <div v-else-if="activeConfigTab === 'Variações de Produto'">
                    <h3 class="text-lg font-semibold">Variações de Produto</h3>
                    <p class="text-gray-600 dark:text-gray-400">Gerencie variações e atributos de produtos.</p>
                </div>

                <div v-else-if="activeConfigTab === 'Período de Inatividade'">
                    <h3 class="text-lg font-semibold">Período de Inatividade</h3>
                    <p class="text-gray-600 dark:text-gray-400">Defina períodos de inatividade para produtos.</p>
                </div>

                <div v-else-if="activeConfigTab === 'Tributações'">
                    <h3 class="text-lg font-semibold">Tributações</h3>
                    <p class="text-gray-600 dark:text-gray-400">Gerencie regras de tributação dos produtos.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import ButtonCustom from "@/components/ButtonCustom.vue";
import DataTable from "@/components/DataTable.vue";
import { usePage } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { formatCurrency } from "@/lib/utils";

defineOptions({ layout: AppLayout });

const page = usePage();
const produtos = ref(page.props.produtos || { data: [], meta: {} });

const mainTabs = [
    { name: "Produtos", icon: "bx bx-box" },
    { name: "Promoções", icon: "bx bx-badge" },
    { name: "Destaques", icon: "bx bx-star" },
    { name: "Configurações", icon: "bx bx-cog" },
];
const activeMainTab = ref("Produtos");

// Sub-tabs Produtos
const produtosTabs = [
    { name: "Produtos e Tabelas", icon: "bx bx-list-ul" },
    { name: "Gerenciar Estoque", icon: "bx bx-store" },
    { name: "Importar Fotos", icon: "bx bx-image-add" },
];
const activeProdutosTab = ref("Produtos e Tabelas");

// Sub-tabs Configurações
const configTabs = [
    { name: "Categorias", icon: "bx bx-category" },
    { name: "Variações de Produto", icon: "bx bx-transfer" },
    { name: "Período de Inatividade", icon: "bx bx-time" },
    { name: "Tributações", icon: "bx bx-receipt" },
];
const activeConfigTab = ref("Categorias");

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
    ...produtos.value.length
        ? (produtos.value[0].precos || []).map(p => ({
            label: p.tabelas?.nome || `Tabela ${p.tabela_id}`,
            key: `tabela_${p.tabela_id}`
        }))
        : [],
];

const produtosNormalizados = produtos.value.map(produto => {
    const precosObj = {};
    (produto.precos || []).forEach(p => {
        precosObj[`tabela_${p.tabela_id}`] = p.preco;
    });
    return { ...produto, ...precosObj };
});
</script>
