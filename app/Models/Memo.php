<?php

namespace App\Models;

use App\Models\Destinataires;
use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    protected $fillable = [
        'object',
        'concern',
        'content',
        'status',
        'current_holder_id',
        'previous_holder_id',
        'signature_sd',
        'signature_dir',
        'qr_code',
        'workflow_comment',
        'user_id',
    ];

    public function user()
    {
        // Laravel devine automatiquement que la clé étrangère est 'user_id'
        return $this->belongsTo(User::class);
    }


    /**
     * Relation vers les destinataires (qui sont des entités).
     */
    public function destinataires()
    {
        // Une mémo "appartient à plusieurs" entités via la table 'destinataires'
        return $this->belongsToMany(Entity::class, 'destinataires', 'memo_id', 'entity_id')
                    ->withPivot('action') // Pour récupérer le champ 'action' de la table pivot
                    ->withTimestamps();
    }

    

    /**
     * Relation vers le détenteur actuel (User).
     */
    public function currentHolder()
    {
        return $this->belongsTo(User::class, 'current_holder_id');
    }
    
}
