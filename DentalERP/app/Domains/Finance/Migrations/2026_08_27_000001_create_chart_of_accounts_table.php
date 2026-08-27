<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->string('account_code', 20)->unique();
            $table->string('account_name', 255);
            $table->string('account_type', 50); // asset, liability, equity, revenue, expense
            $table->string('account_category', 100)->nullable();
            $table->uuid('parent_id')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // system-managed accounts cannot be deleted
            $table->text('description')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
        });

        // Self-referencing FK must be added separately in PostgreSQL
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
