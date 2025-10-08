<template>
    <div class="bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-50 rounded-sm shadow">
        <!-- Tabs principais -->
        <div class="border-b border-gray-200 dark:border-gray-700 flex space-x-6 px-4">
            <Link :href="tab.url" v-for="tab in mainTabs" :key="tab.name" @click="activeMainTab = tab.name"
                class="flex items-center space-x-2 py-4 px-1 border-b-2 font-medium text-sm" :class="activeMainTab === tab.name
                    ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
            <i :class="tab.icon + ' text-lg'"></i>
            <span>{{ tab.name }}</span>
            </Link>
        </div>

        <!-- Conteúdo Clientes -->
        <div v-if="activeMainTab === 'Clientes'" class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Lista de clientes -->
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <button class="px-3 py-1 text-sm bg-indigo-50 border border-indigo-200 rounded text-indigo-600">
                            <i class="bx bx-link"></i> Vínculos e permissões
                        </button>
                        <button class="text-xs text-indigo-600 underline">Exibir todos os clientes</button>
                    </div>
                    <div class="relative">
                        <input v-model="search" type="text" placeholder="Pesquise por Nome ou CNPJ"
                            class="pl-8 pr-3 py-1 text-sm border rounded w-64" />
                        <i class="bx bx-search absolute left-2 top-1.5 text-gray-400"></i>
                    </div>
                </div>

                <!-- Lista -->
                <div v-for="cliente in clientes" :key="cliente.id" class="border-b border-gray-200 pb-3 pt-3">
                    <div class="font-semibold text-indigo-600">{{ cliente.razao_social }}</div>
                    <div class="text-sm text-gray-600">{{ cliente.cnpj }}</div>

                    <div v-for="telefone in cliente.telefones" :key="telefone.id"
                        class="flex items-center space-x-2 mt-1 text-sm text-gray-600">
                        <i class="bx bx-phone"></i> <span>{{ telefone.numero }}</span>
                    </div>
                    <div v-for="email in cliente.emails" :key="email.id"
                        class="flex items-center space-x-2 mt-1 text-sm text-gray-600">
                        <i class="bx bx-envelope"></i> <span>{{ email.email }}</span>
                    </div>
                    <div v-for="endereco in cliente.enderecos" :key="endereco.id"
                        class="flex items-center space-x-2 mt-1 text-sm text-gray-600">
                        <i class="bx bx-map"></i> <span>{{ endereco.rua }}</span>
                    </div>

                    <div class="mt-2 space-x-2">
                        <button class="px-3 py-1 text-sm border rounded text-indigo-600 border-indigo-400">
                            <i class="bx bx-edit"></i> Alterar
                        </button>
                        <button class="px-3 py-1 text-sm border rounded text-red-600 border-red-400">
                            <i class="bx bx-trash"></i> Excluir
                        </button>
                    </div>
                </div>
            </div>

            <!-- Card lateral com gráfico -->
            <div class="bg-white rounded shadow p-6">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-sm font-semibold text-gray-700">CARTEIRA DE CLIENTES</h3>
                    <span class="text-xs text-gray-500">SETEMBRO DE 2025</span>
                </div>

                <apexchart type="pie" height="220" :options="chartOptions" :series="series"></apexchart>

                <div class="text-xs text-gray-600 space-y-1 mt-2">
                    <div><span class="text-green-500">●</span> {{ counts.ativos }} ativos</div>
                    <div><span class="text-yellow-500">●</span> {{ counts.inativos_recentes }} inativos recentes</div>
                    <div><span class="text-red-500">●</span> {{ counts.inativos_antigos }} inativos antigos</div>
                    <div><span class="text-gray-400">●</span> {{ counts.prospects }} prospectados</div>
                </div>

                <button
                    class="mt-3 px-3 py-1 w-full text-sm bg-indigo-50 text-indigo-600 rounded border border-indigo-200">
                    <i class="bx bx-bar-chart-alt-2"></i> Detalhar carteira
                </button>
            </div>
        </div>

        <!-- Conteúdo Configurações -->
        <div v-else>
            <div class="bg-gray-100 dark:bg-gray-800">
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

            <div class="p-6 text-center text-gray-500" v-if="activeConfigTab.toLowerCase() != 'geral'">
                <p>Nenhum {{ activeConfigTab.toLowerCase() }} configurado.</p>
                <button class="mt-3 px-4 py-2 bg-indigo-600 text-white text-sm rounded">
                    <i class="bx bx-plus"></i> Adicionar {{ activeConfigTab.toLowerCase() }}
                </button>
            </div>

            <div v-else class="p-6 text-start text-gray-500">

                <!-- DUPLICIDADE DE CPF / CNPJ -->
                <h3 class="text-gray-700 dark:text-gray-200 font-semibold my-3">
                    DUPLICIDADE DE CPF / CNPJ
                </h3>
                <div
                    class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 space-y-3">
                    <label class="flex items-start space-x-2">
                        <input type="checkbox" v-model="clienteConfig.bloquearDuplicidade" class="mt-1">
                        <span class="text-gray-800 dark:text-gray-50 font-medium">
                            Bloquear o cadastro de mais de um cliente com o mesmo CPF / CNPJ
                        </span>
                    </label>
                </div>

                <!-- CAMPOS OBRIGATÓRIOS -->
                <h3 class="text-gray-700 dark:text-gray-200 font-semibold my-3">
                    CAMPOS OBRIGATÓRIOS
                </h3>
                <div class="bg-gray-50 p-4 rounded-xl dark:border-gray-700 space-y-4">
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Selecione os campos do cadastro de clientes que devem ser obrigatórios:
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Coluna 1 -->
                        <div>
                            <h4 class="text-gray-600 dark:text-gray-300 font-semibold mb-2">Dados básicos</h4>
                            <div class="space-y-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" v-model="clienteConfig.camposObrigatorios.cpfCnpj">
                                    <span>CPF / CNPJ</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" v-model="clienteConfig.camposObrigatorios.nomeFantasia">
                                    <span>Nome Fantasia / Apelido</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" v-model="clienteConfig.camposObrigatorios.telefone">
                                    <span>Telefone</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" v-model="clienteConfig.camposObrigatorios.email">
                                    <span>E-mail</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" v-model="clienteConfig.camposObrigatorios.inscricaoEstadual">
                                    <span>Inscrição estadual</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" v-model="clienteConfig.camposObrigatorios.infoAdicional">
                                    <span>Informação adicional</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" v-model="clienteConfig.camposObrigatorios.segmento">
                                    <span>Segmento</span>
                                </label>
                            </div>
                        </div>

                        <!-- Coluna 2 -->
                        <div>
                            <h4 class="text-gray-600 dark:text-gray-300 font-semibold mb-2">Endereço</h4>
                            <div class="space-y-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" v-model="clienteConfig.camposObrigatorios.cep">
                                    <span>CEP</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" v-model="clienteConfig.camposObrigatorios.endereco">
                                    <span>Endereço</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" v-model="clienteConfig.camposObrigatorios.numero">
                                    <span>Número</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" v-model="clienteConfig.camposObrigatorios.complemento">
                                    <span>Complemento</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" v-model="clienteConfig.camposObrigatorios.bairro">
                                    <span>Bairro</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" v-model="clienteConfig.camposObrigatorios.cidade">
                                    <span>Cidade</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" v-model="clienteConfig.camposObrigatorios.estado">
                                    <span>Estado</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Mensagem informativa -->
                    <div
                        class="flex items-start space-x-3 mt-4 bg-gray-50 dark:bg-gray-900 p-3 rounded-md border border-gray-200 dark:border-gray-700">
                        <i class="bx bx-info-circle text-indigo-500 text-xl"></i>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            A Razão social (pessoa jurídica) e o Nome (pessoa física) são campos obrigatórios por
                            padrão.
                        </p>
                    </div>
                </div>

                <!-- Botão salvar -->
                <div class="mt-6">
                    <ButtonCustom icon="bx-save" text="Salvar" :outline="false" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { usePage, Link } from "@inertiajs/vue3";
