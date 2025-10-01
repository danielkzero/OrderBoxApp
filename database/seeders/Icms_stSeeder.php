<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Icms_st;

class Icms_stSeeder extends Seeder
{
    public function run(): void
    {
        // Cria 20 regras de ICMS-ST distribuídas entre as empresas existentes
        Icms_st::factory()->count(200)->create();
    }
}
