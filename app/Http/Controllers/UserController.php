<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        return response()->ok(['users' => User::where('deleted', false)->get()]);
    }

    public function deleteUser(Request $req, $id) {
        $authUser = Auth::user();
        $userToDelete = User::find($id);
        $role_id_admin = Role::where('name', 'ADMIN')->value('id');
        $role_id_user = Role::where('name', 'USER')->value('id');
        
            if (!$userToDelete) {
                return response()->notFound(['message' => 'El usuario no existe']);

            } elseif ($authUser->role_id == $role_id_admin) {
                $userToDelete->update(['deleted' => 1]);
                return response()->ok(['message' => "Usuario '$userToDelete->name''$userToDelete->last_name' eliminado correctamente", 200]);

            } elseif ($authUser->role_id == $role_id_user && $authUser->id === $userToDelete->id) {
                $userToDelete->update(['deleted' => 1]);
                return response()->ok(['message' => 'Ud. ha sido eliminado correctamente', 200]);

            } else {
                return response()->forbidden(['message' => 'No tienes permisos para realizar esta acción']);
            }
    }

    public function paginate(Request $req)
    {
        $users = User::where('deleted', false)->simplePaginate(10);
        return response()->ok(['users' => $users]);
    }
}