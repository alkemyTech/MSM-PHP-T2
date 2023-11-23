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
        Schema::create('users', function (Blueprint $table) {   // => Creación de la tabla "users" para alojar los datos de usuario
            $table->id();  // ID de usuario
            $table->string('name');  // Nombre del usuario
            $table->string('last_name'); // Apellido del Usuario
            $table->string('email')->unique(); // E-mail del usuario
            $table->string('password'); // Contraseña del Usuario
            $table->foreignId('role_id')->references('id')->on('roles'); // ID del rol del usuario, referenciado a la columna id de la tabla role.
            $table->timestamps(); // Establece las columnas "created_at" y "updated_at"
            $table->boolean('deleted')->default(false); // Indica si el usuario ha sido eliminado. Por default es falso.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }

    
};
