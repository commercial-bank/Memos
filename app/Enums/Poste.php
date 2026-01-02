<?php

namespace App\Enums;

enum Poste: string
{
    case STAGIAIRE = 'Stagiaire Professionnel';
    case EMPLOYE = 'Employer'; 
    case CHEF_SERVICE = 'Chef-Service';
    case CHEF_DEPARTEMENT = 'Chef-Departement';
    case SECRETAIRE = 'Secretaire';
    case SOUS_DIRECTEUR = 'Sous-Directeur';
    case DIRECTEUR = 'Directeur';

    // Optionnel : une méthode pour obtenir un libellé propre si besoin
    public function label(): string
    {
        return match($this) {
            self::STAGIAIRE => 'Stagiaire Professionnel',
            self::EMPLOYE => 'Employé',
            self::CHEF_SERVICE => 'Chef de Service',
            self::CHEF_DEPARTEMENT => 'Chef de Département',
            self::SECRETAIRE => 'Secrétaire / Assistante',
            self::SOUS_DIRECTEUR => 'Sous-Directeur',
            self::DIRECTEUR => 'Directeur',
        };
    }
}