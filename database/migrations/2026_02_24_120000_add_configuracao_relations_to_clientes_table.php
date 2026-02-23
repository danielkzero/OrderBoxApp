<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'tag_id')) {
                $table->unsignedBigInteger('tag_id')->nullable()->after('municipio_codigo');
                $table->foreign('tag_id')->references('id')->on('clientes_tags')->nullOnDelete();
            }

            if (!Schema::hasColumn('clientes', 'segmento_id')) {
                $table->unsignedBigInteger('segmento_id')->nullable()->after('tag_id');
                $table->foreign('segmento_id')->references('id')->on('clientes_segmentos')->nullOnDelete();
            }

            if (!Schema::hasColumn('clientes', 'rede_id')) {
                $table->unsignedBigInteger('rede_id')->nullable()->after('segmento_id');
                $table->foreign('rede_id')->references('id')->on('clientes_redes')->nullOnDelete();
            }

            if (!Schema::hasColumn('clientes', 'excecao_fiscal_id')) {
                $table->unsignedBigInteger('excecao_fiscal_id')->nullable()->after('rede_id');
                $table->foreign('excecao_fiscal_id')->references('id')->on('clientes_excecoes_fiscais')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (Schema::hasColumn('clientes', 'excecao_fiscal_id')) {
                $table->dropForeign(['excecao_fiscal_id']);
                $table->dropColumn('excecao_fiscal_id');
            }

            if (Schema::hasColumn('clientes', 'rede_id')) {
                $table->dropForeign(['rede_id']);
                $table->dropColumn('rede_id');
            }

            if (Schema::hasColumn('clientes', 'segmento_id')) {
                $table->dropForeign(['segmento_id']);
                $table->dropColumn('segmento_id');
            }

            if (Schema::hasColumn('clientes', 'tag_id')) {
                $table->dropForeign(['tag_id']);
                $table->dropColumn('tag_id');
            }
        });
    }
};
