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
        $cbu = '';
        for ($i = 0; $i < 22; $i++) {
            $cbu .= rand(0, 9);
        }
        return $cbu;
    }
}
