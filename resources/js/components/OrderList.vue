<template>
  <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <!-- Filtros Ativos -->
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 text-sm text-gray-600">
      Mostrando 
      <span class="font-medium">Pedidos ativos</span>
      feitos por 
      <span class="font-medium">Todos os vendedores</span>
      via 
      <span class="font-medium">Todas as plataformas</span>
    </div>

    <!-- Lista de Pedidos -->
    <div class="divide-y divide-gray-200">
      <div 
        v-for="order in orders.data" 
        :key="order.id"
        class="p-6 hover:bg-gray-50 cursor-pointer transition-colors"
        @click="viewOrder(order)"
      >
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center space-x-3">
            <input 
              type="checkbox" 
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              @click.stop
            >
            <Link 
              :href="route('pedidos.show', order.id)"
              class="font-semibold text-blue-600 hover:text-blue-800"
              @click.stop
            >
              #{{ order.numero }}
            </Link>
            <span class="text-sm text-gray-500">emitido por {{ order.vendedor }}</span>
          </div>
          
          <div class="flex items-center space-x-2">
            <!-- Badges de Status -->
            <span 
              :class="[
                'px-2 py-1 text-xs font-medium rounded-full',
                statusClasses[order.status]
              ]"
            >
              {{ order.status_label }}
            </span>
            
            <!-- Ícone IA -->
            <span v-if="order.criado_com_ia" class="text-yellow-500 material-icons" title="Criado com IA">
              auto_awesome
            </span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
          <!-- Cliente -->
          <div class="flex items-center">
            <span class="material-icons text-gray-400 mr-2">person</span>
            <div>
              <div class="font-medium">{{ order.cliente.razao_social }}</div>
              <div v-if="order.cliente.nome_fantasia" class="text-xs text-gray-500">
                {{ order.cliente.nome_fantasia }}
              </div>
            </div>
          </div>

          <!-- Prazo -->
          <div v-if="order.prazo_entrega" class="flex items-center">
            <span class="material-icons text-gray-400 mr-2">event</span>
            {{ order.prazo_entrega }}
          </div>

          <!-- Valor -->
          <div class="flex items-center justify-between md:justify-end">
            <span class="font-semibold text-gray-900">{{ formatCurrency(order.valor_total) }}</span>
            <button 
              @click.stop="toggleOptions(order)"
              class="p-1 rounded hover:bg-gray-200 transition-colors"
            >
              <span class="material-icons text-gray-400">more_vert</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Estado Vazio -->
    <div v-if="orders.data.length === 0" class="text-center py-12">
      <span class="material-icons text-gray-400 text-6xl mb-4">description</span>
      <div class="text-gray-500">Nenhum pedido encontrado</div>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'

defineProps({
  orders: Object
})

const statusClasses = {
  'orcamento': 'bg-yellow-100 text-yellow-800',
  'concluido': 'bg-green-100 text-green-800',
  'cancelado': 'bg-red-100 text-red-800',
  'faturado': 'bg-blue-100 text-blue-800'
}

const viewOrder = (order) => {
  router.visit(route('pedidos.show', order.id))
}

const toggleOptions = (order) => {
  // Lógica para menu de opções
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}
</script>