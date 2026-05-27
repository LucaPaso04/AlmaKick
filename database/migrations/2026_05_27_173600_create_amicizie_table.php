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
        Schema::create('amicizie', function (Blueprint $table) {
            $table->foreignId('id_utente_richiedente')->constrained('users')->cascadeOnDelete();
            $table->foreignId('id_utente_ricevente')->constrained('users')->cascadeOnDelete();
            $table->enum('stato', ['pending', 'accepted', 'declined', 'blocked'])->default('pending');
            $table->timestamp('created_at')->nullable();

            $table->primary(['id_utente_richiedente', 'id_utente_ricevente']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amicizie');
    }
};
