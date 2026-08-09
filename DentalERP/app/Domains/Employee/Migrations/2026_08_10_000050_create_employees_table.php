<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('employees', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->string('employee_code', 30)->unique('employees_employee_code_unique');
            $t->string('full_name', 200);
            $t->uuid('organization_id');
            $t->uuid('branch_id')->nullable();
            $t->string('employment_status', 20);
            $t->date('hire_date');
            $t->date('resignation_date')->nullable();
            $t->string('position', 100)->nullable();
            $t->string('gender', 10)->nullable();
            $t->string('religion', 20)->nullable();
            $t->string('marital_status', 20)->nullable();
            $t->uuid('nationality_id')->nullable();
            $t->string('phone', 20)->nullable();
            $t->string('email', 100)->nullable();
            $t->text('address')->nullable();
            $t->uuid('district_id')->nullable();
            $t->uuid('village_id')->nullable();
            $t->boolean('is_active')->default(true);
            $t->uuid('created_by')->nullable(); $t->uuid('updated_by')->nullable(); $t->uuid('deleted_by')->nullable();
            $t->timestamptz('created_at')->useCurrent(); $t->timestamptz('updated_at')->nullable(); $t->softDeletesTz('deleted_at');
            $t->foreign('organization_id','employees_organization_id_foreign')->references('id')->on('organizations')->onDelete('restrict');
            $t->foreign('branch_id','employees_branch_id_foreign')->references('id')->on('branches')->onDelete('set null');
            $t->foreign('nationality_id','employees_nationality_id_foreign')->references('id')->on('nationalities')->onDelete('set null');
            $t->foreign('district_id','employees_district_id_foreign')->references('id')->on('districts')->onDelete('set null');
            $t->foreign('village_id','employees_village_id_foreign')->references('id')->on('villages')->onDelete('set null');
            $t->index(['organization_id','is_active'],'employees_org_id_is_active_idx');
            $t->index(['organization_id','branch_id'],'employees_org_id_branch_id_idx');
            $t->index('employment_status','employees_employment_status_idx');
            $t->index('is_active','employees_is_active_idx');
        });
    }
    public function down(): void { Schema::dropIfExists('employees'); }
};
