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
        $table->foreignId('previous_holder_id')->nullable()->constrained('users');
    });

        // 2. Pour le circuit "Aval" (Une fois arrivé chez les destinataires)
        Schema::table('memos', function (Blueprint $table) {
            // Statut interne à l'entité (ex: 'recu', 'transmis_dg', 'traité')
            $table->string('local_status')->default('waiting'); 
            
            // Qui détient le mémo DANS l'entité (ex: la secrétaire RH, puis le DRH)
            $table->foreignId('local_holder_id')->nullable()->constrained('users'); 
            
            // La référence "Arrivée" enregistrée par la secrétaire destinataire
            $table->string('incoming_reference_number')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
