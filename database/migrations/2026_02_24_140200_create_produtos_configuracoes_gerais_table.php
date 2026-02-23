<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('produtos_configuracoes_gerais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->unsignedInteger('inativos_recentes_dias')->default(180);
            $table->unsignedInteger('inativos_antigos_dias')->default(365);
            $table->timestamp('ultima_alteracao')->nullable();
            $table->timestamps();
            $table->unique('empresa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produtos_configuracoes_gerais');
    }
};

