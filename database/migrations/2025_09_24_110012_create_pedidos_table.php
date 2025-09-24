<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->unsignedBigInteger('cliente_id');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');

            $table->unsignedBigInteger('forma_pagamento_id')->nullable();
            $table->foreign('forma_pagamento_id')->references('id')->on('formas_pagamento')->onDelete('set null');

            $table->unsignedBigInteger('condicao_pagamento_id')->nullable();
            $table->foreign('condicao_pagamento_id')->references('id')->on('condicoes_pagamento')->onDelete('set null');

            $table->unsignedBigInteger('transportadora_id')->nullable();  // Se houver tabela de transportadoras
            $table->string('nome_contato')->nullable();
            $table->string('status', 10)->nullable();
            $table->string('status_b2b', 10)->nullable();
            $table->string('status_faturamento', 10)->nullable();
            $table->string('cupom_de_desconto')->nullable();
            $table->decimal('valor_frete', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->default(0);

            $table->string('transportadora_nome')->nullable();

            $table->date('data_emissao')->nullable();
            $table->timestamp('data_criacao')->nullable();
            $table->timestamp('ultima_alteracao')->nullable();

            $table->unsignedBigInteger('criador_id')->nullable();  // FK para usuário/funcionário

            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
