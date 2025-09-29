<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Clientes;
use App\Models\Empresas;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientesEmails>
 */
class ClientesEmailsFactory extends Factory
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
            'cliente_id' => Clientes::inRandomOrder()->first()->id ?? Clientes::factory()->create()->id,
            'email' => $this->faker->safeEmail(),
            'tipo' => $this->faker->randomElement(['M', 'F', 'C']), // M=mobile, F=fixo, C=comercial (ajuste se tiver enum)
            'ultima_alteracao' => now(),
        ];
    }
}
