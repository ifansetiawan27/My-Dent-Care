<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('organization_id', 36)->index();
            $table->string('report_type', 50); // balance_sheet, income_statement, cash_flow, trial_balance
            $table->string('report_name', 255);
            $table->date('period_start');
            $table->date('period_end');
            $table->json('filters')->nullable();
            $table->json('report_data')->nullable(); // stored report data
            $table->string('status', 20)->default('draft'); // draft, generated, exported
            $table->string('export_format', 20)->nullable(); // pdf, excel, csv
            $table->string('generated_by')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->char('created_by', 36)->nullable();
            $table->char('updated_by', 36)->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->index(['report_type', 'period_start', 'period_end']);
        });

        // Add CHECK constraints via raw SQL (PostgreSQL compatible)
        DB::statement("ALTER TABLE financial_reports ADD CONSTRAINT financial_reports_report_type_check CHECK (report_type IN ('balance_sheet', 'income_statement', 'cash_flow', 'trial_balance'))");
        DB::statement("ALTER TABLE financial_reports ADD CONSTRAINT financial_reports_status_check CHECK (status IN ('draft', 'generated', 'exported'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_reports');
    }
};
