<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('skill_categories')) {
            Schema::create('skill_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('skills') && !Schema::hasColumn('skills', 'category_id')) {
            Schema::table('skills', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->after('category')->constrained('skill_categories')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('contact_messages')) {
            Schema::create('contact_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('name');
                $table->string('email');
                $table->string('phone')->nullable();
                $table->string('subject_type')->default('general');
                $table->string('subject');
                $table->text('message');
                $table->boolean('is_read')->default(false);
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('skills') && Schema::hasColumn('skills', 'category_id')) {
            Schema::table('skills', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }

        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('skill_categories');
    }
};
