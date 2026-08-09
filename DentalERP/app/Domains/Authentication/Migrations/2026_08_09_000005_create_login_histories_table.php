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
        Schema::create('login_histories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->uuid('organization_id')->nullable()->index();
            $table->uuid('branch_id')->nullable()->index();
            $table->uuid('device_id')->nullable()->index();
            $table->string('identifier', 150)->index();
            $table->string('login_status', 20)->index();
            $table->string('failure_reason', 100)->nullable();
            $table->ipAddress('ip_address')->nullable()->index();
            $table->string('browser', 100)->nullable();
            $table->string('operating_system', 100)->nullable();
            $table->string('device_name', 150)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->timestampTz('login_at')->index();
            $table->timestampTz('logout_at')->nullable()->index();

            $table->foreign('user_id', 'login_histories_user_id_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('organization_id', 'login_histories_organization_id_foreign')
                ->references('id')
                ->on('organizations')
                ->restrictOnDelete();

            $table->foreign('branch_id', 'login_histories_branch_id_foreign')
                ->references('id')
                ->on('branches')
                ->restrictOnDelete();
        });

        DB::statement('ALTER TABLE login_histories ALTER COLUMN ip_address TYPE inet USING ip_address::inet');
        DB::statement("ALTER TABLE login_histories ADD CONSTRAINT login_histories_status_check CHECK (login_status IN ('success', 'failed'))");
        DB::statement("COMMENT ON TABLE login_histories IS 'Operational History Projection — immutable except logout_at Controlled One-Time Mutation'");

        DB::statement('CREATE INDEX login_histories_user_login_at_index ON login_histories (user_id, login_at DESC, id DESC)');
        DB::statement('CREATE INDEX login_histories_tenant_login_at_index ON login_histories (organization_id, branch_id, login_at DESC, id DESC)');
        DB::statement('CREATE INDEX login_histories_identifier_status_login_at_index ON login_histories (identifier, login_status, login_at DESC, id DESC)');
    }

    public function down(): void
    {
        Schema::dropIfExists('login_histories');
    }
};
