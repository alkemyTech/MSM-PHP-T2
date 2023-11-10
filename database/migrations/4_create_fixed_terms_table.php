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
        Schema::create('fixed_terms', function (Blueprint $table) {
            $table->id(); // ID del plazo fijo.
            $table->double('amount'); // Valor del monto a invertir.
            $table->foreignId('account_id')->references('id')->on('accounts'); // ID de la cuenta, referenciado a la columna id de la tabla account.
            $table->double('interest'); // Valor de los intereses a devenegar por el monto invertido.
            $table->double('total'); // Valor total de la inversión, intereses + monto inicial.
            $table->integer('duration'); // Duración en dias del plazo fijo.
            $table->timestamps(); // Columnas "created_at" y "updated_at"
            $table->timestamp('closed_at')->nullable();     // Fecha de finalización del plazo fijo.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_terms');
    }
};
