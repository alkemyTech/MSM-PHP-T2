<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function create(Request $request){
        $user = Auth::user();
        // Valida que la moneda sea ARS o USD
        $request->validate([
            'currency' => 'required|in:ARS,USD'
        ]);
        // Crea la cuenta y el cbu
        $account = new Account();
        $account->currency = $request->currency;
        $account->user_id = $user->id;
        $account->balance = 0;
        $account->transaction_limit = $request->currency == 'ARS' ? 300000 : 1000;
        $account->cbu = $account->generateCbu();
        $account->save();
        return response()->created(['account' => $account],'Account successfully created');
    }
    public function index(Request $request,$user_id){
        //verificar si el usuario tiene el Role ADMIN
        if(($request->user()->hasRole('ADMIN'))){
            return response()->json(['message' =>"Unauthorized access"],403);
        //obtener el listado de cuentas del usuario
        $accounts = Account::where('user_id',$user_id)->get();
        return response()->json(['accounts' => $accounts],200); 

        }
    }
}
