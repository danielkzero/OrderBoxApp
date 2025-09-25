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
        Schema::create('pedidos_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->unsignedBigInteger('pedido_id');
            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');

            $table->unsignedBigInteger('produto_id');
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('restrict');

            $table->unsignedBigInteger('tabela_preco_id')->nullable();
            $table->foreign('tabela_preco_id')->references('id')->on('tabelas_precos')->onDelete('set null');

            $table->decimal('preco_tabela', 12, 2)->nullable();
            $table->decimal('preco_liquido', 12, 2)->nullable();
            $table->decimal('ipi', 12, 2)->nullable();
            $table->decimal('st', 12, 2)->nullable();
            $table->decimal('subtotal', 12, 2)->nullable();

            $table->integer('quantidade')->default(1);
            $table->json('quantidade_grades')->nullable();
            $table->text('observacoes')->nullable();

            $table->json('descontos_do_vendedor')->nullable();
            $table->json('descontos_de_promocoes')->nullable();
            $table->json('descontos_de_politicas')->nullable();
            $table->decimal('desconto_de_cupom', 12, 2)->nullable();

            $table->boolean('excluido')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos_itens');
    }
};
