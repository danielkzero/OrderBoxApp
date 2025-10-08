# Estrutura do Banco de Dados

## Entidades principais

| Tabela | Descrição | Relações |
|--------|------------|-----------|
| **empresas** | Cadastro de empresas (multiempresa) | 1:N com `users`, `clientes`, `produtos`, `pedidos` |
| **users** | Usuários do sistema | FK `empresa_id` |
| **clientes** | Cadastro de clientes | 1:N com `pedidos`, `clientes_contatos ..._enderecos ..._extras ..._telefones`, `clientes_contatos_emails ..._telefones` |
| **produtos** | Produtos disponíveis para pedido | 1:N com `produtos_precos`, `produtos_grades` |
| **pedidos** | Pedidos realizados | 1:N com `pedidos_itens` e `pedidos_extras` |
| **pedidos_itens** | Itens que compõem o pedido | FK `pedido_id`, `produto_id` |
| **tabelas_precos** | Tabelas de preço por empresa | 1:N com `tabelas_precos_cidades` |
| **icms_st** | Tabelas de ICMS ST | Associada a produtos e empresas |
| **categorias** | Categorias de produtos | FK `empresa_id` |

## Tabelas auxiliares

| Tabela | Função |
|--------|---------|
| `formas_pagamentos` | Lista de formas de pagamento |
| `condicoes_pagamentos` | Condições de pagamento (ex: 30/60/90 dias) |
| `roles` | Perfis de acesso |
