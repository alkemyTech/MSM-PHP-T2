<?php

namespace App\Http;

use App\Models\Account;
use App\Models\FixedTerm;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BalanceDTO
{
    private $user;
    private $accounts;
    private $balance;
    private $history;
    private $fixed_term_deposits;

    public function __construct()
    {
        $this->user = User::where('id', Auth::user()->id)->without('accounts')->first(); // te trate el usuario donde el id sea igual al usuario logueado. No uso directamente Auth::user() porque no se puede implementar el método without() para devolver las cuentas más abajo
        $this->accounts = Account::where('user_id', $this->user->id)->get(); // traigo todas las cuentas relacionadas al usuario
        $this->balance = ['ARS accounts balance' => 0, 'USD accounts balance' => 0]; // creo la variable del balance donde se inicializa en 0 cada balance de la cuenta
        $this->history = Transaction::whereIn('account_id', $this->accounts->pluck('id'))->get(); // busco todas las transacciones de todas las cuentas del usuario donde los id de la cuenta sean los que agarra el método pluck()
        $this->fixed_term_deposits = FixedTerm::whereIn('account_id', $this->accounts->pluck('id'))->get(); // busco todos los plazos fijos de todas las cuentas del usuario donde los id de la cuenta sean los que agarra el método pluck()
    }

    private function getBalance() // recorro todas las cuentas, sumo el balance que tenía la variable con el que encuentra
    {

        foreach ($this->accounts as $account) {
            if ($account->currency === 'ARS') {
                $this->balance['ARS accounts balance'] += $account->balance;
            } elseif ($account->currency === 'USD') {
                $this->balance['USD accounts balance'] += $account->balance;
            }
        }
        return $this->balance;
    }

    public function toArray() // creo una función toArray para crear el array asociativo con las propiedades que quiero devolver en el endpoint
    {
        return [
            'user' => $this->user,
            'accounts' => $this->accounts,
            'balance' => $this->getBalance(),
            'history' => $this->history,
            'fixed_term_deposits' => $this->fixed_term_deposits
        ];
    }
}
