<?php

declare(strict_types=1);

namespace App\Domains\RolePermission\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * RolePermissionSeeder
 *
 * Seeds all roles and permissions for the Dental ERP platform.
 * Convention: permission names follow `{domain}.{action}` pattern.
 * Guard: sanctum
 *
 * Run: php artisan db:seed --class=App\\Domains\\RolePermission\\Seeders\\RolePermissionSeeder
 */
class RolePermissionSeeder extends Seeder
{
    private const GUARD = 'sanctum';

    /**
     * Run the seeder.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->createPermissions();
        $this->createRoles();
        $this->assignPermissionsToRoles();

        $this->command->info('✅ Roles and Permissions seeded successfully.');
    }

    // -------------------------------------------------------------------------
    // Permissions
    // -------------------------------------------------------------------------

    /**
     * Create all permissions grouped by domain.
     */
    private function createPermissions(): void
    {
        $permissions = $this->permissionList();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => self::GUARD,
            ]);
        }

        $this->command->info('  Permissions created: ' . count($permissions));
    }

    /**
     * Full list of all permissions.
     * Convention: {domain}.{action}
     *
     * @return array<string>
     */
    private function permissionList(): array
    {
        return [
            // Organization
            'organization.view',
            'organization.update',

            // Branch
            'branch.view',
            'branch.create',
            'branch.update',
            'branch.delete',
            'branch.restore',

            // User
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            'user.restore',

            // Role & Permission
            'role.view',
            'role.assign',
            'permission.view',
            'permission.assign',

            // Patient
            'patient.view',
            'patient.create',
            'patient.update',
            'patient.delete',
            'patient.export',

            // Appointment
            'appointment.view',
            'appointment.create',
            'appointment.update',
            'appointment.delete',
            'appointment.export',

            // Medical Record (EMR)
            'medical_record.view',
            'medical_record.create',
            'medical_record.update',
            'medical_record.delete',

            // Odontogram
            'odontogram.view',
            'odontogram.create',
            'odontogram.update',

            // Treatment
            'treatment.view',
            'treatment.create',
            'treatment.update',
            'treatment.delete',

            // Inventory
            'inventory.view',
            'inventory.create',
            'inventory.update',
            'inventory.delete',
            'inventory.export',

            // Finance
            'finance.view',
            'finance.create',
            'finance.update',
            'finance.delete',
            'finance.export',

            // Asset
            'asset.view',
            'asset.create',
            'asset.update',
            'asset.delete',

            // CRM
            'crm.view',
            'crm.create',
            'crm.update',
            'crm.delete',

            // Dashboard
            'dashboard.view',
            'dashboard.export',

            // Report
            'report.view',
            'report.export',
        ];
    }

    // -------------------------------------------------------------------------
    // Roles
    // -------------------------------------------------------------------------

    /**
     * Create all roles.
     */
    private function createRoles(): void
    {
        foreach ($this->roleList() as $role) {
            Role::firstOrCreate([
                'name'       => $role,
                'guard_name' => self::GUARD,
            ]);
        }

        $this->command->info('  Roles created: ' . count($this->roleList()));
    }

    /**
     * All role slugs.
     *
     * @return array<string>
     */
    private function roleList(): array
    {
        return [
            'super_admin',
            'owner',
            'branch_manager',
            'doctor',
            'dentist_specialist',
            'nurse',
            'receptionist',
            'cashier',
            'pharmacist',
            'laboratory',
            'inventory_staff',
            'hr',
            'finance',
            'marketing',
            'customer_service',
        ];
    }

    // -------------------------------------------------------------------------
    // Assign Permissions to Roles
    // -------------------------------------------------------------------------

    /**
     * Assign permissions to each role according to the permission matrix.
     */
    private function assignPermissionsToRoles(): void
    {
        $this->assignSuperAdmin();
        $this->assignOwner();
        $this->assignBranchManager();
        $this->assignDoctor();
        $this->assignDentistSpecialist();
        $this->assignNurse();
        $this->assignReceptionist();
        $this->assignCashier();
        $this->assignPharmacist();
        $this->assignLaboratory();
        $this->assignInventoryStaff();
        $this->assignHR();
        $this->assignFinance();
        $this->assignMarketing();
        $this->assignCustomerService();

        $this->command->info('  Permissions assigned to roles.');
    }

    /** Super Admin — all permissions. */
    private function assignSuperAdmin(): void
    {
        $role = Role::where('name', 'super_admin')->where('guard_name', self::GUARD)->first();
        $role?->givePermissionTo(Permission::where('guard_name', self::GUARD)->pluck('name'));
    }

    /** Owner — full access within own organization. */
    private function assignOwner(): void
    {
        $this->assignToRole('owner', [
            'organization.view', 'organization.update',
            'branch.view', 'branch.create', 'branch.update', 'branch.delete', 'branch.restore',
            'user.view', 'user.create', 'user.update', 'user.delete', 'user.restore',
            'role.view', 'role.assign',
            'patient.view', 'patient.create', 'patient.update', 'patient.delete', 'patient.export',
            'appointment.view', 'appointment.create', 'appointment.update', 'appointment.delete', 'appointment.export',
            'medical_record.view', 'medical_record.create', 'medical_record.update', 'medical_record.delete',
            'odontogram.view', 'odontogram.create', 'odontogram.update',
            'treatment.view', 'treatment.create', 'treatment.update', 'treatment.delete',
            'inventory.view', 'inventory.create', 'inventory.update', 'inventory.delete', 'inventory.export',
            'finance.view', 'finance.create', 'finance.update', 'finance.delete', 'finance.export',
            'asset.view', 'asset.create', 'asset.update', 'asset.delete',
            'crm.view', 'crm.create', 'crm.update', 'crm.delete',
            'dashboard.view', 'dashboard.export',
            'report.view', 'report.export',
        ]);
    }

    /** Branch Manager — full access within own branch. */
    private function assignBranchManager(): void
    {
        $this->assignToRole('branch_manager', [
            'organization.view',
            'branch.view',
            'user.view', 'user.create', 'user.update',
            'patient.view', 'patient.create', 'patient.update', 'patient.delete', 'patient.export',
            'appointment.view', 'appointment.create', 'appointment.update', 'appointment.delete', 'appointment.export',
            'medical_record.view',
            'odontogram.view',
            'treatment.view',
            'inventory.view', 'inventory.export',
            'finance.view',
            'asset.view',
            'crm.view',
            'dashboard.view', 'dashboard.export',
            'report.view', 'report.export',
        ]);
    }

    /** Doctor — clinical access. */
    private function assignDoctor(): void
    {
        $this->assignToRole('doctor', [
            'patient.view', 'patient.create', 'patient.update',
            'appointment.view', 'appointment.create', 'appointment.update',
            'medical_record.view', 'medical_record.create', 'medical_record.update',
            'odontogram.view', 'odontogram.create', 'odontogram.update',
            'treatment.view', 'treatment.create', 'treatment.update',
            'dashboard.view',
        ]);
    }

    /** Dentist Specialist — same as doctor. */
    private function assignDentistSpecialist(): void
    {
        $this->assignToRole('dentist_specialist', [
            'patient.view', 'patient.create', 'patient.update',
            'appointment.view', 'appointment.create', 'appointment.update',
            'medical_record.view', 'medical_record.create', 'medical_record.update',
            'odontogram.view', 'odontogram.create', 'odontogram.update',
            'treatment.view', 'treatment.create', 'treatment.update',
            'dashboard.view',
        ]);
    }

    /** Nurse — limited clinical write access. */
    private function assignNurse(): void
    {
        $this->assignToRole('nurse', [
            'patient.view', 'patient.update',
            'appointment.view', 'appointment.update',
            'medical_record.view', 'medical_record.create',
            'odontogram.view',
            'treatment.view', 'treatment.create',
            'dashboard.view',
        ]);
    }

    /** Receptionist — patient registration and appointments. */
    private function assignReceptionist(): void
    {
        $this->assignToRole('receptionist', [
            'patient.view', 'patient.create', 'patient.update',
            'appointment.view', 'appointment.create', 'appointment.update', 'appointment.delete',
            'crm.view',
            'dashboard.view',
        ]);
    }

    /** Cashier — finance transactions. */
    private function assignCashier(): void
    {
        $this->assignToRole('cashier', [
            'finance.view', 'finance.create',
            'dashboard.view',
        ]);
    }

    /** Pharmacist — pharmacy and medication. */
    private function assignPharmacist(): void
    {
        $this->assignToRole('pharmacist', [
            'inventory.view',
            'dashboard.view',
        ]);
    }

    /** Laboratory — lab requests and results. */
    private function assignLaboratory(): void
    {
        $this->assignToRole('laboratory', [
            'medical_record.view',
            'dashboard.view',
        ]);
    }

    /** Inventory Staff — stock management. */
    private function assignInventoryStaff(): void
    {
        $this->assignToRole('inventory_staff', [
            'inventory.view', 'inventory.create', 'inventory.update', 'inventory.delete', 'inventory.export',
            'asset.view', 'asset.create', 'asset.update',
            'dashboard.view',
            'report.view', 'report.export',
        ]);
    }

    /** HR — staff management. */
    private function assignHR(): void
    {
        $this->assignToRole('hr', [
            'user.view', 'user.create', 'user.update', 'user.delete', 'user.restore',
            'dashboard.view',
            'report.view', 'report.export',
        ]);
    }

    /** Finance — full financial access. */
    private function assignFinance(): void
    {
        $this->assignToRole('finance', [
            'finance.view', 'finance.create', 'finance.update', 'finance.delete', 'finance.export',
            'dashboard.view', 'dashboard.export',
            'report.view', 'report.export',
        ]);
    }

    /** Marketing — CRM and campaigns. */
    private function assignMarketing(): void
    {
        $this->assignToRole('marketing', [
            'patient.view',
            'crm.view', 'crm.create', 'crm.update', 'crm.delete',
            'dashboard.view',
            'report.view',
        ]);
    }

    /** Customer Service — read-only access. */
    private function assignCustomerService(): void
    {
        $this->assignToRole('customer_service', [
            'patient.view',
            'appointment.view',
            'crm.view',
            'dashboard.view',
        ]);
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    /**
     * Assign a list of permissions to a role by slug.
     *
     * @param  string        $roleSlug
     * @param  array<string> $permissions
     */
    private function assignToRole(string $roleSlug, array $permissions): void
    {
        $role = Role::where('name', $roleSlug)
            ->where('guard_name', self::GUARD)
            ->first();

        $role?->syncPermissions($permissions);
    }
}
