<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('branch_id')->nullable();
            $table->string('fileable_type', 255);
            $table->uuid('fileable_id');
            $table->string('folder', 50);
            $table->string('disk', 20);
            $table->string('path', 500);
            $table->string('original_name', 255);
            $table->string('stored_name', 255);
            $table->string('mime_type', 100);
            $table->string('extension', 10);
            $table->bigInteger('size');
            $table->string('hash', 64);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamptz('created_at')->useCurrent();
            $table->timestamptz('updated_at')->nullable();
            $table->softDeletesTz('deleted_at');

            $table->foreign('organization_id', 'files_organization_id_foreign')
                ->references('id')->on('organizations')->onDelete('restrict');
            $table->foreign('branch_id', 'files_branch_id_foreign')
                ->references('id')->on('branches')->onDelete('set null');
            $table->foreign('created_by', 'files_created_by_foreign')
                ->references('id')->on('users')->onDelete('set null');

            $table->index(['organization_id', 'folder'], 'files_org_folder_idx');
            $table->index(['organization_id', 'branch_id'], 'files_org_branch_idx');
            $table->index(['fileable_type', 'fileable_id'], 'files_fileable_idx');
            $table->index('hash', 'files_hash_idx');
            $table->index('folder', 'files_folder_idx');
            $table->index(['organization_id', 'folder', 'created_at'], 'files_org_folder_created_idx');
            $table->index('created_by', 'files_created_by_idx');
        });

        DB::statement("ALTER TABLE files ADD CONSTRAINT files_folder_check CHECK (folder IN ('patient','doctor','organization','branch','lab','radiology','asset'))");
        DB::statement("ALTER TABLE files ADD CONSTRAINT files_disk_check CHECK (disk IN ('local','s3'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
