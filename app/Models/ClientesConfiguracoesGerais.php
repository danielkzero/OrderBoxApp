<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientesConfiguracoesGerais extends Model
{
    use HasFactory;

    protected $table = 'clientes_configuracoes_gerais';

    protected $fillable = [
        'empresa_id',
        'bloquear_duplicidade_cpf_cnpj',
        'obrigar_cpf_cnpj',
        'obrigar_nome_fantasia',
        'obrigar_telefone',
        'obrigar_email',
        'obrigar_inscricao_estadual',
        'obrigar_info_adicional',
        'obrigar_segmento',
        'obrigar_cep',
        'obrigar_endereco',
        'obrigar_numero',
        'obrigar_complemento',
        'obrigar_bairro',
        'obrigar_cidade',
        'obrigar_estado',
    ];

    protected $casts = [
        'bloquear_duplicidade_cpf_cnpj' => 'boolean',
        'obrigar_cpf_cnpj' => 'boolean',
        'obrigar_nome_fantasia' => 'boolean',
        'obrigar_telefone' => 'boolean',
        'obrigar_email' => 'boolean',
        'obrigar_inscricao_estadual' => 'boolean',
        'obrigar_info_adicional' => 'boolean',
        'obrigar_segmento' => 'boolean',
        'obrigar_cep' => 'boolean',
        'obrigar_endereco' => 'boolean',
        'obrigar_numero' => 'boolean',
        'obrigar_complemento' => 'boolean',
        'obrigar_bairro' => 'boolean',
        'obrigar_cidade' => 'boolean',
        'obrigar_estado' => 'boolean',
    ];
}

