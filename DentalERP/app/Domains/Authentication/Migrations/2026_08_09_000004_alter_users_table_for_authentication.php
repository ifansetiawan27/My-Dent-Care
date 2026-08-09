<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'pgsql';

    public function up(): void
    {
        DB::statement("COMMENT ON COLUMN users.password IS 'Argon2id password hash — never plaintext'");
        DB::statement("COMMENT ON COLUMN users.last_login_at IS 'Timestamp of the latest successful login'");
    }

    public function down(): void
    {
        DB::statement("COMMENT ON COLUMN users.password IS 'Hashed password — never plaintext'");
        DB::statement("COMMENT ON COLUMN users.last_login_at IS 'Timestamp of the last successful login — auto-updated on auth'");
    }
};
