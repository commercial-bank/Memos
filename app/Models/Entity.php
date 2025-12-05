<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{

     

    protected $fillable = [
        'ref',
        'name',
    ];

    protected $appends = ['acronym'];

    /**
     * Accesseur pour récupérer l'acronyme.
     * Exemple : "Direction des Ressources Humaines" -> "DDRH"
     */
    public function getAcronymAttribute()
    {
        // 1. On nettoie la chaine (on remplace les tirets par des espaces pour gérer "Sous-Direction")
        $title = str_replace('-', ' ', $this->title);

        // 2. On découpe la phrase en mots
        $words = explode(' ', $title);

        $acronym = '';

        // 3. On boucle sur chaque mot pour prendre la première lettre
        foreach ($words as $word) {
            // On vérifie que le mot n'est pas vide (cas des doubles espaces)
            if (!empty($word)) {
                $acronym .= mb_substr($word, 0, 1);
            }
        }

        // 4. On retourne le tout en majuscules
        return mb_strtoupper($acronym);
    }

    public static function StatgetAcronymAttribute($string)
    {
        if (empty($string)) return '';

        // Nettoyage et découpage
        $string = str_replace(['-', '\'', '_'], ' ', $string);
        $words = explode(' ', $string);
        $acronym = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $acronym .= mb_substr($word, 0, 1);
            }
        }

        return mb_strtoupper($acronym);
    }
}
