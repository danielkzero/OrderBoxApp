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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('icms_st_id')->constrained('icms_st')->onDelete('restrict');
            $table->string('tipo', 1)->default('J');  // J = Jurídica, F = Física
            $table->string('razao_social')->nullable();
            $table->string('nome_fantasia')->nullable();

            $table->string('cnpj', 20)->nullable();
            $table->string('inscricao_estadual', 50)->nullable();
            $table->string('suframa', 50)->nullable();

            $table->string('rua')->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cep', 20)->nullable();

            // Relacionamento com tabela de cidades IBGE
            $table->char('municipio_codigo', 7)->nullable();
            $table
                ->foreign('municipio_codigo')
                ->references('municipio_codigo')
                ->on('cidades_ibge')
                ->onDelete('restrict');

            $table->boolean('bloqueado')->default(false);
            $table->unsignedBigInteger('motivo_bloqueio_id')->nullable();

            $table->text('observacao')->nullable();

            $table->timestamp('ultima_alteracao')->nullable();
            $table->boolean('excluido')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
