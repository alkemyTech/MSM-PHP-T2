<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\FixedTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankMovementsController extends Controller
{
    public function create(Request $req)
    {
        $user = Auth::user(); // busco el usuario autenticado

        $account = Account::where('user_id', $user->id)->where('currency', 'ARS')->first(); // busco la cuenta en pesos que pertenece al usuario

        $req->validate([ // valido que el dinero a meter en el plazo fijo sea mayor o igual a 1000 y que la duración de este sea mayor o igual a 30 dias
            'amount' => "numeric|gte:1000",
            'duration' => 'numeric|gte:30',
        ]);

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
}
