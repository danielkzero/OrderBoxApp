<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Empresas;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Variacoes>
 */
class VariacoesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id' => Empresas::inRandomOrder()->first()->id ?? Empresas::factory()->create()->id,
            'nome' => $this->faker->word(),
            'ordem' => $this->faker->numberBetween(0, 10),
            'excluido' => $this->faker->boolean(10), // 10% excluido
            'ultima_alteracao' => now(),
        ];
    }
}
