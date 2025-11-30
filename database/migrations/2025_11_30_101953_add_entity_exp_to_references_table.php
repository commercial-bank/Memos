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
        Schema::table('references', function (Blueprint $table) {
            // On ajoute le champ, 'nullable' est conseillé si tu as déjà des lignes existantes
            // 'after' permet de placer la colonne après 'concerne' (plus propre)
            $table->string('entity_exp')->nullable()->after('concerne');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('references', function (Blueprint $table) {
            $table->dropColumn('entity_exp');
        });
    }
};
