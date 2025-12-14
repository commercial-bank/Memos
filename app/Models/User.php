<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Memo;
use App\Models\Entity;
use App\Models\WrittenMemo;

// 1. AJOUTER CES DEUX IMPORTATIONS
use Illuminate\Notifications\Notifiable;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

// 2. AJOUTER "implements LdapAuthenticatable"
class User extends Authenticatable implements LdapAuthenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    // 3. AJOUTER LE TRAIT "AuthenticatesWithLdap"
    use HasFactory, Notifiable, AuthenticatesWithLdap;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    // Ton tableau fillable est PARFAIT, il contient bien tout ce qu'on a configuré.
    protected $fillable = [
        'first_name',
        'last_name',
        'user_name',
        'email',
        'email_verified_at',
        'guid',
        'domain',
        'password',
        'poste',
        'entity_id',
        'sous_direction_id',
        'departement',
        'service',
        'is_admin',
        'is_active',
        'manager_id',
    ];


    public function memos()
    {
        return $this->hasMany(Memo::class);
    }

    // ...
    public function replacements()
    {
        // Les remplacements que j'ai définis (je suis absent)
        return $this->hasMany(ReplacesUser::class, 'user_id');
    }

    public function replacing()
    {
        // Les remplacements où je suis le suppléant (je remplace quelqu'un)
        return $this->hasMany(ReplacesUser::class, 'user_id_replace');
    }
    // ...



    public function sentMemos()
    {
        // Un User a plusieurs Memos... à travers les WrittenMemos
        return $this->hasManyThrough(Memo::class, WrittenMemo::class);
    }


    public function favorites()
    {
        return $this->belongsToMany(Memo::class, 'favoris', 'user_id', 'memo_id')->withTimestamps();
    }

    public function sousDirection() {
         return $this->belongsTo(SousDirection::class); // Ou le nom exact de ta classe
    }

     /**
     * Relation avec l'Entité (table entities)
     */
    public function entity()
    {
        // Laravel va chercher automatiquement la colonne 'entity_id'
        return $this->belongsTo(Entity::class);
    }

     /**
     * Relation pour le manager (N+1) - Utile aussi
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

       

    /**
     * Accesseur pour créer l'acronyme de l'entité du user.
     * Utilisation : $user->entity_acronym
     */
    public function getEntityAcronymAttribute()
    {
        // Si le champ est vide, on ne renvoie rien
        if (empty($this->entity_name)) {
            return '';
        }

        // 1. On remplace les tirets par des espaces
        $name = str_replace('-', ' ', $this->entity_name);

        // 2. On découpe en mots
        $words = explode(' ', $name);
        $acronym = '';

        // 3. On prend la première lettre de chaque mot
        foreach ($words as $word) {
            if (!empty($word)) {
                $acronym .= mb_substr($word, 0, 1);
            }
        }

        // 4. On retourne en majuscules
        return mb_strtoupper($acronym);
    }


    /**
     * Vérifie si le profil est incomplet.
     */
    public function hasIncompleteProfile(): bool
    {
        // Liste des champs OBLIGATOIRES pour utiliser l'app
        $requiredFields = [
            'poste',
            'entity_id',
        ];

        foreach ($requiredFields as $field) {
            if (empty($this->$field)) {
                return true; // Le profil est incomplet
            }
        }

        return false; // Tout est bon
    }

        /**
     * Vérifie si le compte de l'utilisateur est désactivé.
     */
        public function isInactive(): bool
        {
            // Retourne true si is_active est à 0 (false) ou null
            return !$this->is_active;
        }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}