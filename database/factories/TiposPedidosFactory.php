<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TiposPedidos>
 */
class TiposPedidosFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->word(),
            'excluido' => 0,
            'empresa_id' => \App\Models\Empresas::inRandomOrder()->first()->id ?? \App\Models\Empresas::factory()->create()->id,
            'ultima_alteracao' => now()
        ];
    }
}
