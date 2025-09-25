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
        Schema::create('pedidos_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->unsignedBigInteger('pedido_id');
            $table->string('nome');
            $table->string('tipo', 10)->nullable();

            $table->text('valor_texto')->nullable();
            $table->date('valor_data')->nullable();
            $table->decimal('valor_decimal', 15, 2)->nullable();
            $table->decimal('valor_hora', 10, 2)->nullable();
            $table->json('valor_lista')->nullable();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos_extras');
    }
};
