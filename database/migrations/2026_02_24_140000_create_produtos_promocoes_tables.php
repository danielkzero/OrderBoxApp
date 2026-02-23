<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('produtos_promocoes', function (Blueprint $table) {
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

        Schema::create('produtos_promocoes_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('promocao_id')->constrained('produtos_promocoes')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->decimal('desconto_percentual', 8, 4)->nullable();
            $table->boolean('excluido')->default(false);
            $table->timestamps();

            $table->unique(['promocao_id', 'produto_id'], 'produtos_promocoes_itens_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produtos_promocoes_itens');
        Schema::dropIfExists('produtos_promocoes');
    }
};

