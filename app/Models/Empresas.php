<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresas extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [ 
        'empresa_id', 'nome', 'cnpj', 'inscricao_estadual', 'excluido' 
    ];

    public function matriz()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }

    public function filiais()
    {
        return $this->hasMany(Empresas::class, 'empresa_id', 'id');
    }

    public function usuarios()
    {
        return $this->hasMany(Users::class, 'empresa_id', 'id');
    }
}
