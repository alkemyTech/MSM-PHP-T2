<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller

{
    public function login(Request $req)
    {
        $credentials = $req->validate([
            'email' => 'required|exists:users,email',
            'password' => 'required'
        ]);
        if (Auth::attempt($credentials)) {
            $user = User::find(Auth::user()->id);
            $token = $user->createToken('token')->accessToken;
            return response()->ok(['token' => $token, 'user' => $user]);
        }
        return response()->unauthorized(['message' => 'Credenciales incorrectas.']);
    }
    
    public function register(Request $req)
    {
        try {
            $req->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            $role = Role::where('name', 'USER')->first();
            if (!$role) {
                $role = new Role();
                $role->name = 'USER';
                $role->save();
            }

            $user = new User();
            $user->name = $req->name;
            $user->last_name = $req->last_name;
            $user->email = $req->email;
            $user->password = $req['password'] = Hash::make($req['password']);
            $user->role_id = $role->id;

            // Crear cuentas ARS y USD
            $this->createAccount($user, 'ARS', 300000);
            $this->createAccount($user, 'USD', 1000);

            // Generar el token de acceso
            $token = $user->createToken('token')->accessToken;
            return response()->created(['token' => $token, 'user' => $user]);
        } catch (QueryException $e) {
            return response()->internalServerError(['message' => $e->errorInfo[2]]); // usa el provider de internalServerError para devolver si hay un error de base de datos y accede al mensaje de error
        }
    }
    protected function createAccount($user, $currency, $limit) // como creamos 2 cuentas usamos una sola función donde se pasa como parámetro el usuario para obtener el id, el tipo de moneda y el límite de transacción
    {
        $faker = \Faker\Factory::create();

        do { // genera un cbu aleatorio
            $cbu = $faker->numerify(str_repeat('#', 22)); // genera un cbu aleatorio
            $cbuUsed = Account::where('cbu', $cbu)->first(); // busca si el cbu generado existe en la base de datos
        } while ($cbuUsed); // si existe el cbu en la base de datos va a reiniciar el ciclo y crear uno nuevo

        $user->save(); // el usuario se crea acá para evitar que se genere un usuario sin cbu si ocurren problemas

        $account = new Account();
        $account->currency = $currency;
        $account->transaction_limit = $limit;
        $account->balance = 0;
        $account->user_id = $user->id;
        $account->cbu = $cbu;
        $account->save();
    }
}
