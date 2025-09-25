<?php

namespace App\Http\Controllers;

use App\Models\Categorias;
use Illuminate\Http\Request;

class CategoriasController extends Controller
{
    // Listar todas as categorias
    public function index()
    {
        $categorias = Categorias::with('categoriaPai', 'categoriasFilhas')->get();
        return response()->json($categorias);
    }

    // Criar nova categoria
    public function store(Request $request)
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'nome' => 'required|string|max:255',
            'categoria_pai_id' => 'nullable|exists:categorias,id',
        ]);

        $categoria = Categorias::create([
            'empresa_id' => $request->empresa_id,
            'nome' => $request->nome,
            'categoria_pai_id' => $request->categoria_pai_id,
        ]);

        return response()->json($categoria, 201);
    }

    // Mostrar categoria específica
    public function show($id)
    {
        $categoria = Categorias::with('categoriaPai', 'categoriasFilhas')->findOrFail($id);
        return response()->json($categoria);
    }

    // Atualizar categoria
    public function update(Request $request, $id)
    {
        $categoria = Categorias::findOrFail($id);

        $request->validate([
            'empresa_id' => 'sometimes|required|exists:empresas,id',
            'nome' => 'sometimes|required|string|max:255',
            'categoria_pai_id' => 'nullable|exists:categorias,id',
            'excluido' => 'nullable|boolean',
        ]);

        $categoria->fill($request->only(['empresa_id', 'nome', 'categoria_pai_id', 'excluido']));
        $categoria->ultima_alteracao = now();
        $categoria->save();

        return response()->json($categoria);
    }

    // Deletar categoria
    public function destroy($id)
    {
        $categoria = Categorias::findOrFail($id);
        $categoria->delete();
        return response()->json(['message' => 'Categoria deletada com sucesso']);
    }
}
