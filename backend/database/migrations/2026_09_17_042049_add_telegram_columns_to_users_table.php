<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'telegram_id')) {
                $table->unsignedBigInteger('telegram_id')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('users', 'telegram_username')) {
                $table->string('telegram_username')->nullable();
            }
            if (! Schema::hasColumn('users', 'telegram_photo')) {
                $table->string('telegram_photo')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['telegram_id', 'telegram_username', 'telegram_photo'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};