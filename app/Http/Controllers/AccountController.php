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

    public function list($id)
    {
        $userId = Auth::user()->id;
        $transaction = Transaction::find($id);
        $account = Account::where('user_id', $userId)->where('id', $transaction->account_id)->where('deleted', false)->first();

        if (!$account) {
            return response()->unprocessableContent([], 'Invalid transaction id');
        }

        return response()->ok(['transaction' => $transaction]);
    }

    public function obtain($user_id)
    {
        $accounts = Account::where('user_id', $user_id)->where('deleted', false)->get();
        return response()->ok(['accounts' => $accounts]);
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

    public function updateAccountLimit(Request $request, $account_id)
    {
        $request->validate([
            'transaction_limit' => 'required',
        ]);

        $account_id = Account::find($account_id);
        if (!$account_id) {
            return response()->notFound(['message' => "The Account doesn't exist"]);
        }

        $authUser_id = (Auth::user())->id;
        $owner_user_id = $account_id->user->id;

        if ($authUser_id != $owner_user_id) {
            return response()->forbidden(['message' => "The Account doesn't belong to you"]);
        } else {
            $account_id->update(['transaction_limit' => $request->transaction_limit]);
            $account_id->makeHidden('user');
            return response()->ok(['message' => 'Transaction Limit successfully updated', $account_id]);
        }
    }
}
