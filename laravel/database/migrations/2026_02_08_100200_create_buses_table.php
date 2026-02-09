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
        // Rename capacite to capacity if it exists
        if (Schema::hasColumn('bus', 'capacite') && !Schema::hasColumn('bus', 'capacity')) {
            Schema::table('bus', function (Blueprint $table) {
                $table->renameColumn('capacite', 'capacity');
            });
        }
        
        // Rename matricule to registration_number if it exists
        if (Schema::hasColumn('bus', 'matricule') && !Schema::hasColumn('bus', 'registration_number')) {
            Schema::table('bus', function (Blueprint $table) {
                $table->renameColumn('matricule', 'registration_number');
            });
        }
        
        // Add missing columns
        if (!Schema::hasColumn('bus', 'model')) {
            Schema::table('bus', function (Blueprint $table) {
                if (!Schema::hasColumn('bus', 'registration_number')) {
                    $table->string('registration_number')->unique()->nullable();
                }
                $table->string('model')->nullable();
                $table->enum('type', ['standard', 'comfort', 'premium'])->default('standard');
                if (!Schema::hasColumn('bus', 'available_seats')) {
                    $table->integer('available_seats')->nullable();
                }
                $table->boolean('wifi')->default(false);
                $table->boolean('power_outlets')->default(false);
                $table->boolean('toilet')->default(false);
                $table->enum('status', ['in_service', 'maintenance', 'out_of_service'])->default('in_service');
                $table->date('last_maintenance')->nullable();
                $table->date('next_maintenance')->nullable();
                if (!Schema::hasColumn('bus', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
