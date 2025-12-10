<?php

namespace App\Models;

use App\Models\Memo;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Historiques extends Model
{
    protected $fillable = [
        'workflow_comment',
        'visa',
        'memo_id',
        'user_id',
    ];

     // Relation inverse : Une ligne d'historique appartient à un User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relation inverse : Une ligne d'historique appartient à un Memo
    public function memo()
    {
        return $this->belongsTo(Memo::class, 'memo_id');
    }
}
