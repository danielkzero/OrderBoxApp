<template>
    <div class="flex md:hidden flex-col gap-3">
        <template v-for="row in paginatedData" :key="row.id">
            <div class="border rounded-lg p-3 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="flex justify-between items-center mb-2">
                    <!-- Título: primeira coluna do card -->
                    <h3 class="font-semibold text-sm">
                        {{ cardColumns.length ? resolveField(row, cardColumns[0].key) : '' }}
                    </h3>

                    <!-- Botão de expandir -->
                    <button v-if="props.expandable" @click="toggleRow(row.id)"
                        class="p-1 rounded hover:bg-indigo-100 dark:hover:bg-indigo-700">
                        <i :class="isExpanded(row.id) ? 'bx bx-chevron-down' : 'bx bx-chevron-right'"></i>
                    </button>
                </div>

                <!-- Conteúdo do card -->
                <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-400">
                    <template v-for="col in cardColumns" :key="col.key">
                        <div class="font-semibold">{{ col.label }}:</div>
                        <div>
                            <!-- Verifica se há slot personalizado para esta célula -->
                            <slot v-if="$slots[`cell-${col.key}`]" :name="`cell-${col.key}`" :row="row">
                                {{ resolveField(row, col.key) }}
                            </slot>
                            <!-- Fallback se não houver slot -->
                            <template v-else>{{ resolveField(row, col.key) }}</template>
                        </div>
                    </template>
                </div>

                <!-- Expandable details -->
                <div v-if="isExpanded(row.id) && hasRowDetailsSlot" class="mt-2 border-t pt-2">
                    <slot name="row-details" :row="row"></slot>
                </div>
            </div>
        </template>

        <!-- Scroll infinito (sentinela) -->
        <div v-if="enablePagination && totalPages > 1" ref="infiniteScrollTarget" class="h-6"></div>

    </div>

    <div class="hidden md:block">
        <!-- filtros - mantidos como antes -->
        <div class="flex items-center justify-between mb-3" v-if="enableSearch || enablePageSize">
            <div class="flex items-center gap-2 text-sm" v-if="enablePageSize">
                <span>Exibir</span>
                <select v-model.number="perPageLocal" class="border border-gray-500 rounded p-1 text-sm">
                    <option :value="10">10</option>
                    <option :value="20">20</option>
                    <option :value="50">50</option>
                </select>
                <span>resultados por página</span>
            </div>

            <div v-if="enableSearch">
                <input v-model="searchLocal" type="text" placeholder="Buscar registros"
                    class="border border-gray-500 rounded px-3 py-1 text-sm" />
            </div>
        </div>

        <!-- tabela -->
        <div class="overflow-x-auto rounded-lg border border-indigo-200 dark:border-gray-800 whitespace-nowrap">
            <table class="text-sm w-full">
                <thead class="bg-indigo-100 dark:bg-gray-800 text-indigo-800 dark:text-indigo-100">
                    <tr>
                        <th v-for="col in allColumns" :key="col.key" class="py-2 px-4 border-b cursor-pointer"
                            @click="col.sortable !== false && toggleSort(col.key)">
                            <div class="flex items-center gap-1">
                                <span v-if="col.key !== '_expander'">{{ col.label }}</span>
                                <span v-else class="sr-only">Expandir</span>
                                <i v-if="sortKey === col.key && sortOrder === 'asc'" class="bx bx-up-arrow text-xs"></i>
                                <i v-else-if="sortKey === col.key && sortOrder === 'desc'"
                                    class="bx bx-down-arrow text-xs"></i>
                            </div>
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-indigo-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                    <template v-for="row in paginatedData" :key="row.id">
                        <tr
                            :class="['hover:bg-indigo-200 dark:hover:bg-indigo-700 transition odd:bg-white dark:bg-gray-900 even:bg-indigo-50 dark:odd:bg-gray-900 dark:even:bg-gray-800']">
                            <td v-for="col in allColumns" :key="col.key" class="py-1 px-2 align-top">
                                <!-- coluna de expander (botão) -->
                                <template v-if="col.key === '_expander'">
                                    <button @click="toggleRow(row.id)"
                                        class="w-6 h-6 flex items-center justify-center rounded hover:bg-indigo-100 dark:bg-gray-600/50">
                                        <i v-if="isExpanded(row.id)" class="bx bx-chevron-down"></i>
                                        <i v-else class="bx bx-chevron-right"></i>
                                    </button>
                                </template>

                                <!-- slot customizável para qualquer coluna (agora passa isExpanded e toggleExpand também) -->
                                <template v-else>
                                    <slot :name="`cell-${col.key}`" :row="row" :parentRow="props.parentRow"
                                        :isExpanded="isExpanded(row.id)" :toggleExpand="() => toggleRow(row.id)">
                                        <!-- fallback: exibe valor bruto -->
                                        <span>{{ resolveField(row, col.key) }}</span>
                                    </slot>
                                </template>
                            </td>
                        </tr>

                        <!-- linha de detalhes / itens (renderiza o slot row-details) -->
                        <tr v-if="isExpanded(row.id) && hasRowDetailsSlot" class="bg-gray-50 dark:bg-gray-950">
                            <td :colspan="allColumns.length" class="p-4 border-b">
                                <slot name="row-details" :row="row"></slot>
                            </td>
                        </tr>
                    </template>

                    <!-- LOADING -->
                    <tr v-if="loading">
                        <td :colspan="allColumns.length" class="text-center py-6">
                            <slot name="loading">
                                <!-- fallback padrão -->
                                <div class="flex justify-center items-center gap-2 text-gray-600 dark:text-gray-400">
                                    <svg class="animate-spin h-5 w-5 text-indigo-600 dark:text-indigo-50"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    Carregando...
                                </div>
                            </slot>
                        </td>
                    </tr>

                    <tr v-if="!loading && filteredData.length === 0">
                        <td :colspan="allColumns.length"
                            class="text-center py-4 text-gray-500 dark:text-gray-300 italic border-b">
                            Nenhum registro encontrado.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- paginação (mantida) -->
        <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400 mt-3"
            v-if="enablePagination">
            <p>Mostrando <b>{{ startRecord }}</b> até <b>{{ endRecord }}</b> de <b>{{ filteredData.length }}</b>
                registros</p>
            <div class="flex gap-1">
                <button @click="currentPageLocal = Math.max(1, currentPageLocal - 1)" :disabled="currentPageLocal === 1"
                    class="px-3 py-1 rounded border hover:bg-indigo-100 dark:bg-indigo-800 disabled:opacity-50">
                    Anterior
                </button>

                <button v-if="totalPages > 1" @click="currentPageLocal = 1"
                    class="px-3 py-1 rounded border hover:bg-indigo-100 dark:bg-indigo-800"
                    :class="currentPageLocal === 1 ? 'bg-indigo-600 text-white' : ''">1</button>

                <span v-if="currentPageLocal > 3"> ... </span>

                <button v-for="n in visiblePages" :key="n" @click="currentPageLocal = n"
                    class="px-3 py-1 rounded border hover:bg-indigo-100 dark:bg-indigo-800"
                    :class="currentPageLocal === n ? 'bg-indigo-600 text-white' : ''">
                    {{ n }}
                </button>

                <span v-if="currentPageLocal < totalPages - 2"> ... </span>

                <button v-if="totalPages > 1" @click="currentPageLocal = totalPages"
                    class="px-3 py-1 rounded border hover:bg-indigo-100 dark:bg-indigo-800"
                    :class="currentPageLocal === totalPages ? 'bg-indigo-600 text-white' : ''">
                    {{ totalPages }}
                </button>

                <button @click="currentPageLocal = Math.min(totalPages, currentPageLocal + 1)"
                    :disabled="currentPageLocal === totalPages"
                    class="px-3 py-1 rounded border hover:bg-indigo-100 dark:bg-indigo-800 disabled:opacity-50">
                    Próximo
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, useSlots, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    data: { type: Array, default: () => [] },
    columns: { type: Array, default: () => [] },
    extraColumns: { type: Array, default: () => [] },
    perPage: { type: Number, default: 20 },
    expandable: { type: Boolean, default: false },
    defaultSortKey: { type: String, default: '' },
    defaultSortOrder: { type: String, default: '' },
    loading: { type: Boolean, default: false },

    parentRow: { type: Object, default: null },

    enableSearch: { type: Boolean, default: true },
    enablePagination: { type: Boolean, default: true },
    enablePageSize: { type: Boolean, default: true },
});

