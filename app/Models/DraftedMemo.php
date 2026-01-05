<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DraftedMemo extends Model
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
        'qr_code',
        'workflow_direction', 

        //Pieces Jointe
        'pieces_jointes',

        //destinataires
        'destinataires',
        
        'user_id',

        'parent_id',
    ];

    protected $casts = [
        // ⚠️ IMPORTANT : La colonne 'current_holder_id' doit être de type JSON dans la migration
        // si tu veux que ce cast fonctionne. Sinon, retire cette ligne.
        'current_holders' => 'array',

        // Convertit automatiquement le JSON de la BDD en tableau PHP
        'previous_holders'  => 'array',

        // Conversion automatique JSON <-> Array
        'pieces_jointes' => 'array',
        'destinataires' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
