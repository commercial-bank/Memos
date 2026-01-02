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
        Schema::create('destinataires', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            // Statuts possibles : 'en_cours', 'traite', 'decision_prise'
            $table->string('processing_status')->default('en_cours'); 
            $table->foreignId('memo_id')->constrained()->onDelete('cascade');
            $table->foreignId('entity_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destinataires');
    }
};
