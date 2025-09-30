<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TabelasPrecosCidades>
 */
class TabelasPrecosCidadesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id' => \App\Models\Empresas::inRandomOrder()->first()->id,
            'tabela_preco_id' => \App\Models\TabelasPrecos::inRandomOrder()->first()->id,
            'municipio_codigo' => \App\Models\CidadesIbge::inRandomOrder()->first()->municipio_codigo,
            'ultima_alteracao' => now()
        ];
    }
}
