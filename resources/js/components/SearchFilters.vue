<template>
  <div class="flex flex-col space-y-4">
    <!-- Busca Rápida -->
    <div class="flex items-center space-x-2">
      <div class="relative flex-1">
        <input 
          v-model="form.texto"
          type="text" 
          placeholder="Pedido, cliente ou representada" 
          class="w-full pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          @keyup.enter="submit"
        >
        <button @click="submit" class="absolute right-2 top-2 text-gray-400 hover:text-gray-600">
          <span class="material-icons">search</span>
        </button>
      </div>
    </div>

    <!-- Filtros Avançados -->
    <div class="bg-gray-50 rounded-lg p-4">
      <button 
        @click="showAdvanced = !showAdvanced"
        class="text-sm text-blue-600 hover:text-blue-800 flex items-center"
      >
        <span class="material-icons text-sm mr-1">
          {{ showAdvanced ? 'expand_less' : 'expand_more' }}
        </span>
        Pesquise por nota fiscal, data de emissão, etc.
      </button>

      <div v-show="showAdvanced" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Cliente -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
          <input 
            v-model="form.cliente"
            type="text" 
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
        </div>

        <!-- Nota Fiscal -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nota Fiscal</label>
          <input 
            v-model="form.nota_fiscal"
            type="text" 
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
        </div>

        <!-- Data de Emissão -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Data de Emissão</label>
          <div class="flex items-center space-x-2">
            <input 
              v-model="form.data_emissao_inicio"
              type="date" 
              class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
            <span>até</span>
            <input 
              v-model="form.data_emissao_fim"
              type="date" 
              class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
          </div>
        </div>

        <!-- Botão Pesquisar -->
        <div class="md:col-span-2">
          <button 
            @click="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center"
          >
            <span class="material-icons mr-2">search</span>
            Pesquisar pedidos
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'

const emit = defineEmits(['search'])
const props = defineProps({
  filters: Object
})

const showAdvanced = ref(false)

const form = reactive({
  texto: props.filters?.texto || '',
  cliente: props.filters?.cliente || '',
  nota_fiscal: props.filters?.nota_fiscal || '',
  data_emissao_inicio: props.filters?.data_emissao_inicio || '',
  data_emissao_fim: props.filters?.data_emissao_fim || ''
})

const submit = () => {
  emit('search', form)
}

// Atualizar form quando filters mudar
watch(() => props.filters, (newFilters) => {
  Object.assign(form, newFilters)
}, { deep: true })
</script>