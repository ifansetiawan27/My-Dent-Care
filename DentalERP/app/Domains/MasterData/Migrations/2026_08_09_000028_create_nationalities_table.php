<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nationalities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 10)->unique('nationalities_code_unique');
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamptz('created_at')->useCurrent();
            $table->timestamptz('updated_at')->nullable();
            $table->softDeletesTz('deleted_at');
            $table->index('is_active', 'nationalities_is_active_idx');
        });
    }

    public function down(): void { Schema::dropIfExists('nationalities'); }
};
