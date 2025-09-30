<?php

namespace App\Http\Controllers;

use App\Models\Produtos;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;


class ProdutosController extends Controller
{
    public function index($empresa)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Usuário não logado.');
        }

        // Pega os IDs das empresas que o usuário tem acesso
        $empresaIds = $user->empresas()->pluck('empresas.id')->toArray();

        if (!in_array($empresa, $empresaIds)) {
            abort(403, 'Empresa inválida.');
        }

        $produtos = Produtos::with([
            'imagens',
            'categorias',
            'precos.tabelas',
            'grades.variacoes'
        ])
            ->where('empresa_id', $empresa)
            ->get();

        return Inertia::render('Produtos/Index', [
            'produtos' => $produtos,
            'empresa_selecionada' => $empresa
        ]);
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
        $produto = Produtos::with([
            'categorias',
            'precos',
            'grades.variacoes'
        ])
            ->findOrFail($id);

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
