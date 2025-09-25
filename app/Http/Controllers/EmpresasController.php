<?php

namespace App\Http\Controllers;

use App\Models\Empresas;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function index()
    {
        $empresas = Empresas::with('matriz', 'filiais')->get();
        return response()->json($empresas);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'empresa_id' => 'nullable|exists:empresas,id',
            'nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:14',
            'inscricao_estadual' => 'nullable|string|max:20',
        ]);

        $empresa = Empresas::create($data);

        return response()->json($empresa, 201);
    }

    public function show(Empresas $empresa)
    {
        $empresa->load('matriz', 'filiais');
        return response()->json($empresa);
    }

    public function update(Request $request, Empresas $empresa)
    {
        $data = $request->validate([
            'empresa_id' => 'nullable|exists:empresas,id',
            'nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:14',
            'inscricao_estadual' => 'nullable|string|max:20',
            'excluido' => 'boolean',
        ]);

        $empresa->update($data);

        return response()->json($empresa);
    }

    public function destroy(Empresas $empresa)
    {
        $empresa->delete(); // soft delete
        return response()->json(null, 204);
    }
}
