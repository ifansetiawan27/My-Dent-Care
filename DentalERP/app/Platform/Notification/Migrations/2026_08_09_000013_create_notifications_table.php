<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('branch_id')->nullable();
            $table->string('notifiable_type', 255);
            $table->uuid('notifiable_id');
            $table->string('channel', 20);
            $table->string('type', 100);
            $table->string('title', 255);
            $table->text('body');
            $table->jsonb('data')->default('{}');
            $table->string('locale', 10)->nullable()->default('id');
            $table->string('status', 20)->default('pending');
            $table->timestamptz('sent_at')->nullable();
            $table->timestamptz('read_at')->nullable();
            $table->text('failed_reason')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamptz('created_at')->useCurrent();
            $table->timestamptz('updated_at')->nullable();
            $table->softDeletesTz('deleted_at');

            $table->foreign('organization_id', 'notifications_organization_id_foreign')
                ->references('id')->on('organizations')->onDelete('restrict');
            $table->foreign('branch_id', 'notifications_branch_id_foreign')
                ->references('id')->on('branches')->onDelete('set null');
            $table->foreign('created_by', 'notifications_created_by_foreign')
                ->references('id')->on('users')->onDelete('set null');

            $table->index(['organization_id', 'status'], 'notifications_org_status_idx');
            $table->index(['organization_id', 'channel'], 'notifications_org_channel_idx');
            $table->index(['notifiable_type', 'notifiable_id'], 'notifications_notifiable_idx');
            $table->index(['status', 'channel'], 'notifications_status_channel_idx');
            $table->index('type', 'notifications_type_idx');
            $table->index(['organization_id', 'created_at'], 'notifications_org_created_idx');
            $table->index(['organization_id', 'status', 'channel'], 'notifications_org_status_channel_idx');
        });

        DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_channel_check CHECK (channel IN ('email','whatsapp','sms','push','in_app'))");
        DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_status_check CHECK (status IN ('pending','sent','failed','read'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
