<?php

namespace Tests\Unit;

use Tests\TestCase;

class LoginTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_log_in_returns_token_and_user(): void
    {
        $logData = [
            'email' => 'test1@gmail.com',
            'password' => 'passwordtest'
        ];

        $registerData = [
            'name' => 'nombretest',
            'last_name' => 'apellidotest',
            'email' => 'test1@gmail.com',
            'password' => 'passwordtest'
        ];
        // Create a user
        $this->createPersonalClient();
        $this->postJson('/api/auth/register', $registerData);

        // Login with the user
        $response = $this->postJson('/api/auth/login', $logData);
        $response
            ->assertStatus(201)
            ->assertJsonPath('message', 'Request successfully processed')
            ->assertJsonPath('data.user.email', $logData['email'])
            ->assertJsonStructure(['data' => ['token', 'user']]);

        $this->assertDatabaseHas('users', ['email' => $logData['email']]);
    }

    public function test_log_in_with_invalid_password(): void
    {
        $logData = [
            'email' => 'test1@gmail.com',
            'password' => 'invalidpasswordtest'
        ];

        $registerData = [
            'name' => 'nombretest',
            'last_name' => 'apellidotest',
            'email' => 'test1@gmail.com',
            'password' => 'passwordtest'
        ];
        // Create a user
        $this->createPersonalClient();
        $this->postJson('/api/auth/register', $registerData);

        // Login with the wrong password
        $response = $this->postJson('/api/auth/login', $logData);
        $response
            ->assertStatus(401)
            ->assertJsonPath('message', 'Unauthorized User');
    }

    public function test_log_in_with_non_existent_email(): void
    {
        $logData = [
            'email' => 'test1@gmail.com',
            'password' => 'invalidpasswordtest'
        ];

        // Login with invalid email
        $response = $this->postJson('/api/auth/login', $logData);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', 'The selected email is invalid.');
    }

    public function test_nule_data_log_in(): void
    {
        $logData = [];

        // Login with no login data
        $response = $this->postJson('/api/auth/login', $logData);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', 'The email field is required. (and 1 more error)')
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
