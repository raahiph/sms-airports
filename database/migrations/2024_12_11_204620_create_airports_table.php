<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create airports table
        Schema::create('airports', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('country');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add airport_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('airport_id')->after('id')->nullable();
            $table->foreign('airport_id')
                  ->references('id')
                  ->on('airports')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        // Remove foreign key and column from users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['airport_id']);
            $table->dropColumn('airport_id');
        });

        // Drop airports table
        Schema::dropIfExists('airports');
    }
};