<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radiology_orders', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('organization_id');
            $t->uuid('patient_id');
            $t->uuid('doctor_id');
            $t->string('order_number', 50)->unique();
            $t->string('radiology_type', 50);
            $t->string('body_part', 100)->nullable();
            $t->text('clinical_notes')->nullable();
            $t->string('priority', 20)->default('routine');
            $t->string('status', 20)->default('ordered');
            $t->timestamptz('ordered_at')->useCurrent();
            $t->timestamptz('completed_at')->nullable();
            $t->uuid('created_by')->nullable();
            $t->uuid('updated_by')->nullable();
            $t->uuid('deleted_by')->nullable();
            $t->timestamptz('created_at')->useCurrent();
            $t->timestamptz('updated_at')->nullable();
            $t->softDeletesTz('deleted_at');
            $t->foreign('organization_id')->references('id')->on('organizations')->onDelete('restrict');
            $t->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $t->foreign('doctor_id')->references('id')->on('doctors')->onDelete('restrict');
            $t->index(['organization_id', 'status']);
            $t->index(['organization_id', 'created_at']);
            $t->index('order_number');
            $t->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radiology_orders');
    }
};
