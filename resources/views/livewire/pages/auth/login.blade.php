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
    
    // --- LOGIQUE PHP INCHANGÉE ---
    
    #[Validate('required|string')]
    public string $user_name = ''; 

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate();
        $this->ensureIsNotRateLimited();

        $credentials = [
            'user_name' => $this->user_name, 
            'password' => $this->password,
        ];

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

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->user_name).'|'.request()->ip());
    }
}; 
?>

<!-- VUE HTML / BLADE NETTOYÉE -->
<div>
    <!-- Injection des variables CSS localement si elles ne sont pas encore dans votre app.css -->
    <style>
        :root {
            --c-primary: #daaf2c;      /* Or */
            --c-secondary: #707173;    /* Gris */
            --c-dark: #000000;         /* Noir */
            --c-light: #f9fafb;        /* Fond très clair */
            --c-white: #ffffff;
        }
        
        /* Classes utilitaires personnalisées pour la charte */
        .text-primary { color: var(--c-primary); }
        .bg-primary { background-color: var(--c-primary); }
        .border-primary { border-color: var(--c-primary); }
        
        .ring-focus:focus-within {
            box-shadow: 0 0 0 3px rgba(218, 175, 44, 0.3);
            border-color: var(--c-primary);
        }
    </style>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="min-h-screen flex items-center justify-center p-6" style="background-color: var(--c-light);">
        
        <!-- CARTE DE LOGIN -->
        <div class="max-w-xl w-full bg-white rounded-2xl shadow-xl overflow-hidden relative">
            
            <!-- Barre supérieure décorative (Or) -->
            <div class="h-2 w-full bg-primary"></div>

            <div class="p-10">
                <!-- LOGO -->
                <div class="text-center mb-10">
                    <div class="w-24 h-24 mx-auto mb-6 rounded-full flex items-center justify-center font-bold text-3xl shadow-inner text-black">
                        <!-- Remplacez par votre <img> si nécessaire -->
                        <img src="{{ asset('images/lo.png') }}" alt="">
                    </div>
                    <h2 class="text-3xl font-extrabold" style="color: var(--c-dark);">
                        Bienvenue
                    </h2>
                    <p class="mt-2 text-sm" style="color: var(--c-secondary);">
                        Connectez-vous à votre espace
                    </p>
                </div>

                <form wire:submit="login">
                    @csrf
                    
                    <!-- CHAMP USERNAME -->
                    <div class="mb-6">
                        <label for="user_name" class="sr-only">Identifiant</label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                <!-- Icône User -->
                                <svg class="h-6 w-6 transition-colors duration-200 text-gray-400 group-focus-within:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </span>
                            
                            <input type="text" wire:model="user_name"
                                id="user_name" 
                                name="user_name" 
                                class="w-full pl-12 pr-6 py-4 border border-gray-200 rounded-full focus:outline-none ring-focus transition-all duration-200 bg-gray-50 text-lg placeholder-gray-400" 
                                style="color: var(--c-dark);"
                                placeholder="Identifiant (ex: cbc_digitalis)" 
                                required 
                                autofocus
                            >
                        </div>
                        <x-input-error :messages="$errors->get('user_name')" class="mt-2" />
                    </div>

                    <!-- CHAMP PASSWORD -->
                    <div class="mb-8">
                        <label for="password" class="sr-only">Mot de passe</label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                <!-- Icône Cadenas -->
                                <svg class="h-6 w-6 transition-colors duration-200 text-gray-400 group-focus-within:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </span>
                            
                            <input wire:model="password" type="password" 
                                id="password" 
                                name="password" 
                                class="w-full pl-12 pr-6 py-4 border border-gray-200 rounded-full focus:outline-none ring-focus transition-all duration-200 bg-gray-50 text-lg placeholder-gray-400" 
                                style="color: var(--c-dark);"
                                placeholder="Mot de passe" 
                                required
                            >
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- CHECKBOX -->
                    <div class="flex items-center justify-between mb-8 text-base">
                        <label class="flex items-center cursor-pointer">
                            <input wire:model="remember" type="checkbox" id="remember_me" name="remember_me" 
                                class="h-5 w-5 border-gray-300 rounded focus:ring-opacity-50 text-primary focus:ring-[#daaf2c]"
                                style="color: var(--c-primary);"
                            >
                            <span class="ml-3 block" style="color: var(--c-secondary);">Se souvenir de moi</span>
                        </label>
                    </div>

                    <!-- BOUTON LOGIN -->
                    <div>
                        <button type="submit" 
                            class="w-full flex justify-center py-4 px-8 border border-transparent rounded-full shadow-lg text-xl font-bold transform transition-all duration-200 hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#daaf2c]" 
                            style="background-color: var(--c-primary); color: var(--c-dark);"
                        >
                            Log In
                        </button>
                    </div>  
                </form>
            </div>
        </div>
    </div>    
</div>