<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'ldap', // On utilise le provider défini ci-dessous
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        // Provider classique Eloquent (base de données locale)
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // Provider LDAP (LdapRecord)
        'ldap' => [
            'driver' => 'ldap',
            'model' => App\Ldap\User::class, // Modèle LDAP
            'rules' => [],
            'authenticate_with' => env('LDAP_AUTHENTICATE_WITH', 'mail'),
            'database' => [
                'model' => App\Models\User::class, // Modèle Local
                'sync_passwords' => false,
                'sync_attributes' => [
                    // Colonne DB Local => Attribut LDAP
                    'user_name'  => 'samaccountname',
                    'first_name' => 'givenname',
                    'last_name'  => 'sn',
                    'email'      => 'mail',
                    'poste'      => 'title',
                    'entity'     => 'department',
                ],
                'sync_existing'=>[
                    'email'=>'mail'
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];