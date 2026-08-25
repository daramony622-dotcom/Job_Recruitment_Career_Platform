<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Job applications submitted by Job Seekers.
     * Company/HR can view, shortlist, reject applicants.
     * Admin can view all applications.
     * Job Seekers can view their own application history and status.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained('job_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Job Seeker
            $table->text('cover_letter')->nullable();
            $table->string('cv_path')->nullable();           // CV used for this application
            $table->string('cv_original_name')->nullable();
            $table->enum('status', [
                'pending',       // Just applied
                'reviewing',     // HR is reviewing
                'shortlisted',   // Candidate shortlisted
                'interview',     // Interview scheduled
                'offered',       // Job offer sent
                'hired',         // Hired / Accepted
                'rejected',      // Rejected by company
                'withdrawn',     // Withdrawn by job seeker
            ])->default('pending');
            $table->text('hr_notes')->nullable();            // Internal notes by HR
            $table->text('rejection_reason')->nullable();
            $table->timestamp('shortlisted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('hired_at')->nullable();
            $table->timestamps();

            $table->unique(['job_post_id', 'user_id']); // One application per job per user
            $table->index('status');
            $table->index('user_id');
            $table->index('job_post_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
