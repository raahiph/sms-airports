<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add airport_id to hazard_reports if it doesn't exist
        if (!Schema::hasColumn('hazard_reports', 'airport_id')) {
            Schema::table('hazard_reports', function (Blueprint $table) {
                $table->foreignId('airport_id')
                      ->after('id')
                      ->nullable()
                      ->constrained()
                      ->onDelete('cascade');
            });
        }

        // Add airport_id to bird_entries
        if (!Schema::hasColumn('bird_entries', 'airport_id')) {
            Schema::table('bird_entries', function (Blueprint $table) {
                $table->foreignId('airport_id')
                      ->after('id')
                      ->nullable()
                      ->constrained()
                      ->onDelete('cascade');
            });
        }

        // Add airport_id to occurrences
        if (!Schema::hasColumn('occurrences', 'airport_id')) {
            Schema::table('occurrences', function (Blueprint $table) {
                $table->foreignId('airport_id')
                      ->after('id')
                      ->nullable()
                      ->constrained()
                      ->onDelete('cascade');
            });
        }

        // Add airport_id to risk_assessments
        if (!Schema::hasColumn('risk_assessments', 'airport_id')) {
            Schema::table('risk_assessments', function (Blueprint $table) {
                $table->foreignId('airport_id')
                      ->after('id')
                      ->nullable()
                      ->constrained()
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        // Remove airport_id from all tables
        $tables = ['hazard_reports', 'bird_entries', 'occurrences', 'risk_assessments'];
        
        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'airport_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['airport_id']);
                    $table->dropColumn('airport_id');
                });
            }
        }
    }
};