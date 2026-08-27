<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('integration_logs', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('integration_config_id');
            $t->string('direction', 20);
            $t->text('endpoint')->nullable();
            $t->jsonb('request_payload')->nullable();
            $t->jsonb('response_payload')->nullable();
            $t->string('status', 20)->default('pending');
            $t->string('response_code', 10)->nullable();
            $t->integer('duration_ms')->nullable();
            $t->text('error_message')->nullable();
            $t->timestamptz('created_at')->useCurrent();
            $t->foreign('integration_config_id')->references('id')->on('integration_configs')->onDelete('cascade');
            $t->index(['integration_config_id', 'created_at']);
            $t->index(['integration_config_id', 'status']);
            $t->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
    }
};
