@include('layouts.authHead')


<div class="min-h-screen flex items-center justify-center bg-gray-100 p-6" style="background-color: #f0f0f0;">
  <div class="max-w-xl w-full p-10 bg-white rounded-2xl shadow-3xl" style="border-top: 8px solid #b8962f;">
    <div class="text-center mb-10">
      <!-- Placeholder pour le logo -->
      <div class="w-32 h-32 mx-auto mb-6 bg-gray-300 rounded-full flex items-center justify-center text-white font-bold text-4xl shadow-inner" style="background-color: #b8962f;">
        LOGO
      </div>
      <h2 class="text-4xl font-extrabold text-gray-900">Welcome back!</h2>
    </div>

    <form method="POST" action="{{ route('login.store') }}">
      @csrf

      {{-- Affichage des erreurs générales ou spécifiques à l'email --}}
      @error('user_name')
          <p class="text-red-500 text-sm italic mb-4">{{ $message }}</p>
      @enderror
      {{-- Affichage des erreurs générales ou spécifiques au mot de passe --}}
      @error('password')
          <p class="text-red-500 text-sm italic mb-4">{{ $message }}</p>
      @enderror

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
            <input 
                type="text" 
                id="user_name" 
                name="user_name" 
                value="{{ old('user_name') }}" 
                class="w-full pl-12 pr-6 py-4 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-[#b8962f] placeholder-gray-400 text-white text-lg" 
                placeholder="Identifiant (ex: cbc_digitalis)" 
                style="background-color: #5c5c5c;" 
                required 
                autofocus
            >
          </div>
        </div>

      <div class="mb-8">
        <label for="password" class="sr-only">Password</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 flex items-center pl-4">
            <svg class="h-6 w-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
          </span>
          <input type="password" id="password" name="password" class="w-full pl-12 pr-6 py-4 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-[#b8962f] placeholder-gray-400 text-white text-lg" placeholder="Password" style="background-color: #5c5c5c;" required>
        </div>
      </div>

      <div class="flex items-center justify-between mb-8 text-base">
        <div class="flex items-center">
          <input type="checkbox" id="remember_me" name="remember_me" class="h-5 w-5 text-[#b8962f] focus:ring-[#b8962f] border-gray-300 rounded-md">
          <label for="remember_me" class="ml-3 block text-gray-700">Remember me</label>
        </div>
        
      </div>

      <div>
        <button type="submit" class="w-full flex justify-center py-4 px-8 border border-transparent rounded-full shadow-xl text-xl font-bold text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#b8962f]" style="background-color: #b8962f;">
          Log In
        </button>
      </div>
    </form>

  </div>
</div>

<style>
  :root {
    --primary-color: #b8962f;          /* Or/Jaune foncé du logo */
    --secondary-color: #5c5c5c;         /* Gris foncé du logo */
  }
</style>


           



@include('layouts.authHead')