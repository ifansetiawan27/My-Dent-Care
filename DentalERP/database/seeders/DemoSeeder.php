<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Appointment\Models\Appointment;
use App\Domains\Branch\Models\Branch;
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
            'medical_record_number' => 'MR20260001',
            'name' => 'John Doe',
            'phone' => '+62 815 1111 2222',
            'email' => 'john.doe@example.com',
            'gender' => 'male',
            'birth_date' => '1990-05-15',
            'address' => 'Jl. Kebon Jeruk No. 789',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'postal_code' => '11530',
            'is_active' => true,
            'created_by' => $receptionist->id,
        ]);

        $patients[] = Patient::create([
            'id' => (string) Str::orderedUuid(),
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'medical_record_number' => 'MR20260002',
            'name' => 'Maria Garcia',
            'phone' => '+62 816 3333 4444',
            'email' => 'maria.garcia@example.com',
            'gender' => 'female',
            'birth_date' => '1985-08-20',
            'address' => 'Jl. Menteng Raya No. 321',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'postal_code' => '10340',
            'is_active' => true,
            'created_by' => $receptionist->id,
        ]);

        $patients[] = Patient::create([
            'id' => (string) Str::orderedUuid(),
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'medical_record_number' => 'MR20260003',
            'name' => 'Robert Chen',
            'phone' => '+62 817 5555 6666',
            'email' => 'robert.chen@example.com',
            'gender' => 'male',
            'birth_date' => '1995-03-10',
            'address' => 'Jl. Kuningan No. 567',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'postal_code' => '12950',
            'is_active' => true,
            'created_by' => $receptionist->id,
        ]);

        // 5. Create Demo Appointments
        Appointment::create([
            'id' => (string) Str::orderedUuid(),
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'patient_id' => $patients[0]->id,
            'doctor_id' => $doctor->id,
            'appointment_number' => 'APT20260001',
            'appointment_date' => now()->addDays(1)->setTime(9, 0),
            'duration' => 30,
            'reason' => 'General Checkup',
            'notes' => 'Regular dental checkup',
            'status' => 'scheduled',
            'created_by' => $receptionist->id,
        ]);

        Appointment::create([
            'id' => (string) Str::orderedUuid(),
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'patient_id' => $patients[1]->id,
            'doctor_id' => $doctor->id,
            'appointment_number' => 'APT20260002',
            'appointment_date' => now()->addDays(1)->setTime(10, 0),
            'duration' => 60,
            'reason' => 'Tooth Extraction',
            'notes' => 'Wisdom tooth removal',
            'status' => 'scheduled',
            'created_by' => $receptionist->id,
        ]);

        Appointment::create([
            'id' => (string) Str::orderedUuid(),
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'patient_id' => $patients[2]->id,
            'doctor_id' => $doctor->id,
            'appointment_number' => 'APT20260003',
            'appointment_date' => now()->addDays(2)->setTime(14, 0),
            'duration' => 45,
            'reason' => 'Teeth Cleaning',
            'notes' => 'Scaling and polishing',
            'status' => 'scheduled',
            'created_by' => $receptionist->id,
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
        $this->command->info('Appointments: 3 upcoming appointments');
    }
}
