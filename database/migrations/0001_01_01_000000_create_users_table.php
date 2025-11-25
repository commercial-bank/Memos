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
       Schema::create('users', function (Blueprint $table) {
                $table->id();
                
                // IDENTITÉ
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                
                // IMPORTANT : Il faut que le login soit unique !
                $table->string('user_name')->unique(); 
                
                $table->string('email')->unique()->nullable();
                $table->timestamp('email_verified_at')->nullable();
                
                // LDAP
                $table->string('guid')->unique()->nullable();
                $table->string('domain')->nullable();
                
                // PASSWORD : On retire '->change()' car on est dans un Schema::create
                $table->string('password')->nullable(); 

                // INFO MÉTIER (Tout en nullable, c'est parfait)
                $table->string('poste')->nullable();
                $table->string('entity')->nullable();
                $table->string('entity_sigle')->nullable();
                $table->string('n1')->nullable();
                $table->string('service')->nullable();
                
                $table->rememberToken();
                $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
