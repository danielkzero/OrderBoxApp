<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EmpresasUsers;

class EmpresasUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empresas = EmpresasUsers::factory()->create([
            'user_id' => 1,
            'empresa_id' => 1,
        ]);
        EmpresasUsers::factory()->count(50)->create();
    }
}
