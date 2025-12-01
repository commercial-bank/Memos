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
        'user_id',
        'memo_id'
    ];

    public function memo()
    {
        return $this->belongsTo(Memo::class);
    }
}
