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

    public function memo()
    {
        // Un historique appartient à un Mémo
        return $this->belongsTo(Memo::class);
    }

    

    // C'est cette fonction que le "with('user')" appelle
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
