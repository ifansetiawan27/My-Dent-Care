<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('emrs', function (Blueprint $t): void {
            $t->uuid('id')->primary(); $t->uuid('organization_id'); $t->uuid('patient_id');
            $t->uuid('doctor_id')->nullable(); $t->uuid('appointment_id')->nullable();
            $t->text('chief_complaint')->nullable(); $t->text('diagnosis')->nullable();
            $t->text('treatment_notes')->nullable(); $t->jsonb('vital_signs')->nullable();
            $t->string('status',20)->default('open');
            $t->uuid('created_by')->nullable(); $t->uuid('updated_by')->nullable(); $t->uuid('deleted_by')->nullable();
            $t->timestamptz('created_at')->useCurrent(); $t->timestamptz('updated_at')->nullable(); $t->softDeletesTz('deleted_at');
            $t->foreign('organization_id')->references('id')->on('organizations')->onDelete('restrict');
            $t->foreign('patient_id')->references('id')->on('patients')->onDelete('restrict');
            $t->foreign('doctor_id')->references('id')->on('doctors')->onDelete('set null');
            $t->foreign('appointment_id')->references('id')->on('appointments')->onDelete('set null');
            $t->index(['organization_id','patient_id']); $t->index(['patient_id','created_at']); $t->index('status');
        });
    }
    public function down(): void { Schema::dropIfExists('emrs'); }
};
