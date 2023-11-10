<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class FixedTerm extends Model
{
    use HasFactory;
    protected $fillable = ['amount', 'account_id', 'interest', 'total', 'duration'];
    protected $dates = ['closed_at'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $with = ['account'];

    public function account(): BelongsTo    {
        return $this->belongsTo(Account::class);
    }
}
