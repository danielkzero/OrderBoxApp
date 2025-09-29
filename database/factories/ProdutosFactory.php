<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Empresas;
use App\Models\Categorias;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Produtos>
 */
class ProdutosFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $preco_tabela = $this->faker->randomFloat(2, 10, 1000);
        return [
            'empresa_id' => Empresas::inRandomOrder()->first()->id ?? Empresas::factory()->create()->id,
            'codigo' => $this->faker->unique()->bothify('##.###'),
            'nome' => $this->faker->word(),
            'observacoes' => $this->faker->sentence(),
            'preco_tabela' => $preco_tabela,
            'preco_minimo' => $this->faker->randomFloat(2, 5, $preco_tabela),
            'ipi' => $this->faker->randomFloat(2, 0, 20),
            'tipo_ipi' => $this->faker->randomElement(['perc', 'valor']),
            'comissao' => $this->faker->randomFloat(2, 0, 20),
            'saldo_estoque' => $this->faker->randomFloat(2, 0, 100),
            'st' => $this->faker->randomFloat(2, 0, 50),
            'ativo' => $this->faker->boolean(90),
            'excluido' => 0,
            'moeda' => 'BRL',
            'codigo_ncm' => $this->faker->numerify('##########'),
            'multiplo' => $this->faker->numberBetween(1, 10),
            'peso_bruto' => $this->faker->randomFloat(3, 0.1, 50),
            'largura' => $this->faker->randomFloat(3, 0.1, 2),
            'altura' => $this->faker->randomFloat(3, 0.1, 2),
            'comprimento' => $this->faker->randomFloat(3, 0.1, 2),
            'peso_dimensoes_unitario' => $this->faker->boolean(),
            'exibir_no_b2b' => $this->faker->boolean(),
            'unidade' => $this->faker->randomElement(['cx', 'un', 'sc']),
            'ultima_alteracao' => now(),
            'categoria_id' => Categorias::inRandomOrder()->first()->id ?? Categorias::factory()->create()->id,
        ];
    }
}
