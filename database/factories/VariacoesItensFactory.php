<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Variacoes;
use App\Models\Empresas;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VariacoesItens>
 */
class VariacoesItensFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $variacoes = Variacoes::inRandomOrder()->first() ?? Variacoes::factory()->create();

        return [
            'empresa_id' => $variacoes->empresa_id,
            'variacao_id' => $variacoes->id,
            'nome' => $this->faker->word(),
            'cor' => $this->faker->optional()->safeColorName(),
            'imagem' => $this->faker->optional()->imageUrl(200, 200),
            'excluido' => $this->faker->boolean(10),
            'ultima_alteracao' => now(),
        ];
    }
}
