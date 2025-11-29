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
        Schema::create('memos', function (Blueprint $table) {
            $table->id();
            $table->string('object');
            $table->string('reference');
            $table->string('concern');
            $table->longText('content');
            $table->string('status')->default('brouillon'); 
            $table->json('current_holders')->nullable();
            $table->json('previous_holders')->nullable();
            $table->string('signature_sd')->nullable();
            $table->string('signature_dir')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('workflow_direction')->default('sortant');
            $table->string('workflow_comment')->nullable();
            $table->string('numero_ref')->default('FOR-ME-07-V1');
            $table->boolean('archive_status_sec')->default(false);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('written_memos');
    }
};
