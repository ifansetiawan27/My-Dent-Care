<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('appointments', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('organization_id'); $t->uuid('branch_id')->nullable();
            $t->uuid('patient_id')->nullable(); $t->uuid('doctor_id')->nullable();
            $t->dateTime('scheduled_at'); $t->dateTime('end_at')->nullable();
            $t->string('status', 20)->default('scheduled');
            $t->string('type', 50)->nullable(); $t->text('notes')->nullable();
            $t->uuid('created_by')->nullable(); $t->uuid('updated_by')->nullable(); $t->uuid('deleted_by')->nullable();
            $t->timestamptz('created_at')->useCurrent(); $t->timestamptz('updated_at')->nullable(); $t->softDeletesTz('deleted_at');
            $t->foreign('organization_id')->references('id')->on('organizations')->onDelete('restrict');
            $t->foreign('patient_id')->references('id')->on('patients')->onDelete('set null');
            $t->foreign('doctor_id')->references('id')->on('doctors')->onDelete('set null');
            $t->index(['organization_id','scheduled_at']); $t->index(['organization_id','status']); $t->index(['doctor_id','scheduled_at']); $t->index('patient_id');
        });
    }
    public function down(): void { Schema::dropIfExists('appointments'); }
};
