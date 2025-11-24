<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WrittenMemo extends Model
{
    protected $fillable = [
        'object',
        'content',
        'type_memo',
        'dest_status',
        'user_id',
    ];

    public function user()
        
    {
        return $this->belongsTo(User::class);   
    }

    /**
     * Un mémo écrit a plusieurs attributions/destinataires (table memos)
     */
    public function memos()
    {
        return $this->hasMany(Memo::class);
    }
}
