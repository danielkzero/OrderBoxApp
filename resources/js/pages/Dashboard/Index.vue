<template>
    <div class="space-y-3">
        <!-- Header com filtros -->
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Dashboard de Vendas</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Visão geral do seu desempenho</p>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <i
                            class="bx bx-calendar absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input v-model="filtros.inicio" type="date"
                            class="pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>
                    <span class="text-gray-400 mx-1">—</span>
                    <div class="relative">
                        <i
                            class="bx bx-calendar absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input v-model="filtros.final" type="date"
                            class="pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>
                </div>
                <button @click="buscarDados"
                    class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                    <i class="bx bx-filter-alt text-sm"></i>
                    Aplicar Filtros
                </button>
            </div>
        </div>

        <!-- Row 1: Pedidos + Vendas Mensais + Top Categorias -->
        <div class="grid grid-cols-12 gap-3">
            <!-- Cards de Métricas -->
            <!-- Coluna Principal -->
            <div class="md:col-span-9 col-span-12">
                <div class="grid grid-cols-12 gap-3">
                    <!-- Cards de Métricas -->
                    <div class="xl:col-span-3 col-span-12" v-for="card in cards" :key="card.label">
                        <div
                            class="cursor-pointer p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 transition-colors hover:border-indigo-300 dark:hover:border-indigo-600">
                            <div class="flex items-start justify-between mb-3 flex-wrap">
                                <div>
                                    <span class="block mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ card.label }}
                                    </span>
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white leading-none mb-0">
                                        {{ card.value }}
                                    </h3>
                                </div>
                                <div class="text-end">
                                    <div class="mb-4">
                                        <span
                                            class="h-10 w-10 flex items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                                            <i :class="card.icon + ' text-lg'"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <a href="javascript:void(0);"
                                    class="text-gray-500 dark:text-gray-400 underline font-medium text-[13px] hover:text-indigo-600">
                                    Ver detalhes
                                </a>
                                <span class="text-xs flex items-center gap-1"
                                    :class="card.change >= 0 ? 'text-green-600' : 'text-red-600'">
                                    <i :class="card.change >= 0 ? 'bx bx-up-arrow-alt' : 'bx bx-down-arrow-alt'"></i>
                                    {{ Math.abs(card.change) }}%
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico de Pedidos por Status -->
                    <div class="md:col-span-4 col-span-12">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                            <div class="flex items-center justify-between mb-5">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pedidos por Status</h3>
                                <i class="bx bx-pie-chart-alt-2 text-gray-400 text-xl"></i>
                            </div>
                            <apexchart type="donut" height="300" :options="chartStatus.options"
                                :series="chartStatus.series" />
                        </div>
                    </div>

                    <!-- Gráfico de Vendas Mensais -->
                    <div class="md:col-span-8 col-span-12">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                            <div class="flex items-center justify-between mb-5">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Vendas Mensais</h3>
                                <i class="bx bx-bar-chart-alt-2 text-gray-400 text-xl"></i>
                            </div>
                            <apexchart type="bar" height="285" class="text-gray-700" :options="chartVendas.options"
                                :series="chartVendas.series" />
                        </div>
                    </div>
                </div>
            </div>
            <!-- Sidebar Direita -->
            <div class="md:col-span-3 col-span-12 space-y-3">
                <!-- Top Categorias -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden min-h-full">
                    <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Categorias</h3>
                        <div class="relative">
                            <button
                                class="p-1 text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                <i class="bx bx-dots-vertical-rounded text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="space-y-4">
                            <div v-for="categoria in topCategorias" :key="categoria.nome"
                                class="flex items-center justify-between p-3 rounded-lg border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-lg flex items-center justify-center"
                                        :class="categoria.cor">
                                        <i :class="categoria.icone + ' text-white text-sm'"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white text-sm">{{ categoria.nome }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ categoria.vendas }} vendas</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(categoria.valor) }}</div>
                                    <div class="text-xs flex items-center justify-end gap-1"
                                        :class="categoria.variacao >= 0 ? 'text-green-600' : 'text-red-600'">
                                        <i
                                            :class="categoria.variacao >= 0 ? 'bx bx-up-arrow-alt' : 'bx bx-down-arrow-alt'"></i>
                                        {{ Math.abs(categoria.variacao) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Vendas Recentes + Top Usuários + Produtos Recomprados + Top Regiões -->
        <div class="grid grid-cols-12 gap-3">
            <div
                class="md:col-span-3 col-span-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Vendas Recentes</h3>
                    <i class="bx bx-time-five text-gray-400 text-xl"></i>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div v-for="(venda, idx) in vendasRecentes" :key="idx"
                        class="p-4 flex justify-between hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <div>
                            <p class="text-sm font-medium">{{ venda.cliente }}</p>
                            <p class="text-xs text-gray-500">{{ venda.produto }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold">{{ formatCurrency(venda.valor) }}</p>
                            <p class="text-xs text-gray-500">{{ venda.data }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="md:col-span-3 col-span-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Usuários</h3>
                    <i class="bx bx-user-circle text-gray-400 text-xl"></i>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div v-for="(user, idx) in topUsuarios" :key="idx" class="flex items-center justify-between p-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="h-9 w-9 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-600 font-bold">
                                {{ user.nome.charAt(0) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user.nome }}</p>
                                <p class="text-xs text-gray-500">{{ user.vendas }} vendas</p>
                            </div>
                        </div>
                        <p class="text-sm font-semibold">{{ formatCurrency(user.valor) }}</p>
                    </div>
                </div>
            </div>

            <div
                class="md:col-span-3 col-span-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Produtos Recomprados</h3>
                    <i class="bx bx-refresh text-gray-400 text-xl"></i>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div v-for="(prod, idx) in produtosRecompra" :key="idx"
                        class="flex items-center justify-between p-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ prod.nome }}</p>
                        <p class="text-xs text-gray-500">{{ prod.recompras }}x</p>
                    </div>
                </div>
            </div>

            <div
                class="md:col-span-3 col-span-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Regiões</h3>
                    <i class="bx bx-map text-gray-400 text-xl"></i>
                </div>
                <div v-for="(regiao, idx) in topRegioes" :key="idx" class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ regiao.nome }}</p>
                    <p class="text-sm text-gray-500">{{ regiao.vendas }} vendas</p>
                </div>
            </div>
        </div>

        <!-- Row 4: Produtos Mais Vendidos -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Produtos Mais Vendidos</h3>
                <i class="bx bx-trending-up text-gray-400 text-xl"></i>
            </div>
            <apexchart type="line" height="300" :options="chartProdutos.options" :series="chartProdutos.series" />
        </div>
    </div>
</template>


<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { ref, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { formatCurrency } from "@/lib/utils";

defineOptions({ layout: AppLayout });

const page = usePage();

// Filtros (exemplo de datas)
const filtros = ref({
    inicio: page.props.inicio || new Date().toISOString().slice(0, 10),
    final: page.props.final || new Date().toISOString().slice(0, 10),
});

// Cards principais
const cards = ref([
    {
        label: "Total de Pedidos",
        value: page.props.totalPedidos ?? "0",
        icon: "bx bx-cart",
        change: 1.5
    },
    {
        label: "Clientes Ativos",
        value: page.props.totalClientes ?? "0",
        icon: "bx bx-user",
        change: -0.7
    },
    {
        label: "Produtos Vendidos",
        value: page.props.totalProdutos ?? "0",
        icon: "bx bx-package",
        change: 2.3
    },
    {
        label: "Receita Total",
        value: formatCurrency(page.props.receitaTotal ?? 0),
        icon: "bx bx-dollar",
        change: 0.9
    },
]);

// Chart status de pedidos (donut)
const chartStatus = ref({
    series: page.props.pedidosStatusSeries ?? [0, 0, 0],
    options: {
        chart: { type: 'donut', fontFamily: 'inherit' },
        labels: ["Aprovado", "Pendente", "Cancelado"],
        colors: ["#10B981", "#F59E0B", "#EF4444"],
        legend: { position: "bottom", fontFamily: 'inherit' },
        plotOptions: { pie: { donut: { size: '65%' } } },
        dataLabels: { enabled: false },
    },
});

const chartVendas = ref({
    series: [],
    options: {
        chart: {
            type: 'line',
            toolbar: { show: false },
            fontFamily: 'inherit',
            zoom: {
            enabled: false
          },
        },
        xaxis: {
            categories: page.props.categoriasMeses ?? [],
            labels: {
                style: { colors: '#374151', fontFamily: 'inherit' }
            }
        },
        yaxis: {
            labels: {
                style: { colors: '#374151', fontFamily: 'inherit' }
            }
        },
        dataLabels: {
          enabled: false
        },
        stroke: { curve: 'smooth', width: 2 },
        grid: { borderColor: '#f3f3f3' },
        legend: {
            show: true,
            position: 'top',
            labels: { colors: '#374151' }
        },
        tooltip: {
            y: { formatter: (val) => `${formatCurrency(val.toFixed(2))}` }
        },
        markers: {
          size: 4,
          hover: {
            sizeOffset: 6
          }
        }
    },
});

// Chart produtos mais vendidos
const chartProdutos = ref({
    series: [
        { name: "Quantidade Vendida", data: page.props.produtosQtd ?? [] }
    ],
    options: {
        chart: { toolbar: { show: false }, fontFamily: 'inherit', zoom: { enabled: false }},
        xaxis: { categories: page.props.produtosNomes ?? [], labels: { style: { fontFamily: 'inherit' } } },
        yaxis: { labels: { style: { fontFamily: 'inherit' } } },
        stroke: { curve: "smooth", width: 3 },
        colors: ["#8B5CF6"],
        dataLabels: { enabled: false },
        grid: { borderColor: '#f1f5f9' },
    },
});

const topUsuarios = ref(page.props.topUsuarios ?? []);
const vendasRecentes = ref(page.props.vendasRecentes ?? []);
const produtosRecompra = ref(page.props.produtosRecompra ?? []);
const topRegioes = ref(page.props.topRegioes ?? []);
const topCategorias = ref(page.props.topCategorias ?? []);

const urlParts = window.location.pathname.split('/').filter(Boolean);
const empresa = ref(Number(urlParts[0]) || null);

function buscarDados() {
    if (!empresa.value) return;

    router.reload({
        only: [
            'totalPedidos',
            'totalClientes',
            'totalProdutos',
            'receitaTotal',
            'pedidosStatusSeries',
            'vendasMensais',
            'meses',
            'produtosQtd',
            'produtosNomes',
            'topUsuarios',
            'vendasRecentes',
            'produtosRecompra',
            'topRegioes',
            'topCategorias',
            'inicio',
            'final'
        ],
        data: {
            inicio: filtros.value.inicio,
            final: filtros.value.final
        },
        preserveState: true
    });
}
watch(
    () => page.props,
    (newProps) => {
        cards.value[0].value = newProps.totalPedidos ?? "0";
        cards.value[1].value = newProps.totalClientes ?? "0";
        cards.value[2].value = newProps.totalProdutos ?? "0";
        cards.value[3].value = formatCurrency(newProps.receitaTotal ?? 0);

        chartStatus.value.series = newProps.pedidosStatusSeries ?? [0, 0, 0];

        chartVendas.value.series = newProps.vendasMensais ?? [];

        chartVendas.value.options = {
            ...chartVendas.value.options,
            xaxis: {
                ...chartVendas.value.options.xaxis,
                categories: newProps.categoriasMeses ?? [],
            }
        };

        chartProdutos.value.series[0].data = newProps.produtosQtd ?? [];
        chartProdutos.value.options.xaxis.categories = newProps.produtosNomes ?? [];

        topUsuarios.value = newProps.topUsuarios ?? [];
        vendasRecentes.value = newProps.vendasRecentes ?? [];
        produtosRecompra.value = newProps.produtosRecompra ?? [];
        topRegioes.value = newProps.topRegioes ?? [];
        topCategorias.value = newProps.topCategorias ?? [];
    },
    { deep: true, immediate: true }
);

</script>
