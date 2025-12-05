<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlocsEnregistrements extends Model
{
    protected $fillable = [
        'nature_memo',
        'date_enreg',
        'reference',
        'memo_id',
        'user_id',
    ];

   
}
