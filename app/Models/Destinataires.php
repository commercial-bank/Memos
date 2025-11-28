<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destinataires extends Model
{
    protected $fillable = [
        'action',
        'memo_id',
        'entity_id',
    ];
}
