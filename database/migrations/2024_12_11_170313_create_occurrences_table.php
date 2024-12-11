<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('occurrences', function (Blueprint $table) {
            $table->id();
            
            // Reporter Information
            $table->string('reporter_name');
            $table->string('reporter_mobile');
            $table->string('reporter_email');
            $table->string('organization');
            
            // Occurrence Details
            $table->date('occurrence_date');
            $table->time('occurrence_time');
            $table->string('occurrence_location');
            $table->string('occurrence_type'); // Bird Strike, Runway Incursion, etc.
            $table->text('occurrence_description');
            $table->text('immediate_actions_taken');
            
            // Investigation Details
            $table->text('investigation_findings')->nullable();
            $table->text('root_causes')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('investigation_status')->default('pending'); // pending, in-progress, completed
            
            // Impact Details
            $table->boolean('injuries_reported')->default(false);
            $table->text('injury_details')->nullable();
            $table->boolean('damage_reported')->default(false);
            $table->text('damage_details')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            
            // Weather Conditions
            $table->string('weather_conditions')->nullable();
            $table->string('visibility')->nullable();
            $table->string('wind_direction')->nullable();
            $table->string('wind_speed')->nullable();
            
            // Aircraft Details (if applicable)
            $table->string('aircraft_type')->nullable();
            $table->string('aircraft_registration')->nullable();
            $table->string('flight_phase')->nullable();
            
            // Attachments & Evidence
            $table->json('attachments')->nullable();
            
            // Status and Classification
            $table->string('status')->default('open'); // open, closed
            $table->string('severity_level')->nullable();
            $table->string('probability_level')->nullable();
            $table->string('risk_level')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('occurrences');
    }
};