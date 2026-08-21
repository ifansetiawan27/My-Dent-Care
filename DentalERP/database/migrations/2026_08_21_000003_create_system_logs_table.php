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
        Schema::create('system_logs', function (Blueprint $table): void {
            // Primary key
            $table->uuid('id')->primary();
            
            // Foreign keys
            $table->uuid('organization_id')->nullable();
            $table->uuid('user_id')->nullable();
            
            // Log metadata
            $table->string('level', 20);
            $table->string('channel', 50)->default('application');
            $table->text('message');
            
            // Context data
            $table->jsonb('context')->nullable();
            $table->jsonb('extra')->nullable();
            
            // Exception data
            $table->text('exception_class')->nullable();
            $table->text('exception_message')->nullable();
            $table->text('exception_trace')->nullable();
            
            // Request context
            $table->string('request_id', 36)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('method', 10)->nullable();
            $table->text('url')->nullable();
            
            // Timestamp (immutable — no updated_at)
            $table->timestampTz('created_at');
            
            // Foreign key constraints
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->onDelete('set null');
                
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        // Indexes for query performance
        Schema::table('system_logs', function (Blueprint $table): void {
            $table->index(['level', 'created_at'], 'system_logs_level_created_index');
            $table->index(['organization_id', 'level', 'created_at'], 'system_logs_org_level_created_index');
            $table->index(['request_id'], 'system_logs_request_id_index');
            $table->index(['channel', 'created_at'], 'system_logs_channel_created_index');
            $table->index(['created_at'], 'system_logs_created_at_index');
        });

        // CHECK constraints
        DB::statement('ALTER TABLE system_logs ADD CONSTRAINT system_logs_level_check CHECK (level IN (\'emergency\', \'alert\', \'critical\', \'error\', \'warning\', \'notice\', \'info\', \'debug\'))');
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
