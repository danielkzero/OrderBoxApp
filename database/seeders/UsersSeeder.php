<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Users::factory()->create([
            'name' => 'Daniel',
            'telefone' => '24999699849',
            'role_id' => 1,
            'email' => 'danikzero@hotmail.com',
            'ultima_alteracao' => now(),
            'password' => Hash::make('100#Senha'),
        ]);

        Users::factory(2)->create();
    }
}
