<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Users extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'telefone',
        'role_id',
        'acesso_bloqueado',
        'excluido',
        'ultima_alteracao',
        'password'
    ];

    protected $hidden = ['password', 'remember_token'];

    public function roles()
    {
        return $this->belongsTo(Roles::class, 'id', 'role_id');
    }

    public function empresas()
    {
        return $this->belongsToMany(
            Empresas::class,          // Model relacionado
            'empresas_users',         // Tabela pivô
            'user_id',                // FK do usuário
            'empresa_id'              // FK da empresa
        );
    }
}
