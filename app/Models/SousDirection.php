<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SousDirection extends Model
{
    protected $table = 'sous_direction';
    
    protected $fillable = [
        'ref',
        'name',
    ];
    
}
