<?php

declare(strict_types=1);

namespace App\Domains\RolePermission\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * RoleSeeder
 *
 * Seeds default roles for the Dental ERP platform.
 * This seeder creates roles only — permissions are assigned by RolePermissionSeeder.
 *
 * UUID Note:
 * Spatie Role records use bigint IDs by default.
 * UUID compatibility is maintained via the `model_uuid` column in pivot tables,
 * which stores the User model's UUID primary key.
 * The `team_id` column (organization scope) also uses UUID — set via:
 *   setPermissionsTeamId($organizationId)
 *
 * Guard: sanctum
 *
 * Run:
 *   php artisan db:seed --class=App\\Domains\\RolePermission\\Seeders\\RoleSeeder
 */
class RoleSeeder extends Seeder
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
        // Reset cached roles and permissions before seeding
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $created = 0;
        $skipped = 0;

        foreach ($this->roles() as $role) {
            $result = Role::firstOrCreate(
                [
                    'name'       => $role['name'],
                    'guard_name' => self::GUARD,
                ],
            );

            if ($result->wasRecentlyCreated) {
                $created++;
            } else {
                $skipped++;
            }
        }

        $this->command->info("✅ Roles seeded — Created: {$created}, Skipped (already exists): {$skipped}");
    }

    // -------------------------------------------------------------------------
    // Role Definitions
    // -------------------------------------------------------------------------

    /**
     * Default roles for the Dental ERP platform.
     * Each role uses a machine-readable `name` (slug) and a guard.
     *
     * Display names are shown in comments for readability.
     * Add a `description` column to the `roles` table if needed for UI display.
     *
     * @return array<int, array<string, string>>
     */
    private function roles(): array
    {
        return [
            // ---------------------------------------------------------------
            // System Roles
            // ---------------------------------------------------------------
            [
                'name'       => 'super_admin',        // Super Admin
                'guard_name' => self::GUARD,
            ],
            [
                'name'       => 'owner',               // Owner
                'guard_name' => self::GUARD,
            ],

            // ---------------------------------------------------------------
            // Management Roles
            // ---------------------------------------------------------------
            [
                'name'       => 'branch_manager',     // Branch Manager
                'guard_name' => self::GUARD,
            ],

            // ---------------------------------------------------------------
            // Clinical Roles
            // ---------------------------------------------------------------
            [
                'name'       => 'doctor',              // Doctor
                'guard_name' => self::GUARD,
            ],
            [
                'name'       => 'dentist_specialist',  // Dentist Specialist
                'guard_name' => self::GUARD,
            ],
            [
                'name'       => 'nurse',               // Nurse
                'guard_name' => self::GUARD,
            ],

            // ---------------------------------------------------------------
            // Operational Roles
            // ---------------------------------------------------------------
            [
                'name'       => 'receptionist',        // Receptionist
                'guard_name' => self::GUARD,
            ],
            [
                'name'       => 'cashier',             // Cashier
                'guard_name' => self::GUARD,
            ],
            [
                'name'       => 'laboratory',          // Laboratory
                'guard_name' => self::GUARD,
            ],
            [
                'name'       => 'inventory_staff',     // Inventory
                'guard_name' => self::GUARD,
            ],

            // ---------------------------------------------------------------
            // Corporate Roles
            // ---------------------------------------------------------------
            [
                'name'       => 'finance',             // Finance
                'guard_name' => self::GUARD,
            ],
            [
                'name'       => 'hr',                  // HR
                'guard_name' => self::GUARD,
            ],
            [
                'name'       => 'marketing',           // Marketing
                'guard_name' => self::GUARD,
            ],
            [
                'name'       => 'customer_service',    // Customer Service
                'guard_name' => self::GUARD,
            ],
        ];
    }
}
