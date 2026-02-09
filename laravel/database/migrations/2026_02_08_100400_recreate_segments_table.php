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
        // Temporarily disable foreign key checks to drop old segments table
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('segments');
        Schema::enableForeignKeyConstraints();
        
        Schema::create('segments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('route_id');
            $table->unsignedBigInteger('departure_stop_id'); // Stop de départ
            $table->unsignedBigInteger('arrival_stop_id');   // Stop d'arrivée
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->timestamps();
            
            $table->foreign('route_id')->references('id')->on('route')->onDelete('cascade');
            $table->foreign('departure_stop_id')->references('id')->on('stops')->onDelete('cascade');
            $table->foreign('arrival_stop_id')->references('id')->on('stops')->onDelete('cascade');
            
            // Un segment unique entre deux arrêts d'une même ligne
            $table->unique(['route_id', 'departure_stop_id', 'arrival_stop_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('segments');
    }
};
