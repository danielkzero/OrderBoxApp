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
        Schema::create('cliente_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->unsignedBigInteger('cliente_id');

            $table->unsignedBigInteger('campo_extra_id');
            $table->string('nome');
            $table->string('tipo', 10)->nullable();

            $table->text('valor_texto')->nullable();
            $table->date('valor_data')->nullable();
            $table->decimal('valor_decimal', 15, 2)->nullable();
            $table->string('nome_arquivo')->nullable();
            $table->string('valor_arquivo')->nullable();

            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente_extras');
    }
};