const isMobile = ref(false)
const infiniteScrollTarget = ref(null)
let observer

const loadMore = () => {
    if (currentPageLocal.value < totalPages.value) {
        currentPageLocal.value++
    }
}

const slots = useSlots();

// monta lista de colunas (prefixa coluna de expander se solicitado)
const baseColumns = computed(() => [
    ...props.columns.map(c => ({ ...c, sortable: c.sortable !== false })),
    ...props.extraColumns.map(c => ({ ...c, sortable: false }))
]);

const allColumns = computed(() => {
    if (props.expandable) {
        return [{ key: '_expander', label: '' }, ...baseColumns.value];
    }
    return baseColumns.value;
});

const perPageLocal = ref(props.perPage);
const currentPageLocal = ref(1);
const searchLocal = ref('');
const sortKey = ref(props.defaultSortKey);
const sortOrder = ref(props.defaultSortOrder);

function toggleSort(key) {
    if (sortKey.value !== key) { sortKey.value = key; sortOrder.value = 'asc'; }
    else {
        if (sortOrder.value === 'asc') sortOrder.value = 'desc';
        else if (sortOrder.value === 'desc') { sortKey.value = ''; sortOrder.value = ''; }
        else sortOrder.value = 'asc';
    }
}

// busca/filtra
const filteredData = computed(() => {
    let data = props.data || [];

    if (searchLocal.value) {
        data = data.filter(row =>
            props.columns.some(col => {
                const v = String(resolveField(row, col.key) ?? '');
                return v.toLowerCase().includes(searchLocal.value.toLowerCase());
            })
        );
    }

    if (sortKey.value && sortOrder.value) {
        data = [...data].sort((a, b) => {
            const va = resolveField(a, sortKey.value);
            const vb = resolveField(b, sortKey.value);
            if (va == null) return 1;
            if (vb == null) return -1;
            if (va < vb) return sortOrder.value === 'asc' ? -1 : 1;
            if (va > vb) return sortOrder.value === 'asc' ? 1 : -1;
            return 0;
        });
    }

    return data;
});

