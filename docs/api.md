# Rotas Web / API — OrderBox

> **Framework:** Laravel 12  
> **Frontend:** Inertia + Vue 3  
> **Autenticação:** Middleware `auth`  
> **Prefixo de empresa:** todas as rotas autenticadas contêm o parâmetro `{empresa}`.

---

## Rotas públicas

| Método | Rota | Controller / Ação | Descrição |
|--------|------|--------------------|------------|
| `GET` | `/` | Inertia render (`Welcome`) | Página inicial (landing / boas-vindas) |
| `GET` | `/login` | `AuthController@showLogin` | Exibe formulário de login |
| `POST` | `/login` | `AuthController@login` | Realiza autenticação do usuário |
| `POST` | `/logout` | `AuthController@logout` | Efetua logout do usuário autenticado |

---

## Rotas autenticadas (middleware `auth`)

> Todas as rotas abaixo exigem autenticação.  
> O parâmetro `{empresa}` deve ser um ID numérico válido.

| Método | Rota | Controller / Ação | Descrição |
|--------|------|--------------------|------------|
| `GET` | `/{empresa}/` | `Inertia render (Pedidos/Index)` | Página inicial do módulo de pedidos |
| `GET` | `/{empresa}/pedidos` | `PedidosController@index` | Lista pedidos da empresa |
| `GET` | `/{empresa}/pedidos/{id}` | `PedidosController@show` | Exibe detalhes de um pedido |
| `POST` | `/{empresa}/pedidos` | `PedidosController@store` | Cria novo pedido |
| `PUT/PATCH` | `/{empresa}/pedidos/{id}` | `PedidosController@update` | Atualiza pedido existente |
| `DELETE` | `/{empresa}/pedidos/{id}` | `PedidosController@destroy` | Exclui pedido |

| Método | Rota | Controller / Ação | Descrição |
|--------|------|--------------------|------------|
| `GET` | `/{empresa}/clientes` | `ClientesController@index` | Lista clientes da empresa |
| `GET` | `/{empresa}/clientes/{id}` | `ClientesController@show` | Exibe cliente |
| `POST` | `/{empresa}/clientes` | `ClientesController@store` | Cria cliente |
| `PUT/PATCH` | `/{empresa}/clientes/{id}` | `ClientesController@update` | Atualiza cliente |
| `DELETE` | `/{empresa}/clientes/{id}` | `ClientesController@destroy` | Exclui cliente |

| Método | Rota | Controller / Ação | Descrição |
|--------|------|--------------------|------------|
| `GET` | `/{empresa}/produtos` | `ProdutosController@index` | Lista produtos disponíveis |
| `GET` | `/{empresa}/produtos/{id}` | `ProdutosController@show` | Detalha produto |
| `POST` | `/{empresa}/produtos` | `ProdutosController@store` | Cria novo produto |
| `PUT/PATCH` | `/{empresa}/produtos/{id}` | `ProdutosController@update` | Atualiza produto |
| `DELETE` | `/{empresa}/produtos/{id}` | `ProdutosController@destroy` | Exclui produto |

| Método | Rota | Controller / Ação | Descrição |
|--------|------|--------------------|------------|
| `GET` | `/{empresa}/dashboard` | `DashboardController@index` | Exibe painel de controle da empresa |

---

## Observações técnicas

- O parâmetro `{empresa}` é validado por `->where('empresa', '[0-9]+')` para evitar acesso inválido.  
- A autenticação é feita via middleware padrão `auth`.  
- As páginas são renderizadas com **InertiaJS**, enviando dados do Laravel direto para componentes Vue.  
- No futuro, as rotas podem ser migradas para `/api/(***versão***)/{empresa}/...` para atender um app mobile ou e-commerce.  

---

## Próximas adições planejadas

- Rotas REST para integração com e-commerce (`/api/(***versão***)/public/pedidos`, `/api/(***versão***)/public/produtos`)  
- Middleware para controle de acesso por `role` (admin, vendedor, cliente)  
- Endpoint de sincronização (para futura função offline ou ajustes de rota)
