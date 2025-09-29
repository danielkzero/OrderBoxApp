<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Empresas;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TabelasPrecos>
 */
class TabelasPrecosFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $empresa = Empresas::inRandomOrder()->first() ?? Empresas::factory()->create();

        return [
            'empresa_id' => $empresa->id,
            'nome' =>$this->faker->randomElement(['Interior','Capital','Interior Varejo', 'Capital Varejo', 'Interior Atacado', 'Capital Atacado']) . ' - ' .  $this->faker->word() ,
            'tipo' => $this->faker->randomElement(['P','C']), // P = Produto, C = Cliente, por exemplo
            'acrescimo' => $this->faker->optional()->randomFloat(2, 0, 50),
            'desconto' => $this->faker->optional()->randomFloat(2, 0, 50),
            'excluido' => $this->faker->boolean(10),
            'ultima_alteracao' => now(),
        ];
    }
}
