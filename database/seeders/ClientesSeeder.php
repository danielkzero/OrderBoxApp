<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Clientes;

class ClientesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Clientes::factory()
            ->count(50) // 50 clientes
            ->create()
            ->each(function ($cliente) {
                // telefones
                $cliente->telefones()->createMany(
                    \App\Models\ClienteTelefone::factory()->count(rand(1, 3))->make()->toArray()
                );

                // emails
                $cliente->emails()->createMany(
                    \App\Models\ClienteEmail::factory()->count(rand(1, 2))->make()->toArray()
                );

                // endereços extras
                $cliente->enderecos()->createMany(
                    \App\Models\ClienteEndereco::factory()->count(rand(1, 2))->make()->toArray()
                );

                // campos extras
                $cliente->extras()->createMany(
                    \App\Models\ClienteExtra::factory()->count(rand(0, 2))->make()->toArray()
                );

                // contatos
                $cliente->contatos()->createMany(
                    \App\Models\ClienteContato::factory()->count(rand(1, 3))->make()->toArray()
                )->each(function ($contato) {
                    $contato->telefones()->createMany(
                        \App\Models\ClienteContatoTelefone::factory()->count(rand(1, 2))->make()->toArray()
                    );
                    $contato->emails()->createMany(
                        \App\Models\ClienteContatoEmail::factory()->count(rand(1, 2))->make()->toArray()
                    );
                });
            });
    }
}
