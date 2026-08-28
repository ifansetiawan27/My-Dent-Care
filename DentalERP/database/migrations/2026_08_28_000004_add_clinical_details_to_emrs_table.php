<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emrs', function (Blueprint $table): void {
            // Examination context
            $table->timestampTz('examination_date')->nullable()->after('appointment_id')
                  ->comment('Date/time of the clinical examination');
            $table->string('tooth_number', 50)->nullable()->after('examination_date')
                  ->comment('Related tooth element(s), e.g. 11, 26, 36-37');
            $table->string('icd_code', 20)->nullable()->after('tooth_number')
                  ->comment('Primary ICD-10 code');

            // Subjective (S)
            $table->text('present_illness')->nullable()->after('chief_complaint')
                  ->comment('History of present illness / anamnesis');
            $table->text('medical_history')->nullable()->after('present_illness')
                  ->comment('Systemic medical history');
            $table->text('allergies')->nullable()->after('medical_history')
                  ->comment('Drug / food / material allergies');

            // Objective (O) — vital_signs jsonb already exists
            $table->text('extra_oral_exam')->nullable()->after('vital_signs')
                  ->comment('Extra-oral examination findings');
            $table->text('intra_oral_exam')->nullable()->after('extra_oral_exam')
                  ->comment('Intra-oral examination findings');
            $table->text('radiology_findings')->nullable()->after('intra_oral_exam')
                  ->comment('Radiology / imaging findings');

            // Assessment (A) — diagnosis text already exists
            $table->text('secondary_diagnosis')->nullable()->after('diagnosis')
                  ->comment('Secondary / differential diagnosis');

            // Plan (P) — treatment_notes text already exists
            $table->text('treatment_plan')->nullable()->after('treatment_notes')
                  ->comment('Planned treatment');
            $table->text('prescription')->nullable()->after('treatment_plan')
                  ->comment('Prescribed medication');
            $table->text('follow_up_plan')->nullable()->after('prescription')
                  ->comment('Post-treatment instructions and follow-up schedule');
        });
    }

    public function down(): void
    {
        Schema::table('emrs', function (Blueprint $table): void {
            $table->dropColumn([
                'examination_date', 'tooth_number', 'icd_code',
                'present_illness', 'medical_history', 'allergies',
                'extra_oral_exam', 'intra_oral_exam', 'radiology_findings',
                'secondary_diagnosis',
                'treatment_plan', 'prescription', 'follow_up_plan',
            ]);
        });
    }
};
