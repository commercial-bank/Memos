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
        'numero_ref',    

        //Pieces Jointe
        'pieces_jointes',
        
        'user_id',
    ];

    protected $casts = [
        // ⚠️ IMPORTANT : La colonne 'current_holder_id' doit être de type JSON dans la migration
        // si tu veux que ce cast fonctionne. Sinon, retire cette ligne.
        'current_holders' => 'array',

        // Convertit automatiquement le JSON de la BDD en tableau PHP
        'previous_holders'  => 'array',

        // Conversion automatique JSON <-> Array
        'pieces_jointes' => 'array',
        'created_at' => 'datetime',
    ];




    public function destinataires()
    {
        // Attention au nom du modèle : Destinataire ou Destinataires (selon votre fichier)
        return $this->hasMany(Destinataires::class, 'memo_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
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
