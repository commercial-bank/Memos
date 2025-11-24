<?php

namespace App\Models;

use App\Models\WrittenMemo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Memo extends Model
{
     // C'est la table pivot qui contient l'action
    protected $table = 'memos'; 

    protected $fillable = [
        'written_memo_id',
        'entity_id',
        'action'
    ];


     // Relation vers le WrittenMemo (le parent)
    // Lien vers le mémo écrit (Le parent)
    public function writtenMemo(): BelongsTo
    {
        return $this->belongsTo(WrittenMemo::class);
    }

    // Lien vers le destinataire (L'entité)
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }
}
