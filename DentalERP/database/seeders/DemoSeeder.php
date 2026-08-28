<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Appointment\Models\Appointment;
use App\Domains\Branch\Models\Branch;
use App\Domains\Doctor\Models\Doctor;
use App\Domains\EMR\Models\EMR;
use App\Domains\Organization\Models\Organization;
use App\Domains\Patient\Models\Patient;
use App\Domains\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DemoSeeder extends Seeder
{
    /**
     * Seed demo data for live review.
     */
    public function run(): void
    {
        // 1. Create Demo Organization
        $organization = Organization::create([
            'id' => (string) Str::orderedUuid(),
            'company_code' => 'DEMO001',
            'company_name' => 'Demo Dental Clinic Group',
            'legal_name' => 'PT Demo Dental Indonesia',
            'tax_number' => '01.234.567.8-901.000',
            'email' => 'info@demodental.com',
            'phone' => '+62 21 1234 5678',
            'website' => 'https://demodental.com',
            'address' => 'Jl. Sudirman No. 123',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'country' => 'ID',
            'postal_code' => '12345',
            'timezone' => 'Asia/Jakarta',
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        // 2. Create Demo Branch
        $branch = Branch::create([
            'id' => (string) Str::orderedUuid(),
            'organization_id' => $organization->id,
            'branch_code' => 'JKT001',
            'branch_name' => 'Demo Dental Jakarta Pusat',
            'branch_type' => 'main',
            'email' => 'jakarta@demodental.com',
            'phone' => '+62 21 9876 5432',
            'address' => 'Jl. Thamrin No. 456',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'country' => 'ID',
            'postal_code' => '10230',
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);

        // 3. Create roles (idempotent — safe to run multiple times)
        $rolesSuperAdmin  = Role::firstOrCreate(['name' => 'super_admin',  'guard_name' => 'sanctum']);
        $roleAdmin        = Role::firstOrCreate(['name' => 'admin',        'guard_name' => 'sanctum']);
        $roleDoctor       = Role::firstOrCreate(['name' => 'doctor',       'guard_name' => 'sanctum']);
        $roleReceptionist = Role::firstOrCreate(['name' => 'receptionist', 'guard_name' => 'sanctum']);
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'sanctum']);

        // 4. Create Demo Users
        $superAdmin = User::create([
            'id' => (string) Str::orderedUuid(),
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'employee_code' => 'SA001',
            'name' => 'Super Admin Demo',
            'username' => 'superadmin',
            'email' => 'superadmin@demodental.com',
            'phone' => '+62 812 3456 7890',
            'password' => Hash::make('password123'),
            'gender' => 'male',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $doctor = User::create([
            'id' => (string) Str::orderedUuid(),
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'employee_code' => 'DOC001',
            'name' => 'Dr. Jane Smith',
            'username' => 'drjane',
            'email' => 'drjane@demodental.com',
            'phone' => '+62 813 9876 5432',
            'password' => Hash::make('password123'),
            'gender' => 'female',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $receptionist = User::create([
            'id' => (string) Str::orderedUuid(),
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'employee_code' => 'REC001',
            'name' => 'Sarah Receptionist',
            'username' => 'sarah',
            'email' => 'sarah@demodental.com',
            'phone' => '+62 814 5555 6666',
            'password' => Hash::make('password123'),
            'gender' => 'female',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Assign roles to demo users
        $superAdmin->assignRole($rolesSuperAdmin);
        $doctor->assignRole($roleDoctor);
        $receptionist->assignRole($roleReceptionist);

        // 5. Create Demo Patients
        $patients = [];

        $patients[] = Patient::create([
            'id' => (string) Str::orderedUuid(),
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'patient_code' => 'PAT20260001',
            'full_name' => 'John Doe',
            'phone' => '+62 815 1111 2222',
            'email' => 'john.doe@example.com',
            'gender' => 'male',
            'birth_date' => '1990-05-15',
            'address' => 'Jl. Kebon Jeruk No. 789, Jakarta',
            'is_active' => true,
            'created_by' => $receptionist->id,
        ]);

        $patients[] = Patient::create([
            'id' => (string) Str::orderedUuid(),
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'patient_code' => 'PAT20260002',
            'full_name' => 'Maria Garcia',
            'phone' => '+62 816 3333 4444',
            'email' => 'maria.garcia@example.com',
            'gender' => 'female',
            'birth_date' => '1985-08-20',
            'address' => 'Jl. Menteng Raya No. 321, Jakarta',
            'is_active' => true,
            'created_by' => $receptionist->id,
        ]);

        $patients[] = Patient::create([
            'id' => (string) Str::orderedUuid(),
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'patient_code' => 'PAT20260003',
            'full_name' => 'Robert Chen',
            'phone' => '+62 817 5555 6666',
            'email' => 'robert.chen@example.com',
            'gender' => 'male',
            'birth_date' => '1995-03-10',
            'address' => 'Jl. Kuningan No. 567, Jakarta',
            'is_active' => true,
            'created_by' => $receptionist->id,
        ]);

        // 6. Create a doctor profile (doctors table) for appointments.
        $doctorProfile = Doctor::create([
            'id'              => (string) Str::orderedUuid(),
            'organization_id' => $organization->id,
            'branch_id'       => $branch->id,
            'doctor_code'     => 'DOC20260001',
            'full_name'       => 'drg. Jane Doe',
            'phone'           => '+62 813 8888 9999',
            'email'           => 'jane.doe@demodental.com',
            'gender'          => 'female',
            'is_active'       => true,
            'created_by'      => $superAdmin->id,
        ]);

        // 7. Create Demo Appointments.
        $appointments = [];

        // Near-term (reminder due soon) — exercises the WhatsApp reminder flow.
        $appointments[] = Appointment::create([
            'id'               => (string) Str::orderedUuid(),
            'organization_id'  => $organization->id,
            'branch_id'        => $branch->id,
            'patient_id'       => $patients[0]->id,
            'doctor_id'        => $doctorProfile->id,
            'scheduled_at'     => now()->addMinutes(75),
            'end_at'           => now()->addMinutes(105),
            'status'           => 'confirmed',
            'type'             => 'checkup',
            'notes'            => 'Kontrol rutin.',
            'reminder_minutes' => 30,
            'reminder_sent'    => false,
            'created_by'       => $receptionist->id,
        ]);

        $appointments[] = Appointment::create([
            'id'               => (string) Str::orderedUuid(),
            'organization_id'  => $organization->id,
            'branch_id'        => $branch->id,
            'patient_id'       => $patients[1]->id,
            'doctor_id'        => $doctorProfile->id,
            'scheduled_at'     => now()->addDay()->setTime(10, 0),
            'end_at'           => now()->addDay()->setTime(11, 0),
            'status'           => 'scheduled',
            'type'             => 'treatment',
            'notes'            => 'Perawatan saluran akar.',
            'reminder_minutes' => 1440,
            'reminder_sent'    => false,
            'created_by'       => $receptionist->id,
        ]);

        $appointments[] = Appointment::create([
            'id'               => (string) Str::orderedUuid(),
            'organization_id'  => $organization->id,
            'branch_id'        => $branch->id,
            'patient_id'       => $patients[2]->id,
            'doctor_id'        => $doctorProfile->id,
            'scheduled_at'     => now()->addDays(2)->setTime(14, 30),
            'end_at'           => now()->addDays(2)->setTime(15, 0),
            'status'           => 'scheduled',
            'type'             => 'consultation',
            'notes'            => 'Konsultasi orthodonti.',
            'reminder_minutes' => 60,
            'reminder_sent'    => false,
            'created_by'       => $receptionist->id,
        ]);

        // 8. Create Demo EMR (Rekam Medis) records with full SOAP detail.
        $emrs = [];

        $emrs[] = EMR::create([
            'id'                  => (string) Str::orderedUuid(),
            'organization_id'     => $organization->id,
            'patient_id'          => $patients[0]->id,
            'doctor_id'           => $doctorProfile->id,
            'appointment_id'      => $appointments[0]->id,
            'examination_date'    => now()->subDays(1),
            'tooth_number'        => '46',
            'icd_code'            => 'K02.1',
            'chief_complaint'     => 'Sakit gigi belakang kanan bawah saat makan manis dan dingin.',
            'present_illness'     => 'Nyeri sejak 3 hari, terasa tajam saat terkena makanan manis/dingin, hilang timbul.',
            'medical_history'     => 'Tidak ada riwayat penyakit sistemik (diabetes, hipertensi, jantung disangkal).',
            'allergies'           => 'Tidak ada alergi obat maupun makanan.',
            'vital_signs'         => ['blood_pressure' => '120/80', 'pulse' => 78, 'temperature' => 36.5, 'respiratory_rate' => 18],
            'extra_oral_exam'     => 'Tidak ada pembengkakan pada pipi kanan, KGB submandibula tidak teraba membesar.',
            'intra_oral_exam'     => 'Karies media pada oklusal gigi 46, perkusi (-), palpasi (-), tes vitalitas (+) normal.',
            'radiology_findings'  => 'Radiograf periapikal: radiolusensi pada oklusal 46 mencapai dentin, belum mencapai pulpa.',
            'diagnosis'           => 'Karies media gigi 46 (K02.1)',
            'secondary_diagnosis' => '-',
            'treatment_notes'     => 'Preparasi kavitas dan restorasi komposit light-cured pada gigi 46.',
            'treatment_plan'      => 'Restorasi komposit; kontrol 1 minggu untuk evaluasi adaptasi restorasi.',
            'prescription'        => 'Paracetamol 500 mg, 3x1 sehari bila nyeri.',
            'follow_up_plan'      => 'Kontrol 1 minggu lagi. Hindari makanan terlalu manis/dingin sementara.',
            'status'              => 'completed',
            'created_by'          => $doctorProfile->id,
        ]);

        $emrs[] = EMR::create([
            'id'                  => (string) Str::orderedUuid(),
            'organization_id'     => $organization->id,
            'patient_id'          => $patients[1]->id,
            'doctor_id'           => $doctorProfile->id,
            'examination_date'    => now()->subDays(3),
            'tooth_number'        => '11, 21',
            'icd_code'            => 'K03.6',
            'chief_complaint'     => 'Karang gigi dan gusi mudah berdarah saat menyikat gigi.',
            'present_illness'     => 'Keluhan sejak 2 bulan, gusi depan atas terasa bengkak dan mudah berdarah.',
            'medical_history'     => 'Riwayat hipertensi terkontrol dengan obat.',
            'allergies'           => 'Alergi penisilin.',
            'vital_signs'         => ['blood_pressure' => '140/90', 'pulse' => 82, 'temperature' => 36.7, 'respiratory_rate' => 20],
            'extra_oral_exam'     => 'Simetris, tidak ada kelainan.',
            'intra_oral_exam'     => 'Akumulasi kalkulus pada region anterior rahang atas, gingiva hiperemis dan mudah berdarah pada probing.',
            'radiology_findings'  => 'Tidak dilakukan.',
            'diagnosis'           => 'Gingivitis akibat kalkulus (K03.6)',
            'secondary_diagnosis' => 'Deposit kalkulus supragingiva',
            'treatment_notes'     => 'Scaling ultrasonik rahang atas region anterior.',
            'treatment_plan'      => 'Scaling penuh dilanjutkan root planing bila diperlukan; edukasi oral hygiene.',
            'prescription'        => 'Obat kumur chlorhexidine 0.12%, 2x sehari selama 1 minggu.',
            'follow_up_plan'      => 'Kontrol 2 minggu untuk evaluasi kebersihan mulut dan rencana scaling lanjutan.',
            'status'              => 'open',
            'created_by'          => $doctorProfile->id,
        ]);

        $emrs[] = EMR::create([
            'id'                  => (string) Str::orderedUuid(),
            'organization_id'     => $organization->id,
            'patient_id'          => $patients[2]->id,
            'doctor_id'           => $doctorProfile->id,
            'examination_date'    => now()->subHours(2),
            'tooth_number'        => '38',
            'icd_code'            => 'K05.3',
            'chief_complaint'     => 'Nyeri dan bengkak pada gusi belakang kiri bawah.',
            'present_illness'     => 'Bengkak sejak 2 hari, sulit membuka mulut dan menelan, nyeri berdenyut.',
            'medical_history'     => 'Tidak ada penyakit sistemik.',
            'allergies'           => 'Tidak ada alergi.',
            'vital_signs'         => ['blood_pressure' => '118/76', 'pulse' => 88, 'temperature' => 37.8, 'respiratory_rate' => 19],
            'extra_oral_exam'     => 'Pembengkakan ringan pada pipi kiri bawah, trismus ringan.',
            'intra_oral_exam'     => 'Gigi 38 impaksi sebagian, operkulum hiperemis dan bengkak, ada pus pada tekanan.',
            'radiology_findings'  => 'Panoramic: gigi 38 impaksi mesioangular, mendekati kanalis mandibula.',
            'diagnosis'           => 'Perikoronitis gigi 38 (K05.3)',
            'secondary_diagnosis' => 'Impaksi gigi 38',
            'treatment_notes'     => 'Irigasi operkulum, drainase, dan pembersihan area perikoronal.',
            'treatment_plan'      => 'Antibiotik dan analgesik; rencana odontektomi 38 setelah fase akut teratasi.',
            'prescription'        => 'Amoxicillin 500 mg 3x1 (7 hari), Asam mefenamat 500 mg 3x1.',
            'follow_up_plan'      => 'Kontrol 3 hari untuk evaluasi infeksi; jadwalkan odontektomi setelah kondisi stabil.',
            'status'              => 'open',
            'created_by'          => $doctorProfile->id,
        ]);

        $this->command->info('✅ Demo data seeded successfully!');
        $this->command->info('');
        $this->command->info('Demo Credentials:');
        $this->command->info('─────────────────────────────────────────');
        $this->command->info('Super Admin:');
        $this->command->info('  Username: superadmin');
        $this->command->info('  Email: superadmin@demodental.com');
        $this->command->info('  Password: password123');
        $this->command->info('');
        $this->command->info('Doctor:');
        $this->command->info('  Username: drjane');
        $this->command->info('  Email: drjane@demodental.com');
        $this->command->info('  Password: password123');
        $this->command->info('');
        $this->command->info('Receptionist:');
        $this->command->info('  Username: sarah');
        $this->command->info('  Email: sarah@demodental.com');
        $this->command->info('  Password: password123');
        $this->command->info('─────────────────────────────────────────');
        $this->command->info('');
        $this->command->info('Organization: ' . $organization->company_name);
        $this->command->info('Branch: ' . $branch->branch_name);
        $this->command->info('Patients: ' . count($patients) . ' demo patients created');
        $this->command->info('Appointments: ' . count($appointments) . ' upcoming appointments');
        $this->command->info('EMR: ' . count($emrs) . ' rekam medis created');
    }
}
