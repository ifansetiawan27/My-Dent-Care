<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radiology_reports', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('radiology_order_id');
            $t->uuid('radiologist_id');
            $t->text('findings')->nullable();
            $t->text('impression')->nullable();
            $t->text('diagnosis')->nullable();
            $t->boolean('is_final')->default(false);
            $t->timestamptz('reviewed_at')->nullable();
            $t->uuid('created_by')->nullable();
            $t->uuid('updated_by')->nullable();
            $t->uuid('deleted_by')->nullable();
            $t->timestamptz('created_at')->useCurrent();
            $t->timestamptz('updated_at')->nullable();
            $t->softDeletesTz('deleted_at');
            $t->foreign('radiology_order_id')->references('id')->on('radiology_orders')->onDelete('cascade');
            $t->foreign('radiologist_id')->references('id')->on('doctors')->onDelete('restrict');
            $t->index('radiology_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radiology_reports');
    }
};
