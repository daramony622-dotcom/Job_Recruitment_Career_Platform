<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot: Skills a Job Seeker has on their profile.
     * Many-to-many: users <-> skills
     * Job Seekers add/manage their skills from profile settings.
     */
    public function up(): void
    {
        Schema::create('user_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->enum('level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('intermediate');
            $table->unsignedSmallInteger('years_of_experience')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'skill_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_skill');
    }
};
