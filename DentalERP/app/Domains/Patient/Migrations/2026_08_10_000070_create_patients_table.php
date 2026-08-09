<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('patients', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->string('patient_code', 30)->unique('patients_patient_code_unique');
            $t->string('full_name', 200);
            $t->date('birth_date')->nullable();
            $t->string('gender', 10)->nullable();
            $t->string('blood_type', 5)->nullable();
            $t->string('religion', 20)->nullable();
            $t->string('marital_status', 20)->nullable();
            $t->uuid('nationality_id')->nullable();
            $t->uuid('patient_type_id')->nullable();
            $t->uuid('organization_id');
            $t->uuid('branch_id')->nullable();
            $t->string('phone', 20)->nullable();
            $t->string('email', 100)->nullable();
            $t->text('address')->nullable();
            $t->uuid('district_id')->nullable();
            $t->uuid('village_id')->nullable();
            $t->boolean('is_active')->default(true);
            $t->uuid('created_by')->nullable(); $t->uuid('updated_by')->nullable(); $t->uuid('deleted_by')->nullable();
            $t->timestamptz('created_at')->useCurrent(); $t->timestamptz('updated_at')->nullable(); $t->softDeletesTz('deleted_at');
            $t->foreign('organization_id')->references('id')->on('organizations')->onDelete('restrict');
            $t->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $t->foreign('patient_type_id')->references('id')->on('patient_types')->onDelete('set null');
            $t->foreign('nationality_id')->references('id')->on('nationalities')->onDelete('set null');
            $t->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
            $t->foreign('village_id')->references('id')->on('villages')->onDelete('set null');
            $t->index(['organization_id','is_active']);
            $t->index(['organization_id','branch_id']);
            $t->index('patient_type_id');
        });
    }
    public function down(): void { Schema::dropIfExists('patients'); }
};
