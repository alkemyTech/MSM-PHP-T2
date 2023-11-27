<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\FixedTerm;
use Carbon\Carbon;
use Tests\TestCase;

class FixedTermTest extends TestCase
{
    public function test_successful_fixed_term_endpoint(): void
    {
        $data = [
            'email' => 'emailtest@gmail.com',
            'password' => 'passwordtest'
        ];

        $registerResponse = $this->postJson('/api/auth/login', $data);

        $jsonResponse = $registerResponse->json();
        $token = $jsonResponse['data']['token'];

        $account = Account::where('user_id', $jsonResponse['data']['user']['id'])->where('currency', 'ARS')->first();
        $account->balance = 20000;
        $account->save();

        $fixedTermData = [
            'account_id' => $account->id,
            'amount' => 1000,
            'duration' => 30
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/fixed_terms', $fixedTermData);

        $fixedTerm = new FixedTerm();
        $fixedTerm->amount = $fixedTermData['amount'];
        $fixedTerm->account_id = $account->id;
        $fixedTerm->interest = env('FIXED_TERM_INTEREST');
        $fixedTerm->total = $fixedTermData['amount'] + ((($fixedTermData['amount'] * $fixedTerm->interest) / 100) * $fixedTermData['duration']);
        $fixedTerm->duration = $fixedTermData['duration'];
        $fixedTerm->closed_at = Carbon::parse($fixedTerm->created_at)->addDays(intval($fixedTermData['duration']));
        $fixedTerm->save();

        $account->balance -= $fixedTermData['amount'];
        $account->save();

        $response
            ->assertStatus(201)
            ->assertJsonPath('message', 'Request successfully processed')
            ->assertJsonStructure([
                'data' => [
                    'fixed_term' => [
                        'amount',
                        'duration',
                        'interest',
                        'total',
                        'closed_at',
                        'id',
                        'account'
                    ],
                ]
            ]);

        $this->assertEquals($fixedTermData['account_id'], $response['data']['fixed_term']['account']['id']);
        $this->assertEquals($fixedTermData['amount'],  $response['data']['fixed_term']['amount']);
        $this->assertEquals($fixedTermData['duration'],  $response['data']['fixed_term']['duration']);
        $this->assertEquals($fixedTerm->total,  $response['data']['fixed_term']['total']);
        $this->assertEquals($account->balance,  $response['data']['fixed_term']['account']['balance']);
        $this->assertEquals($fixedTerm->closed_at,  Carbon::parse($response['data']['fixed_term']['closed_at'])->toDateTimeString());
        $this->assertEquals($fixedTerm->interest,  $response['data']['fixed_term']['interest']);
    }

    public function test_not_enough_money_fixed_term_endpoint(): void
    {
        $data = [
            'email' => 'emailtest@gmail.com',
            'password' => 'passwordtest'
        ];

        $registerResponse = $this->postJson('/api/auth/login', $data);

        $jsonResponse = $registerResponse->json();
        $token = $jsonResponse['data']['token'];

        $account = Account::where('user_id', $jsonResponse['data']['user']['id'])->where('currency', 'ARS')->first();
        $account->balance = 10000;
        $account->save();

        $fixedTermData = [
            'account_id' => $account->id,
            'amount' => 20000,
            'duration' => 30
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/fixed_terms', $fixedTermData);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', 'You do not have enough money in your account');
    }

    public function test_above_transaction_limit_fixed_term_endpoint(): void
    {
        $data = [
            'email' => 'emailtest@gmail.com',
            'password' => 'passwordtest'
        ];

        $registerResponse = $this->postJson('/api/auth/login', $data);

        $jsonResponse = $registerResponse->json();
        $token = $jsonResponse['data']['token'];

        $account = Account::where('user_id', $jsonResponse['data']['user']['id'])->where('currency', 'ARS')->first();
        $account->balance = 20000;
        $account->save();

        $fixedTermData = [
            'account_id' => $account->id,
            'amount' => 300001,
            'duration' => 30
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/fixed_terms', $fixedTermData);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', "The amount field must be less than or equal to $account->transaction_limit.");
    }

    public function test_above_min_duration_fixed_term_endpoint(): void
    {
        $data = [
            'email' => 'emailtest@gmail.com',
            'password' => 'passwordtest'
        ];

        $registerResponse = $this->postJson('/api/auth/login', $data);

        $jsonResponse = $registerResponse->json();
        $token = $jsonResponse['data']['token'];

        $account = Account::where('user_id', $jsonResponse['data']['user']['id'])->where('currency', 'ARS')->first();
        $account->balance = 20000;
        $account->save();

        $fixedTermData = [
            'account_id' => $account->id,
            'amount' => 10000,
            'duration' => 29
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/fixed_terms', $fixedTermData);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', "The duration field must be greater than or equal to 30.");
    }

    public function test_USD_currency_fixed_term_endpoint(): void
    {
        $data = [
            'email' => 'emailtest@gmail.com',
            'password' => 'passwordtest'
        ];

        $registerResponse = $this->postJson('/api/auth/login', $data);

        $jsonResponse = $registerResponse->json();
        $token = $jsonResponse['data']['token'];

        $account = Account::where('user_id', $jsonResponse['data']['user']['id'])->where('currency', 'USD')->first();
        $account->balance = 20000;
        $account->save();

        $fixedTermData = [
            'account_id' => $account->id,
            'amount' => 1000,
            'duration' => 30
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/fixed_terms', $fixedTermData);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', "The selected account id is invalid.");
    }
}
