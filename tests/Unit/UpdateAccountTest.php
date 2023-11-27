<?php

namespace Tests\Unit;

use App\Models\Account;
use Tests\TestCase;

class UpdateAccountTest extends TestCase
{

    public function test_successful_update_account_endpoint(): void
    {
        $data = [
            'name' => 'account',
            'last_name' => 'update',
            'email' => 'updateaccount@gmail.com',
            'password' => 'passwordtest'
        ];

        $registerResponse = $this->postJson('/api/auth/register', $data);

        $jsonResponse = $registerResponse->json();
        $token = $jsonResponse['data']['token'];

        $account = Account::where('user_id', $jsonResponse['data']['user']['id'])->where('deleted', false)->first(); // buscamos la primer cuenta del usuario que no esté borrada

        $updatedData = ['transaction_limit' => 100000];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/accounts/$account->id", $updatedData);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Request successfully processed')
            ->assertJsonStructure([
                'data' => [
                    'message',
                    '0' => [
                        'id',
                        'currency',
                        'transaction_limit',
                        'balance',
                        'cbu'
                    ]
                ]
            ]);
        $this->assertEquals($account->id, $response['data']['0']['id']);
        $this->assertEquals($account->currency, $response['data']['0']['currency']);
        $this->assertEquals($updatedData['transaction_limit'], $response['data']['0']['transaction_limit']);
        $this->assertEquals($account->balance, $response['data']['0']['balance']);
        $this->assertEquals($account->cbu, $response['data']['0']['cbu']);
    }

    public function test_transaction_field_not_sent(): void
    {
        $data = [
            'email' => 'updateaccount@gmail.com',
            'password' => 'passwordtest'
        ];

        $registerResponse = $this->postJson('/api/auth/login', $data);

        $jsonResponse = $registerResponse->json();
        $token = $jsonResponse['data']['token'];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/accounts/1000");

        $response->assertUnprocessable()
            ->assertJsonValidationErrorFor('transaction_limit');
    }

    public function test_account_does_not_exist(): void
    {
        $data = [
            'email' => 'updateaccount@gmail.com',
            'password' => 'passwordtest'
        ];

        $registerResponse = $this->postJson('/api/auth/login', $data);

        $jsonResponse = $registerResponse->json();
        $token = $jsonResponse['data']['token'];

        $updatedData = ['transaction_limit' => 100000];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/accounts/1000", $updatedData);

        $response->assertNotFound()
            ->assertJsonStructure([
                "errors" => [
                    "message"
                ]
            ]);
    }

    public function test_account_is_not_yours(): void
    {
        $data = [
            'email' => 'updateaccount@gmail.com',
            'password' => 'passwordtest'
        ];

        $registerResponse = $this->postJson('/api/auth/login', $data);

        $jsonResponse = $registerResponse->json();
        $token = $jsonResponse['data']['token'];

        $account_id = Account::whereNot('user_id', $jsonResponse['data']['user']['id'])->value('id');
        $updatedData = ['transaction_limit' => 100000];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/accounts/$account_id", $updatedData);

        $response->assertForbidden()
            ->assertJsonStructure([
                "errors" => [
                    "message"
                ]
            ]);
    }
}
