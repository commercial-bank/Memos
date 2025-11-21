<?php

namespace App\Ldap;

// On importe le modèle Active Directory de base
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

// On l'étend ici (au lieu de 'Model')
class User extends LdapUser
{
    // Tu n'as besoin de rien mettre ici pour l'instant.
    // Le parent 'LdapUser' contient déjà les objectClasses 
    // (top, person, organizationalPerson, user).
}