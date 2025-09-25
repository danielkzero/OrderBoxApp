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
        Schema::create('variacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('nome');
            $table->integer('ordem')->default(0);
            $table->boolean('excluido')->default(false);
            $table->timestamp('ultima_alteracao')->nullable();
            $table->timestamps();
        });

        Schema::create('variacoes_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('variacao_id')->constrained('variacoes')->cascadeOnDelete();
            $table->string('nome');
            $table->string('cor', 20)->nullable();
            $table->string('imagem')->nullable();
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
        Schema::dropIfExists('variacoes');
    }
};
