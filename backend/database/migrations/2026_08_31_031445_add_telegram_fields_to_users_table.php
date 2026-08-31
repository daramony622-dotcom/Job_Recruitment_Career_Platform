<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Telegram IDs are large integers — string is safer
            $table->string('telegram_id')->nullable()->unique()->after('google_id');
            $table->string('telegram_username')->nullable()->after('telegram_id');
            $table->string('telegram_photo')->nullable()->after('telegram_username');

            // Allow null password for social-only accounts
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['telegram_id']);
            $table->dropColumn(['telegram_id', 'telegram_username', 'telegram_photo']);
            $table->string('password')->nullable(false)->change();
        });
    }
};