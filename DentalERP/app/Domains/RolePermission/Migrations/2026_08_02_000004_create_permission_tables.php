<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spatie Laravel Permission — Modified Migration
 *
 * Changes from the default Spatie migration:
 *
 * 1. UUID support:
 *    - `model_uuid` column uses uuid type (matches User model UUID primary key).
 *    - Default Spatie uses `model_id` as unsignedBigInteger.
 *
 * 2. Teams support enabled:
 *    - `team_id` column added to `model_has_roles` as uuid type.
 *    - Stores the organization_id UUID as the team scope.
 *    - Default Spatie uses unsignedBigInteger for team_id.
 *
 * 3. Column name:
 *    - `model_morph_key` configured as `model_uuid` in config/permission.php.
 *
 * Reference: https://spatie.be/docs/laravel-permission
 */
return new class extends Migration
{
    /**
     * The database connection to use.
     *
     * @var string
     */
    protected $connection = 'pgsql';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames  = config('permission.table_names');
        $columnNames = config('permission.column_names');

        // -----------------------------------------------------------------------
        // permissions
        // -----------------------------------------------------------------------
        Schema::create($tableNames['permissions'], function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name', 125);
            $table->string('guard_name', 125);
            $table->timestamps();

            $table->unique(['name', 'guard_name'], 'permissions_name_guard_name_unique');
        });

        // -----------------------------------------------------------------------
        // roles
        // -----------------------------------------------------------------------
        Schema::create($tableNames['roles'], function (Blueprint $table) use ($columnNames): void {
            $table->bigIncrements('id');

            // Team scope — stores organization_id UUID
            if (config('permission.teams') === true) {
                $table->uuid($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }

            $table->string('name', 125);
            $table->string('guard_name', 125);
            $table->timestamps();

            if (config('permission.teams') === true) {
                $table->unique(
                    [$columnNames['team_foreign_key'], 'name', 'guard_name'],
                    'roles_team_name_guard_unique'
                );
            } else {
                $table->unique(['name', 'guard_name'], 'roles_name_guard_name_unique');
            }
        });

        // -----------------------------------------------------------------------
        // model_has_permissions
        // Pivot: User (UUID) ↔ Permission (direct assignment)
        // -----------------------------------------------------------------------
        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames): void {
            $table->unsignedBigInteger($columnNames['permission_pivot_key']);

            // model_uuid: UUID — matches User model primary key
            $table->string('model_type');
            $table->uuid($columnNames['model_morph_key']);

            $table->index(
                [$columnNames['model_morph_key'], 'model_type'],
                'model_has_permissions_model_uuid_model_type_index'
            );

            $table->foreign($columnNames['permission_pivot_key'])
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->primary(
                [$columnNames['permission_pivot_key'], $columnNames['model_morph_key'], 'model_type'],
                'model_has_permissions_permission_model_type_primary'
            );
        });

        // -----------------------------------------------------------------------
        // model_has_roles
        // Pivot: User (UUID) ↔ Role — scoped by team (organization UUID)
        // -----------------------------------------------------------------------
        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames): void {
            $table->unsignedBigInteger($columnNames['role_pivot_key']);

            // model_uuid: UUID — matches User model primary key
            $table->string('model_type');
            $table->uuid($columnNames['model_morph_key']);

            $table->index(
                [$columnNames['model_morph_key'], 'model_type'],
                'model_has_roles_model_uuid_model_type_index'
            );

            $table->foreign($columnNames['role_pivot_key'])
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            // Team scope — organization_id UUID (nullable for super admin)
            if (config('permission.teams') === true) {
                $table->uuid($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary(
                    [$columnNames['role_pivot_key'], $columnNames['model_morph_key'], 'model_type', $columnNames['team_foreign_key']],
                    'model_has_roles_role_model_type_team_primary'
                );
            } else {
                $table->primary(
                    [$columnNames['role_pivot_key'], $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            }
        });

        // -----------------------------------------------------------------------
        // role_has_permissions
        // Pivot: Role ↔ Permission
        // -----------------------------------------------------------------------
        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames): void {
            $table->unsignedBigInteger($columnNames['permission_pivot_key']);
            $table->unsignedBigInteger($columnNames['role_pivot_key']);

            $table->foreign($columnNames['permission_pivot_key'])
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($columnNames['role_pivot_key'])
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(
                [$columnNames['permission_pivot_key'], $columnNames['role_pivot_key']],
                'role_has_permissions_permission_id_role_id_primary'
            );
        });

        // Clear Spatie permission cache after migration
        app()['cache']->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};
