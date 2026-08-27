<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('scan_files', function(Blueprint $t){
            $t->uuid('id')->primary();
            $t->uuid('scan_session_id');
            $t->string('file_path',500);
            $t->unsignedInteger('file_size');
            $t->string('file_format',10);
            $t->boolean('is_primary')->default(false);
            $t->string('processing_status',20)->default('pending');
            $t->timestamptz('created_at')->useCurrent();
            $t->timestamptz('updated_at')->nullable();
            $t->softDeletesTz('deleted_at');
            $t->foreign('scan_session_id')->references('id')->on('scan_sessions')->onDelete('cascade');
            $t->index(['scan_session_id','is_primary']);
            $t->index('processing_status');
        });
    }
    public function down(): void { Schema::dropIfExists('scan_files'); }
};
