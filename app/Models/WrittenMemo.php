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
        'user_id',
    ];

    public function user()
        
    {
        return $this->belongsTo(User::class);   
    }
}
