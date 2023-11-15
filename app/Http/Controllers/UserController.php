<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        return response()->ok(['users' => User::all()]);
    }

    public function deleteUser(Request $req, $id) {
        $authUser = Auth::user();
        $userToDelete = User::find($id);
        
            if (!$userToDelete) {
                return response()->notFound(['message' => 'El usuario no existe']);

            } elseif ($authUser->role_id == '1') {
                $userToDelete->update(['deleted' => 1]);
                return response()->ok(['message' => "Usuario '$userToDelete->id' eliminado correctamente", 200]);

            } elseif ($authUser->role_id == '2' && $authUser->id === $userToDelete->id) {
                $userToDelete->update(['deleted' => 1]);
                return response()->ok(['message' => 'Ud. ha sido eliminado correctamente', 200]);

            } else {
                return response()->forbidden(['message' => 'No tienes permisos para realizar esta acción']);
            }
    }
}