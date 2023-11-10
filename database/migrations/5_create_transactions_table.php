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
        Schema::create('transactions', function (Blueprint $table) { // tabla transactions para los datos de las transacciones de las cuentas de los usuarios
            $table->id(); // ID de la transacción
            $table->double('amount'); // Monto de la transacción
            $table->enum('type',['INCOME','PAYMENT','DEPOSIT']); // Tipo de transacción: INCOME (ingreso), PAYMENT (pago), DEPOSIT (depósito)
            $table->string('description')->nullable(); // Descripción de la transacción (opcional)
            $table->foreignId('account_id')->references('id')->on('accounts'); // ID de la cuenta a la que pertenece la transacción
            $table->timestamp('transaction_date')->useCurrent(); // Fecha y hora de la transacción (por defecto, la fecha y hora actual)
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};