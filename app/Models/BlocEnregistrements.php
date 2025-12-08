<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlocEnregistrements extends Model
{
    protected $table = 'blocs_enregistrements';

    protected $fillable = [
        'nature_memo',
        'date_enreg',
        'reference',
        'memo_id',
        'user_id',
    ];

    // Relation vers le Mémo pour récupérer l'objet et le concerne
    public function memo()
    {
        return $this->belongsTo(Memo::class);
    }

    // Relation vers l'User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
