<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * TARIFICATION SEGMENTÉE SATAS:
     * - Chaque segment a un prix indépendant
     * - Casa→Marrakech (direct) ≠ Casa→Settat + Settat→Marrakech
     * - La compagnie peut offrir plusieurs prix selon le bus_type ou période
     */
    public function up(): void
    {
        Schema::create('fares', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('segment_id');
            $table->enum('bus_type', ['standard', 'comfort', 'premium'])->default('standard');
            $table->decimal('price', 10, 2); // Prix en MAD
            $table->boolean('active')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->timestamps();
            
            $table->foreign('segment_id')->references('id')->on('segments')->onDelete('cascade');
            
            // Un segment peut avoir plusieurs tarifs (par type de bus ou période)
            $table->unique(['segment_id', 'bus_type', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fares');
    }
};
