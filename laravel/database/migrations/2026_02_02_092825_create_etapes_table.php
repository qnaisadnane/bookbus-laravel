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
       Schema::create('etapes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('gare_id')->constrained('gares')->onDelete('cascade');
    $table->foreignId('segment_id')->constrained('segments')->onDelete('cascade');
    $table->foreignId('route_id')->constrained('route')->onDelete('cascade');
    $table->integer('ordre');
    $table->time('heure_passage');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etapes');
    }
};
