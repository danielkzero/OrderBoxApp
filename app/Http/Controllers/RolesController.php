<?php

namespace App\Http\Controllers;

use App\Models\Roles;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function index()
    {
        $roles = Roles::with('empresa')->get();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'permissoes' => 'nullable|array',
        ]);

        $role = Roles::create($data);
        return response()->json($role, 201);
    }

    public function show(Roles $role)
    {
        $role->load('empresa');
        return response()->json($role);
    }

    public function update(Request $request, Roles $role)
    {
        $data = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'permissoes' => 'nullable|array',
        ]);

        $role->update($data);
        return response()->json($role);
    }

    public function destroy(Roles $role)
    {
        $role->delete();
        return response()->json(null, 204);
    }
}
