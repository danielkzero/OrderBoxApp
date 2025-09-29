<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Empresas;
use App\Models\TabelasPrecos;
use App\Models\Produtos;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProdutosPrecos>
 */
class ProdutosPrecosFactory extends Factory
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
            'tabela_id' => TabelasPrecos::inRandomOrder()->first()->id ?? TabelasPrecos::Tabelas()->create()->id,
            'produto_id' => Produtos::inRandomOrder()->first()->id ?? Produtos::factory()->create()->id,
            'preco' => $this->faker->randomFloat(2, 10, 1000),
            'ultima_alteracao' => now()
        ];
    }
}
