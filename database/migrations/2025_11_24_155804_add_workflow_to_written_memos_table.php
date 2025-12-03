<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('written_memos', function (Blueprint $table) {
                // Qui détient le mémo pour traitement (Nullable = personne, ou retour au créateur)
            $table->foreignId('current_holder_id')->nullable()->constrained('users');
            
            // Statut du workflow
            $table->string('status')->default('brouillon'); // brouillon, pending, rejected, validated, distributed
            
            // Signatures (Nom de la personne qui a signé)
            $table->string('signature_sd')->nullable(); // Sous-Directeur
            $table->string('signature_dir')->nullable(); // Directeur
            
            // Référence finale (ajoutée par secrétaire)
            $table->string('reference_number')->nullable();
            
            // Commentaires de rejet ou de transmission
            $table->text('workflow_comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('written_memos', function (Blueprint $table) {
            //
        });
    }
};
