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
        Schema::create('icms_st', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('codigo_ncm', 20);
            $table->string('nome_excecao_fiscal')->nullable();
            $table->char('estado_destino', 2);  // Ex: SP, SC
            $table->string('tipo_st', 10)->nullable();  // Ex: MVA, PMC

            $table->decimal('valor_mva', 10, 4)->nullable();
            $table->decimal('valor_pmc', 10, 4)->nullable();

            $table->decimal('icms_credito', 10, 4)->nullable();
            $table->decimal('icms_destino', 10, 4)->nullable();

            $table->string('preco_considerado_no_calculo')->nullable();  // ex: "TABELA_DE_PRECO"
            $table->decimal('reducao_de_base', 10, 4)->nullable();

            $table->boolean('excluido')->default(false);
            $table->timestamp('ultima_alteracao')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('icms_st');
    }
};
