<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('produtos_configuracoes_gerais', function (Blueprint $table) {
            $table->boolean('gerenciar_estoque')->default(false)->after('inativos_antigos_dias');
        });
    }

    public function down(): void
    {
        Schema::table('produtos_configuracoes_gerais', function (Blueprint $table) {
            $table->dropColumn('gerenciar_estoque');
        });
    }
};
