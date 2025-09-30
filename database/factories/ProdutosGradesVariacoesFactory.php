<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProdutosGradesVariacoes>
 */
class ProdutosGradesVariacoesFactory extends Factory
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
            'produto_grade_id' => \App\Models\ProdutosGrades::inRandomOrder()->first()->id ?? \App\Models\ProdutosGrades::factory()->create()->id,
            'nome' =>  $this->faker->word(),
            'valor' => $this->faker->randomFloat(2, 5, 200)
        ];
    }
}
