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
        Schema::create('etape', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_gare')->constrained('gare')->onDelete('cascade');
            $table->foreignId('id_segment')->constrained('segment')->onDelete('cascade');
            $table->foreignId('id_route')->constrained('route')->onDelete('cascade');
            $table->int('ordre');
            $table->time('heure_passage');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_etape');
    }
};
