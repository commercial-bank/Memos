<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
        // J'ai retiré la validation 'email' stricte car LDAP utilise souvent des identifiants (ex: jdupont)
        #[Validate('required|string')]
        public string $user_name = ''; 

        #[Validate('required|string')]
        public string $password = '';

        public bool $remember = false;

        /**
         * Handle an incoming authentication request.
         */
        /*public function login(): void
        {
            $this->validate();
            
            // J'ai supprimé le dd($this->password); qui arrêtait le code ici

            $this->ensureIsNotRateLimited();

            // CONFIGURATION LDAP :
            // Si vous utilisez LdapRecord, il faut souvent utiliser le 'samaccountname', 'uid' ou 'mail'.
            // Si votre configuration auth.php utilise le driver 'ldap' par défaut, Auth::attempt suffit.
            
            // Cas 1 : Authentification Standard (Si LdapRecord remplace le provider 'users')
            $credentials = [
                // Changez 'email' ci-dessous par 'samaccountname', 'uid' ou 'mail' selon votre annuaire LDAP
                'samaccountname' => $this->user_name, 
                'password' => $this->password,
            ];

            $credentials = [
                // Changez 'email' ci-dessous par 'samaccountname', 'uid' ou 'mail' selon votre annuaire LDAP
                'user_name' => $this->user_name, 
                'password' => $this->password,
            ];

            // Cas 2 : Si vous avez un guard spécifique (ex: 'ldap') dans config/auth.php
            // if (! Auth::guard('ldap')->attempt($credentials, $this->remember)) { ... }

            if (! Auth::attempt($credentials, $this->remember)) {
                
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'user_name' => __('auth.failed'),
                ]);
            }

            RateLimiter::clear($this->throttleKey());
            Session::regenerate();

            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        }*/

            public function login(): void
{
    $this->validate();
    $this->ensureIsNotRateLimited();

    // ---------------------------------------------------------
    // C'est ici la modification que tu as faite (C'EST BON)
    // ---------------------------------------------------------
    
    // On utilise 'user_name' car c'est le nom de ta colonne dans MySQL
    // On n'utilise PAS 'samaccountname' (qui sert au LDAP)
    $credentials = [
        'user_name' => $this->user_name, 
        'password' => $this->password,
    ];

    // Auth::attempt va utiliser le driver par défaut défini dans config/auth.php
    if (! Auth::attempt($credentials, $this->remember)) {
        
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'user_name' => __('auth.failed'),
        ]);
    }

    RateLimiter::clear($this->throttleKey());
    Session::regenerate();

    $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
}

        /**
         * Ensure the authentication request is not rate limited.
         */
        protected function ensureIsNotRateLimited(): void
        {
            if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
                return;
            }

            event(new Lockout(request()));

            $seconds = RateLimiter::availableIn($this->throttleKey());

            throw ValidationException::withMessages([
                'user_name' => __('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        /**
         * Get the authentication rate limiting throttle key.
         */
        protected function throttleKey(): string
        {
            return Str::transliterate(Str::lower($this->user_name).'|'.request()->ip());
        }
    }; 
?>










<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="min-h-screen flex items-center justify-center bg-gray-100 p-6" style="background-color: #f0f0f0;">
        <div class="max-w-xl w-full p-10 bg-white rounded-2xl shadow-3xl" style="border-top: 8px solid #b8962f;">
            <div class="text-center mb-10">
            <!-- Placeholder pour le logo -->
            <div class="w-32 h-32 mx-auto mb-6 bg-gray-300 rounded-full flex items-center justify-center text-white font-bold text-4xl shadow-inner" style="background-color: #D9B348;">
                LOGO
            </div>
            <h2 class="text-2xl font-extrabold text-gray-900">Welcome back!</h2>
            </div>

            <form wire:submit="login">
            @csrf
                <div class="mb-6">
                    <label for="user_name" class="sr-only">Identifiant (Username)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4">
                        <!-- J'ai changé l'icône pour une icône "User" au lieu de "Email" -->
                        <svg class="h-6 w-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        </span>
                        
                        {{-- MODIFICATIONS ICI : type="text" et name="user_name" --}}
                        <input type="text" wire:model="user_name"
                            id="user_name" 
                            name="user_name" 
                            value="{{ old('user_name') }}" 
                            class="w-full pl-12 pr-6 py-4 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-[#b8962f] placeholder-gray-400 text-white text-lg" 
                            placeholder="Identifiant (ex: cbc_digitalis)" 
                            style="background-color: #5c5c5c;" 
                            required 
                            autofocus
                        >
                        <x-input-error :messages="$errors->get('user_name')" class="mt-2" />
                    </div>
                </div>

                <div class="mb-8">
                    <label for="password" class="sr-only">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-6 w-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </span>
                        <input wire:model="password" type="password" id="password" name="password" class="w-full pl-12 pr-6 py-4 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-[#b8962f] placeholder-gray-400 text-white text-lg" placeholder="Password" style="background-color: #5c5c5c;" required>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center justify-between mb-8 text-base">
                    <div class="flex items-center">
                        <input wire:model="remember" type="checkbox" id="remember_me" name="remember_me" class="h-5 w-5 text-[#b8962f] focus:ring-[#b8962f] border-gray-300 rounded-md">
                        <label for="remember_me" class="ml-3 block text-gray-700">Remember me</label>
                    </div>
                <div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-4 px-8 border border-transparent rounded-full shadow-xl text-xl font-bold text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#b8962f]" style="background-color: #b8962f;">
                    Log In
                    </button>
                </div>  

            </form>

        </div>
    </div>    
</div>