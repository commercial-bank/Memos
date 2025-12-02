<?php

namespace App\Models;

use App\Models\Destinataires;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    protected $fillable = [
        'object',
        'reference',
        'concern',
        'content',
        'status',
        
        // GESTION DES DÉTENTEURS
        'current_holders',   // L'ID du détenteur actuel (ou tableau d'IDs si JSON)
        'previous_holders',    // L'historique des anciens (Array/JSON)
        
        // SIGNATURES & WF
        'signature_sd',
        'signature_dir',
        'qr_code',
        'workflow_direction',
        'workflow_comment',
        
        // RÉFÉRENCES
        'numero_ref',        // Si tu utilises toujours celui-ci
        
        'user_id',
    ];

    protected $casts = [
        // ⚠️ IMPORTANT : La colonne 'current_holder_id' doit être de type JSON dans la migration
        // si tu veux que ce cast fonctionne. Sinon, retire cette ligne.
        'current_holders' => 'array',

        // Convertit automatiquement le JSON de la BDD en tableau PHP
        'previous_holders'  => 'array',
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

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites', 'memo_id', 'user_id')->withTimestamps();
    }

    // Accessor pour utiliser $document->is_favorited dans la vue
    public function getIsFavoritedAttribute()
    {
        // Si l'utilisateur n'est pas connecté, ce n'est pas un favori
        if (!Auth::check()) {
            return false;
        }
        
        // Vérifie si l'ID de l'utilisateur actuel existe dans la relation favoritedBy
        return $this->favoritedBy()->where('user_id', Auth::id())->exists();
    }

    

    /**
     * Relation vers le détenteur actuel (User).
     */
    public function currentHolder()
    {
        return $this->belongsTo(User::class, 'current_holder_id');
    }
    
}
