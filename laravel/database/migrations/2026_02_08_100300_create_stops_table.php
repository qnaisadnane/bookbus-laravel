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
        Schema::create('stops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('route_id');
            $table->unsignedBigInteger('station_id');
            $table->integer('order'); // Ordre de l'arrêt sur la ligne
            $table->integer('duration_minutes')->default(0); // Durée de l'arrêt
            $table->timestamps();
            
            $table->foreign('route_id')->references('id')->on('route')->onDelete('cascade');
            $table->foreign('station_id')->references('id')->on('stations')->onDelete('restrict');
            $table->unique(['route_id', 'station_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stops');
    }
};
