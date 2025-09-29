<?php

namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    // Listar todos os usuários
    public function index()
    {
        $users = Users::all();
        return response()->json($users);
    }

    // Criar um novo usuário
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'telefone' => 'nullable|string',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $user = Users::create([
            'name' => $request->name,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'role_id' => $request->role_id,
            'password' => Hash::make($request->password),
        ]);

        return response()->json($user, 201);
    }

    // Mostrar um usuário específico
    public function show($id)
    {
        $user = Users::findOrFail($id);
        return response()->json($user);
    }

    // Atualizar usuário
    public function update(Request $request, $id)
    {
        $user = Users::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'telefone' => 'nullable|string',
            'role_id' => 'nullable|exists:roles,id',
            'acesso_bloqueado' => 'nullable|boolean',
            'excluido' => 'nullable|boolean',
        ]);

        $user->fill($request->only([
            'name', 'email', 'telefone', 'role_id', 'acesso_bloqueado', 'excluido'
        ]));

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->ultima_alteracao = now();
        $user->save();

        return response()->json($user);
    }

    // Deletar usuário
    public function destroy($id)
    {
        $user = Users::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'Usuário deletado com sucesso']);
    }
}
