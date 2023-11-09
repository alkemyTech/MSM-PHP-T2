<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = ['id', 'name', 'descrition'];
    protected $hidden = ['created_at', 'updated_at'];
}
