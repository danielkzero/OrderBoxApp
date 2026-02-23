# Arquitetura do Sistema

## Backend (Laravel 12)
- `./app/Models` -> Modelos Eloquent (Empresas, Clientes, Pedidos, Produtos, etc.)
- `./app/Http/Controllers` -> Controladores de API e rotas Inertia.
- `./routes/web.php` -> Rotas de interface com Vue.
- `./routes/api.php` -> Rotas REST (clientes, pedidos, produtos).
- `./database/migrations` -> Estrutura de tabelas.
- `./database/factory` -> Classes para criação de base de dados fictícia.
- `./database/seeders` -> Classe para popular a base de dados teste.

## Frontend (Vue 3 + InertiaJS)
- `./resources/js/Pages` -> Páginas principais.
- `./resources/js/Components` -> Componentes reutilizáveis.
- `./resources/js/Layouts` -> Layouts (sidebar, header, etc).
- `./resources/js/Lib` -> 'Biblioteca' utilidades de uso global (formatcurrency, moment e etc.).

## Fluxo de requisição
-> Usuário -> Vue 3 (Inertia) -> Controller Laravel -> Model -> Banco MySQL -> Retorno JSON -> Inertia renderiza.
