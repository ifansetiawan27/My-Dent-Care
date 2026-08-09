<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('level', 20);
            $table->text('message');
            $table->jsonb('context')->default('{}');
            $table->string('channel', 100);
            $table->uuid('user_id')->nullable();
            $table->uuid('organization_id')->nullable();
            $table->uuid('branch_id')->nullable();
            $table->string('exception_class', 255)->nullable();
            $table->string('file', 500)->nullable();
            $table->integer('line')->nullable();
            $table->text('trace')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamptz('created_at')->useCurrent();

            $table->foreign('user_id', 'system_logs_user_id_foreign')
                ->references('id')->on('users')->onDelete('set null');
            $table->foreign('organization_id', 'system_logs_organization_id_foreign')
                ->references('id')->on('organizations')->onDelete('set null');

            $table->index(['level', 'created_at'], 'system_logs_level_created_idx');
            $table->index(['organization_id', 'created_at'], 'system_logs_org_created_idx');
            $table->index(['channel', 'created_at'], 'system_logs_channel_created_idx');
            $table->index('created_at', 'system_logs_created_at_idx');
            $table->index(['level', 'organization_id'], 'system_logs_level_org_idx');
        });

        DB::statement("ALTER TABLE system_logs ADD CONSTRAINT system_logs_level_check CHECK (level IN ('emergency','alert','critical','error','warning','notice','info','debug'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
