<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destinataires extends Model
{
    protected $fillable = [
        'action',
        'memo_id',
        'processing_status',
        'completed_at',
        'entity_id',
    ];

    // Cette relation est celle que le "with('destinataires.entity')" cherche
    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    public function memo()
    {
        return $this->belongsTo(Memo::class, 'memo_id');
    }
}
