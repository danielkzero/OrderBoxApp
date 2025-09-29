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
        $categirias = Categorias::factory()
            ->count(10)
            ->create();

        $categirias->each(function ($categirias) {
            Categorias::factory()
                ->count(rand(1, 3))
                ->create([
                    'categoria_pai_id' => $categoria->id,
                ]);
        });
    }
}
