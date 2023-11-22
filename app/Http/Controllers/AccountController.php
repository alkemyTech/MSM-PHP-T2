<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->id;
        $accounts = Account::where('user_id', $userId)->where('deleted', false)->get();

        $transactions = Transaction::whereIn('account_id', $accounts->pluck('id'))->get();
        return response()->ok(['transactions' => $transactions]);
    }

    public function create(Request $request)
    {
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
        return response()->created(['account' => $account], 'Account successfully created');
    }
}
