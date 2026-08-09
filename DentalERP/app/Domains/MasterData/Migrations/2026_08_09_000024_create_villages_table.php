<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('district_id');
            $table->string('code', 10)->unique('villages_code_unique');
            $table->string('name', 100);
            $table->string('postal_code', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamptz('created_at')->useCurrent();
            $table->timestamptz('updated_at')->nullable();
            $table->softDeletesTz('deleted_at');

            $table->foreign('district_id', 'villages_district_id_foreign')
                ->references('id')->on('districts')->onDelete('restrict');
            $table->index('district_id', 'villages_district_id_idx');
            $table->index('is_active', 'villages_is_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};
