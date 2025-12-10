<?php

namespace App\Models;

use App\Models\User;
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

     // C'EST ICI QUE LA MAGIE OPÈRE :
    protected $casts = [
        // Cela dit à Laravel : "Transforme l'array en JSON quand tu sauvegardes"
        // et "Transforme le JSON en array quand tu lis"
        'action_replace' => 'array', 
        
        'date_begin_replace' => 'date',
        'date_end_replace' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function substitute()
    {
        return $this->belongsTo(User::class, 'user_id_replace');
    }
}
