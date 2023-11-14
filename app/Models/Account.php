<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;
    protected $fillable = ['currency', 'transaction_limit', 'balance', 'user_id', 'cbu', 'deleted'];
    protected $hidden = ['user_id', 'created_at', 'updated_at', 'deleted'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
