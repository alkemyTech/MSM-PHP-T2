<?php

namespace Tests\Unit;

use App\Models\Account;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TransactionSendTest extends TestCase
{
    use DatabaseMigrations;
    protected $initialBalance = 10000;

    public function test_send_transaction()
    {
        // Set up the test
        $this->createPersonalClient();
        $data = ['name' => 'nombretest','last_name' => 'apellidotest','email' => 'emailtest@gmail.com','password' => 'passwordtest'];
        $user1 = $this->postJson('/api/auth/register', $data);
        $data2 = ['name' => 'nombretest2','last_name' => 'apellidotest2','email' => 'emailtest2@gmail.com','password' => 'passwordtest2'];
        $user2 = $this->postJson('/api/auth/register', $data2);
        $jsonResponse = $user1->json();
        $token = $jsonResponse['data']['token'];
        $account = Account::where('user_id', $jsonResponse['data']['user']['id'])->where('currency', 'ARS')->first();
        $account->balance = $this->initialBalance;
        $account->save();

        // Send transaction
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/transactions/send', [
            "sender_account_id" => 1,
            "receiver_account_id" => 3,
            "amount" => 100,
            "description"=> "Envio de dinero"
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('message', 'Transaction successfully processed')
            ->assertJsonPath('data.amount', 100);
        
        $this->assertDatabaseHas('transactions', ['id' => $response->json('data.payment_transaction.id')]);
        $this->assertDatabaseHas('transactions', ['id' => $response->json('data.income_transaction.id')]);
        $this->assertDatabaseHas('accounts', ['id' => $response->json('data.payment_transaction.account_id'), 'balance' => $this->initialBalance - 100]);
        $this->assertDatabaseHas('accounts', ['id' => $response->json('data.income_transaction.account_id'), 'balance' => 100]);
    }

    public function test_send_transaction_with_insufficient_funds()
    {
        // Set up the test
        $this->createPersonalClient();
        $data = ['name' => 'nombretest','last_name' => 'apellidotest','email' => 'emailtest@gmail.com','password' => 'passwordtest'];
        $user1 = $this->postJson('/api/auth/register', $data);
        $data2 = ['name' => 'nombretest2','last_name' => 'apellidotest2','email' => 'emailtest2@gmail.com','password' => 'passwordtest2'];
        $user2 = $this->postJson('/api/auth/register', $data2);
        $jsonResponse = $user1->json();
        $token = $jsonResponse['data']['token'];
        $account = Account::where('user_id', $jsonResponse['data']['user']['id'])->where('currency', 'ARS')->first();
        $account->balance = 50;
        $account->save();

        // Send transaction
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/transactions/send', [
            "sender_account_id" => 1,
            "receiver_account_id" => 3,
            "amount" => 100,
            "description"=> "Envio de dinero"
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', 'You do not have enough money in your account');
        
        $this->assertDatabaseMissing('transactions', ['id' => $response->json('data.payment_transaction.id')]);
        $this->assertDatabaseMissing('transactions', ['id' => $response->json('data.income_transaction.id')]);
        $this->assertDatabaseHas('accounts', ['id' => $user1->json('data.user.id'), 'balance' => 50]);
        $this->assertDatabaseHas('accounts', ['id' => $user2->json('data.user.id'), 'balance' => 0]);
    }

    public function test_send_transaction_with_invalid_amount()
    {
        // Set up the test
        $this->createPersonalClient();
        $data = ['name' => 'nombretest','last_name' => 'apellidotest','email' => 'emailtest@gmail.com','password' => 'passwordtest'];
        $user1 = $this->postJson('/api/auth/register', $data);
        $data2 = ['name' => 'nombretest2','last_name' => 'apellidotest2','email' => 'emailtest2@gmail.com','password' => 'passwordtest2'];
        $user2 = $this->postJson('/api/auth/register', $data2);
        $jsonResponse = $user1->json();
        $token = $jsonResponse['data']['token'];
        $account = Account::where('user_id', $jsonResponse['data']['user']['id'])->where('currency', 'ARS')->first();
        $account->balance = $this->initialBalance;
        $account->save();

        // Send transaction
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/transactions/send', [
            "sender_account_id" => 1,
            "receiver_account_id" => 3,
            "amount" => 400000,
            "description"=> "Envio de dinero"
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', 'The amount field must be less than or equal to 300000.')
            ->assertJsonPath('errors.amount', ['The amount field must be less than or equal to 300000.']);

            $this->assertDatabaseMissing('transactions', ['id' => $response->json('data.payment_transaction.id')]);
            $this->assertDatabaseMissing('transactions', ['id' => $response->json('data.income_transaction.id')]);
            $this->assertDatabaseHas('accounts', ['id' => $user1->json('data.user.id'), 'balance' => $this->initialBalance]);
            $this->assertDatabaseHas('accounts', ['id' => $user2->json('data.user.id'), 'balance' => 0]);
    }

    public function test_send_transaction_with_invalid_sender_account()
    {
        // Set up the test
        $this->createPersonalClient();
        $data = ['name' => 'nombretest','last_name' => 'apellidotest','email' => 'emailtest@gmail.com','password' => 'passwordtest'];
        $user1 = $this->postJson('/api/auth/register', $data);
        $data2 = ['name' => 'nombretest2','last_name' => 'apellidotest2','email' => 'emailtest2@gmail.com','password' => 'passwordtest2'];
        $user2 = $this->postJson('/api/auth/register', $data2);
        $jsonResponse = $user1->json();
        $token = $jsonResponse['data']['token'];
        $account = Account::where('user_id', $jsonResponse['data']['user']['id'])->where('currency', 'ARS')->first();
        $account->balance = $this->initialBalance;
        $account->save();

        // Send transaction
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/transactions/send', [
            "sender_account_id" => 3,
            "receiver_account_id" => 1,
            "amount" => 100,
            "description"=> "Envio de dinero"
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', 'The selected sender account id is invalid.')
            ->assertJsonPath('errors.sender_account_id', ['The selected sender account id is invalid.']);

            $this->assertDatabaseMissing('transactions', ['id' => $response->json('data.payment_transaction.id')]);
            $this->assertDatabaseMissing('transactions', ['id' => $response->json('data.income_transaction.id')]);
            $this->assertDatabaseHas('accounts', ['id' => $user1->json('data.user.id'), 'balance' => $this->initialBalance]);
            $this->assertDatabaseHas('accounts', ['id' => $user2->json('data.user.id'), 'balance' => 0]);

    }

    public function test_send_transaction_with_invalid_receiver_account()
    {
        // Set up the test
        $this->createPersonalClient();
        $data = ['name' => 'nombretest','last_name' => 'apellidotest','email' => 'emailtest@gmail.com','password' => 'passwordtest'];
        $user1 = $this->postJson('/api/auth/register', $data);
        $data2 = ['name' => 'nombretest2','last_name' => 'apellidotest2','email' => 'emailtest2@gmail.com','password' => 'passwordtest2'];
        $user2 = $this->postJson('/api/auth/register', $data2);
        $jsonResponse = $user1->json();
        $token = $jsonResponse['data']['token'];
        $account = Account::where('user_id', $jsonResponse['data']['user']['id'])->where('currency', 'ARS')->first();
        $account->balance = $this->initialBalance;
        $account->save();

        // Send transaction
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/transactions/send', [
            "sender_account_id" => 1,
            "receiver_account_id" => 4,
            "amount" => 100,
            "description"=> "Envio de dinero"
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', 'The selected receiver account id is invalid.')
            ->assertJsonPath('errors.receiver_account_id', ['The selected receiver account id is invalid.']);

        $this->assertDatabaseMissing('transactions', ['id' => $response->json('data.payment_transaction.id')]);
        $this->assertDatabaseMissing('transactions', ['id' => $response->json('data.income_transaction.id')]);
        $this->assertDatabaseHas('accounts', ['id' => $user1->json('data.user.id'), 'balance' => $this->initialBalance]);
        $this->assertDatabaseHas('accounts', ['id' => $user2->json('data.user.id'), 'balance' => 0]);
    }
}
