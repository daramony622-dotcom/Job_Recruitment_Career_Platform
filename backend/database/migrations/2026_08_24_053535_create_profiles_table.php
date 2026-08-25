<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extended profile for Job Seekers.
     * One-to-one with users table.
     * Includes CV upload, availability, and visibility settings.
     */
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('avatar')->nullable();
            $table->string('headline')->nullable();       // e.g. "Senior Laravel Developer"
            $table->text('bio')->nullable();              // About me
            $table->string('phone', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->string('nationality')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('cv_path')->nullable();         // Uploaded CV file path
            $table->string('cv_original_name')->nullable();
            $table->timestamp('cv_uploaded_at')->nullable();
            $table->enum('availability', ['immediately', 'within_1_month', 'within_3_months', 'not_available'])->default('immediately');
            $table->decimal('expected_salary_min', 12, 2)->nullable();
            $table->decimal('expected_salary_max', 12, 2)->nullable();
            $table->string('salary_currency', 10)->default('USD');
            $table->boolean('is_open_to_work')->default(true);
            $table->boolean('is_profile_visible')->default(true); // Visible to Company/HR search
            $table->unsignedInteger('profile_views')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
