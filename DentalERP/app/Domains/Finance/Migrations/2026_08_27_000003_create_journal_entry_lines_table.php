<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('journal_entry_id')->index();
            $table->uuid('account_id')->index();
            $table->string('description')->nullable();
            $table->enum('entry_type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->cascadeOnDelete();
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->restrictOnDelete();
        });

        // PostgreSQL check constraints via raw SQL
        DB::statement('ALTER TABLE journal_entry_lines ADD CONSTRAINT chk_amount_positive CHECK (amount >= 0)');
        DB::statement("ALTER TABLE journal_entry_lines ADD CONSTRAINT chk_entry_type CHECK (entry_type IN ('debit', 'credit'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};
