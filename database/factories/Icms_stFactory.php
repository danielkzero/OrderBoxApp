<?php

namespace Database\Factories;

use App\Models\Icms_st;
use App\Models\Empresas;
use Illuminate\Database\Eloquent\Factories\Factory;

class Icms_stFactory extends Factory
{
    protected $model = Icms_st::class;

    public function definition(): array
    {
        return [
            'empresa_id' => Empresas::inRandomOrder()->first()->id ?? Empresas::factory()->create()->id,
            'codigo_ncm' => $this->faker->numerify('####.##.##'),
            'nome_excecao_fiscal' => $this->faker->words(3, true),
            'estado_destino' => $this->faker->randomElement(['SP', 'RJ', 'MG', 'RS', 'BA']),
            'tipo_st' => $this->faker->randomElement(['ST1', 'ST2', 'ST3']),
            'valor_mva' => $this->faker->randomFloat(4, 0, 50),
            'valor_pmc' => $this->faker->randomFloat(4, 0, 100),
            'icms_credito' => $this->faker->randomFloat(4, 0, 20),
            'icms_destino' => $this->faker->randomFloat(4, 0, 20),
            'preco_considerado_no_calculo' => $this->faker->sentence(3),
            'reducao_de_base' => $this->faker->randomFloat(4, 0, 10),
            'excluido' => 0,
            'ultima_alteracao' => now(),
        ];
    }
}
