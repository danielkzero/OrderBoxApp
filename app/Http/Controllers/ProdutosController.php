<?php

namespace App\Http\Controllers;

use App\Models\Produtos;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index()
    {
        $produtos = Produtos::with(['categoria', 'precos', 'grades.variacoes'])->get();
        return response()->json($produtos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'nome' => 'required|string|max:255',
            'categoria_id' => 'nullable|exists:categorias,id',
            'codigo' => 'nullable|string|max:255',
            'preco_tabela' => 'nullable|numeric',
            'preco_minimo' => 'nullable|numeric',
        ]);

        $produto = Produtos::create($request->all());
        return response()->json($produto, 201);
    }

    public function show($id)
    {
        $produto = Produtos::with(['categoria', 'precos', 'grades.variacoes'])->findOrFail($id);
        return response()->json($produto);
    }

    public function update(Request $request, $id)
    {
        $produto = Produtos::findOrFail($id);
        $produto->fill($request->all());
        $produto->ultima_alteracao = now();
        $produto->save();

        return response()->json($produto);
    }

    public function destroy($id)
    {
        $produto = Produtos::findOrFail($id);
        $produto->delete();

        return response()->json(['message' => 'Produto deletado com sucesso']);
    }
}
