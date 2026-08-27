<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->string('entry_number', 50)->unique();
            $table->string('reference_type', 50)->nullable(); // invoice, payment, adjustment, etc.
            $table->uuid('reference_id')->nullable()->index();
            $table->date('entry_date');
            $table->date('period_date'); // accounting period
            $table->string('description')->nullable();
            $table->string('status', 20)->default('draft'); // draft, posted, cancelled
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->boolean('is_balanced')->default(false);
            $table->string('posted_by')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->index(['entry_date', 'status']);
            $table->index(['period_date', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
