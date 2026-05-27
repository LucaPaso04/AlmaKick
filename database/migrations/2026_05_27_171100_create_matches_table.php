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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->time('time');
            $table->string('format', 20)->comment('e.g., 5v5, 7v7');
            $table->integer('max_players');
            $table->string('location');
            $table->decimal('latitude', 10, 8)->nullable()->comment('For Leaflet Maps API');
            $table->decimal('longitude', 11, 8)->nullable()->comment('For Leaflet Maps API');
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->decimal('total_cost', 8, 2)->default(0.00);
            $table->enum('status', ['open', 'full', 'finished', 'cancelled'])->default('open');
            $table->string('cancellation_reason')->nullable()->comment('e.g., Bad weather');
            $table->boolean('is_urgent')->default(false)->comment('If a player is missing');
            $table->integer('result_home')->nullable();
            $table->integer('result_away')->nullable();
            $table->dateTime('mvp_deadline')->nullable();
            $table->boolean('mvp_assigned')->default(false);
            $table->foreignId('mvp_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
