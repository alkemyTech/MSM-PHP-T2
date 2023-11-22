<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\FixedTerm;
use App\Models\Transaction;
use Tests\TestCase;

class BalanceTest extends TestCase
{
    public function test_successful_balance_endpoint(): void
    {
        $data = [
            'name' => 'balance',
            'last_name' => 'test',
            'email' => 'balancetest@gmail.com',
            'password' => 'passwordtest'
        ];

        $registerResponse = $this->postJson('/api/auth/register', $data);

        $jsonResponse = $registerResponse->json();
        $token = $jsonResponse['data']['token'];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/accounts/balance');

        $accounts = Account::where('user_id', $jsonResponse['data']['user']['id'])->get();

        $ARSsum = $accounts->where('currency', 'ARS')->sum('balance');
        $USDsum = $accounts->where('currency', 'USD')->sum('balance');

        $accountsQuantity = $accounts->count();
        $historyQuantity = Transaction::whereIn('account_id', $accounts->pluck('id'))->count();
        $fixedTermsQuantity = FixedTerm::whereIn('account_id', $accounts->pluck('id'))->count();

        $response
            ->assertStatus(200)
            ->assertJsonPath('message', 'Request successfully processed')
            ->assertJsonCount($accountsQuantity, 'data.Account Balance.accounts')
            ->assertJsonCount($fixedTermsQuantity, 'data.Account Balance.fixed_term_deposits')
            ->assertJsonCount($historyQuantity, 'data.Account Balance.history')
            ->assertJsonStructure([
                'data' => [
                    'Account Balance' => [
                        'user',
                        'accounts',
                        'balance',
                        'history',
                        'fixed_term_deposits',
                    ],
                ],
            ]);

        $jsonResponse = $response->json();

        $arsBalanceSum = $jsonResponse['data']['Account Balance']['balance']['ARS accounts balance'];
        $usdBalanceSum = $jsonResponse['data']['Account Balance']['balance']['USD accounts balance'];

        $this->assertEquals($ARSsum, $arsBalanceSum);
        $this->assertEquals($USDsum, $usdBalanceSum);
    }
}
