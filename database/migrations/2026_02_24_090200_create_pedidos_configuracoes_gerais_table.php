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
        Schema::create('pedidos_configuracoes_gerais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->boolean('permitir_itens_duplicados')->default(false);
            $table->boolean('nao_permitir_preco_zerado')->default(false);
            $table->boolean('obrigar_transportadora')->default(false);
            $table->boolean('obrigar_valor_frete')->default(false);
            $table->timestamps();

            $table->unique('empresa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos_configuracoes_gerais');
    }
};

