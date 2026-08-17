<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('ai_queries', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('organization_id');
            $t->uuid('user_id')->nullable();
            $t->string('query_type', 50);
            $t->text('prompt');
            $t->text('response')->nullable();
            $t->string('model', 50)->nullable();
            $t->integer('tokens_used')->nullable();
            $t->string('status', 20)->default('pending');
            $t->text('error_message')->nullable();
            $t->uuid('created_by')->nullable();
            $t->uuid('updated_by')->nullable();
            $t->uuid('deleted_by')->nullable();
            $t->timestamptz('created_at')->useCurrent();
            $t->timestamptz('updated_at')->nullable();
            $t->softDeletesTz('deleted_at');
            $t->foreign('organization_id')->references('id')->on('organizations')->onDelete('restrict');
            $t->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $t->index(['organization_id', 'query_type']);
            $t->index(['organization_id', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('ai_queries'); }
};