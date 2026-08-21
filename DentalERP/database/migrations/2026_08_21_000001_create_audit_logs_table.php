<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            // Primary key
            $table->uuid('id')->primary();
            
            // Foreign keys
            $table->uuid('user_id')->nullable();
            $table->uuid('organization_id');
            $table->uuid('branch_id')->nullable();
            
            // Audit metadata
            $table->string('module', 100);
            $table->string('action', 20);
            $table->string('auditable_type', 255)->nullable();
            $table->uuid('auditable_id')->nullable();
            
            // Audit payload
            $table->jsonb('old_value')->nullable();
            $table->jsonb('new_value')->nullable();
            
            // Request context
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device', 20)->nullable();
            
            // Timestamp (immutable — no updated_at)
            $table->timestampTz('created_at');
            
            // Foreign key constraints
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
                
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->onDelete('restrict');
                
            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->onDelete('set null');
        });

        // Indexes for query performance
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->index(['organization_id', 'created_at'], 'audit_logs_organization_created_index');
            $table->index(['organization_id', 'branch_id', 'created_at'], 'audit_logs_org_branch_created_index');
            $table->index(['organization_id', 'user_id', 'created_at'], 'audit_logs_org_user_created_index');
            $table->index(['organization_id', 'module', 'action', 'created_at'], 'audit_logs_org_module_action_index');
            $table->index(['auditable_type', 'auditable_id'], 'audit_logs_auditable_index');
            $table->index(['created_at'], 'audit_logs_created_at_index');
        });

        // CHECK constraints
        DB::statement('ALTER TABLE audit_logs ADD CONSTRAINT audit_logs_action_check CHECK (action IN (\'login\', \'logout\', \'create\', \'update\', \'delete\', \'restore\', \'export\', \'import\', \'print\', \'sync\', \'integration\'))');
        DB::statement('ALTER TABLE audit_logs ADD CONSTRAINT audit_logs_device_check CHECK (device IN (\'desktop\', \'mobile\', \'tablet\', \'api\'))');
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
