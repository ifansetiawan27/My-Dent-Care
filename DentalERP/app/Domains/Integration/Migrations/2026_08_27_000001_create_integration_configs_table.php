<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('integration_configs', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('organization_id');
            $t->string('integration_type', 30);
            $t->string('name', 100);
            $t->boolean('is_active')->default(false);
            $t->text('endpoint_url')->nullable();
            $t->text('api_key')->nullable();
            $t->text('api_secret')->nullable();
            $t->jsonb('config')->nullable();
            $t->timestamptz('last_sync_at')->nullable();
            $t->uuid('created_by')->nullable();
            $t->uuid('updated_by')->nullable();
            $t->uuid('deleted_by')->nullable();
            $t->timestamptz('created_at')->useCurrent();
            $t->timestamptz('updated_at')->nullable();
            $t->softDeletesTz('deleted_at');
            $t->foreign('organization_id')->references('id')->on('organizations')->onDelete('restrict');
            $t->index(['organization_id', 'integration_type']);
            $t->index(['organization_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_configs');
    }
};
