<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('payment_transactions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('organization_id');
            $t->uuid('subscription_id')->nullable();
            $t->string('provider', 20);
            $t->string('provider_transaction_id', 100)->nullable();
            $t->string('order_id', 100);
            $t->bigInteger('amount');
            $t->string('currency', 3)->default('IDR');
            $t->string('status', 20);
            $t->string('payment_method', 30)->nullable();
            $t->integer('attempt_number')->default(1);
            $t->bigInteger('gateway_fee')->nullable();
            $t->jsonb('provider_response')->nullable();
            $t->timestamptz('paid_at')->nullable();
            $t->timestamptz('failed_at')->nullable();
            $t->timestamptz('expired_at')->nullable();
            $t->timestamptz('created_at')->useCurrent();
            $t->timestamptz('updated_at')->nullable();
            $t->foreign('organization_id','pt_org_fk')->references('id')->on('organizations')->onDelete('restrict');
            $t->foreign('subscription_id','pt_sub_fk')->references('id')->on('subscriptions')->onDelete('set null');
            $t->unique('order_id','pt_order_id_unique');
            $t->index(['organization_id','status'],'pt_org_status_idx');
            $t->index(['subscription_id','attempt_number'],'pt_sub_attempt_idx');
            $t->index('status','pt_status_idx');
        });
    }
    public function down(): void { Schema::dropIfExists('payment_transactions'); }
};