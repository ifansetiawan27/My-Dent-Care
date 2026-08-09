<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->uuid('organization_id');
            $table->uuid('branch_id')->nullable();
            $table->string('module', 100);
            $table->string('action', 20);
            $table->string('auditable_type', 255)->nullable();
            $table->uuid('auditable_id')->nullable();
            $table->jsonb('old_value')->default('{}');
            $table->jsonb('new_value')->default('{}');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device', 20)->nullable();
            $table->timestamptz('created_at')->useCurrent();

            $table->foreign('user_id', 'audit_logs_user_id_foreign')
                ->references('id')->on('users')->onDelete('set null');
            $table->foreign('organization_id', 'audit_logs_organization_id_foreign')
                ->references('id')->on('organizations')->onDelete('restrict');
            $table->foreign('branch_id', 'audit_logs_branch_id_foreign')
                ->references('id')->on('branches')->onDelete('set null');

            $table->index(['organization_id', 'created_at'], 'audit_logs_org_created_idx');
            $table->index(['auditable_type', 'auditable_id'], 'audit_logs_auditable_idx');
            $table->index(['user_id', 'created_at'], 'audit_logs_user_created_idx');
            $table->index(['module', 'action', 'created_at'], 'audit_logs_module_action_idx');
            $table->index(['branch_id', 'created_at'], 'audit_logs_branch_created_idx');
        });

        DB::statement("ALTER TABLE audit_logs ADD CONSTRAINT audit_logs_action_check CHECK (action IN ('login','logout','create','update','delete','restore','export','import','print','sync','integration'))");
        DB::statement("ALTER TABLE audit_logs ADD CONSTRAINT audit_logs_device_check CHECK (device IS NULL OR device IN ('desktop','mobile','tablet','api'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
