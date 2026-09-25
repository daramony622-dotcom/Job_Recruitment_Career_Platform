<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Expand the role ENUM/check-constraint to include all roles used in the app.
     *
     * PostgreSQL does not support ALTER TABLE ... MODIFY COLUMN for enums,
     * so we drop the old CHECK constraint and add a new one that allows
     * all valid application roles.
     */
    public function up(): void
    {
        // PostgreSQL: drop the old role check constraint and re-add it with full role list.
        // The constraint name is auto-generated as: {table}_{column}_check
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");

        // Add new constraint covering all roles the app uses
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin','hr','company','user','job_seeker','candidate'))");

        // Also set default to 'user'
        DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'user'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin','hr','user'))");
        DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'user'");
    }
};