<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hazard_report_id')->constrained()->onDelete('cascade');
            $table->text('executive_summary');
            $table->text('risk_analysis');
            $table->text('impact_assessment');
            $table->text('mitigation_measures');
            $table->text('implementation_timeline');
            $table->text('monitoring_requirements');
            $table->string('generated_by');
            $table->string('status');
            $table->timestamp('generated_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_assessments');
    }
};