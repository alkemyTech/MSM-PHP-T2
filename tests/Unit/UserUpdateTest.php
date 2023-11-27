<?php

namespace Tests\Unit;

use Tests\TestCase;

class UserUpdateTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_successful_update_user(): void
    {
        $this->createPersonalClient();
        $data = [
            'name' => 'nombretest',
            'last_name' => 'apellidotest',
            'email' => 'nombre@gmail.com',
            'password' => 'passwordtest',
        ];
        $updateData = [
            'name' => 'nombretest2',
            'last_name' => 'apellidotest2',
            'password' => 'passwordtest2',
        ];

        $registerResponse = $this->postJson('/api/auth/register', $data);
        // Update con autenticación
        $jsonResponse = $registerResponse->json();
        $token = $jsonResponse['data']['token'];
        $updateResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson('/api/auth/me', $updateData);
        
        $updateResponse
            ->assertStatus(200)
            ->assertJsonPath('message', 'Request successfully processed')
            ->assertJsonPath('data.user.name', $updateData['name'])
            ->assertJsonPath('data.user.last_name', $updateData['last_name'])
            ->assertJsonStructure(['data' => ['user']]);
    }

    public function test_update_user_without_authentication(): void
    {
        $this->createPersonalClient();
        $data = [
            'name' => 'nombretest',
            'last_name' => 'apellidotest',
            'email' => 'prueba@gmail.com',
            'password' => 'passwordtest',
        ];
        $updateData = [
            'name' => 'nombretest2',
            'last_name' => 'apellidotest2',
            'password' => 'passwordtest2',
        ];

        $registerResponse = $this->postJson('/api/auth/register', $data);
        // Update sin autenticación
        $updateResponse = $this->patchJson('/api/auth/me', $updateData);
        $updateResponse
            ->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_update_user_without_password(): void
    {
        $this->createPersonalClient();
        $data = [
            'name' => 'nombretest',
            'last_name' => 'apellidotest',
            'email' => 'prueba@gmail.com',
            'password' => 'passwordtest',
        ];
        $updateData = [
            'name' => 'nombretest2',
            'last_name' => 'apellidotest2',
        ];

        $registerResponse = $this->postJson('/api/auth/register', $data);
        // Update sin password
        $jsonResponse = $registerResponse->json();
        $token = $jsonResponse['data']['token'];
        $updateResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson('/api/auth/me', $updateData);
        $updateResponse
            ->assertStatus(200)
            ->assertJsonPath('message', 'Request successfully processed')
            ->assertJsonPath('data.user.name', $updateData['name'])
            ->assertJsonPath('data.user.last_name', $updateData['last_name']);
    }


    public function test_update_user_with_invalid_data(): void
    {
        $this->createPersonalClient();
        $data = [
            'name' => 'nombretest',
            'last_name' => 'apellidotest',
            'email' => 'prueba@gmail.com',
            'password' => 'passwordtest',
        ];
        $updateData = [
            'name' => '',
            'last_name' => 'apellidotest2',
            'password' => 'passwordtest2',
        ];

        $registerResponse = $this->postJson('/api/auth/register', $data);
        // Update con datos inválidos
        $jsonResponse = $registerResponse->json();
        $token = $jsonResponse['data']['token'];
        $updateResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson('/api/auth/me', $updateData);
        $updateResponse
            ->assertStatus(422)
            ->assertJsonPath('message', 'The name field must be a string. (and 1 more error)')
            ->assertJsonPath('errors.name.0', 'The name field must be a string.')
            ->assertJsonPath('errors.name.1', 'The name field must be at least 3 characters.');
    }

    public function test_update_user_with_invalid_field(): void
    {
        $this->createPersonalClient();
        $data = [
            'name' => 'nombretest',
            'last_name' => 'apellidotest',
            'email' => 'prueba@gmail.com',
            'password' => 'passwordtest',
        ];
        $updateData = [
            'name' => '',
            'last_name' => 'apellidotest2',
            'password' => 'passwordtest2',
            'email' => 'prueba2@gmail.com',
        ];

        $registerResponse = $this->postJson('/api/auth/register', $data);
        // Update con datos inválidos
        $jsonResponse = $registerResponse->json();
        $token = $jsonResponse['data']['token'];
        $updateResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson('/api/auth/me', $updateData);
        $updateResponse
            ->assertStatus(422)
            ->assertJsonPath('message', 'The name field must be a string. (and 2 more errors)')
            ->assertJsonPath('errors.email.0', 'The email field is prohibited.');
    }
}
