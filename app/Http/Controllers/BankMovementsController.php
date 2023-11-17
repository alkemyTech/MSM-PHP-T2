<?php

namespace App\Http\Controllers;

use App\Http\BalanceDTO;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\FixedTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BankMovementsController extends Controller
{
    public function index()
    {
        return response()->ok(['Account Balance' => (new BalanceDTO())->toArray()]);
    }

    public function create(Request $req)
    {
        $user = Auth::user(); // busco el usuario autenticado

        $req->validate([ // valido que el dinero a meter en el plazo fijo sea mayor o igual a 1000, que la duración de este sea mayor o igual a 30 dias y que el id de la cuenta pertenezca al usuario
            'account_id' => Rule::exists('accounts', 'id')->where('user_id', $user->id),
            'amount' => "numeric|gte:1000",
            'duration' => 'numeric|gte:30',
        ]);

        $account = Account::where('id', $req->account_id)->first(); // busco la cuenta en pesos que pertenece al usuario

        $enoughMoney = $account->balance >= $req->amount;

        if (!$enoughMoney) {
            return response()->json(['error' => 'You do not have enough money in your account to create a fixed term'], 422);
        }

        $fixedTermInterest = $_ENV['FIXED_TERM_INTEREST']; // agarro el interés por día de la variable de entorno

        $fixedTermTotal = $req->amount + ((($req->amount * $fixedTermInterest) / 100) * $req->duration); // sumo el dinero más lo que se tiene que agregar por dia multiplicado a la duración

        $fixedTerm = new FixedTerm();
        $fixedTerm->amount = $req->amount;
        $fixedTerm->duration = $req->duration;
        $fixedTerm->account_id = $account->id;
        $fixedTerm->interest = $fixedTermInterest;
        $fixedTerm->total = $fixedTermTotal;

        $account->balance -= $req->amount; // resto al balance el dinero metido en el plazo fijo

        $fixedTerm->save(); // guardo el plazo fijo creado
        $account->save(); // guardo la cuenta con el balance actualizado

        $fixedTerm->load('account'); // cargo la cuenta para que la devuelva en el json

        return response()->created(['message' => 'Fixed term successfully created', 'fixed_term' => $fixedTerm]);
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
