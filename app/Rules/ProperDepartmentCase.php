<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ProperDepartmentCase implements Rule
{
    protected $lowerWords = [
         'à', 'de', 'du', 'des', 'pour', 'sur', 'dans', 'en',
        'mais', 'ou', 'et', 'donc', 'or', 'ni', 'car'
    ];

    public function passes($attribute, $value)
    {
        $words = explode(' ', $value);

        foreach ($words as $word) {

            // Si c'est une préposition ou conjonction → doit être en minuscule
            if (in_array(mb_strtolower($word), $this->lowerWords)) {
                if ($word !== mb_strtolower($word)) {
                    return false;
                }
            } 
            else {
                // Pour les autres mots → doivent commencer par une majuscule
                if (!preg_match('/^[A-ZÀÂÄÉÈÊËÎÏÔÖÙÛÜÇ]/u', $word)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function message()
    {
        return "Le champ :attribute doit avoir des majuscules uniquement au début des mots importants (prépositions et conjonctions en minuscules).";
    }
}
