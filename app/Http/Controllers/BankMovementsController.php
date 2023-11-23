<?php

namespace App\Http\Controllers;

use App\Http\BalanceDTO;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\FixedTerm;
use App\Models\Transaction;
use Carbon\Carbon;
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

        $account = Account::where('id', $req->account_id)->first(); // busco la cuenta en pesos que pertenece al usuario

        $req->validate([ // valido que el dinero a meter en el plazo fijo sea mayor o igual a 1000, que la duración de este sea mayor o igual a 30 dias y que el id de la cuenta pertenezca al usuario        
            'account_id' => Rule::exists('accounts', 'id')->where('user_id', $user->id)->where('currency', 'ARS')->where('deleted', false),
            'amount' => "numeric|gte:1000|lte:{$account->transaction_limit}",
            'duration' => 'numeric|gte:30',
        ]);

        $enoughMoney = $account->balance >= $req->amount;

        if (!$enoughMoney) {
            return response()->unprocessableContent([], 'You do not have enough money in your account');
        }

        $fixedTermInterest = $_ENV['FIXED_TERM_INTEREST']; // agarro el interés por día de la variable de entorno

        $fixedTermTotal = $req->amount + ((($req->amount * $fixedTermInterest) / 100) * $req->duration); // sumo el dinero más lo que se tiene que agregar por dia multiplicado a la duración

        $fixedTerm = new FixedTerm();
        $fixedTerm->amount = $req->amount;
        $fixedTerm->duration = $req->duration;
        $fixedTerm->account_id = $account->id;
        $fixedTerm->interest = $fixedTermInterest;
        $fixedTerm->total = $fixedTermTotal;
        $fixedTerm->closed_at = Carbon::parse($fixedTerm->created_at)->addDays(intval($req->duration));

        $account->balance -= $req->amount; // resto al balance el dinero metido en el plazo fijo

        $fixedTerm->save(); // guardo el plazo fijo creado
        $account->save(); // guardo la cuenta con el balance actualizado

        $fixedTerm->load('account'); // cargo la cuenta para que la devuelva en el json

        return response()->created(['message' => 'Fixed term successfully created', 'fixed_term' => $fixedTerm]);
    }

    public function updateTransaction(Request $request, $transaction_id)
    {

        $request->validate([
            'description' => 'required',
        ]);

        $transaction = Transaction::find($transaction_id);
        $transaction->update(['description' => $request->description]);
        $transaction->makeHidden('account');

        return response()->created(['message' => 'Description successfully updated', $transaction]);
    }

    public function payment(Request $req)
    {
        $user = Auth::user(); // busco el usuario autenticado

        $account = Account::where('id', $req->account_id)->first(); // busco la cuenta que pertenece al usuario

        $req->validate([ // valido que haga un pago de mínimo 1 peso/dólar, que el id de la cuenta pertenezca al usuario y que no supere el límite de dinero en una transacción
            'account_id' => ['required', Rule::exists('accounts', 'id')->where('user_id', $user->id)->where('deleted', false)],
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

    public function send(Request $request)
    {
        $request->validate([
            'sender_account_id' => 'required|exists:accounts,id',
            'receiver_account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|gt:0'
        ]);

        // Busco al sender y al receiver
        $user = Auth::user();
        $sender = Account::where('id', $request->sender_account_id)->first();
        $receiver = Account::where('id', $request->receiver_account_id)->first();

        $request->validate([
            'sender_account_id' => ['required', Rule::exists('accounts', 'id')->where('user_id', $user->id)->where('deleted', false)], // busco que exista, que pertenezca al usuario y que no haya sido borrada
            'receiver_account_id' => ['required', Rule::exists('accounts', 'id')->whereNot('id', $sender->id)->where('currency', $sender->currency)->where('deleted', false)], // busco que exista, que sea una cuenta diferente a la del usuario, que sea el mismo tipo de moneda y que no haya sido borrada
            'amount' => "required|numeric|gte:1|lte:{$sender->transaction_limit}",
            'description' => 'string|max:255'
        ]);

        // Validación custom 
        if ($sender->balance < $request->amount)
            return response()->unprocessableContent([], 'You do not have enough money in your account');

        // Actualizo los balances de las cuentas
        $sender->balance -= $request->amount;
        $receiver->balance += $request->amount;
        $sender->save();
        $receiver->save();

        // Creo la transacción de tipo PAYMENT para el usuario que envía el dinero
        $paymentTransaction = new Transaction();
        $paymentTransaction->amount = $request->amount;
        $paymentTransaction->account_id = $sender->id;
        $paymentTransaction->type = 'PAYMENT';
        $paymentTransaction->description = $request->description;
        $paymentTransaction->save();

        // Creo la transacción de tipo INCOME para el usuario que recibe el dinero
        $incomeTransaction = new Transaction();
        $incomeTransaction->amount = $request->amount;
        $incomeTransaction->account_id = $receiver->id;
        $incomeTransaction->type = 'INCOME';
        $incomeTransaction->description = $request->description;
        $incomeTransaction->save();

        return response()->created(['amount' => $request->amount, 'from' => $sender->user, 'payment_transaction' => $paymentTransaction, 'to' => $receiver->user, 'income_transaction' => $incomeTransaction], 'Transaction successfully processed');
    }
}
