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
        Schema::create('memo_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('written_memo_id')->constrained()->onDelete('cascade');
            $table->foreignId('actor_id')->constrained('users'); // Celui qui fait l'action
            $table->string('action'); // 'created', 'sent', 'rejected', 'validated', 'distributed'
            $table->text('comment')->nullable(); // Le commentaire ou motif
            $table->string('step_name')->nullable(); // Ex: "Validation Directeur", "Enregistrement"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memo_histories');
    }
};
