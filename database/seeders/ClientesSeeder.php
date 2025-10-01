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
        \App\Models\Clientes::factory()
            ->count(500) // 50 clientes
            ->create()
            ->each(function ($cliente) {
                // telefones
                $cliente->telefones()->createMany(
                    \App\Models\ClientesTelefones::factory()->count(rand(1, 3))->make()->toArray()
                );

                // emails
                $cliente->emails()->createMany(
                    \App\Models\ClientesEmails::factory()->count(rand(1, 2))->make()->toArray()
                );

                // endereços extras
                $cliente->enderecos()->createMany(
                    \App\Models\ClientesEnderecos::factory()->count(rand(1, 2))->make()->toArray()
                );

                // campos extras
                $cliente->campos_extras()->createMany(
                    \App\Models\ClientesExtras::factory()->count(rand(0, 2))->make()->toArray()
                );

                // contatos
                $cliente->contatos()->createMany(
                    \App\Models\ClientesContatos::factory()->count(rand(1, 3))->make()->toArray()
                )->each(function ($contato) {
                    $contato->telefones()->createMany(
                        \App\Models\ClientesContatosTelefones::factory()->count(rand(1, 2))->make()->toArray()
                    );
                    $contato->emails()->createMany(
                        \App\Models\ClientesContatosEmails::factory()->count(rand(1, 2))->make()->toArray()
                    );
                });
            });
    }
}
