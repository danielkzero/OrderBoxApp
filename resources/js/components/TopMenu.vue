<template>
  <header class="bg-white border-b border-gray-200">
    <div class="flex items-center justify-between px-6 py-4">
      <!-- Lado Esquerdo -->
      <div class="flex items-center space-x-4">
        <button @click="toggleSidebar" class="lg:hidden p-2 rounded-md text-gray-600 hover:bg-gray-100">
          <span class="material-icons">menu</span>
        </button>
        
        <div class="hidden md:flex items-center space-x-4">
          <Link 
            v-for="link in topLinks" 
            :key="link.name"
            :href="link.href"
            class="text-sm text-gray-600 hover:text-gray-900"
          >
            {{ link.name }}
          </Link>
        </div>
      </div>

      <!-- Lado Direito -->
      <div class="flex items-center space-x-4">
        <!-- Busca -->
        <div class="hidden md:block">
          <div class="relative">
            <input 
              type="text" 
              placeholder="Busca rápida..." 
              class="w-64 pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
            <span class="absolute right-3 top-2 material-icons text-gray-400">search</span>
          </div>
        </div>

        <!-- Notificações -->
        <button class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
          <span class="material-icons">notifications</span>
          <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
            34
          </span>
        </button>

        <!-- Perfil do Usuário -->
        <div class="relative">
          <button @click="toggleUserMenu" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100">
            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
              {{ user.initials }}
            </div>
            <span class="material-icons text-gray-600">expand_more</span>
          </button>

          <!-- Dropdown Menu -->
          <div v-show="userMenuOpen" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
            <div class="px-4 py-2 border-b border-gray-100">
              <div class="font-medium text-gray-900">{{ user.name }}</div>
            </div>
            <Link :href="route('profile.edit')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
              Meus Dados
            </Link>
            <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 border-t border-gray-100">
              Sair
            </Link>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

defineProps({
  user: Object
})

const userMenuOpen = ref(false)
const sidebarOpen = ref(false)

const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value
}

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
  // Emitir evento para controlar sidebar mobile
}

const topLinks = [
  { name: 'Meu plano', href: '/plano' },
  { name: 'Guia Inicial', href: '/guia-inicial' },
  { name: 'Ajuda', href: '/ajuda' }
]
</script>