<template>
  <div v-if="open" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h3 class="font-semibold">Descontos e acrescimos</h3>
        <button type="button" class="text-gray-500 hover:text-gray-900" @click="$emit('close')">X</button>
      </div>

      <div class="p-4 space-y-4 max-h-[70vh] overflow-y-auto">
        <div>
          <h4 class="font-medium mb-2">Descontos (%)</h4>
          <div v-for="(_, index) in localDiscounts" :key="`d-${index}`" class="flex items-center gap-2 mb-2">
            <input v-model.number="localDiscounts[index]" type="number" min="0" step="0.01" class="w-full border rounded px-2 py-1 bg-white dark:bg-gray-900 dark:border-gray-600" />
            <button type="button" class="px-2 py-1 rounded border border-red-300 text-red-700" @click="removeDiscount(index)">-</button>
          </div>
          <button type="button" class="px-3 py-1 rounded border border-indigo-300 text-indigo-700" @click="addDiscount">Adicionar desconto</button>
        </div>

        <div>
          <h4 class="font-medium mb-2">Acrescimos (%)</h4>
          <div v-for="(_, index) in localIncreases" :key="`a-${index}`" class="flex items-center gap-2 mb-2">
            <input v-model.number="localIncreases[index]" type="number" min="0" step="0.01" class="w-full border rounded px-2 py-1 bg-white dark:bg-gray-900 dark:border-gray-600" />
            <button type="button" class="px-2 py-1 rounded border border-red-300 text-red-700" @click="removeIncrease(index)">-</button>
          </div>
          <button type="button" class="px-3 py-1 rounded border border-indigo-300 text-indigo-700" @click="addIncrease">Adicionar acrescimo</button>
        </div>

        <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded p-2">
          Ajustes globais nao serao aplicados em itens com preco liquido alterado manualmente.
        </p>
      </div>

      <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-2">
        <button type="button" class="px-3 py-2 rounded border border-gray-300" @click="$emit('close')">Cancelar</button>
        <button type="button" class="px-3 py-2 rounded border border-indigo-300 text-indigo-700" @click="emitReplace">Substituir</button>
        <button type="button" class="px-3 py-2 rounded bg-indigo-600 text-white" @click="emitAppend">Adicionar</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  discounts: { type: Array, default: () => [] },
  increases: { type: Array, default: () => [] },
});

const emit = defineEmits(['close', 'replace', 'append']);

const localDiscounts = ref([]);
const localIncreases = ref([]);

watch(
  () => [props.open, props.discounts, props.increases],
  () => {
    localDiscounts.value = [...(props.discounts || [])];
    localIncreases.value = [...(props.increases || [])];
  },
  { immediate: true },
);

function addDiscount() {
  localDiscounts.value.push(0);
}

function removeDiscount(index) {
  localDiscounts.value.splice(index, 1);
}

function addIncrease() {
  localIncreases.value.push(0);
}

function removeIncrease(index) {
  localIncreases.value.splice(index, 1);
}

function clean(list) {
  return (list || []).map((v) => Number(v)).filter((v) => Number.isFinite(v) && v > 0);
}

function emitReplace() {
  emit('replace', { discounts: clean(localDiscounts.value), increases: clean(localIncreases.value) });
}

function emitAppend() {
  emit('append', { discounts: clean(localDiscounts.value), increases: clean(localIncreases.value) });
}
</script>
