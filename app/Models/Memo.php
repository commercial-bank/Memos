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
     * Relation inverse (si besoin)
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favoris', 'memo_id', 'user_id');
    }
    
    /**
     * Helper pour savoir si le mémo est favori pour l'utilisateur connecté
     * (Utile dans les vues Blade pour colorer l'étoile)
     */
    public function getIsFavoriteAttribute()
    {
        // Attention aux performances si utilisé dans une boucle de 100 éléments sans eager loading.
        // Pour une pagination de 10, c'est acceptable.
        return \Illuminate\Support\Facades\Auth::user()->favorites()->where('memo_id', $this->id)->exists();
    }

    

    /**
     * Relation vers le détenteur actuel (User).
     */
    public function currentHolder()
    {
        return $this->belongsTo(User::class, 'current_holder_id');
    }
    
}
