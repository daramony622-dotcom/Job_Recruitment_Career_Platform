<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('role')
                ->constrained('companies')
                ->nullOnDelete();
        });

        DB::table('companies')
            ->whereNotNull('user_id')
            ->get(['id', 'user_id'])
            ->each(function (object $company): void {
                DB::table('users')
                    ->where('id', $company->user_id)
                    ->whereNull('company_id')
                    ->update(['company_id' => $company->id]);
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};