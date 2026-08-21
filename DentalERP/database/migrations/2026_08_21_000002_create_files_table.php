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
        Schema::create('files', function (Blueprint $table): void {
            // Primary key (also serves as stored_name)
            $table->uuid('id')->primary();
            
            // Foreign keys
            $table->uuid('organization_id');
            $table->uuid('branch_id')->nullable();
            
            // Polymorphic owner
            $table->string('fileable_type', 255);
            $table->uuid('fileable_id');
            
            // Storage metadata
            $table->string('folder', 50);
            $table->string('disk', 20);
            $table->string('path', 500);
            
            // File metadata
            $table->string('original_name', 255);
            $table->string('stored_name', 255);
            $table->string('mime_type', 100);
            $table->string('extension', 10);
            $table->bigInteger('size');
            $table->string('hash', 64)->nullable();
            
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
        Schema::table('files', function (Blueprint $table): void {
            $table->index(['organization_id', 'created_at'], 'files_organization_created_index');
            $table->index(['organization_id', 'branch_id', 'created_at'], 'files_org_branch_created_index');
            $table->index(['fileable_type', 'fileable_id'], 'files_fileable_index');
            $table->index(['organization_id', 'folder', 'created_at'], 'files_org_folder_created_index');
            $table->index(['hash'], 'files_hash_index');
            $table->index(['created_at'], 'files_created_at_index');
            $table->index(['deleted_at'], 'files_deleted_at_index');
        });

        // CHECK constraints
        DB::statement('ALTER TABLE files ADD CONSTRAINT files_folder_check CHECK (folder IN (\'patient\', \'doctor\', \'organization\', \'branch\', \'lab\', \'radiology\', \'asset\'))');
        DB::statement('ALTER TABLE files ADD CONSTRAINT files_disk_check CHECK (disk IN (\'local\', \'s3\'))');
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
