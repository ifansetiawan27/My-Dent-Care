<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('refresh_tokens', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('session_id')->index();
            $table->char('token_hash', 64)->unique();
            $table->timestampTz('expires_at')->index();
            $table->timestampTz('last_used_at')->nullable();
            $table->timestampTz('revoked_at')->nullable()->index();
            $table->uuid('replaced_by_id')->nullable();
            $table->timestampTz('created_at')->nullable();
            $table->timestampTz('updated_at')->nullable();

            $table->foreign('session_id', 'refresh_tokens_session_id_foreign')
                ->references('id')
                ->on('user_sessions')
                ->restrictOnDelete();

            $table->foreign('replaced_by_id', 'refresh_tokens_replaced_by_id_foreign')
                ->references('id')
                ->on('refresh_tokens')
                ->nullOnDelete();
        });

        DB::statement('CREATE UNIQUE INDEX refresh_tokens_session_active_unique ON refresh_tokens (session_id) WHERE revoked_at IS NULL');
        DB::statement("COMMENT ON COLUMN refresh_tokens.token_hash IS 'SHA-256 hash; plaintext refresh token is never persisted'");
    }

    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
