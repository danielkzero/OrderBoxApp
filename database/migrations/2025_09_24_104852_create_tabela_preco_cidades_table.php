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
        Schema::create('tabelas_precos_cidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->unsignedBigInteger('tabela_preco_id');
            $table->char('municipio_codigo', 7);

            $table->timestamp('ultima_alteracao')->nullable();
            $table->boolean('excluido')->default(false);

            $table
                ->foreign('tabela_preco_id')
                ->references('id')
                ->on('tabelas_precos')
                ->onDelete('cascade');

            $table
                ->foreign('municipio_codigo')
                ->references('municipio_codigo')
                ->on('cidades_ibge')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tabelas_precos_cidades');
    }
};
