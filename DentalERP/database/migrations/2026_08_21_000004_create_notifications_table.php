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
        Schema::create('notifications', function (Blueprint $table): void {
            // Primary key
            $table->uuid('id')->primary();
            
            // Foreign keys
            $table->uuid('organization_id');
            $table->uuid('branch_id')->nullable();
            
            // Polymorphic recipient
            $table->string('notifiable_type', 255);
            $table->uuid('notifiable_id');
            
            // Notification metadata
            $table->string('type', 50);
            $table->string('channel', 20);
            $table->string('status', 20);
            
            // Notification content
            $table->string('subject', 255)->nullable();
            $table->text('body')->nullable();
            $table->jsonb('data')->nullable();
            
            // Delivery metadata
            $table->string('external_id', 255)->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestampTz('sent_at')->nullable();
            $table->timestampTz('failed_at')->nullable();
            $table->timestampTz('read_at')->nullable();
            
            // Audit columns
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            
            // Timestamps
            $table->timestampTz('created_at')->nullable();
            $table->timestampTz('updated_at')->nullable();
            $table->timestampTz('deleted_at')->nullable();
            
            // Foreign key constraints
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->onDelete('restrict');
                
            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->onDelete('set null');
                
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        // Indexes for query performance
        Schema::table('notifications', function (Blueprint $table): void {
            $table->index(['organization_id', 'created_at'], 'notifications_organization_created_index');
            $table->index(['notifiable_type', 'notifiable_id', 'created_at'], 'notifications_notifiable_created_index');
            $table->index(['organization_id', 'status', 'created_at'], 'notifications_org_status_created_index');
            $table->index(['organization_id', 'channel', 'created_at'], 'notifications_org_channel_created_index');
            $table->index(['status', 'retry_count'], 'notifications_status_retry_index');
            $table->index(['deleted_at'], 'notifications_deleted_at_index');
        });

        // CHECK constraints
        DB::statement('ALTER TABLE notifications ADD CONSTRAINT notifications_status_check CHECK (status IN (\'pending\', \'sent\', \'failed\', \'read\', \'archived\'))');
        DB::statement('ALTER TABLE notifications ADD CONSTRAINT notifications_channel_check CHECK (channel IN (\'email\', \'whatsapp\', \'sms\', \'push\', \'in_app\'))');
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
