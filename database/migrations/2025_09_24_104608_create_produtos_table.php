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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');

            $table->string('codigo')->nullable();
            $table->string('nome');
            $table->text('observacoes')->nullable();

            $table->decimal('preco_tabela', 12, 2)->nullable();
            $table->decimal('preco_minimo', 12, 2)->nullable();

            $table->decimal('ipi', 12, 2)->nullable();
            $table->string('tipo_ipi', 5)->nullable();

            $table->decimal('comissao', 8, 2)->nullable();

            $table->decimal('saldo_estoque', 12, 2)->nullable();
            $table->decimal('st', 12, 2)->nullable();

            $table->boolean('ativo')->default(true);
            $table->boolean('excluido')->default(false);

            $table->string('moeda', 5)->default('0');
            $table->string('codigo_ncm', 20)->nullable();

            $table->integer('multiplo')->nullable();

            $table->decimal('peso_bruto', 10, 3)->nullable();
            $table->decimal('largura', 10, 3)->nullable();
            $table->decimal('altura', 10, 3)->nullable();
            $table->decimal('comprimento', 10, 3)->nullable();

            $table->boolean('peso_dimensoes_unitario')->default(false);
            $table->boolean('exibir_no_b2b')->default(false);

            $table->string('unidade', 10)->nullable();
            $table->timestamp('ultima_alteracao')->nullable();

            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
