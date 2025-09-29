<?php

namespace Database\Factories;

use App\Models\Clientes;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientesFactory extends Factory
{
    protected $model = Clientes::class;

    public function definition(): array
    {
        return [
            'empresa_id' => \App\Models\Empresas::inRandomOrder()->first()->id ?? \App\Models\Empresas::factory()->create()->id,
            'icms_st_id' => $this->faker->numberBetween(1, 5),
            'tipo' => 'J', // Apenas Jurídico por padrão
            'razao_social' => $this->faker->company,
            'nome_fantasia' => $this->faker->companySuffix,
            'cnpj' => $this->faker->numerify('##.###.###/####-##'),
            'inscricao_estadual' => $this->faker->numerify('#########'),
            'suframa' => null,
            'rua' => $this->faker->streetName,
            'numero' => $this->faker->buildingNumber,
            'complemento' => $this->faker->secondaryAddress,
            'bairro' => $this->faker->citySuffix,
            'cep' => $this->faker->postcode,
            'municipio_codigo' => null, // Pode preencher se quiser gerar IBGE válido
            'bloqueado' => 0,
            'motivo_bloqueio_id' => null,
            'observacao' => $this->faker->sentence,
            'ultima_alteracao' => now(),
            'excluido' => 0,
        ];
    }
}
