<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class References extends Model
{
    protected $fillable = [
        'nature',
        'date',
        'numero_ordre',
        'lettre_type_ordre',
        'memo_id',
    ];
}
