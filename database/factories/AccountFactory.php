<?php

namespace Database\Factories;

use App\Models\User;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $array_currency = ['ARS','USD'];
        $currency = fake()->randomElement($array_currency);

        if ($currency === 'ARS') {
            $transaction_limit = 300000;
        } else {
            $transaction_limit = 1000;
        }

        $user_id = User::all()->pluck('id')->toArray();

        $nro_entidad = 101;
        $nro_sucursal = fake()->numerify('00##');
        $nro_cuenta = fake()->numerify('0000###########');
        $cbu = $nro_entidad . $nro_sucursal . $nro_cuenta;

        return [
            'currency' => $currency, 
            'transaction_limit' => $transaction_limit, 
            'balance'=> 0, 
            'user_id' => fake()->randomElement($user_id),
            'cbu'=> $cbu,
        ];
    }
}
