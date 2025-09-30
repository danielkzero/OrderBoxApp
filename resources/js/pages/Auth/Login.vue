<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 p-6">
    <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
      
      <!-- Topo com logo -->
      <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 text-white py-6 px-6 flex items-center space-x-3">
        <i class="bx bxs-package text-3xl"></i>
        <h1 class="text-xl font-bold">OrderBox</h1>
      </div>

      <!-- Formulário -->
      <div class="p-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 text-center mb-6">
          Bem-vindo de volta
        </h2>

        <form @submit.prevent="submit" class="space-y-4">
          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Email</label>
            <div class="relative">
              <i class="bx bx-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
              <input
                v-model="form.email"
                type="email"
                placeholder="seu@email.com"
                class="w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 
                       bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100
                       focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                required
              />
            </div>
            <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
          </div>

          <!-- Senha -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Senha</label>
            <div class="relative">
              <i class="bx bx-lock-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
              <input
                v-model="form.password"
                type="password"
                placeholder="********"
                class="w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 
                       bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100
                       focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                required
              />
            </div>
            <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
          </div>

          <!-- Botão -->
          <button
            type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg font-medium 
                   flex items-center justify-center space-x-2 transition-colors"
            :disabled="form.processing"
          >
            <i class="bx bx-log-in"></i>
            <span v-if="!form.processing">Entrar</span>
            <span v-else>Carregando...</span>
          </button>
        </form>

        <!-- Links extras -->
        <div class="mt-6 text-center text-sm">
          <a href="#" class="text-indigo-600 dark:text-indigo-400 hover:underline">Esqueceu a senha?</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  email: '',
  password: ''
})

const submit = () => {
  form.post('/login')
}
</script>
