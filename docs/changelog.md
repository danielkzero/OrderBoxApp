# Changelog
- O changelog foi implementado no dia 08/10/2025, por perceber necessidade do mesmo.

## [0.0.1] — 08-10-2025
### Adicionado
- Estrutura base Laravel + Inertia + Vue 3.
- Criação de tabelas principais (empresas, clientes, pedidos, produtos).
- Layout inicial com TailwindCSS.
- Sistema multiempresa básico.

### Corrigido
- Sidebar no mobile (bug de visibilidade).
- Alteração de componente 'button' por componente 'Link' do inertia e adicionando campo url nas arrays de tabs e sub-tabs.
- Corrigindo bug (aviso) de importação do componente reutilizável 'ButtonCustom'.
- Correção das urls do links para formato './página/sub-página' ... assim reconhecendo o parâmetro da url de 'empresa_id'.
- Trocando ordem de geração de seeds e deixar EmpresasSeeder no topo da lista.

### Próximos passos
- Implementar controle de usuários por empresa.
- Iniciar desenvolvimento do módulo de e-commerce.
