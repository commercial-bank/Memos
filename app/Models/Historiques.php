<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Historiques extends Model
{
    protected $fillable = [
        'workflow_comment',
        'action',
        'memo_id',
        'user_id',
    ];
}
