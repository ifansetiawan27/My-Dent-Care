<?php

declare(strict_types=1);

namespace App\Domains\EMR\DTO;

final readonly class UpdateEMRDTO
{
    public function __construct(
        public ?string $examinationDate = null,
        public ?string $toothNumber = null,
        public ?string $icdCode = null,
        public ?string $chiefComplaint = null,
        public ?string $presentIllness = null,
        public ?string $medicalHistory = null,
        public ?string $allergies = null,
        public ?array $vitalSigns = null,
        public ?string $extraOralExam = null,
        public ?string $intraOralExam = null,
        public ?string $radiologyFindings = null,
        public ?string $diagnosis = null,
        public ?string $secondaryDiagnosis = null,
        public ?string $treatmentNotes = null,
        public ?string $treatmentPlan = null,
        public ?string $prescription = null,
        public ?string $followUpPlan = null,
        public ?string $status = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'examination_date'    => $this->examinationDate,
            'tooth_number'        => $this->toothNumber,
            'icd_code'            => $this->icdCode,
            'chief_complaint'     => $this->chiefComplaint,
            'present_illness'     => $this->presentIllness,
            'medical_history'     => $this->medicalHistory,
            'allergies'           => $this->allergies,
            'vital_signs'         => $this->vitalSigns,
            'extra_oral_exam'     => $this->extraOralExam,
            'intra_oral_exam'     => $this->intraOralExam,
            'radiology_findings'  => $this->radiologyFindings,
            'diagnosis'           => $this->diagnosis,
            'secondary_diagnosis' => $this->secondaryDiagnosis,
            'treatment_notes'     => $this->treatmentNotes,
            'treatment_plan'      => $this->treatmentPlan,
            'prescription'        => $this->prescription,
            'follow_up_plan'      => $this->followUpPlan,
            'status'              => $this->status,
        ], fn ($v) => $v !== null);
    }
}
