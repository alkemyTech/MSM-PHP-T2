<?php

namespace App\Http\Controllers;

use App\Http\BalanceDTO;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\FixedTerm;
use App\Models\Transaction;
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
            'account_id' => ['required', Rule::exists('accounts', 'id')->where('user_id', $user->id)],
            'amount' => "required|numeric|gte:1000",
            'duration' => 'required|numeric|gte:30',
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

    public function payment(Request $req)
    {
        $user = Auth::user(); // busco el usuario autenticado

        $account = Account::where('id', $req->account_id)->first(); // busco la cuenta que pertenece al usuario

        $req->validate([ // valido que haga un pago de mínimo 1 peso/dólar, que el id de la cuenta pertenezca al usuario y que no supere el límite de dinero en una transacción
            'account_id' => ['required', Rule::exists('accounts', 'id')->where('user_id', $user->id)],
            'amount' => "required|numeric|gte:1|lte:{$account->transaction_limit}",
            'description' => 'string'
        ]);

        $enoughMoney = $account->balance >= $req->amount; // chequeo que tenga la cantidad suficiente para realizar el pago

        if (!$enoughMoney) {
            return response()->json(['error' => 'You do not have enough money in your account to make a transaction'], 422);
        }

        $transaction = new Transaction(); // creo la transacción
        $transaction->amount = $req->amount;
        $transaction->type = 'PAYMENT';
        $transaction->description = $req->description;
        $transaction->account_id = $req->account_id;

        $account->balance -= $req->amount; // resto al balance el dinero enviado en el pago

        $account->save(); // guardo la cuenta con el balance actualizado
        $transaction->save(); // guardo la transacción creada

        $transaction->load('account');
        return response()->created(['message' => 'Payment successfully made', 'transaction' => $transaction]);
    }
}
