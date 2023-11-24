<?php

namespace Tests\Unit;

use App\Models\Account;
use Carbon\Carbon;
use Tests\TestCase;

class SimulateFixedTermTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_successful_fixed_term_simulate_endpoint(): void
    {
        $data = [
            'name' => 'fixed_term',
            'last_name' => 'simulate',
            'email' => 'fixedtermsimulate@gmail.com',
            'password' => 'passwordtest'
        ];

        $registerResponse = $this->postJson('/api/auth/register', $data);

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
            ->postJson('/api/fixed_terms/simulate', $fixedTermData);

        $fixedTermInterest = env('FIXED_TERM_INTEREST');

        $created_at = now();
        $totalAmount = intval($fixedTermData['amount']) + (((intval($fixedTermData['amount']) * $fixedTermInterest) / 100) * intval($fixedTermData['duration']));
        $totalInterest = $totalAmount - intval($fixedTermData['amount']);
        $closed_at = Carbon::parse($created_at)->addDays(intval($fixedTermData['duration']));

        $response
            ->assertStatus(201)
            ->assertJsonPath('message', 'Request successfully processed')
            ->assertJsonStructure([
                'data' => [
                    'message',
                    'fixed_term' => [
                        'creation_date',
                        'finalization_date',
                        'amount',
                        'total_interest',
                        'total_amount'
                    ],
                ]
            ]);

        $this->assertEquals($created_at,  Carbon::parse($response['data']['fixed_term']['creation_date'])->toDateTimeString());
        $this->assertEquals($closed_at,  Carbon::parse($response['data']['fixed_term']['finalization_date'])->toDateTimeString());
        $this->assertEquals($fixedTermData['amount'],  $response['data']['fixed_term']['amount']);
        $this->assertEquals($totalInterest,  $response['data']['fixed_term']['total_interest']);
        $this->assertEquals($totalAmount,  $response['data']['fixed_term']['total_amount']);
    }


    public function test_not_enough_money_fixed_term_simulate_endpoint(): void
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
            ->postJson('/api/fixed_terms/simulate', $fixedTermData);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', 'You do not have enough money in your account');
    }

    public function test_above_transaction_limit_fixed_term_simulate_endpoint(): void
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
            ->postJson('/api/fixed_terms/simulate', $fixedTermData);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', "The amount field must be less than or equal to $account->transaction_limit.");
    }

    public function test_above_min_duration_fixed_term_simulate_endpoint(): void
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
            ->postJson('/api/fixed_terms/simulate', $fixedTermData);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', "The duration field must be greater than or equal to 30.");
    }

    public function test_USD_currency_fixed_term_simulate_endpoint(): void
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
            ->postJson('/api/fixed_terms/simulate', $fixedTermData);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', "The selected account id is invalid.");
    }
}
