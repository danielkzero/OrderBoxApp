<template>
  <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform lg:translate-x-0 lg:static lg:inset-0">
    <div class="flex flex-col h-full">
      <!-- Logo e Nome da Empresa -->
      <div class="flex items-center p-4 border-b border-gray-200">
        <span class="font-semibold text-gray-800">{{ company.name }}</span>
      </div>

      <!-- Menu de Navegação -->
      <nav class="flex-1 p-4 space-y-2">
        <Link 
          v-for="item in menuItems" 
          :key="item.name"
          :href="item.href"
          :class="[
            'flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors',
            item.active 
              ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' 
              : 'text-gray-600 hover:bg-gray-100'
          ]"
        >
          <span class="material-icons text-lg mr-3">{{ item.icon }}</span>
          <span>{{ item.name }}</span>
          <span v-if="item.badge" class="ml-auto bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
            {{ item.badge }}
          </span>
        </Link>
      </nav>

      <!-- Rodapé do Sidebar -->
      <div class="p-4 border-t border-gray-200">
        <Link 
          :href="route('profile.edit')"
          class="flex items-center px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100"
        >
          <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold mr-3">
            {{ user.initials }}
          </div>
          <div>
            <div class="font-medium">{{ user.name }}</div>
            <div class="text-xs text-gray-500">Minha conta</div>
          </div>
        </Link>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  user: Object,
  company: Object
})

const menuItems = [
  { name: 'Indicadores', href: '/indicadores', icon: '📊', active: false },
  { name: 'Pedidos', href: '/pedidos', icon: '📋', active: true },
  { name: 'Clientes', href: '/clientes', icon: '👥', active: false },
  { name: 'Produtos', href: '/produtos', icon: '📦', active: false },
  { name: 'Portal', href: '/portal', icon: '🌐', active: false, badge: 'Novo' },
  { name: 'Agenda', href: '/agenda', icon: '📅', active: false },
]
</script>