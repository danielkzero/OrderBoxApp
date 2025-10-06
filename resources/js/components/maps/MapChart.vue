<template>
  <!-- Container do Mapa -->
  <div :class="['relative w-full flex flex-col justify-center items-center', options.containerClass]">
    <svg v-if="Array.isArray(series) && series.length" :viewBox="viewBox" xmlns="http://www.w3.org/2000/svg"
      class="w-full h-[600px]">
      <g>
        <path v-for="(mun, idx) in series" :key="idx" :d="mun.PATH" :fill="getColor(mun)" stroke="#fff"
          stroke-width="0.1" class="cursor-pointer hover:opacity-80" @mousemove="showTooltip($event, mun)"
          @mouseleave="hideTooltip" />
      </g>
    </svg>

    <div v-else class="text-center py-10 text-gray-500">
      <div class="flex items-center justify-between mb-5">
        <i class="bx bx-map text-xl"></i>
        <span class="ml-2 text-sm">Carregando mapa...</span>
      </div>
    </div>

    <!-- Tooltip flutuante -->
    <teleport to="body">
      <div v-if="tooltip.show"
        class="fixed bg-white text-gray-800 p-2 rounded-lg shadow-lg pointer-events-none transition-transform duration-150 z-[9999]"
        :style="tooltipStyle">
        <div class="font-semibold">{{ tooltip.data.NOME_CI }}</div>
        <div class="text-sm">{{ formatCurrency(tooltip.data.value) || 0 }}</div>
      </div>
    </teleport>

    <!-- Legenda -->
    <div v-if="options.legend?.show" class="mt-4 flex gap-4 justify-center text-sm">
      <div v-for="(legend, idx) in options.legend.items" :key="idx" class="flex items-center gap-1">
        <span class="w-4 h-4 block rounded" :style="{ background: legend.color }"></span>
        {{ legend.label }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, reactive } from "vue";
import { formatCurrency } from "@/lib/utils";

// Props de configuração (estilo gráfico)
const props = defineProps({
  options: {
    type: Object,
    default: () => ({
      containerClass: "",
      legend: {
        show: true,
        items: [
          { color: "#1e3a8a", label: "Alto" },
          { color: "#3b82f6", label: "Médio" },
          { color: "#facc15", label: "Baixo" },
          { color: "#ef4444", label: "Muito baixo" },
        ],
      },
      colorScale: [
        { value: 100000, color: "#1e3a8a" },
        { value: 50000, color: "#3b82f6" },
        { value: 20000, color: "#facc15" },
        { value: 0, color: "#ef4444" },
      ],
    }),
  },
  series: {
    type: Array,
    default: () => [],
  },
});

const maxValue = computed(() => {
  if (!props.series || !props.series.length) return 0;
  return Math.max(...props.series.map(m => m.value || 0));
});

// Gera a colorScale dinamicamente (4 níveis)
const dynamicColorScale = computed(() => {
  const max = maxValue.value || 1; // evita divisão por zero
  return [
    { value: max * 0.75, color: "#1e3a8a" }, // Alto
    { value: max * 0.5, color: "#3b82f6" },  // Médio
    { value: max * 0.10, color: "#facc15" }, // Baixo
    { value: 1, color: "#ef4444" }           // Muito baixo
  ];
});

const tooltip = reactive({
  show: false,
  x: 0,
  y: 0,
  data: {}
});

// Retorna estilo dinâmico do tooltip
const tooltipStyle = computed(() => {
  const offsetY = 20; // distancia acima do mouse
  const offsetX = 0;  // centralizado horizontal
  return {
    top: tooltip.y - offsetY + "px",
    left: tooltip.x + offsetX + "px",
    transform: "translate(-50%, -100%)", // centraliza horizontal e sobe vertical
  };
});

function showTooltip(event, mun) {
  tooltip.show = true;
  tooltip.x = event.clientX;
  tooltip.y = event.clientY;
  tooltip.data = mun;
}

function hideTooltip() {
  tooltip.show = false;
}

// Estado interno
const viewBox = ref("");

// Função de cor baseada em escala dinâmica
function getColor(mun) {
  const valor = mun.value || 0;
  for (const step of dynamicColorScale.value) {
    if (valor >= step.value) return step.color;
  }
  return "#ccc"; // fallback
}

// Calcular bounding box para centralizar estado
function calcularViewBox(muns) {
  let xs = [], ys = [];
  muns.forEach((m) => {
    if (m.PATH) {
      const coords = m.PATH.match(/[-+]?[0-9]*\.?[0-9]+/g)?.map(Number) || [];
      for (let i = 0; i < coords.length; i += 2) {
        xs.push(coords[i]);
        ys.push(coords[i + 1]);
      }
    }
  });
  if (xs.length && ys.length) {
    const minX = Math.min(...xs);
    const maxX = Math.max(...xs);
    const minY = Math.min(...ys);
    const maxY = Math.max(...ys);
    return `${minX} ${minY} ${maxX - minX} ${maxY - minY}`;
  }
  return "0 0 800 600"; // fallback
}

// Atualizar quando series mudar
watch(
  () => props.series,
  (val) => {
    if (Array.isArray(val) && val.length) {
      viewBox.value = calcularViewBox(val);
    }
  },
  { deep: true, immediate: true }
);
</script>
