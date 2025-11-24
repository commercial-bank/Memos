<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
     // C'est la table pivot qui contient l'action
    protected $table = 'memos'; 

    protected $fillable = [
        'written_memo_id',
        'entity_id',
        'action'
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }
}
