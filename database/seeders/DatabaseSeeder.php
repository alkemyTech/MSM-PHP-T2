<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->count(10)->create(); //En el parámetro del metodo 'count' se puede colocar el numero de la cantidad de registros que se quiere ingresar.
        Account::factory()->count(10)->create(); //En el parámetro del metodo 'count' se puede colocar el numero de la cantidad de registros que se quiere ingresar.
        Transaction::factory()->count(10)->create(); //En el parámetro del metodo 'count' se puede colocar el numero de la cantidad de registros que se quiere ingresar.
    }
}
