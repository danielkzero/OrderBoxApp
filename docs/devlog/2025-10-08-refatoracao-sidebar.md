# Refatoração — Sidebar (menu lateral)
**Data:** 08/10/2025  
**Autor:** Daniel Ramos  
**Contexto:** Ajuste de comportamento do menu lateral para dispositivos mobile.

---

## Objetivo
Melhorar o comportamento da sidebar em dispositivos **mobile**, garantindo que:
- Por padrão, o menu fique **escondido no mobile** (`hidden`).
- No desktop (`md:` em diante), o menu se comporte normalmente.
- A transição entre estados (abrir/fechar) seja suave e responsiva.
- O colapso automático no desktop continue funcionando com `mouseenter` / `mouseleave`.

---

## Problema identificado
Anteriormente, a sidebar apresentava os seguintes problemas:
- No mobile, ela aparecia parcialmente aberta ao carregar a página.  
- O estado `mobileOpen` e `sidebarCollapsed` se sobrepunham, causando bugs visuais.  
- Breakpoints Tailwind (`md:`) não estavam sendo aplicados corretamente em conjunto com as classes dinâmicas do Vue.  

---

## Solução implementada
Foi realizada uma refatoração completa do componente `<Sidebar>`:

### 📄 Estrutura Vue 3
```vue
<aside
  :class="[
    'bg-white dark:bg-gray-800 shadow-md transition-all duration-300',
    sidebarCollapsed ? 'w-16' : 'w-60',
    mobileOpen 
      ? 'absolute z-50 h-full left-0 top-0 md:relative' 
      : 'hidden md:block'
  ]"
  @mouseenter="toggleCollapsed(false)"
  @mouseleave="toggleCollapsed(true)"
>
</aside>
