
<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex flex-col">
    <!-- Navbar fixa -->
    <header ref="headerRef" class="fixed top-0 left-0 w-full bg-gradient-to-r from-indigo-600 to-indigo-500 text-white shadow-lg z-50">
      <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-2">
          <i class="bx bxs-package text-2xl"></i>
          <h1 class="text-xl font-bold">OrderBox</h1>
        </div>
        <nav class="flex items-center space-x-6 text-sm font-medium">
          <a href="#sobre" class="hover:underline">Sobre</a>
          <a href="#funcionalidades" class="hover:underline">Funcionalidades</a>
          <button
            @click="$inertia.visit('/login')"
            class="bg-white text-indigo-600 px-4 py-2 rounded-lg shadow hover:bg-gray-100 transition cursor-pointer"
          >
            Login
          </button>
        </nav>
      </div>
    </header>

    <section class="flex-1 flex items-center justify-center px-6 py-24 pt-40 bg-gradient-to-br from-indigo-600 to-indigo-500 text-white">
      <div class="max-w-3xl text-center">
        <h2 class="text-4xl sm:text-6xl font-extrabold mb-6">Organize suas vendas com o <span class="underline">OrderBox</span></h2>
        <p class="text-lg sm:text-xl text-indigo-100 mb-8">
          O OrderBox é a solução de força de vendas que ajuda sua equipe a vender mais, gerenciar clientes e acompanhar pedidos em tempo real.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <button
            @click="$inertia.visit('/login')"
            class="bg-white text-indigo-600 font-medium px-6 py-3 rounded-lg shadow-lg hover:bg-gray-100 transition flex items-center justify-center space-x-2 cursor-pointer"
          >
            <i class="bx bx-log-in"></i>
            <span>Entrar no sistema</span>
          </button>
          <a
            href="#funcionalidades"
            class="bg-indigo-700 hover:bg-indigo-800 text-white px-6 py-3 rounded-lg shadow transition flex items-center justify-center space-x-2"
          >
            <i class="bx bx-info-circle"></i>
            <span>Saiba mais</span>
          </a>
        </div>
      </div>
    </section>

    <!-- Sobre (id para âncora) -->
    <section id="sobre" class="py-24 px-6 bg-white dark:bg-gray-800">
      <div class="max-w-4xl mx-auto text-center">
        <h3 class="text-3xl font-bold mb-6 text-gray-800 dark:text-gray-100">O que é o OrderBox?</h3>
        <p class="text-lg text-gray-600 dark:text-gray-300">
          O <strong>OrderBox</strong> é um sistema moderno de <em>força de vendas</em> desenvolvido para empresas que precisam de controle total sobre pedidos, clientes e performance da equipe.
        </p>
      </div>
    </section>

    <!-- Funcionalidades -->
    <section id="funcionalidades" class="py-24 px-6 bg-gray-100 dark:bg-gray-900">
      <div class="max-w-6xl mx-auto">
        <h3 class="text-3xl font-bold text-center mb-12 text-gray-800 dark:text-gray-100">Funcionalidades</h3>
        <div class="grid md:grid-cols-3 gap-8">
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 text-center">
            <i class="bx bx-cart text-4xl text-indigo-600 mb-4"></i>
            <h4 class="text-xl font-semibold mb-2">Gestão de Pedidos</h4>
            <p class="text-gray-600 dark:text-gray-300">Cadastre, acompanhe e gerencie pedidos em tempo real.</p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 text-center">
            <i class="bx bx-user text-4xl text-indigo-600 mb-4"></i>
            <h4 class="text-xl font-semibold mb-2">Controle de Clientes</h4>
            <p class="text-gray-600 dark:text-gray-300">Organize sua carteira de clientes de forma simples e eficiente.</p>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 text-center">
            <i class="bx bx-line-chart text-4xl text-indigo-600 mb-4"></i>
            <h4 class="text-xl font-semibold mb-2">Dashboard Inteligente</h4>
            <p class="text-gray-600 dark:text-gray-300">Monitore métricas de vendas e performance da equipe.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Final -->
    <section class="py-20 text-center bg-gradient-to-r from-indigo-600 to-indigo-500 text-white">
      <h3 class="text-3xl font-bold mb-6">Pronto para aumentar suas vendas?</h3>
      <button
        @click="$inertia.visit('/login')"
        class="bg-white text-indigo-600 px-6 py-3 rounded-lg shadow-lg hover:bg-gray-100 transition flex items-center justify-center space-x-2 mx-auto"
      >
        <i class="bx bx-log-in"></i>
        <span>Faça login agora</span>
      </button>
    </section>

    <footer class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">
      © {{ new Date().getFullYear() }} OrderBox - Todos os direitos reservados
    </footer>
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref, nextTick } from 'vue'

const headerRef = ref(null)

function onDocumentClick(e) {
  // pega o <a> mais próximo do alvo do clique
  const a = e.target.closest && e.target.closest('a[href^="#"]')
  if (!a) return

  const href = a.getAttribute('href')
  if (!href || href === '#') return

  const id = href.slice(1)
  const el = document.getElementById(id)
  if (!el) return

  e.preventDefault()

  const headerEl = headerRef.value || document.querySelector('header')
  const headerHeight = headerEl ? headerEl.getBoundingClientRect().height : 0
  const gap = 12 // espaço extra acima da seção
  const top = window.pageYOffset + el.getBoundingClientRect().top - headerHeight - gap

  // atualiza a hash (sem provocar jump) e faz o scroll suave
  try {
    history.pushState(null, '', '#' + id)
  } catch (err) {
    // fallback se history.pushState não funcionar
    location.hash = '#' + id
  }

  window.scrollTo({ top, behavior: 'smooth' })
}

onMounted(() => {
  // intercepta cliques no documento (inclui links do menu)
  document.addEventListener('click', onDocumentClick)

  // Se a página foi aberta com hash (ex: /#funcionalidades), fazemos o scroll suave
  nextTick(() => {
    const hash = window.location.hash
    if (hash) {
      const id = hash.replace('#', '')
      const el = document.getElementById(id)
      if (el) {
        const headerEl = headerRef.value || document.querySelector('header')
        const headerHeight = headerEl ? headerEl.getBoundingClientRect().height : 0
        const gap = 12
        const top = window.pageYOffset + el.getBoundingClientRect().top - headerHeight - gap
        // delay pequeno para garantir layout
        setTimeout(() => window.scrollTo({ top, behavior: 'smooth' }), 60)
      }
    }
  })
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocumentClick)
})
</script>

<style>
/* garante comportamento suave em navegadores que suportam CSS (fallback útil) */
html { scroll-behavior: smooth; }
</style>
