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
        Schema::create('clientes_configuracoes_gerais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->boolean('bloquear_duplicidade_cpf_cnpj')->default(false);
            $table->boolean('obrigar_cpf_cnpj')->default(false);
            $table->boolean('obrigar_nome_fantasia')->default(false);
            $table->boolean('obrigar_telefone')->default(false);
            $table->boolean('obrigar_email')->default(false);
            $table->boolean('obrigar_inscricao_estadual')->default(false);
            $table->boolean('obrigar_info_adicional')->default(false);
            $table->boolean('obrigar_segmento')->default(false);
            $table->boolean('obrigar_cep')->default(false);
            $table->boolean('obrigar_endereco')->default(false);
            $table->boolean('obrigar_numero')->default(false);
            $table->boolean('obrigar_complemento')->default(false);
            $table->boolean('obrigar_bairro')->default(false);
            $table->boolean('obrigar_cidade')->default(false);
            $table->boolean('obrigar_estado')->default(false);
            $table->timestamps();

            $table->unique('empresa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes_configuracoes_gerais');
    }
};

