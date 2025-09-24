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
        Schema::create('cidades_ibge', function (Blueprint $table) {
            $table->id();
            $table->char('uf_codigo', 2)->nullable();
            $table->string('uf_nome')->nullable();

            $table->char('meso_codigo', 4)->nullable();
            $table->string('meso_nome')->nullable();

            $table->char('micro_codigo', 5)->nullable();
            $table->string('micro_nome')->nullable();

            $table->char('municipio_codigo', 7)->unique();
            $table->string('municipio_nome')->nullable();

            $table->integer('pop_urbana')->nullable();
            $table->integer('pop_urbana_central')->nullable();
            $table->integer('pop_rural')->nullable();

            $table->float('area_km2')->nullable();
            $table->float('pop_km2')->nullable();

            $table->string('lat', 30)->nullable();
            $table->string('lon', 30)->nullable();

            $table->integer('pop_total')->nullable();
            $table->longText('svg')->nullable();
            $table->longText('path')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cidades_ibge');
    }
};
