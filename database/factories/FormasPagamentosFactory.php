<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormasPagamentos>
 */
class FormasPagamentosFactory extends Factory
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
            'nome' => $this->faker->word(),
            'excluido' => $this->faker->boolean(),
            'ultima_alteracao' => now()
        ];
    }
}
