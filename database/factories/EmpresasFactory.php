<?php

namespace Database\Factories;

use App\Models\Empresas;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpresasFactory extends Factory
{
    protected $model = Empresas::class;

    public function definition(): array
    {
        return [
            // primeiro criamos sem empresa_id, depois no Seeder podemos associar
            'empresa_id' => null,
            'nome' => $this->faker->company,
            'cnpj' => $this->faker->numerify('##.###.###/####-##'),
            'inscricao_estadual' => $this->faker->numerify('#########'),
            'excluido' => 0,
        ];
    }
}
