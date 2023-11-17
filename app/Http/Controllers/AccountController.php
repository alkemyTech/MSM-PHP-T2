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

        public function deposit(Request $request){
            //Recuperar los datos de la solicitud
            $account=$request->input('account');
            $user_id=$request->input('user_id');
            $AmountDeposit=$request->input('amount deposit');
            //Validar si el tipo de cuenta existe para el usuario logueado
            $account=Account::where('user_id',$user_id)->where('account',$account)->first();
            if ($account){
                //crear un registro en la tabla de movimientos
                $transaction=new Transaction();
                $transaction->user_id=$user_id;
                $transaction->account_id=$account->id;
                $transaction->amount=$AmountDeposit;
                $transaction->type='deposit';
                $transaction->save();
                //actualizar el saldo de la cuenta
                $account->balance=$account->balance+$AmountDeposit;
                $account->save();
                //devolver el registro generado y la cuenta con el balance actualizado
                return response()->json(['transaction'=>$transaction,'account'=>$account],200);
            }else{
                return response()->json(['message'=>'Account not found'],404);
                


            }
        }
    }
}

