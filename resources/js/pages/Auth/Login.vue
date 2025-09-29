<template>
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="w-full max-w-md bg-white shadow rounded-lg p-6">
            <h1 class="text-2xl font-bold mb-6 text-center">Login</h1>

            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="block text-gray-700">Email</label>
                    <input v-model="form.email" type="email"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                    <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Senha</label>
                    <input v-model="form.password" type="password"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                    <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">
                        {{ form.errors.password }}
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg">
                    Entrar
                </button>
            </form>
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
