<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Job posts created by Company/HR.
     * Admin can view and manage all posts.
     * Job seekers can search, filter, save, and apply.
     */
    public function up(): void
    {
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('job_categories')->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->text('benefits')->nullable();
            $table->enum('job_type', ['full_time', 'part_time', 'contract', 'internship', 'freelance', 'remote'])->default('full_time');
            $table->enum('work_mode', ['onsite', 'remote', 'hybrid'])->default('onsite');
            $table->enum('experience_level', ['entry', 'junior', 'mid', 'senior', 'lead', 'executive'])->nullable();
            $table->string('location')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('salary_currency', 10)->default('USD');
            $table->enum('salary_period', ['hourly', 'daily', 'monthly', 'yearly'])->default('monthly');
            $table->boolean('is_salary_visible')->default(true);
            $table->unsignedInteger('vacancies')->default(1);
            $table->date('deadline')->nullable();
            $table->enum('status', ['draft', 'published', 'closed', 'suspended'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('job_type');
            $table->index('work_mode');
            $table->index('experience_level');
            $table->index('is_featured');
            $table->index('deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
