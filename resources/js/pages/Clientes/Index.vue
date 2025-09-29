<template>
    <div class="space-y-6 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-50 rounded-sm shadow">
        <!-- Tabs principais -->
        <div class="border-b border-gray-200 dark:border-gray-700 flex space-x-6 px-4">
            <button v-for="tab in mainTabs" :key="tab.name" @click="activeMainTab = tab.name"
                class="flex items-center space-x-2 py-4 px-1 border-b-2 font-medium text-sm" :class="activeMainTab === tab.name
                    ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                <i :class="tab.icon + ' text-lg'"></i>
                <span>{{ tab.name }}</span>
            </button>
        </div>

        <!-- Conteúdo Clientes -->
        <div v-if="activeMainTab === 'Clientes'" class="p-4 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Lista de clientes -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Header -->
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
                    <div class="flex items-center space-x-2 mt-1 text-sm text-gray-600" v-for="telefone in cliente.telefones" :key="telefone.id">
                        <i class="bx bx-phone"></i> <span>{{ telefone.numero }}</span>
                    </div>
                    <div class="flex items-center space-x-2 mt-1 text-sm text-gray-600" v-for="email in cliente.emails" :key="email.id">
                        <i class="bx bx-envelope"></i> <span>{{ email.email }}</span>
                    </div>
                    <div class="flex items-center space-x-2 mt-1 text-sm text-gray-600" v-for="endereco in cliente.enderecos" :key="endereco.id">
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
            <div class="bg-white rounded shadow p-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-sm font-semibold text-gray-700">CARTEIRA DE CLIENTES</h3>
                    <span class="text-xs text-gray-500">SETEMBRO DE 2025</span>
                </div>
                <apexchart type="pie" height="220" :options="chartOptions" :series="series"></apexchart>
                <div class="text-xs text-gray-600 space-y-1 mt-2">
                    <div><span class="text-green-500">●</span> 0 ativos</div>
                    <div><span class="text-yellow-500">●</span> 1 inativos recentes</div>
                    <div><span class="text-red-500">●</span> 0 inativos antigos</div>
                    <div><span class="text-gray-400">●</span> 43827 prospects</div>
                </div>
                <button
                    class="mt-3 px-3 py-1 w-full text-sm bg-indigo-50 text-indigo-600 rounded border border-indigo-200">
                    <i class="bx bx-bar-chart-alt-2"></i> Detalhar carteira
                </button>
            </div>
        </div>

        <!-- Conteúdo Configurações -->
        <div v-else class="p-4">
            <!-- Sub-tabs -->
            <div class="border-b border-gray-50 flex space-x-4">
                <button v-for="sub in configTabs" :key="sub.name" @click="activeConfigTab = sub.name"
                    class="flex items-center space-x-1 py-2 px-2 text-sm font-medium" :class="activeConfigTab === sub.name
                        ? 'text-indigo-600 border-b-2 border-indigo-600'
                        : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent'">
                    <i :class="sub.icon"></i>
                    <span>{{ sub.name }}</span>
                </button>
            </div>

            <!-- Conteúdo -->
            <div class="mt-6 text-center text-gray-500">
                <p>Nenhum {{ activeConfigTab.toLowerCase() }} configurado.</p>
                <button class="mt-3 px-4 py-2 bg-indigo-600 text-white text-sm rounded">
                    <i class="bx bx-plus"></i> Adicionar {{ activeConfigTab.toLowerCase() }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import ApexChart from "vue3-apexcharts";
import AppLayout from "@/layouts/AppLayout.vue";
import ButtonCustom from "@/components/ButtonCustom.vue";
import DataTable from "@/components/DataTable.vue";
import FormField from "@/components/FormField.vue";
import { usePage } from "@inertiajs/vue3";

defineOptions({
    layout: AppLayout,
});

const page = usePage();

const search = ref("");

const clientes = page.props.clientes;

// Tabs
const mainTabs = [
    { name: "Clientes", icon: "bx bx-user" },
    { name: "Configurações", icon: "bx bx-cog" },
];
const activeMainTab = ref("Clientes");

const configTabs = [
    { name: "Campos extras", icon: "bx bx-list-plus" },
    { name: "Tags", icon: "bx bx-purchase-tag-alt" },
    { name: "Segmentos", icon: "bx bx-sitemap" },
    { name: "Redes", icon: "bx bx-network-chart" },
    { name: "Exceções Fiscais", icon: "bx bx-bank" },
    { name: "Resultados dos Atendimentos", icon: "bx bx-task" },
    { name: "Motivos de bloqueio", icon: "bx bx-block" },
    { name: "Geral", icon: "bx bx-slider" },
];
const activeConfigTab = ref("Campos extras");

// Gráfico
const series = [0, 1, 0, 43827];
const chartOptions = {
    labels: ["Ativos", "Inativos recentes", "Inativos antigos", "Prospects"],
    colors: ["#22c55e", "#eab308", "#ef4444", "#9ca3af"],
    legend: { show: false },
    dataLabels: { enabled: false },
};
</script>
