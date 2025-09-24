<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function index()
    {
        $empresas = Empresa::with('matriz', 'filiais')->get();
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

        $empresa = Empresa::create($data);

        return response()->json($empresa, 201);
    }

    public function show(Empresa $empresa)
    {
        $empresa->load('matriz', 'filiais');
        return response()->json($empresa);
    }

    public function update(Request $request, Empresa $empresa)
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

    public function destroy(Empresa $empresa)
    {
        $empresa->delete(); // soft delete
        return response()->json(null, 204);
    }
}
