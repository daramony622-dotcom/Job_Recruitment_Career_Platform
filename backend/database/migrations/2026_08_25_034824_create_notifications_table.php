<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Notifications for all users.
     * Job Seekers receive: application status updates, interview schedules.
     * Company/HR receive: new applications.
     * Admin receives: system alerts.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');                    // Notification class name
            $table->morphs('notifiable');              // user_id + notifiable_type
            $table->text('data');                      // JSON payload
            $table->enum('channel', ['database', 'email', 'sms'])->default('database');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
