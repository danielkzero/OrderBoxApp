<?php

namespace Database\Factories;

use App\Models\ClientesTelefones;
use App\Models\Empresas;
use App\Models\Clientes;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientesTelefonesFactory extends Factory
{
    protected $model = ClientesTelefones::class;

    public function definition(): array
    {
        return [
            'empresa_id' => Empresas::inRandomOrder()->first()->id ?? Empresas::factory()->create()->id,
            'cliente_id' => Clientes::inRandomOrder()->first()->id ?? Clientes::factory()->create()->id,
            'numero' => $this->faker->phoneNumber(),
            'tipo' => $this->faker->randomElement(['M', 'F', 'C']), // M=mobile, F=fixo, C=comercial (ajuste se tiver enum)
            'ultima_alteracao' => now(),
        ];
    }
}
