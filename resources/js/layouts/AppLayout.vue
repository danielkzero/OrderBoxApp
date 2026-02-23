<template>
    <div class="h-screen w-screen bg-gray-100 dark:bg-gray-900 md:grid md:grid-cols-[auto_1fr]">

        <!-- Sidebar -->
        <div class="absolute z-50 bg-black/50 h-screen w-full" v-if="mobileOpen" @click="mobileOpen = !mobileOpen"></div>
        <aside :class="[
            'bg-white dark:bg-gray-800 shadow-md transition-all duration-300',
            sidebarCollapsed ? 'w-16' : 'w-60',
            // Esconde no mobile por padrão, mostra no md pra cima
            mobileOpen ? 'absolute z-50 h-full' : 'hidden md:relative md:block'
        ]" @mouseenter="toogleCollapsed(false)" @mouseleave="toogleCollapsed(true)">

            <!-- Logo + Seletor Empresa -->
            <div class="h-16 flex items-center justify-start border-b border-gray-200 dark:border-gray-700 px-4">
                <i class="bx bxs-package text-3xl text-indigo-600"></i>
                <div v-if="!sidebarCollapsed">
                    <FormField tag="select" v-model="empresa" classField="truncate w-42 ms-2" :removeClass="true"
                        class="text-gray-700 dark:text-gray-50">
                        <option :value="empresa.id" v-for="empresa in empresas" :key="empresa.id"
                            class="text-gray-700 dark:text-gray-50 bg-white dark:bg-gray-700">{{ empresa.nome }}
                        </option>
                    </FormField>
                </div>
            </div>
            <!-- Navegação -->
            <nav class="px-3 py-4 space-y-2 overflow-auto dark:text-gray-50 text-indigo-600">
                <SidebarLink :href="`${menuBasePath}/dashboard`" icon="bx bxs-dashboard" label="Dashboard"
                    :collapsed="sidebarCollapsed" />
                <SidebarLink :href="`${menuBasePath}/pedidos`" icon="bx bxs-cart" label="Pedidos"
                    :collapsed="sidebarCollapsed" />
                <SidebarLink :href="`${menuBasePath}/clientes`" icon="bx bxs-user" label="Clientes"
                    :collapsed="sidebarCollapsed" />
                <SidebarLink :href="`${menuBasePath}/produtos`" icon="bx bxs-box" label="Produtos"
                    :collapsed="sidebarCollapsed" />
            </nav>

            <!-- Rodapé do menu fixo no fundo -->
            <div
                class="absolute bottom-0 left-0 right-0 border-t border-gray-200 dark:border-gray-700 text-indigo-600 px-3 py-4 space-y-2">
                <SidebarLink :href="`${menuBasePath}/b2b`" icon="bx bxs-store" label="E-commerce B2B"
                    :collapsed="sidebarCollapsed" />
                <SidebarLink :href="`${menuBasePath}/conta`" icon="bx bxs-cog" label="Minha Conta"
                    :collapsed="sidebarCollapsed" />
            </div>
        </aside>

        <!-- Main content -->
        <div class="h-screen overflow-hidden">

            <!-- Topbar -->
            <header class="h-16 bg-white dark:bg-gray-800 shadow flex items-center justify-between px-4">
                <!-- Hamburguer (mobile) -->
                <button @click="mobileOpen = !mobileOpen"
                    class="lg:hidden p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700">
                    <i class="bx bx-menu text-2xl text-gray-800 dark:text-gray-200"></i>
                </button>

                <button @click="sidebarCollapsedMode = !sidebarCollapsedMode; toogleCollapsed(sidebarCollapsedMode)"
                    class="hidden lg:flex p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700">
                    <i class="bx bx-menu text-2xl text-gray-800 dark:text-gray-200"
                        :class="!sidebarCollapsedMode ? 'bx-menu' : 'bx-x'"></i>
                </button>

                <div class="flex items-center space-x-4 ms-auto me-4">
                    <button
                        class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-200 hover:text-indigo-600 cursor-pointer hover:scale-110 transition-all">
                        <i class="bx bxs-diamond text-lg"></i><span>Meu Plano</span>
                    </button>
                    <button
                        class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-200 hover:text-indigo-600 cursor-pointer hover:scale-110 transition-all">
                        <i class="bx bxs-book-open text-lg"></i><span>Guia Inicial</span>
                    </button>
                    <button
                        class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-200 hover:text-indigo-600 cursor-pointer hover:scale-110 transition-all">
                        <i class="bx bxs-help-circle text-lg"></i><span>Ajuda</span>
                    </button>
                </div>

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
                            Meus Dados</Link>
                            <button @click="logout"
                                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left">
                                Sair</button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="h-[calc(100%-64px)] overflow-y-auto p-4">
                <div class="overflow-x-auto w-full">
                    <slot />
                </div>
            </main>
        </div>

    </div>
</template>


<script setup>
import { computed, ref, watch } from "vue";
import { Link } from "@inertiajs/vue3";
import SidebarLink from "@/components/SidebarLink.vue";
import FormField from "@/components/FormField.vue";
import { usePage, router } from "@inertiajs/vue3";

const page = usePage();
const empresas = ref(page.props.empresas || []);

const urlParts = window.location.pathname.split('/').filter(Boolean);
const empresaIdDaUrl = Number(urlParts[0]) || (empresas.value[0]?.id || null);

const empresa = ref(empresaIdDaUrl || null);
const menuBasePath = computed(() => `/${empresa.value || empresaIdDaUrl}`);

const sidebarCollapsed = ref(false); 
const mobileOpen = ref(false);
const dropdownOpen = ref(false);
const sidebarCollapsedMode = ref(false);


function toogleCollapsed(valor) {
    if (sidebarCollapsedMode.value) {
        sidebarCollapsed.value = valor;
    } else {
        sidebarCollapsed.value = false;
    }
}

watch(empresa, (novaEmpresa) => {
    if (!novaEmpresa) return;

    const novaEmpresaId = Number(novaEmpresa);
    if (!novaEmpresaId) return;

    const current = new URL(window.location.href);
    const parts = current.pathname.split('/').filter(Boolean);

    if (parts.length > 0 && /^\d+$/.test(parts[0])) {
        parts[0] = String(novaEmpresaId);
    } else {
        parts.unshift(String(novaEmpresaId), 'dashboard');
    }

    const novoPath = `/${parts.join('/')}${current.search}${current.hash}`;
    window.location.href = novoPath;
});

function logout() {
    router.post('/logout');
}
</script>
