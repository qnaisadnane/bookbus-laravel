<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Affecter un bus ET un chauffeur à un trajet
     */
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('bus_id');
            $table->unsignedBigInteger('driver_id');
            $table->enum('status', ['assigned', 'confirmed', 'completed', 'cancelled'])->default('assigned');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->foreign('bus_id')->references('id')->on('bus')->onDelete('restrict');
            $table->foreign('driver_id')->references('id')->on('employees')->onDelete('restrict');
            
            // Un bus ne peut être affecté qu'à un trajet à la fois
            $table->unique(['trip_id', 'bus_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
