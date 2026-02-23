<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutosImagens extends Model
{
    use HasFactory;

    protected $table = 'produtos_imagens';

    protected $fillable = [
        'empresa_id', 'produto_id', 'imagem_base64', 'ordem'
    ];

    public function produto()
    {
        return $this->belongsTo(Produtos::class, 'produto_id', 'id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'empresa_id', 'id');
    }
}
