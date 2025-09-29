<template>
    <div class="flex h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Sidebar -->
        <aside :class="[
            'bg-white dark:bg-gray-800 shadow-md flex flex-col transition-all duration-300',
            sidebarCollapsed ? 'w-16' : 'w-60',
            mobileOpen ? 'absolute z-50 h-full' : 'relative'
        ]" @mouseenter="toogleCollapsed(false)" @mouseleave="toogleCollapsed(true)">
            <!-- Logo + Seletor Empresa -->
            <div class="h-16 flex items-center justify-start border-b border-gray-200 dark:border-gray-700 px-4">
                <i class="bx bxs-package text-3xl text-indigo-600"></i>
                <!-- Seletor de empresa -->
                <div v-if="!sidebarCollapsed">
                    <FormField tag="select" v-model="empresa" classField="truncate w-42 ms-2" :removeClass="true" class="text-gray-700">
                        <option :value="empresa.id" v-for="empresa in empresas" :key="empresa.id">{{ empresa.nome }}</option>
                    </FormField>
                </div>
            </div>

            <!-- Navegação -->
            <nav class="flex-1 px-3 py-4 space-y-2">
                <SidebarLink href="/" icon="bx bx-home" label="Dashboard" :collapsed="sidebarCollapsed" />
                <SidebarLink href="/pedidos" icon="bx bx-cart" label="Pedidos" :collapsed="sidebarCollapsed" />
                <SidebarLink href="/clientes" icon="bx bx-user" label="Clientes" :collapsed="sidebarCollapsed" />
                <SidebarLink href="/produtos" icon="bx bx-box" label="Produtos" :collapsed="sidebarCollapsed" />
            </nav>

            <!-- Rodapé do menu -->
            <div class="border-t border-gray-200 dark:border-gray-700 px-3 py-4 space-y-2">
                <SidebarLink href="/b2b" icon="bx bx-store" label="E-commerce B2B" :collapsed="sidebarCollapsed" />
                <SidebarLink href="/conta" icon="bx bx-cog" label="Minha Conta" :collapsed="sidebarCollapsed" />
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col">
            <!-- Topbar -->
            <header class="h-16 bg-white dark:bg-gray-800 shadow flex items-center justify-between px-4">
                <!-- Hamburguer (mobile) -->
                <button @click="mobileOpen = !mobileOpen"
                    class="lg:hidden p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700">
                    <i class="bx bx-menu text-2xl text-gray-800 dark:text-gray-200"></i>
                </button>

                 <button @click="sidebarCollapsedMode = !sidebarCollapsedMode; toogleCollapsed(sidebarCollapsedMode)"
                    class="hidden lg:flex p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700">
                    <i class="bx bx-menu text-2xl text-gray-800 dark:text-gray-200" :class="!sidebarCollapsedMode ? 'bx-menu' : 'bx-x'"></i>
                </button>

                <!-- Menus esquerda -->
                <div class="flex items-center space-x-4 ms-auto me-4">
                    <button
                        class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-200 hover:text-indigo-600 cursor-pointer hover:scale-110 transition-all">
                        <i class="bx bxs-diamond text-lg"></i>
                        <span>Meu Plano</span>
                    </button>

                    <button
                        class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-200 hover:text-indigo-600 cursor-pointer hover:scale-110 transition-all">
                        <i class="bx bxs-book-open text-lg"></i>
                        <span>Guia Inicial</span>
                    </button>

                    <button
                        class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-200 hover:text-indigo-600 cursor-pointer hover:scale-110 transition-all">
                        <i class="bx bxs-help-circle text-lg"></i>
                        <span>Ajuda</span>
                    </button>
                </div>


                <!-- Menus direita -->
                <div class="flex items-center space-x-4">
                    <!-- Notificações -->
                    <button class="relative">
                        <i class="bx bx-bell text-2xl text-gray-700 dark:text-gray-200"></i>
                        <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                    </button>

                    <!-- Avatar + Dropdown -->
                    <div class="relative" @click="dropdownOpen = !dropdownOpen">
                        <img src="https://i.pravatar.cc/40" alt="avatar" class="w-8 h-8 rounded-full cursor-pointer" />
                        <div v-if="dropdownOpen"
                            class="absolute right-0 mt-2 w-40 bg-white dark:bg-gray-800 rounded shadow-lg py-2 z-50">
                            <Link href="/meus-dados"
                                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                            Meus Dados
                            </Link>
                            <Link href="/logout"
                                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                            Sair
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-4">                
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";
import SidebarLink from "@/components/SidebarLink.vue";
import FormField from "@/components/FormField.vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();

const empresas = page.props.empresas;

const sidebarCollapsed = ref(false); // slim mode padrão
const mobileOpen = ref(false);
const dropdownOpen = ref(false);
const sidebarCollapsedMode = ref(false);
const empresa = ref('hydradigital');

function toogleCollapsed(valor) {
    if (sidebarCollapsedMode.value) {
        sidebarCollapsed.value = valor;
    } else {
        sidebarCollapsed.value = false;
    }
}
</script>
