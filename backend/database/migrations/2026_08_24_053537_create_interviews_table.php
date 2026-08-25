<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Interview scheduling by Company/HR.
     * Job Seekers can view their scheduled interviews.
     * Admin can monitor all interviews.
     */
    public function up(): void
    {
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('job_post_id')->constrained('job_posts')->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained('users')->cascadeOnDelete(); // Job Seeker
            $table->foreignId('interviewer_id')->constrained('users')->cascadeOnDelete(); // HR/Company user
            
            $table->enum('interview_type', ['phone', 'video', 'onsite', 'technical', 'panel'])->default('video');
            $table->string('title')->nullable(); // e.g. "Technical Round 1"
            $table->dateTime('scheduled_at');
            $table->unsignedInteger('duration_minutes')->default(60);
            $table->string('location')->nullable();       // Physical address or meeting room
            $table->string('meeting_link')->nullable();   // Zoom, Google Meet, etc.
            $table->text('notes_for_candidate')->nullable();
            $table->text('internal_notes')->nullable();  // HR internal notes
            $table->text('feedback')->nullable();         // Post-interview feedback
            $table->enum('result', ['pending', 'passed', 'failed', 'no_show', 'rescheduled'])->default('pending');
            $table->enum('status', ['scheduled', 'confirmed', 'cancelled', 'completed', 'rescheduled'])->default('scheduled');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('scheduled_at');
            $table->index('status');
            $table->index('applicant_id');
            $table->index('interviewer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
