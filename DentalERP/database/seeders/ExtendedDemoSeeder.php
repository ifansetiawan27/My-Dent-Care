<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Appointment\Models\Appointment;
use App\Domains\Billing\Models\Billing as Invoice;
use App\Domains\Branch\Models\Branch;
use App\Domains\CRM\Models\CRM as CRMContact;
use App\Domains\Doctor\Models\Doctor;
use App\Domains\EMR\Models\EMR;
use App\Domains\Finance\Models\ChartOfAccount;
use App\Domains\Finance\Models\JournalEntry;
use App\Domains\Finance\Models\JournalEntryLine;
use App\Domains\Inventory\Models\Inventory;
use App\Domains\Laboratory\Models\Laboratory;
use App\Domains\MasterData\Models\InventoryCategory;
use App\Domains\MasterData\Models\LaboratoryCategory;
use App\Domains\MasterData\Models\TreatmentCategory;
use App\Domains\Odontogram\Models\Odontogram;
use App\Domains\Organization\Models\Organization;
use App\Domains\Patient\Models\Patient;
use App\Domains\Pharmacy\Models\Pharmacy;
use App\Domains\Radiology\Models\RadiologyImage;
use App\Domains\Radiology\Models\RadiologyOrder;
use App\Domains\Radiology\Models\RadiologyReport;
use App\Domains\Treatment\Models\Treatment;
use App\Domains\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExtendedDemoSeeder extends Seeder
{
    /**
     * Seed extended demo data for all modules.
     * Idempotent: safe to run multiple times.
     */
    public function run(): void
    {
        $counts = [];

        // ── 1. Bootstrap: get or create org, branch, users, patients ──
        $organization = Organization::firstWhere('company_code', 'DEMO001');
        if (!$organization) {
            $this->command->warn('Base demo data not found. Run DemoSeeder first.');
            return;
        }

        $branch = Branch::firstWhere('branch_code', 'JKT001');
        if (!$branch) {
            $this->command->warn('Branch not found. Run DemoSeeder first.');
            return;
        }

        $doctorUser = User::firstWhere('username', 'drjane');
        $receptionist = User::firstWhere('username', 'sarah');
        $patients = Patient::where('organization_id', $organization->id)->get();

        if ($patients->isEmpty()) {
            $this->command->warn('No patients found. Run DemoSeeder first.');
            return;
        }

        // ── 2. Create Doctor records (links User → Doctor table) ──
        $doctorRecords = [];
        foreach ([$doctorUser] as $u) {
            $doc = Doctor::firstOrNew(['doctor_code' => 'DOC-' . $u->employee_code]);
            if (!$doc->exists) {
                $doc->fill([
                    'id'               => (string) Str::orderedUuid(),
                    'full_name'        => $u->name,
                    'organization_id'  => $organization->id,
                    'branch_id'        => $branch->id,
                    'gender'           => $u->gender,
                    'phone'            => $u->phone,
                    'email'            => $u->email,
                    'license_number'   => 'SIP-' . strtoupper(Str::random(8)),
                    'consultation_fee' => 150000,
                    'hire_date'        => now()->subMonths(6)->format('Y-m-d'),
                    'is_active'        => true,
                    'created_by'       => $u->id,
                ]);
                $doc->save();
            }
            $doctorRecords[$u->id] = $doc;
        }
        $doctorId = reset($doctorRecords)->id;
        $counts['doctors'] = Doctor::where('organization_id', $organization->id)->count();

        // ── 3. Master Data: categories ──
        $invCatSupplies = $this->firstOrCreateCategory(InventoryCategory::class, 'SUPPLIES', 'Dental Supplies');
        $invCatEquip = $this->firstOrCreateCategory(InventoryCategory::class, 'EQUIPMENT', 'Dental Equipment');
        $invMatCat = $this->firstOrCreateCategory(LaboratoryCategory::class, 'CROWN', 'Crown & Bridge');
        $treatCat = $this->firstOrCreateCategory(TreatmentCategory::class, 'RESTORATIVE', 'Restorative Dentistry');
        $counts['inventory_categories'] = 2;
        $counts['lab_categories'] = 1;
        $counts['treatment_categories'] = 1;

        // ── 4. Appointments (various statuses) ──
        $now = now();
        $appointments = [
            [
                'status'     => 'completed',
                'type'       => 'checkup',
                'scheduled'  => $now->copy()->subDays(5)->setTime(9, 0),
                'notes'      => 'Routine dental checkup and cleaning',
            ],
            [
                'status'     => 'completed',
                'type'       => 'filling',
                'scheduled'  => $now->copy()->subDays(3)->setTime(10, 30),
                'notes'      => 'Composite filling tooth 36',
            ],
            [
                'status'     => 'scheduled',
                'type'       => 'consultation',
                'scheduled'  => $now->copy()->addDay()->setTime(14, 0),
                'notes'      => 'Initial consultation for orthodontic treatment',
            ],
            [
                'status'     => 'scheduled',
                'type'       => 'extraction',
                'scheduled'  => $now->copy()->addDays(3)->setTime(11, 0),
                'notes'      => 'Wisdom tooth extraction - upper left',
            ],
            [
                'status'     => 'scheduled',
                'type'       => 'root_canal',
                'scheduled'  => $now->copy()->addDays(5)->setTime(9, 30),
                'notes'      => 'Root canal treatment tooth 26',
            ],
            [
                'status'     => 'cancelled',
                'type'       => 'checkup',
                'scheduled'  => $now->copy()->subDay()->setTime(15, 0),
                'notes'      => 'Patient cancelled - rescheduled',
            ],
            [
                'status'     => 'completed',
                'type'       => 'scaling',
                'scheduled'  => $now->copy()->subDays(7)->setTime(8, 0),
                'notes'      => 'Professional scaling and polishing',
            ],
            [
                'status'     => 'scheduled',
                'type'       => 'crown',
                'scheduled'  => $now->copy()->addDays(7)->setTime(13, 0),
                'notes'      => 'Crown preparation tooth 16',
            ],
        ];

        foreach ($appointments as $i => $apt) {
            $patient = $patients[$i % $patients->count()];
            // Idempotent: check by scheduled_at + patient
            $exists = Appointment::where('patient_id', $patient->id)
                ->where('scheduled_at', $apt['scheduled'])
                ->exists();
            if ($exists) {
                continue;
            }
            Appointment::create([
                'id'              => (string) Str::orderedUuid(),
                'organization_id' => $organization->id,
                'branch_id'       => $branch->id,
                'patient_id'      => $patient->id,
                'doctor_id'       => $doctorId,
                'scheduled_at'    => $apt['scheduled'],
                'end_at'          => $apt['scheduled']->copy()->addHour(),
                'status'          => $apt['status'],
                'type'            => $apt['type'],
                'notes'           => $apt['notes'],
                'created_by'      => $receptionist?->id,
            ]);
        }
        $counts['appointments'] = Appointment::where('organization_id', $organization->id)->count();

        // Re-fetch appointments for linking treatments/EMR
        $completedAppointments = Appointment::where('organization_id', $organization->id)
            ->where('status', 'completed')
            ->get();

        // ── 5. Treatments ──
        $treatments = [
            [
                'patient_idx'  => 0,
                'apt_offset'   => 0, // first completed appointment
                'type'         => 'scaling',
                'status'       => 'completed',
                'cost'         => 350000,
                'description'  => 'Professional scaling and polishing - full mouth',
                'procedure'    => ['steps' => ['ultrasonic scaling', 'hand scaling', 'polishing', 'fluoride application'], 'teeth' => 'all'],
            ],
            [
                'patient_idx'  => 1,
                'apt_offset'   => 1, // second completed appointment
                'type'         => 'filling',
                'status'       => 'completed',
                'cost'         => 500000,
                'description'  => 'Composite resin filling on tooth 36 (lower left first molar)',
                'procedure'    => ['material' => 'composite resin', 'tooth' => '36', 'surface' => 'MO'],
            ],
            [
                'patient_idx'  => 2,
                'apt_offset'   => null,
                'type'         => 'extraction',
                'status'       => 'planned',
                'cost'         => 750000,
                'description'  => 'Surgical extraction of impacted wisdom tooth 38',
                'procedure'    => ['tooth' => '38', 'type' => 'surgical', 'anesthesia' => 'local'],
            ],
            [
                'patient_idx'  => 0,
                'apt_offset'   => null,
                'type'         => 'crown',
                'status'       => 'in_progress',
                'cost'         => 2500000,
                'description'  => 'Porcelain fused to metal crown on tooth 16',
                'procedure'    => ['material' => 'PFM', 'tooth' => '16', 'stage' => 'preparation'],
            ],
            [
                'patient_idx'  => 1,
                'apt_offset'   => null,
                'type'         => 'root_canal',
                'status'       => 'planned',
                'cost'         => 1800000,
                'description'  => 'Root canal treatment on tooth 26 - 3 canals',
                'procedure'    => ['tooth' => '26', 'canals' => 3, 'technique' => 'rotary'],
            ],
        ];

        foreach ($treatments as $t) {
            $patient = $patients[$t['patient_idx']];
            $aptId = null;
            if ($t['apt_offset'] !== null && $completedAppointments->has($t['apt_offset'])) {
                $aptId = $completedAppointments[$t['apt_offset']]->id;
            }

            $exists = Treatment::where('patient_id', $patient->id)
                ->where('treatment_type', $t['type'])
                ->where('description', $t['description'])
                ->exists();
            if ($exists) {
                continue;
            }

            Treatment::create([
                'id'              => (string) Str::orderedUuid(),
                'organization_id' => $organization->id,
                'patient_id'      => $patient->id,
                'doctor_id'       => $doctorId,
                'appointment_id'  => $aptId,
                'treatment_type'  => $t['type'],
                'status'          => $t['status'],
                'cost'            => $t['cost'],
                'description'     => $t['description'],
                'procedure_data'  => $t['procedure'],
                'created_by'      => $doctorUser?->id,
            ]);
        }
        $counts['treatments'] = Treatment::where('organization_id', $organization->id)->count();

        // ── 6. Invoices ──
        $invoices = [
            [
                'patient_idx' => 0,
                'number'      => 'INV-2026-0001',
                'total'       => 350000,
                'paid'        => 350000,
                'status'      => 'paid',
                'due_date'    => $now->copy()->addDays(14)->format('Y-m-d'),
                'items'       => [['description' => 'Scaling & Polishing', 'qty' => 1, 'price' => 350000]],
                'notes'       => 'Paid in full via bank transfer',
            ],
            [
                'patient_idx' => 1,
                'number'      => 'INV-2026-0002',
                'total'       => 500000,
                'paid'        => 250000,
                'status'      => 'sent',
                'due_date'    => $now->copy()->addDays(7)->format('Y-m-d'),
                'items'       => [['description' => 'Composite Filling Tooth 36', 'qty' => 1, 'price' => 500000]],
                'notes'       => '50% deposit paid',
            ],
            [
                'patient_idx' => 2,
                'number'      => 'INV-2026-0003',
                'total'       => 150000,
                'paid'        => 0,
                'status'      => 'draft',
                'due_date'    => $now->copy()->addDays(30)->format('Y-m-d'),
                'items'       => [['description' => 'Consultation Fee', 'qty' => 1, 'price' => 150000]],
                'notes'       => 'Pending - awaiting treatment confirmation',
            ],
            [
                'patient_idx' => 0,
                'number'      => 'INV-2026-0004',
                'total'       => 1250000,
                'paid'        => 625000,
                'status'      => 'sent',
                'due_date'    => $now->copy()->addDays(14)->format('Y-m-d'),
                'items'       => [
                    ['description' => 'PFM Crown Tooth 16 - Stage 1', 'qty' => 1, 'price' => 1250000],
                ],
                'notes'       => 'Two-stage payment plan',
            ],
            [
                'patient_idx' => 1,
                'number'      => 'INV-2026-0005',
                'total'       => 200000,
                'paid'        => 200000,
                'status'      => 'paid',
                'due_date'    => $now->copy()->subDays(5)->format('Y-m-d'),
                'items'       => [['description' => 'X-Ray Panoramic', 'qty' => 1, 'price' => 200000]],
                'notes'       => 'Paid cash at counter',
            ],
        ];

        foreach ($invoices as $inv) {
            $patient = $patients[$inv['patient_idx']];
            $exists = Invoice::where('invoice_number', $inv['number'])->exists();
            if ($exists) {
                continue;
            }

            Invoice::create([
                'id'              => (string) Str::orderedUuid(),
                'organization_id' => $organization->id,
                'patient_id'      => $patient->id,
                'invoice_number'  => $inv['number'],
                'total_amount'    => $inv['total'],
                'paid_amount'     => $inv['paid'],
                'status'          => $inv['status'],
                'due_date'        => $inv['due_date'],
                'items'           => $inv['items'],
                'notes'           => $inv['notes'],
                'created_by'      => $receptionist?->id,
            ]);
        }
        $counts['invoices'] = Invoice::where('organization_id', $organization->id)->count();

        // ── 7. Inventory Items ──
        $inventoryItems = [
            ['code' => 'INV-ANO-001', 'name' => 'Anesthetic Lidocaine 2% (50 cartridges)', 'unit' => 'box', 'qty' => 25, 'min' => 5, 'price' => 450000, 'cat' => $invCatSupplies->id, 'desc' => 'Local anesthetic cartridges for dental procedures'],
            ['code' => 'INV-COM-001', 'name' => 'Composite Resin Filtek Z350 (shade A2)', 'unit' => 'syringe', 'qty' => 12, 'min' => 3, 'price' => 385000, 'cat' => $invCatSupplies->id, 'desc' => 'Light-cured nanofilled composite restorative'],
            ['code' => 'INV-IMP-001', 'name' => 'Impression Material Alginate (1kg)', 'unit' => 'pack', 'qty' => 8, 'min' => 2, 'price' => 175000, 'cat' => $invCatSupplies->id, 'desc' => 'Fast-setting alginate impression material'],
            ['code' => 'INV-GLV-001', 'name' => 'Nitrile Examination Gloves (box 100)', 'unit' => 'box', 'qty' => 50, 'min' => 10, 'price' => 85000, 'cat' => $invCatSupplies->id, 'desc' => 'Powder-free nitrile gloves, medium'],
            ['code' => 'INV-MSK-001', 'name' => 'Surgical Mask 3-Ply (box 50)', 'unit' => 'box', 'qty' => 30, 'min' => 5, 'price' => 45000, 'cat' => $invCatSupplies->id, 'desc' => 'Disposable 3-ply surgical masks'],
            ['code' => 'INV-BUR-001', 'name' => 'Diamond Bur Set (assorted)', 'unit' => 'set', 'qty' => 6, 'min' => 2, 'price' => 520000, 'cat' => $invCatEquip->id, 'desc' => 'Assorted diamond burs for crown preparation'],
            ['code' => 'INV-MIR-001', 'name' => 'Mouth Mirror #5 (stainless steel)', 'unit' => 'pc', 'qty' => 20, 'min' => 5, 'price' => 35000, 'cat' => $invCatEquip->id, 'desc' => 'Standard dental mouth mirror'],
            ['code' => 'INV-EXP-001', 'name' => 'Extractor Forceps #150', 'unit' => 'pc', 'qty' => 4, 'min' => 1, 'price' => 280000, 'cat' => $invCatEquip->id, 'desc' => 'Universal extraction forceps for upper teeth'],
        ];

        foreach ($inventoryItems as $item) {
            $exists = Inventory::where('item_code', $item['code'])->exists();
            if ($exists) {
                continue;
            }

            Inventory::create([
                'id'              => (string) Str::orderedUuid(),
                'organization_id' => $organization->id,
                'branch_id'       => $branch->id,
                'category_id'     => $item['cat'],
                'item_code'       => $item['code'],
                'name'            => $item['name'],
                'description'     => $item['desc'],
                'unit'            => $item['unit'],
                'quantity'        => $item['qty'],
                'min_quantity'    => $item['min'],
                'unit_price'      => $item['price'],
                'is_active'       => true,
                'created_by'      => $receptionist?->id,
            ]);
        }
        $counts['inventory_items'] = Inventory::where('organization_id', $organization->id)->count();

        // ── 8. Pharmacy Items ──
        $pharmacyItems = [
            ['code' => 'PHR-AMX-001', 'name' => 'Amoxicillin 500mg', 'cat' => 'antibiotic', 'qty' => 200, 'unit' => 'tablet', 'price' => 2500, 'expiry' => $now->copy()->addMonths(12)->format('Y-m-d'), 'batch' => 'AMX2026A'],
            ['code' => 'PHR-IBU-001', 'name' => 'Ibuprofen 400mg', 'cat' => 'analgesic', 'qty' => 150, 'unit' => 'tablet', 'price' => 1800, 'expiry' => $now->copy()->addMonths(18)->format('Y-m-d'), 'batch' => 'IBU2026B'],
            ['code' => 'PHR-PMC-001', 'name' => 'Paracetamol 500mg', 'cat' => 'analgesic', 'qty' => 300, 'unit' => 'tablet', 'price' => 800, 'expiry' => $now->copy()->addMonths(24)->format('Y-m-d'), 'batch' => 'PMC2026C'],
            ['code' => 'PHR-DEX-001', 'name' => 'Dexamethasone 0.5mg', 'cat' => 'anti-inflammatory', 'qty' => 100, 'unit' => 'tablet', 'price' => 1500, 'expiry' => $now->copy()->addMonths(10)->format('Y-m-d'), 'batch' => 'DEX2026D'],
            ['code' => 'PHR-CHX-001', 'name' => 'Chlorhexidine Mouthwash 120ml', 'cat' => 'antiseptic', 'qty' => 40, 'unit' => 'bottle', 'price' => 35000, 'expiry' => $now->copy()->addMonths(8)->format('Y-m-d'), 'batch' => 'CHX2026E'],
            ['code' => 'PHR-FLR-001', 'name' => 'Fluoride Gel 250ml', 'cat' => 'preventive', 'qty' => 15, 'unit' => 'bottle', 'price' => 125000, 'expiry' => $now->copy()->addMonths(6)->format('Y-m-d'), 'batch' => 'FLR2026F'],
        ];

        foreach ($pharmacyItems as $pItem) {
            $exists = \App\Domains\Pharmacy\Models\Pharmacy::where('drug_code', $pItem['code'])->exists();
            if ($exists) {
                continue;
            }

            \App\Domains\Pharmacy\Models\Pharmacy::create([
                'id'              => (string) Str::orderedUuid(),
                'organization_id' => $organization->id,
                'branch_id'       => $branch->id,
                'drug_code'       => $pItem['code'],
                'name'            => $pItem['name'],
                'category'        => $pItem['cat'],
                'quantity'        => $pItem['qty'],
                'unit'            => $pItem['unit'],
                'unit_price'      => $pItem['price'],
                'expiry_date'     => $pItem['expiry'],
                'batch_number'    => $pItem['batch'],
                'is_active'       => true,
                'created_by'      => $receptionist?->id,
            ]);
        }
        $counts['pharmacy_items'] = \App\Domains\Pharmacy\Models\Pharmacy::where('organization_id', $organization->id)->count();

        // ── 9. Lab Orders ──
        $labOrders = [
            [
                'number' => 'LAB-2026-0001',
                'patient_idx' => 0,
                'cat_id' => $invMatCat->id,
                'status' => 'completed',
                'desc' => 'PFM Crown tooth 16 - shade A2',
                'ordered' => $now->copy()->subDays(10)->format('Y-m-d'),
                'completed' => $now->copy()->subDays(2)->format('Y-m-d'),
                'results' => ['crown' => 'PFM A2', 'fit' => 'good', 'shade_match' => 'approved'],
                'notes' => 'Crown delivered and cemented',
            ],
            [
                'number' => 'LAB-2026-0002',
                'patient_idx' => 1,
                'cat_id' => $invMatCat->id,
                'status' => 'in_progress',
                'desc' => 'Temporary crown tooth 26',
                'ordered' => $now->copy()->subDays(3)->format('Y-m-d'),
                'completed' => null,
                'results' => null,
                'notes' => 'Waiting for lab fabrication - expected 5 days',
            ],
            [
                'number' => 'LAB-2026-0003',
                'patient_idx' => 2,
                'cat_id' => $invMatCat->id,
                'status' => 'pending',
                'desc' => 'Removable partial denture - lower arch',
                'ordered' => $now->copy()->format('Y-m-d'),
                'completed' => null,
                'results' => null,
                'notes' => 'Impression taken, sent to lab',
            ],
        ];

        foreach ($labOrders as $lo) {
            $patient = $patients[$lo['patient_idx']];
            $exists = \App\Domains\Laboratory\Models\Laboratory::where('order_number', $lo['number'])->exists();
            if ($exists) {
                continue;
            }

            \App\Domains\Laboratory\Models\Laboratory::create([
                'id'              => (string) Str::orderedUuid(),
                'organization_id' => $organization->id,
                'patient_id'      => $patient->id,
                'doctor_id'       => $doctorId,
                'order_number'    => $lo['number'],
                'category_id'     => $lo['cat_id'],
                'status'          => $lo['status'],
                'description'     => $lo['desc'],
                'results'         => $lo['results'],
                'ordered_at'      => $lo['ordered'],
                'completed_at'    => $lo['completed'],
                'notes'           => $lo['notes'],
                'created_by'      => $doctorUser?->id,
            ]);
        }
        $counts['lab_orders'] = \App\Domains\Laboratory\Models\Laboratory::where('organization_id', $organization->id)->count();

        // ── 10. EMR Records ──
        $emrRecords = [
            [
                'patient_idx' => 0,
                'chief'       => 'Routine checkup and cleaning request',
                'diagnosis'   => 'Mild gingivitis, calculus buildup on lower anterior teeth',
                'notes'       => 'Performed full mouth scaling. Patient educated on proper brushing technique. Recommend follow-up in 6 months.',
                'vitals'      => ['blood_pressure' => '120/80', 'heart_rate' => 72, 'temperature' => 36.5],
                'status'      => 'closed',
            ],
            [
                'patient_idx' => 1,
                'chief'       => 'Pain on lower left molar when eating sweets',
                'diagnosis'   => 'Dental caries on tooth 36 (MOD)',
                'notes'       => 'Composite filling placed. Occlusion adjusted. Post-op instructions given. Patient advised to avoid hard foods for 24 hours.',
                'vitals'      => ['blood_pressure' => '130/85', 'heart_rate' => 78, 'temperature' => 36.7, 'pain_level' => 4],
                'status'      => 'closed',
            ],
            [
                'patient_idx' => 2,
                'chief'       => 'Wisdom tooth causing discomfort and crowding',
                'diagnosis'   => 'Impacted mandibular third molar (tooth 38) - mesioangular impaction',
                'notes'       => 'X-ray taken. Surgical extraction planned. Pre-op blood work ordered. Patient consented for procedure.',
                'vitals'      => ['blood_pressure' => '125/82', 'heart_rate' => 75, 'temperature' => 36.6],
                'status'      => 'open',
            ],
        ];

        foreach ($emrRecords as $emr) {
            $patient = $patients[$emr['patient_idx']];
            $exists = EMR::where('patient_id', $patient->id)
                ->where('diagnosis', $emr['diagnosis'])
                ->exists();
            if ($exists) {
                continue;
            }

            EMR::create([
                'id'              => (string) Str::orderedUuid(),
                'organization_id' => $organization->id,
                'patient_id'      => $patient->id,
                'doctor_id'       => $doctorId,
                'chief_complaint' => $emr['chief'],
                'diagnosis'       => $emr['diagnosis'],
                'treatment_notes' => $emr['notes'],
                'vital_signs'     => $emr['vitals'],
                'status'          => $emr['status'],
                'created_by'      => $doctorUser?->id,
            ]);
        }
        $counts['emr_records'] = EMR::where('organization_id', $organization->id)->count();

        // ── 11. Odontogram Data ──
        $odontograms = [
            // Patient 0 (John Doe) - mild issues
            ['patient_idx' => 0, 'tooth' => '16', 'type' => 'permanent', 'surface' => 'O', 'condition' => 'restored', 'notes' => 'Existing amalgam filling - good condition'],
            ['patient_idx' => 0, 'tooth' => '36', 'type' => 'permanent', 'surface' => 'MO', 'condition' => 'restored', 'notes' => 'New composite filling placed today'],
            ['patient_idx' => 0, 'tooth' => '18', 'type' => 'permanent', 'surface' => null, 'condition' => 'present', 'notes' => 'Wisdom tooth - monitoring'],
            ['patient_idx' => 0, 'tooth' => '28', 'type' => 'permanent', 'surface' => null, 'condition' => 'present', 'notes' => 'Wisdom tooth - monitoring'],
            ['patient_idx' => 0, 'tooth' => '38', 'type' => 'permanent', 'surface' => null, 'condition' => 'present', 'notes' => 'Wisdom tooth - monitoring'],
            ['patient_idx' => 0, 'tooth' => '48', 'type' => 'permanent', 'surface' => null, 'condition' => 'missing', 'notes' => 'Previously extracted'],
            // Patient 1 (Maria Garcia) - caries
            ['patient_idx' => 1, 'tooth' => '16', 'type' => 'permanent', 'surface' => 'DO', 'condition' => 'carious', 'notes' => 'Caries detected - treatment planned'],
            ['patient_idx' => 1, 'tooth' => '26', 'type' => 'permanent', 'surface' => 'MOD', 'condition' => 'carious', 'notes' => 'Deep caries - root canal planned'],
            ['patient_idx' => 1, 'tooth' => '36', 'type' => 'permanent', 'surface' => 'MO', 'condition' => 'restored', 'notes' => 'Composite filling completed'],
            ['patient_idx' => 1, 'tooth' => '11', 'type' => 'permanent', 'surface' => null, 'condition' => 'healthy', 'notes' => 'Good condition'],
            ['patient_idx' => 1, 'tooth' => '21', 'type' => 'permanent', 'surface' => null, 'condition' => 'healthy', 'notes' => 'Good condition'],
            // Patient 2 (Robert Chen) - wisdom tooth
            ['patient_idx' => 2, 'tooth' => '38', 'type' => 'permanent', 'surface' => null, 'condition' => 'impacted', 'notes' => 'Mesioangular impaction - surgical extraction planned'],
            ['patient_idx' => 2, 'tooth' => '48', 'type' => 'permanent', 'surface' => null, 'condition' => 'impacted', 'notes' => 'Horizontal impaction - monitoring'],
            ['patient_idx' => 2, 'tooth' => '17', 'type' => 'permanent', 'surface' => null, 'condition' => 'healthy', 'notes' => 'Second molar - good condition'],
            ['patient_idx' => 2, 'tooth' => '27', 'type' => 'permanent', 'surface' => null, 'condition' => 'healthy', 'notes' => 'Second molar - good condition'],
        ];

        foreach ($odontograms as $od) {
            $patient = $patients[$od['patient_idx']];
            $exists = Odontogram::where('patient_id', $patient->id)
                ->where('tooth_number', $od['tooth'])
                ->where('condition', $od['condition'])
                ->exists();
            if ($exists) {
                continue;
            }

            Odontogram::create([
                'id'              => (string) Str::orderedUuid(),
                'organization_id' => $organization->id,
                'patient_id'      => $patient->id,
                'tooth_number'    => $od['tooth'],
                'tooth_type'      => $od['type'],
                'surface'         => $od['surface'],
                'condition'       => $od['condition'],
                'notes'           => $od['notes'],
                'findings'        => ['chart_date' => $now->format('Y-m-d'), 'examined_by' => $doctorUser?->name],
                'created_by'      => $doctorUser?->id,
            ]);
        }
        $counts['odontograms'] = Odontogram::where('organization_id', $organization->id)->count();

        // ── 12. Radiology Orders, Images, Reports ──
        $radiologyOrders = [
            [
                'number' => 'RAD-2026-0001',
                'patient_idx' => 2,
                'type' => 'panoramic',
                'body' => 'mandible',
                'clinical' => 'Pre-surgical assessment for wisdom tooth extraction',
                'priority' => 'routine',
                'status' => 'completed',
                'findings' => 'Mesioangular impaction of tooth 38, horizontal impaction of tooth 48. No pathology detected.',
                'impression' => 'Bilateral mandibular third molar impaction. Surgical extraction indicated.',
                'diagnosis' => 'K01.0 - Impacted teeth',
            ],
            [
                'number' => 'RAD-2026-0002',
                'patient_idx' => 1,
                'type' => 'periapical',
                'body' => 'tooth 26',
                'clinical' => 'Deep caries on tooth 26 - assess root canal anatomy',
                'priority' => 'routine',
                'status' => 'completed',
                'findings' => 'Three canals identified (MB, DB, P). No periapical radiolucency. Root curvature moderate.',
                'impression' => 'Suitable for RCT. Three-canal anatomy confirmed.',
                'diagnosis' => 'K02.1 - Dental caries extending into pulp',
            ],
            [
                'number' => 'RAD-2026-0003',
                'patient_idx' => 0,
                'type' => 'bitewing',
                'body' => 'posterior',
                'clinical' => 'Routine bitewing for caries detection',
                'priority' => 'routine',
                'status' => 'ordered',
                'findings' => null,
                'impression' => null,
                'diagnosis' => null,
            ],
        ];

        foreach ($radiologyOrders as $rad) {
            $patient = $patients[$rad['patient_idx']];
            $exists = RadiologyOrder::where('order_number', $rad['number'])->exists();
            if ($exists) {
                continue;
            }

            $radOrder = RadiologyOrder::create([
                'id'              => (string) Str::orderedUuid(),
                'organization_id' => $organization->id,
                'patient_id'      => $patient->id,
                'doctor_id'       => $doctorId,
                'order_number'    => $rad['number'],
                'radiology_type'  => $rad['type'],
                'body_part'       => $rad['body'],
                'clinical_notes'  => $rad['clinical'],
                'priority'        => $rad['priority'],
                'status'          => $rad['status'],
                'ordered_at'      => $rad['status'] === 'completed' ? $now->copy()->subDays(5) : $now,
                'completed_at'    => $rad['status'] === 'completed' ? $now->copy()->subDays(2) : null,
                'created_by'      => $doctorUser?->id,
            ]);

            // Create image and report for completed orders
            if ($rad['status'] === 'completed') {
                RadiologyImage::create([
                    'id'                   => (string) Str::orderedUuid(),
                    'radiology_order_id'   => $radOrder->id,
                    'image_type'           => $rad['type'],
                    'file_path'            => "radiology/{$radOrder->id}/image.dcm",
                    'file_size'            => 2048576,
                    'file_mime'            => 'application/dicom',
                    'thumbnail_path'       => "radiology/{$radOrder->id}/thumb.jpg",
                    'uploaded_by'          => $doctorUser?->id,
                    'created_by'           => $doctorUser?->id,
                ]);

                RadiologyReport::create([
                    'id'                 => (string) Str::orderedUuid(),
                    'radiology_order_id' => $radOrder->id,
                    'radiologist_id'     => $doctorId,
                    'findings'           => $rad['findings'],
                    'impression'         => $rad['impression'],
                    'diagnosis'          => $rad['diagnosis'],
                    'is_final'           => true,
                    'reviewed_at'        => $now->copy()->subDays(1),
                    'created_by'         => $doctorUser?->id,
                ]);
            }
        }
        $counts['radiology_orders'] = RadiologyOrder::where('organization_id', $organization->id)->count();
        $counts['radiology_images'] = RadiologyImage::count();
        $counts['radiology_reports'] = RadiologyReport::count();

        // ── 13. CRM Contacts ──
        $crmContacts = [
            [
                'patient_idx' => 0,
                'type' => 'complaint',
                'channel' => 'phone',
                'subject' => 'Sensitivity after scaling',
                'message' => 'Patient reports mild tooth sensitivity 2 days after scaling procedure',
                'status' => 'resolved',
                'follow_up' => $now->copy()->subDays(3)->format('Y-m-d'),
                'resolution' => 'Advised to use sensitivity toothpaste. Symptoms expected to resolve within 1 week.',
            ],
            [
                'patient_idx' => 1,
                'type' => 'inquiry',
                'channel' => 'email',
                'subject' => 'Insurance coverage question',
                'message' => 'Patient asking about BPJS coverage for crown procedure',
                'status' => 'follow_up',
                'follow_up' => $now->copy()->addDays(2)->format('Y-m-d'),
                'resolution' => null,
            ],
            [
                'patient_idx' => 2,
                'type' => 'reminder',
                'channel' => 'whatsapp',
                'subject' => 'Pre-surgery instructions',
                'message' => 'Sent pre-surgical instructions for wisdom tooth extraction',
                'status' => 'new',
                'follow_up' => null,
                'resolution' => null,
            ],
        ];

        foreach ($crmContacts as $crm) {
            $patient = $patients[$crm['patient_idx']];
            $exists = CRMContact::where('patient_id', $patient->id)
                ->where('subject', $crm['subject'])
                ->exists();
            if ($exists) {
                continue;
            }

            CRMContact::create([
                'id'              => (string) Str::orderedUuid(),
                'organization_id' => $organization->id,
                'branch_id'       => $branch->id,
                'patient_id'      => $patient->id,
                'contact_type'    => $crm['type'],
                'channel'         => $crm['channel'],
                'subject'         => $crm['subject'],
                'message'         => $crm['message'],
                'status'          => $crm['status'],
                'follow_up_date'  => $crm['follow_up'],
                'resolution'      => $crm['resolution'],
                'created_by'      => $receptionist?->id,
            ]);
        }
        $counts['crm_contacts'] = CRMContact::where('organization_id', $organization->id)->count();

        // ── 14. Chart of Accounts (basic set) ──
        $accounts = [
            ['code' => '1000', 'name' => 'Cash on Hand', 'type' => 'asset', 'cat' => 'current_asset'],
            ['code' => '1100', 'name' => 'Bank Account - BCA', 'type' => 'asset', 'cat' => 'current_asset'],
            ['code' => '1200', 'name' => 'Accounts Receivable', 'type' => 'asset', 'cat' => 'current_asset'],
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability', 'cat' => 'current_liability'],
            ['code' => '3000', 'name' => 'Owner Equity', 'type' => 'equity', 'cat' => 'equity'],
            ['code' => '4000', 'name' => 'Treatment Revenue', 'type' => 'revenue', 'cat' => 'operating_revenue'],
            ['code' => '4100', 'name' => 'Consultation Revenue', 'type' => 'revenue', 'cat' => 'operating_revenue'],
            ['code' => '5000', 'name' => 'Dental Supplies Expense', 'type' => 'expense', 'cat' => 'operating_expense'],
            ['code' => '5100', 'name' => 'Lab Fees Expense', 'type' => 'expense', 'cat' => 'operating_expense'],
            ['code' => '5200', 'name' => 'Salary Expense', 'type' => 'expense', 'cat' => 'operating_expense'],
        ];

        $accountMap = [];
        $superAdminUser = User::firstWhere('username', 'superadmin');

        foreach ($accounts as $acc) {
            $exists = ChartOfAccount::where('organization_id', $organization->id)
                ->where('account_code', $acc['code'])
                ->exists();
            if ($exists) {
                $accountMap[$acc['code']] = ChartOfAccount::where('organization_id', $organization->id)
                    ->where('account_code', $acc['code'])->first()->id;
                continue;
            }

            $model = ChartOfAccount::create([
                'id'              => (string) Str::orderedUuid(),
                'organization_id' => $organization->id,
                'account_code'    => $acc['code'],
                'account_name'    => $acc['name'],
                'account_type'    => $acc['type'],
                'account_category'=> $acc['cat'],
                'is_active'       => true,
                'is_system'       => true,
                'description'     => "Auto-created demo account: {$acc['name']}",
                'created_by'      => $superAdminUser?->id,
            ]);
            $accountMap[$acc['code']] = $model->id;
        }
        $counts['chart_of_accounts'] = ChartOfAccount::where('organization_id', $organization->id)->count();

        // ── 15. Journal Entries (for paid invoices) ──
        $journalEntries = [
            [
                'number' => 'JE-2026-0001',
                'ref_type' => 'invoice',
                'ref_idx' => 0, // INV-2026-0001
                'date' => $now->copy()->subDays(5)->format('Y-m-d'),
                'period' => $now->copy()->subDays(5)->format('Y-m-d'),
                'desc' => 'Revenue recognition for scaling & polishing',
                'status' => 'posted',
                'lines' => [
                    ['account' => '1100', 'type' => 'debit', 'amount' => 350000, 'desc' => 'Cash received'],
                    ['account' => '4000', 'type' => 'credit', 'amount' => 350000, 'desc' => 'Treatment revenue'],
                ],
            ],
            [
                'number' => 'JE-2026-0002',
                'ref_type' => 'invoice',
                'ref_idx' => 4, // INV-2026-0005
                'date' => $now->copy()->subDays(5)->format('Y-m-d'),
                'period' => $now->copy()->subDays(5)->format('Y-m-d'),
                'desc' => 'Revenue recognition for X-Ray Panoramic',
                'status' => 'posted',
                'lines' => [
                    ['account' => '1000', 'type' => 'debit', 'amount' => 200000, 'desc' => 'Cash received at counter'],
                    ['account' => '4000', 'type' => 'credit', 'amount' => 200000, 'desc' => 'Treatment revenue'],
                ],
            ],
        ];

        $invoiceList = Invoice::where('organization_id', $organization->id)->get();

        foreach ($journalEntries as $je) {
            $exists = JournalEntry::where('organization_id', $organization->id)
                ->where('entry_number', $je['number'])
                ->exists();
            if ($exists) {
                continue;
            }

            $refId = $invoiceList[$je['ref_idx']]->id;

            // Calculate totals
            $totalDebit = array_sum(array_column($je['lines'], 'amount'));
            $totalCredit = array_sum(array_column($je['lines'], 'amount'));

            $journalEntry = JournalEntry::create([
                'id'              => (string) Str::orderedUuid(),
                'organization_id' => $organization->id,
                'entry_number'    => $je['number'],
                'reference_type'  => $je['ref_type'],
                'reference_id'    => $refId,
                'entry_date'      => $je['date'],
                'period_date'     => $je['period'],
                'description'     => $je['desc'],
                'status'          => $je['status'],
                'total_debit'     => $totalDebit,
                'total_credit'    => $totalCredit,
                'is_balanced'     => $totalDebit === $totalCredit,
                'posted_by'       => $superAdminUser?->name,
                'posted_at'       => now(),
                'created_by'      => $superAdminUser?->id,
            ]);

            foreach ($je['lines'] as $line) {
                JournalEntryLine::create([
                    'id'               => (string) Str::orderedUuid(),
                    'journal_entry_id' => $journalEntry->id,
                    'account_id'       => $accountMap[$line['account']],
                    'description'      => $line['desc'],
                    'entry_type'       => $line['type'],
                    'amount'           => $line['amount'],
                    'created_by'       => $journalEntry->created_by,
                ]);
            }
        }
        $counts['journal_entries'] = JournalEntry::where('organization_id', $organization->id)->count();
        $counts['journal_entry_lines'] = JournalEntryLine::count();

        // ── Summary ──
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════╗');
        $this->command->info('║         Extended Demo Data Seeded Successfully!          ║');
        $this->command->info('╚══════════════════════════════════════════════════════════╝');
        $this->command->info('');

        $table = [];
        foreach ($counts as $domain => $count) {
            $table[] = [sprintf('  %-25s', $domain), $count];
        }

        $this->command->table(['Domain', 'Records'], $table);

        $this->command->info('');
        $this->command->info('Modules covered:');
        $this->command->info('  ✓ Appointments    (scheduled, completed, cancelled)');
        $this->command->info('  ✓ Treatments      (scaling, filling, crown, RCT, extraction)');
        $this->command->info('  ✓ Invoices        (draft, sent, paid)');
        $this->command->info('  ✓ Inventory       (8 dental supplies & equipment items)');
        $this->command->info('  ✓ Pharmacy        (6 medications & consumables)');
        $this->command->info('  ✓ Lab Orders      (crown, temporary, denture)');
        $this->command->info('  ✓ EMR Records     (3 patient encounters with vitals)');
        $this->command->info('  ✓ Odontograms     (14 tooth records across 3 patients)');
        $this->command->info('  ✓ Radiology       (orders, images, reports)');
        $this->command->info('  ✓ CRM Contacts    (complaint, inquiry, reminder)');
        $this->command->info('  ✓ Finance         (chart of accounts + journal entries)');
        $this->command->info('');
    }

    /**
     * Idempotent category helper.
     */
    private function firstOrCreateCategory($modelClass, string $code, string $name)
    {
        $existing = $modelClass::where('code', $code)->first();
        if ($existing) {
            return $existing;
        }

        return $modelClass::create([
            'id'         => (string) Str::orderedUuid(),
            'code'       => $code,
            'name'       => $name,
            'is_active'  => true,
            'created_by' => User::firstWhere('username', 'superadmin')?->id,
        ]);
    }
}
