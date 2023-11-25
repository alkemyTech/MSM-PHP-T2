<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['id', 'amount', 'description', 'type', 'account_id'];
    protected $guarded = ['transaction_date'];
    protected $with = ['account'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

}