import ButtonCustom from "@/components/ButtonCustom.vue";

defineOptions({
    layout: AppLayout,
});

const page = usePage();

const search = ref("");

// Clientes
const clientes = page.props.clientes;

const clienteConfig = ref({
    bloquearDuplicidade: true,
    camposObrigatorios: {
        cpfCnpj: false,
        nomeFantasia: false,
        telefone: false,
        email: false,
        inscricaoEstadual: false,
        infoAdicional: false,
        segmento: false,
        cep: false,
        endereco: false,
        numero: false,
        complemento: false,
        bairro: false,
        cidade: false,
        estado: false
    }
});

// Dados do gráfico
const series = page.props.chartData.series;
const counts = page.props.chartData.counts;

// Tabs
const mainTabs = [
    { name: "Clientes", icon: "bx bx-user", url: `./clientes` },
    { name: "Configurações", icon: "bx bx-cog", url: "./clientes/configuracoes" },
];
const activeMainTab = ref("Clientes");

const configTabs = [
    { name: "Campos extras", icon: "bx bx-list-plus", url: "./clientes/configuracoes/campos_extras" },
    { name: "Tags", icon: "bx bx-purchase-tag-alt", url: "./clientes/configuracoes/tags" },
    { name: "Segmentos", icon: "bx bx-sitemap", url: "./clientes/configuracoes/segmentos" },
    { name: "Redes", icon: "bx bx-network-chart", url: "./clientes/configuracoes/redes" },
    { name: "Exceções Fiscais", icon: "bx bx-bank", url: "./clientes/configuracoes/excecoes_fiscais" },
    { name: "Resultados dos Atendimentos", icon: "bx bx-task", url: "./clientes/configuracoes/resultados_atendimentos" },
    { name: "Motivos de bloqueio", icon: "bx bx-block", url: "./clientes/configuracoes/tags" },
    { name: "Geral", icon: "bx bx-slider", url: "./clientes/configuracoes/tags/geral" },
];
const activeConfigTab = ref("Campos extras");

// Opções do gráfico
const chartOptions = {
    labels: ["Ativos", "Inativos recentes", "Inativos antigos", "Prospects"],
    colors: ["#22c55e", "#eab308", "#ef4444", "#9ca3af"],
    legend: { show: false },
    dataLabels: { enabled: false },
};

</script>
