<?php

declare(strict_types=1);

namespace App\Domains\RolePermission\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * PermissionSeeder
 *
 * Seeds all permissions for the Dental ERP platform.
 * Convention: {domain}.{action}
 *
 * CRUD actions per domain:
 *  - Standard  : view, create, update, delete
 *  - Extended  : restore (soft-delete recovery)
 *  - Export    : export (data export / reporting)
 *  - Special   : assign (for role/permission management)
 *
 * Guard: sanctum
 *
 * Run:
 *   php artisan db:seed --class=App\\Domains\\RolePermission\\Seeders\\PermissionSeeder
 */
class PermissionSeeder extends Seeder
{
    /**
     * Auth guard — must match Sanctum configuration.
     */
    private const GUARD = 'sanctum';

    /**
     * Run the seeder.
     */
    public function run(): void
    {
        // Reset cached permissions before seeding
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $created = 0;
        $skipped = 0;

        foreach ($this->permissions() as $name) {
            $result = Permission::firstOrCreate([
                'name'       => $name,
                'guard_name' => self::GUARD,
            ]);

            if ($result->wasRecentlyCreated) {
                $created++;
            } else {
                $skipped++;
            }
        }

        $this->command->info(
            "✅ Permissions seeded — Created: {$created}, Skipped (already exists): {$skipped}"
        );
    }

    // -------------------------------------------------------------------------
    // Permission Definitions
    // Convention: {domain}.{action}
    // -------------------------------------------------------------------------

    /**
     * Full permission list grouped by domain.
     * Each domain defines only the actions relevant to its business scope.
     *
     * @return array<string>
     */
    private function permissions(): array
    {
        return array_merge(
            $this->organizationPermissions(),
            $this->branchPermissions(),
            $this->userPermissions(),
            $this->rolePermissions(),
            $this->permissionPermissions(),
            $this->patientPermissions(),
            $this->appointmentPermissions(),
            $this->medicalRecordPermissions(),
            $this->odontogramPermissions(),
            $this->treatmentPermissions(),
            $this->inventoryPermissions(),
            $this->financePermissions(),
            $this->assetPermissions(),
            $this->crmPermissions(),
            $this->dashboardPermissions(),
            $this->reportPermissions(),
        );
    }

    // -------------------------------------------------------------------------
    // Per-domain permissions
    // -------------------------------------------------------------------------

    /**
     * Organization — tenant-level settings.
     * No create/delete — organizations are managed at the system level.
     *
     * @return array<string>
     */
    private function organizationPermissions(): array
    {
        return [
            'organization.view',
            'organization.update',
        ];
    }

    /**
     * Branch — clinic location management.
     *
     * @return array<string>
     */
    private function branchPermissions(): array
    {
        return [
            'branch.view',
            'branch.create',
            'branch.update',
            'branch.delete',
            'branch.restore',
        ];
    }

    /**
     * User — staff account management.
     *
     * @return array<string>
     */
    private function userPermissions(): array
    {
        return [
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            'user.restore',
        ];
    }

    /**
     * Role — role management and assignment.
     * No CRUD for roles themselves — roles are seeded, not UI-created.
     *
     * @return array<string>
     */
    private function rolePermissions(): array
    {
        return [
            'role.view',
            'role.assign',
        ];
    }

    /**
     * Permission — permission management and assignment.
     * No CRUD for permissions — permissions are seeded, not UI-created.
     *
     * @return array<string>
     */
    private function permissionPermissions(): array
    {
        return [
            'permission.view',
            'permission.assign',
        ];
    }

    /**
     * Patient — core clinical entity.
     *
     * @return array<string>
     */
    private function patientPermissions(): array
    {
        return [
            'patient.view',
            'patient.create',
            'patient.update',
            'patient.delete',
            'patient.restore',
            'patient.export',
        ];
    }

    /**
     * Appointment — scheduling management.
     *
     * @return array<string>
     */
    private function appointmentPermissions(): array
    {
        return [
            'appointment.view',
            'appointment.create',
            'appointment.update',
            'appointment.delete',
            'appointment.restore',
            'appointment.export',
        ];
    }

    /**
     * Medical Record (EMR) — electronic medical records.
     * No restore — medical records follow strict compliance rules.
     *
     * @return array<string>
     */
    private function medicalRecordPermissions(): array
    {
        return [
            'medical_record.view',
            'medical_record.create',
            'medical_record.update',
            'medical_record.delete',
        ];
    }

    /**
     * Odontogram — dental chart management.
     * No delete — odontogram entries are clinical records.
     *
     * @return array<string>
     */
    private function odontogramPermissions(): array
    {
        return [
            'odontogram.view',
            'odontogram.create',
            'odontogram.update',
        ];
    }

    /**
     * Treatment — treatment planning and execution.
     *
     * @return array<string>
     */
    private function treatmentPermissions(): array
    {
        return [
            'treatment.view',
            'treatment.create',
            'treatment.update',
            'treatment.delete',
            'treatment.restore',
        ];
    }

    /**
     * Inventory — stock and supply management.
     *
     * @return array<string>
     */
    private function inventoryPermissions(): array
    {
        return [
            'inventory.view',
            'inventory.create',
            'inventory.update',
            'inventory.delete',
            'inventory.restore',
            'inventory.export',
        ];
    }

    /**
     * Finance — billing, invoicing, and transactions.
     *
     * @return array<string>
     */
    private function financePermissions(): array
    {
        return [
            'finance.view',
            'finance.create',
            'finance.update',
            'finance.delete',
            'finance.restore',
            'finance.export',
        ];
    }

    /**
     * Asset — clinic equipment and property.
     *
     * @return array<string>
     */
    private function assetPermissions(): array
    {
        return [
            'asset.view',
            'asset.create',
            'asset.update',
            'asset.delete',
            'asset.restore',
        ];
    }

    /**
     * CRM — customer relationship management.
     *
     * @return array<string>
     */
    private function crmPermissions(): array
    {
        return [
            'crm.view',
            'crm.create',
            'crm.update',
            'crm.delete',
        ];
    }

    /**
     * Dashboard — analytics and KPI views.
     * No CRUD — dashboards are read-only views.
     *
     * @return array<string>
     */
    private function dashboardPermissions(): array
    {
        return [
            'dashboard.view',
            'dashboard.export',
        ];
    }

    /**
     * Report — business reporting and data export.
     * No CRUD — reports are read-only outputs.
     *
     * @return array<string>
     */
    private function reportPermissions(): array
    {
        return [
            'report.view',
            'report.export',
        ];
    }
}
