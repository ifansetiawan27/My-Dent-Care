<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timezones', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique('timezones_code_unique');
            $table->string('name', 100);
            $table->string('offset_utc', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamptz('created_at')->useCurrent();
            $table->timestamptz('updated_at')->nullable();
            $table->softDeletesTz('deleted_at');
            $table->index('is_active', 'timezones_is_active_idx');
        });
    }

    public function down(): void { Schema::dropIfExists('timezones'); }
};
