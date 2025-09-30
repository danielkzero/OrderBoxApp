<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProdutosGrades>
 */
class ProdutosGradesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id' => \App\Models\Empresas::inRandomOrder()->first()->id ?? \App\Models\Empresas::factory()->create()->id,
            'produto_id' => \App\Models\Produtos::inRandomOrder()->first()->id ?? \App\Models\Produtos::factory()->create()->id,
            'codigo' => $this->faker->unique()->bothify('##.###'),
            'ativo' => 0,
            'exibir_no_b2b' => $this->faker->boolean(),
            'saldo_estoque' => $this->faker->randomFloat(2, 0, 100),
            'excluido' => $this->faker->boolean()
        ];
    }
}
