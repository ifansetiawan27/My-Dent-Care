<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('country_id');
            $table->string('code', 10)->unique('provinces_code_unique');
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamptz('created_at')->useCurrent();
            $table->timestamptz('updated_at')->nullable();
            $table->softDeletesTz('deleted_at');

            $table->foreign('country_id', 'provinces_country_id_foreign')
                ->references('id')->on('countries')->onDelete('restrict');
            $table->index('country_id', 'provinces_country_id_idx');
            $table->index('is_active', 'provinces_is_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
