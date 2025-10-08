<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Roles;
use Faker\Factory as Faker;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        Roles::factory()->create([
            'empresa_id' => 1,
            'nome' => 'Administrador',
            'descricao' => $faker->sentence,
            'permissoes' => [
                'clientes' => 'crud',
                'pedidos' => 'crud',
            ],
        ]);

        Roles::factory()->count(3)->create();
    }
}
