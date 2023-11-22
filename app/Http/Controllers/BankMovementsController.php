<?php

namespace App\Http\Controllers;

use App\Http\BalanceDTO;
use App\Http\TransactionDTO;

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
  
    public function updateTransaction(Request $request, $transaction_id) {

        $request -> validate([
            'description' => 'required',
        ]);
        
        $transaction = Transaction::find($transaction_id);
        $oldDescription = $transaction->description;
        $transaction->update(['description' => $request->description]);

        return response()->created(['message' => 'Description successfully updated','Old Description' => $oldDescription, 'New Description' => ($transaction->description)]);
    }

    public function send(Request $request) {
        $request->validate([
            'sender_account_id' => 'required|exists:accounts,id',
            'receiver_account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|gt:0'
        ]);

        // Busco al sender y al receiver
        $user = Auth::user();
        $sender = Account::where('id', $request->sender_account_id)->first();
        $receiver = Account::where('id', $request->receiver_account_id)->first();

        // Validaciones
        if($sender->deleted || $receiver->deleted)
            return response()->badRequest([],'You cannot send money to a deleted account');
        else if($sender->user->deleted || $receiver->user->deleted)
            return response()->badRequest([],'You cannot send money to a deleted user');
        else if($sender->user_id != $user->id)
            return response()->forbidden([],'You do not have permission to perform this action');
        else if($sender->currency != $receiver->currency)
            return response()->badRequest([],'You cannot send money to an account with a different currency');
        else if($sender->balance < $request->amount)
            return response()->badRequest([],'You do not have enough money in your account');
        else if($sender->transaction_limit < $request->amount)
            return response()->badRequest([],'You have exceeded your transaction limit');

        // Actualizo los balances de las cuentas
        $sender->balance -= $request->amount;
        $receiver->balance += $request->amount;
        $sender->save();
        $receiver->save();

        // Creo la transacción de tipo PAYMENT para el usuario que envía el dinero
        $transaction = new Transaction();
        $transaction->amount = $request->amount;
        $transaction->account_id = $sender->id;
        $transaction->type = 'PAYMENT';
        $transaction->save();

        // Creo la transacción de tipo INCOME para el usuario que recibe el dinero
        $transaction = new Transaction();
        $transaction->amount = $request->amount;
        $transaction->account_id = $receiver->id;
        $transaction->type = 'INCOME';
        $transaction->save();
        return response()->created(['amount' => $request->amount, 'from' => $sender->user, 'to' => $receiver->user], 'Transaction successfully processed');
    }
}
