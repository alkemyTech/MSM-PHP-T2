<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;
    protected $fillable = ['currency', 'transaction_limit', 'balance', 'user_id', 'cbu', 'deleted'];
    protected $hidden = ['user_id', 'created_at', 'updated_at', 'deleted'];
    protected $with = ['user'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function fixedTerms()
    {
        return $this->hasMany(FixedTerm::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function generateCbu()
    {
        $faker = \Faker\Factory::create();

        do {
            $cbu = $faker->numerify(str_repeat('#', 22)); // genera un cbu aleatorio
            $cbuUsed = Account::where('cbu', $cbu)->first(); // busca si el cbu generado existe en la base de datos
        } while ($cbuUsed); // si existe el cbu en la base de datos va a reiniciar el ciclo y crear uno nuevo

        return $cbu;
    }
}
