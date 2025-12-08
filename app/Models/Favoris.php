<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favoris extends Model
{
    protected $fillable = [
        'memo_id',
        'user_id',
    ];
}
