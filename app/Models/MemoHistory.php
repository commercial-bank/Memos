<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MemoHistory extends Model
{
     protected $fillable = ['written_memo_id', 'actor_id', 'action', 'comment'];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
