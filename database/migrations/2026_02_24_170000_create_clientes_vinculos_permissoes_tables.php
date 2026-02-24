<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('clientes_categorias_permissoes')) {
            Schema::create('clientes_categorias_permissoes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('empresas', 'id', 'ccp_emp_fk')->onDelete('cascade');
                $table->foreignId('cliente_id')->constrained('clientes', 'id', 'ccp_cli_fk')->onDelete('cascade');
                $table->foreignId('categoria_id')->constrained('categorias', 'id', 'ccp_cat_fk')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['cliente_id', 'categoria_id'], 'clientes_categorias_permissoes_unique');
                $table->index(['empresa_id', 'cliente_id']);
            });
        }

        if (!Schema::hasTable('clientes_condicoes_pagamentos_permissoes')) {
            Schema::create('clientes_condicoes_pagamentos_permissoes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('empresas', 'id', 'ccpp_emp_fk')->onDelete('cascade');
                $table->foreignId('cliente_id')->constrained('clientes', 'id', 'ccpp_cli_fk')->onDelete('cascade');
                $table->foreignId('condicao_pagamento_id')->constrained('condicoes_pagamentos', 'id', 'ccpp_cond_fk')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['cliente_id', 'condicao_pagamento_id'], 'clientes_condicoes_pag_permissoes_unique');
                $table->index(['empresa_id', 'cliente_id']);
            });
        }

        if (!Schema::hasTable('clientes_tabelas_precos_permissoes')) {
            Schema::create('clientes_tabelas_precos_permissoes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('empresas', 'id', 'ctpp_emp_fk')->onDelete('cascade');
                $table->foreignId('cliente_id')->constrained('clientes', 'id', 'ctpp_cli_fk')->onDelete('cascade');
                $table->foreignId('tabela_preco_id')->constrained('tabelas_precos', 'id', 'ctpp_tab_fk')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['cliente_id', 'tabela_preco_id'], 'clientes_tabelas_precos_permissoes_unique');
                $table->index(['empresa_id', 'cliente_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes_tabelas_precos_permissoes');
        Schema::dropIfExists('clientes_condicoes_pagamentos_permissoes');
        Schema::dropIfExists('clientes_categorias_permissoes');
    }
};
