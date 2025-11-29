<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class References extends Model
{
    protected $fillable = [
        'nature',
        'date',
        'numero_ordre_path',
        'object',
        'concerne',
        'memo_id',
    ];
}
