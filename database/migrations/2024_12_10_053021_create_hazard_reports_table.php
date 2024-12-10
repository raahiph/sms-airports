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
        Schema::create('hazard_reports', function (Blueprint $table) {
            $table->id();
            
            // Reporter Information
            $table->string('reporter_name');
            $table->string('reporter_mobile');
            $table->string('reporter_email');
            $table->string('organization');
            
            // Hazard Details
            $table->date('hazard_date');
            $table->time('hazard_time');
            $table->string('hazard_location');
            $table->text('hazard_description');
            $table->text('hazard_reason');
            
            // Risk Assessment
            $table->char('severity', 1);
            $table->char('likelihood', 1);
            $table->string('risk_rating')->nullable();
            
            // Actions
            $table->text('corrective_actions');
            
            // Attachments
            $table->json('attachments')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hazard_reports');
    }
};
