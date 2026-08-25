<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Education history for Job Seeker profiles.
     * One seeker can have multiple education entries.
     */
    public function up(): void
    {
        Schema::create('education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('institution_name');
            $table->string('degree');         // e.g. Bachelor, Master, PhD, Associate
            $table->string('field_of_study'); // e.g. Computer Science
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('grade')->nullable();    // GPA or grade
            $table->string('country')->nullable();
            $table->text('description')->nullable(); // Activities, honors, etc.
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education');
    }
};
