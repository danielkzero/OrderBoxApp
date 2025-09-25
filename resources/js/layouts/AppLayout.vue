<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <Sidebar :user="user" :company="company" />
    
    <!-- Conteúdo Principal -->
    <div class="lg:ml-64">
      <!-- Top Menu -->
      <TopMenu :user="user" :company="company" />
      
      <!-- Conteúdo da Página -->
      <main class="p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '@/Components/Sidebar.vue'
import TopMenu from '@/Components/TopMenu.vue'

defineProps({
  user: Object,
  company: Object
})
</script>