// paginação
const paginatedData = computed(() => {
    if (!props.enablePagination) return filteredData.value;

    if (isMobile.value) {
        // mobile -> acumula (infinite scroll)
        const end = currentPageLocal.value * perPageLocal.value;
        return filteredData.value.slice(0, end);
    } else {
        // desktop -> paginação normal
        const start = (currentPageLocal.value - 1) * perPageLocal.value;
        return filteredData.value.slice(start, start + perPageLocal.value);
    }
});

const visiblePages = computed(() => {
    const pages = [];
    const maxVisible = 3;
    let start = Math.max(2, currentPageLocal.value - maxVisible);
    let end = Math.min(totalPages.value - 1, currentPageLocal.value + maxVisible);
    for (let i = start; i <= end; i++) pages.push(i);
    return pages;
});

const totalPages = computed(() => Math.ceil(filteredData.value.length / perPageLocal.value));
const startRecord = computed(() => filteredData.value.length === 0 ? 0 : (currentPageLocal.value - 1) * perPageLocal.value + 1);
const endRecord = computed(() => Math.min(currentPageLocal.value * perPageLocal.value, filteredData.value.length));

watch([searchLocal, perPageLocal], () => currentPageLocal.value = 1);

// ---------- EXPANDER LOGIC ----------
const expandedRows = ref(new Set());
const hasRowDetailsSlot = !!slots['row-details'];

function toggleRow(id) {
    if (expandedRows.value.has(id)) expandedRows.value.delete(id);
    else expandedRows.value.add(id);
}

function isExpanded(id) {
    return expandedRows.value.has(id);
}

// limpa expandeds se os dados mudarem drasticamente
watch(() => props.data, (newData) => {
    // remove ids que não existem mais
    const ids = new Set((newData || []).map(r => r.id));
    for (const id of Array.from(expandedRows.value)) {
        if (!ids.has(id)) expandedRows.value.delete(id);
    }
});


const cardColumns = computed(() => {
    return allColumns.value.filter(col => col.key !== '_expander');
});

// helper para exibir campos aninhados com segurança
function resolveField(obj, key) {
    if (!obj) return '';
    // permite chaves compostas: "cliente.nome"
    if (key.includes('.')) {
        return key.split('.').reduce((acc, k) => acc && acc[k] !== undefined ? acc[k] : null, obj);
    }
    return obj[key];
}

//watch para observar se o screen width muda (responsivo)

onMounted(() => {
    isMobile.value = window.innerWidth < 768
    observer = new IntersectionObserver(
        (entries) => {
            if (entries[0].isIntersecting) {
                loadMore()
            }
        },
        { rootMargin: "100px" }
    )

    if (infiniteScrollTarget.value) {
        observer.observe(infiniteScrollTarget.value)
    }
})

onBeforeUnmount(() => {
    if (observer && infiniteScrollTarget.value) {
        observer.unobserve(infiniteScrollTarget.value)
    }
})
</script>
