<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use DatabaseTransactions;

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
            ->assertJsonPath('mensaje', 'Solicitud procesada con éxito.')
            ->assertJsonPath('datos.user.name', $data['name'])
            ->assertJsonPath('datos.user.last_name', $data['last_name'])
            ->assertJsonPath('datos.user.email', $data['email']);

        $this->assertDatabaseHas('users', ['email' => $data['email'],]);
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
}
