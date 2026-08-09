<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('province_id');
            $table->string('code', 10)->unique('cities_code_unique');
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamptz('created_at')->useCurrent();
            $table->timestamptz('updated_at')->nullable();
            $table->softDeletesTz('deleted_at');

            $table->foreign('province_id', 'cities_province_id_foreign')
                ->references('id')->on('provinces')->onDelete('restrict');
            $table->index('province_id', 'cities_province_id_idx');
            $table->index('is_active', 'cities_is_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
