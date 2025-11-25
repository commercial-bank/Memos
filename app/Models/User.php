<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Memo;
use App\Models\WrittenMemo;
use Illuminate\Notifications\Notifiable;

// 1. AJOUTER CES DEUX IMPORTATIONS
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
        'entity',
        'entity_sigle',
        'n1',
        'service'
    ];



    public function writtenMemos()
    {
        return $this->hasMany(WrittenMemo::class); 
    }

    public function sentMemos()
    {
        // Un User a plusieurs Memos... à travers les WrittenMemos
        return $this->hasManyThrough(Memo::class, WrittenMemo::class);
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