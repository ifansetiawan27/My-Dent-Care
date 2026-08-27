<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radiology_images', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('radiology_order_id');
            $t->string('image_type', 50);
            $t->string('file_path');
            $t->unsignedInteger('file_size')->nullable();
            $t->string('file_mime', 100)->nullable();
            $t->string('thumbnail_path')->nullable();
            $t->uuid('uploaded_by')->nullable();
            $t->uuid('created_by')->nullable();
            $t->uuid('updated_by')->nullable();
            $t->uuid('deleted_by')->nullable();
            $t->timestamptz('created_at')->useCurrent();
            $t->timestamptz('updated_at')->nullable();
            $t->softDeletesTz('deleted_at');
            $t->foreign('radiology_order_id')->references('id')->on('radiology_orders')->onDelete('cascade');
            $t->index('radiology_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radiology_images');
    }
};
