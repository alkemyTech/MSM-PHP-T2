<?php

namespace Database\Factories;

use App\Models\Account;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currency = Account::all()->pluck('currency')->toArray();
        if ($currency === 'ARS') {
            $amount = fake()->randomFloat(2,0,300000);
        } else {
            $amount = fake()->randomFloat(2,0,1000);
        }

        $account_id = Account::all()->pluck('id')->toArray();
        
        $type_array = ['INCOME','PAYMENT','DEPOSIT'];
        $type = fake()->randomElement($type_array);

        switch($type) {
            case 'INCOME':
                $description_array = ['Cobro de Haberes', 'Deposito ATM', 'Acreditación de Plazo Fijo', 'Cobro de Honorarios'];
                $description = fake()->randomElement($description_array);
                break;
            case 'PAYMENT':
                $description_array =['Pago de Haberes','Pago de Seguro','Pago de Alquiler','Pago Tarjeta VISA'];
                $description = fake()->randomElement($description_array);
                break;
            case 'DEPOSIT':
                $description_array = ['Deposito en ATM'];
                $description = fake()->randomElement($description_array);
                break;
            default:
                $description = "Other";
                break;
        }

        return [
            'amount' => $amount, 
            'description' => $description,
            'type' => $type, 
            'account_id' => fake()->randomElement($account_id,)
        ];
    }
}
