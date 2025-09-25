<template>
  <AppLayout title="Pedidos">
    <div class="max-w-7xl mx-auto">
      <!-- Abas -->
      <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
          <Link 
            href=""
            :class="[
              'flex items-center py-4 px-1 border-b-2 font-medium text-sm',
              $page.url === '/pedidos' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            <span class="material-icons mr-2">description</span>
            PEDIDOS
          </Link>
          
          <Link 
            href=""
            :class="[
              'flex items-center py-4 px-1 border-b-2 font-medium text-sm',
              $page.url.includes('/configuracoes') 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            <span class="material-icons mr-2">settings</span>
            CONFIGURAÇÕES
          </Link>
        </nav>
      </div>

      <!-- Barra de Ações e Busca -->
      <div class="bg-white rounded-lg border border-gray-200 mb-6">
        <div class="p-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <!-- Botões de Ação -->
            <div class="flex flex-wrap gap-3">
              <button 
                @click="criarPedido"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center"
              >
                <span class="material-icons mr-2">add</span>
                Criar pedido / orçamento
              </button>
              
              <button class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors flex items-center">
                <span class="material-icons mr-2">print</span>
                Imprimir pedidos
              </button>
            </div>

            <!-- Busca -->
            <SearchFilters :filters="filters" @search="handleSearch" />
          </div>
        </div>
      </div>

      <!-- Lista de Pedidos -->
      <OrderList :orders="orders" />

      <!-- Paginação -->

      <!-- Botão Flutuante -->
      <button 
        @click="criarPedido"
        class="fixed bottom-6 right-6 bg-blue-600 text-white p-4 rounded-full shadow-lg hover:bg-blue-700 transition-colors z-40"
        title="Criar Pedido"
      >
        <span class="material-icons">description</span>
      </button>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SearchFilters from '@/components/SearchFilters.vue'
import OrderList from '@/components/OrderList.vue'

defineProps({
  orders: Object,
  filters: Object
})

const criarPedido = () => {
  router.visit(route('pedidos.create'))
}

const handleSearch = (filters) => {
  router.get(route('pedidos.index'), filters, {
    preserveState: true,
    replace: true
  })
}
</script>