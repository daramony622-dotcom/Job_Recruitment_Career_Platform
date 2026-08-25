<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Work experience history for Job Seeker profiles.
     * One seeker can have multiple experience entries.
     */
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('job_title');
            $table->string('company_name');
            $table->string('employment_type')->nullable(); // full_time, part_time, contract, etc.
            $table->string('location')->nullable();
            $table->enum('work_mode', ['onsite', 'remote', 'hybrid'])->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable(); // Responsibilities & achievements
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
