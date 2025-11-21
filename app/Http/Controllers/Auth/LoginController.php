<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class LoginController extends Controller
{
    /**
     * Afficher le formulaire de connexion.
     */
    public function create(): View
    {
        return view('auth/Login');
    }

    /**
     * Gérer la requête de connexion.
     */


    // Traite la connexion LDAP
    public function store(Request $request)
    {
       
        // 1. Validation des champs
        $credentials = $request->validate([
            'user_name' => ['required', 'string'], // On valide 'user_name'
            'password' => ['required', 'string'],
        ]);

        // 2. Tentative de connexion
        // Laravel va voir que le provider est 'ldap' (dans config/auth.php)
        // Il va envoyer 'user_name' et 'password' à l'Active Directory
        $remember = $request->filled('remember_me');

        if (Auth::attempt($credentials, $remember)) {
            
            // 3. Si succès : on régénère la session (sécurité)
            $request->session()->regenerate();

            // 4. On redirige vers le dashboard (ou la page voulue)
            return redirect()->intended('dashboard');
        }

        // 5. Si échec : on renvoie avec une erreur
        return back()->withErrors([
            'user_name' => 'Les identifiant',
        ])->onlyInput('user_name');
    }

    /**
     * Déconnecter l'utilisateur.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout(); // Déconnecte l'utilisateur

        $request->session()->invalidate(); // Invalide la session actuelle
        $request->session()->regenerateToken(); // Régénère le jeton CSRF

        return redirect('/'); // Redirige vers la page d'accueil après déconnexion
    }
}
