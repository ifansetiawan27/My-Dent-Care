<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('integration_mappings', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('integration_config_id');
            $t->string('local_type', 50);
            $t->string('local_id', 100);
            $t->string('external_code', 100);
            $t->jsonb('external_data')->nullable();
            $t->boolean('is_synced')->default(false);
            $t->uuid('created_by')->nullable();
            $t->uuid('updated_by')->nullable();
            $t->uuid('deleted_by')->nullable();
            $t->timestamptz('created_at')->useCurrent();
            $t->timestamptz('updated_at')->nullable();
            $t->softDeletesTz('deleted_at');
            $t->foreign('integration_config_id')->references('id')->on('integration_configs')->onDelete('cascade');
            $t->index(['integration_config_id', 'local_type']);
            $t->index(['integration_config_id', 'external_code']);
            $t->unique(['integration_config_id', 'local_type', 'local_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_mappings');
    }
};
