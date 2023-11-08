<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) { // tabla accounts para los datos de las cuentas de los usuarios
            $table->id(); // ID de la cuenta
            $table->enum('currency', ['ARS', 'USD']); // Moneda de la cuenta: ARS (pesos argentinos) o USD (dólares estadounidenses)
            $table->double('transaction_limit'); // Límite de transacción de la cuenta
            $table->double('balance'); // Saldo actual de la cuenta
            $table->foreignId('user_id'); // ID del usuario propietario de la cuenta
            $table->string('cbu', 22)->unique(); // Número de CBU (único) de la cuenta
            $table->timestamps(); // created_at y updated_at
            $table->boolean('deleted')->default(false); // Indica si la cuenta ha sido eliminada (por defecto, no)

            $table->foreign('user_id')->references('id')->on('users'); // hace la relación que la columna user_id pertenece al id de la tabla users
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
