<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('subscriptions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('organization_id');
            $t->string('plan_code', 30);
            $t->string('status', 20);
            $t->timestamptz('trial_starts_at')->nullable();
            $t->timestamptz('trial_ends_at')->nullable();
            $t->timestamptz('current_period_starts_at')->nullable();
            $t->timestamptz('current_period_ends_at')->nullable();
            $t->timestamptz('next_billing_at')->nullable();
            $t->timestamptz('grace_starts_at')->nullable();
            $t->timestamptz('grace_ends_at')->nullable();
            $t->timestamptz('cancelled_at')->nullable();
            $t->timestamptz('reactivated_at')->nullable();
            $t->uuid('created_by')->nullable();
            $t->uuid('updated_by')->nullable();
            $t->timestamptz('created_at')->useCurrent();
            $t->timestamptz('updated_at')->nullable();
            $t->softDeletesTz('deleted_at');
            $t->foreign('organization_id','subscriptions_org_id_foreign')->references('id')->on('organizations')->onDelete('restrict');
            $t->unique('organization_id','subscriptions_org_id_unique');
            $t->index('status','subscriptions_status_idx');
            $t->index('plan_code','subscriptions_plan_code_idx');
            $t->index('trial_ends_at','subscriptions_trial_ends_at_idx');
            $t->index('next_billing_at','subscriptions_next_billing_at_idx');
            $t->index('grace_ends_at','subscriptions_grace_ends_at_idx');
            $t->index(['organization_id','status'],'subscriptions_org_status_idx');
        });
    }
    public function down(): void { Schema::dropIfExists('subscriptions'); }
};