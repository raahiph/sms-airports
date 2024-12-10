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
        Schema::create('bird_entries', function (Blueprint $table) {
            $table->id();
            
            // Bird Information
            $table->string('common_english_name');
            $table->string('local_name');
            $table->string('scientific_name');
            $table->string('species');
            $table->string('mass_weight');
            $table->string('flight_speed');
            $table->string('length');
            $table->string('wingspan');
            $table->boolean('is_migratory')->default(false);
            $table->text('habitat');
            $table->string('food_diet');
            $table->text('appearance');
            $table->boolean('has_flocks')->default(false);
            
            // Observation Details
            $table->date('date_found');
            $table->text('remarks')->nullable();
            $table->string('entered_by');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bird_entries');
    }
};
