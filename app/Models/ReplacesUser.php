<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplacesUser extends Model
{
    protected $fillable = [
        'user_id',
        'user_id_replace',
        'action_replace',
        'date_begin_replace',
        'date_end_replace',

    ];
}
