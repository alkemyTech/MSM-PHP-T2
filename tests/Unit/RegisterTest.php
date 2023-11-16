<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\Role;
use Tests\TestCase;

class RegisterTest extends TestCase
{

    public function test_successful_register_endpoint(): void
    {
        $data = [
            'name' => 'nombretest',
            'last_name' => 'apellidotest',
            'email' => 'emailtest@gmail.com',
            'password' => 'passwordtest'
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response
            ->assertStatus(201)
            ->assertJsonPath('message', 'Request successfully processed')
            ->assertJsonPath('data.user.name', $data['name'])
            ->assertJsonPath('data.user.last_name', $data['last_name'])
            ->assertJsonPath('data.user.email', $data['email']);

        $this->assertDatabaseHas('users', ['email' => $data['email']]);
    }

    public function test_cbu_and_accounts_success_generation_register_endpoint(): void
    {
        $data = [
            'name' => 'nombretest',
            'last_name' => 'apellidotest',
            'email' => 'emailaccounttest@gmail.com',
            'password' => 'passwordtest'
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response
            ->assertStatus(201)
            ->assertJsonPath('message', 'Request successfully processed');

        $this->assertDatabaseHas('accounts', ['user_id' => $response->json('data.user.id')]);

        $userAccounts = Account::where('user_id', $response->json('data.user.id'))->get();
        $this->assertCount(2, $userAccounts);

        $arsAccount = $userAccounts->where('currency', 'ARS')->first();
        $usdAccount = $userAccounts->where('currency', 'USD')->first();

        $this->assertNotNull($arsAccount);
        $this->assertNotNull($usdAccount);

        $this->assertEquals(22, strlen($arsAccount->cbu));
        $this->assertEquals(22, strlen($usdAccount->cbu));
    }


    public function test_assigned_user_role_register_endpoint(): void
    {
        $data = [
            'name' => 'nombretest',
            'last_name' => 'apellidotest',
            'email' => 'emailroletest@gmail.com',
            'password' => 'passwordtest'
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response
            ->assertStatus(201)
            ->assertJsonPath('message', 'Request successfully processed');

        $role_id = Role::where('name', 'USER')->value('id');
        $this->assertDatabaseHas('users', ['role_id' => $role_id]);
    }

    public function test_invalids_fields_register_endpoint(): void
    {
        $data = [
            'name' => 1234,
            'last_name' => 'apellidotest',
            'email' => 'emailtest',
            'password' => 'passwordtest'
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_email_already_taken_register_endpoint(): void
    {
        $data = [
            'name' => 'nombretest',
            'last_name' => 'apellidotest',
            'email' => 'emailtest@gmail.com',
            'password' => 'passwordtest'
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response
            ->assertStatus(422)
            ->assertInvalid([
                'email' => 'The email has already been taken.',
            ]);
    }
}
