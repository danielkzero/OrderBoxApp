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
        $tables = [
            'clientes_tags',
            'clientes_segmentos',
            'clientes_redes',
            'clientes_excecoes_fiscais',
            'clientes_resultados_atendimentos',
            'motivos_bloqueios',
        ];

        foreach ($tables as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
                $table->string('nome');
                $table->unsignedInteger('ordem')->default(0);
                $table->boolean('ativo')->default(true);
                $table->boolean('excluido')->default(false);
                $table->timestamp('ultima_alteracao')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motivos_bloqueios');
        Schema::dropIfExists('clientes_resultados_atendimentos');
        Schema::dropIfExists('clientes_excecoes_fiscais');
        Schema::dropIfExists('clientes_redes');
        Schema::dropIfExists('clientes_segmentos');
        Schema::dropIfExists('clientes_tags');
    }
};

