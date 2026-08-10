<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('subscription_transitions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('subscription_id');
            $t->uuid('organization_id');
            $t->string('previous_state', 20);
            $t->string('new_state', 20);
            $t->string('trigger', 50);
            $t->string('actor_type', 10);
            $t->uuid('actor_id')->nullable();
            $t->string('idempotency_key', 100)->nullable();
            $t->jsonb('metadata')->nullable();
            $t->timestamptz('created_at')->useCurrent();
            $t->foreign('subscription_id','st_sub_id_foreign')->references('id')->on('subscriptions')->onDelete('restrict');
            $t->foreign('organization_id','st_org_id_foreign')->references('id')->on('organizations')->onDelete('restrict');
            $t->unique('idempotency_key','st_idempotency_unique');
            $t->index(['subscription_id','created_at'],'st_sub_created_idx');
            $t->index(['organization_id','created_at'],'st_org_created_idx');
        });
    }
    public function down(): void { Schema::dropIfExists('subscription_transitions'); }
};