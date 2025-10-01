<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categorias;

class CategoriasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Categorias::factory()
        ->count(100)
        ->create()
        ->each(function ($categoria) {
            // Para cada categoria pai, criar de 1 a 3 filhos
            Categorias::factory()
                ->count(rand(1, 3))
                ->create([
                    'categoria_pai_id' => $categoria->id,
                ]);
        });
    }
}
