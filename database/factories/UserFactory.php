<?php

namespace Database\Factories;

use App\Models\Role;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Ramsey\Uuid\Builder\FallbackBuilder;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */

class UserFactory extends Factory

{
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role_id = Role::all()->pluck('id')->toArray();
        $name = fake('es_ES')->firstName($gender = 'male'|'female');
        $last_name = fake('es_ES')->lastName();
        $userName = strtolower($name . '.' . $last_name);
        $email = fake()->freeEmailDomain();
        
        return [
            'name' => $name,
            'last_name' => $last_name,
            'email' => $userName  . '@' . $email,
            'password' => Hash::make(fake()->password()),
            'role_id' => fake()->randomElement($role_id)
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
