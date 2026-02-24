<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('produtos_estoque_movimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 20); // entrada | saida
            $table->decimal('quantidade', 12, 2);
            $table->decimal('saldo_anterior', 12, 2)->nullable();
            $table->decimal('saldo_atual', 12, 2)->nullable();
            $table->text('observacoes')->nullable();
            $table->string('origem', 30)->default('manual');
            $table->timestamps();

            $table->index(['empresa_id', 'produto_id']);
            $table->index(['empresa_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produtos_estoque_movimentos');
    }
};
