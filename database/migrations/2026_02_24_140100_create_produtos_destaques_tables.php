<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('produtos_destaques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('nome');
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('excluido')->default(false);
            $table->timestamp('ultima_alteracao')->nullable();
            $table->timestamps();
        });

        Schema::create('produtos_destaques_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('destaque_id')->constrained('produtos_destaques')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->boolean('excluido')->default(false);
            $table->timestamps();

            $table->unique(['destaque_id', 'produto_id'], 'produtos_destaques_itens_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produtos_destaques_itens');
        Schema::dropIfExists('produtos_destaques');
    }
};

