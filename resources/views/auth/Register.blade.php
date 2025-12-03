@include('layouts.authHead')


<div class="min-h-screen flex items-center justify-center bg-gray-100 p-4" style="background-color: #f0f0f0;">
  <div class="max-w-180 w-full p-10 bg-white rounded-xl shadow-2xl" style="border-top: 5px solid #b8962f;">
    <div class="text-center mb-8">
      <!-- Placeholder pour le logo -->
      <div class="w-28 h-28 mx-auto mb-4 bg-gray-300 rounded-full flex items-center justify-center text-white font-bold text-3xl shadow-inner" style="background-color: #b8962f;">
        LOGO
      </div>
      <h2 class="text-3xl font-extrabold text-gray-900">Créer un compte</h2>
    </div>

    <form method="POST" action="{{ route('register.store') }}">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
        <div>
          <label for="first_name" class="sr-only">Prénom</label>
          <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" class="w-full px-5 py-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-[#b8962f] placeholder-gray-400 text-white" placeholder="Prénom" style="background-color: #5c5c5c;" required autofocus>
          @error('first_name')
              <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="last_name" class="sr-only">Nom</label>
          <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" class="w-full px-5 py-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-[#b8962f] placeholder-gray-400 text-white" placeholder="Nom" style="background-color: #5c5c5c;" required>
          @error('last_name')
              <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>

      <div class="mb-4">
        <label for="email" class="sr-only">Adresse Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full px-5 py-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-[#b8962f] placeholder-gray-400 text-white" placeholder="Adresse Email" style="background-color: #5c5c5c;" required>
        @error('email')
            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-4">
        <label for="password" class="sr-only">Mot de passe</label>
        <input type="password" id="password" name="password" class="w-full px-5 py-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-[#b8962f] placeholder-gray-400 text-white" placeholder="Mot de passe" style="background-color: #5c5c5c;" required>
        @error('password')
            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-6">
        <label for="password_confirmation" class="sr-only">Confirmer le mot de passe</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="w-full px-5 py-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-[#b8962f] placeholder-gray-400 text-white" placeholder="Confirmer le mot de passe" style="background-color: #5c5c5c;" required>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
        <div>
          <label for="statut" class="sr-only">Statut</label>
          <select id="statut" name="statut" class="w-full px-5 py-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-[#b8962f] text-white" style="background-color: #5c5c5c;" required>
              <option value="" disabled selected class="text-gray-400">Sélectionner le statut</option>
              <option value="Actif" {{ old('statut') == 'Actif' ? 'selected' : '' }} class="text-gray-900">Actif</option>
              <option value="Inactif" {{ old('statut') == 'Inactif' ? 'selected' : '' }} class="text-gray-900">Inactif</option>
          </select>
          @error('statut')
              <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="function" class="sr-only">Fonction</label>
          <input type="text" id="function" name="function" value="{{ old('function') }}" class="w-full px-5 py-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-[#b8962f] placeholder-gray-400 text-white" placeholder="Fonction" style="background-color: #5c5c5c;" required>
          @error('function')
              <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>

      <div class="mb-4">
        <label for="higher_level" class="sr-only">Niveau Hiérarchique</label>
        <input type="text" id="higher_level" name="id_higher_level" value="{{ old('higher_level') }}" class="w-full px-5 py-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-[#b8962f] placeholder-gray-400 text-white" placeholder="Niveau Hiérarchique" style="background-color: #5c5c5c;" required>
        @error('higher_level')
            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-8">
        <label for="entity_id" class="sr-only">Entité</label>
        <input type="text" id="entity_id" name="entity_id" value="{{ old('entity_id') }}" class="w-full px-5 py-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-[#b8962f] placeholder-gray-400 text-white" placeholder="Entité" style="background-color: #5c5c5c;" required>
        @error('entity_id')
            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <button type="submit" class="w-full flex justify-center py-3 px-6 border border-transparent rounded-full shadow-lg text-lg font-semibold text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#b8962f]" style="background-color: #b8962f;">
          S'inscrire
        </button>
      </div>
    </form>

    <div class="mt-8 text-center text-sm">
      <p class="text-gray-700">Tu as déjà un compte ? <a href="{{ route('login') }}" class="font-bold hover:underline" style="color: #b8962f;">Connecte-toi ici</a></p>
    </div>
  </div>
</div>


           



@include('layouts.authHead')