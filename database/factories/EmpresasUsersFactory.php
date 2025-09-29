<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class EmpresasUsersFactory extends Factory
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
            'user_id' => \App\Models\Users::inRandomOrder()->first()->id ?? \App\Models\Users::factory()->create()->id,
        ];
    }
}
