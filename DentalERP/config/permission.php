<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Spatie Laravel Permission — Dental ERP Configuration
    |--------------------------------------------------------------------------
    |
    | Package: spatie/laravel-permission
    |
    | Customizations applied to this project:
    |  - Teams enabled: Organization ID used as team scope
    |  - UUID support: model_id uses uuid type (matches User model)
    |  - Guard: sanctum
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Permission and Role Models
    |--------------------------------------------------------------------------
    |
    | Spatie's default models are used. No custom models required.
    | If custom behavior is needed, extend Spatie's models and update here.
    |
    */

    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role'       => Spatie\Permission\Models\Role::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    */

    'table_names' => [
        'roles'                 => 'roles',
        'permissions'           => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles'       => 'model_has_roles',
        'role_has_permissions'  => 'role_has_permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Column Names
    |--------------------------------------------------------------------------
    |
    | model_morph_key: The column name for the model's UUID primary key
    | in the pivot tables. Changed from 'model_id' to 'model_uuid'
    | to clearly indicate this column stores a UUID, not a bigint.
    |
    | team_foreign_key: The column name for the team (organization) scope.
    | Stores the organization_id UUID value.
    |
    */

    'column_names' => [
        'role_pivot_key'       => 'role_id',
        'permission_pivot_key' => 'permission_id',
        'model_morph_key'      => 'model_uuid',   // UUID — matches User model primary key
        'team_foreign_key'     => 'team_id',       // Stores organization_id UUID
    ],

    /*
    |--------------------------------------------------------------------------
    | Teams Feature
    |--------------------------------------------------------------------------
    |
    | When enabled, each role assignment is scoped to a "team".
    | In Dental ERP, the team is the ORGANIZATION.
    |
    | Usage:
    |   setPermissionsTeamId($user->organization_id);
    |   $user->assignRole('doctor'); // assigned within that org scope
    |
    | Branch-level scoping is enforced at the application/service layer,
    | not at the Spatie team level.
    |
    */

    'teams' => true,

    /*
    |--------------------------------------------------------------------------
    | Passport Integration (disabled — using Sanctum)
    |--------------------------------------------------------------------------
    */

    'use_passport_client_credentials' => false,

    /*
    |--------------------------------------------------------------------------
    | Display Permission Check Method in Blade
    |--------------------------------------------------------------------------
    */

    'register_permission_check_method' => true,

    /*
    |--------------------------------------------------------------------------
    | Register Octane Reset Listener
    |--------------------------------------------------------------------------
    */

    'register_octane_reset_listener' => false,

    /*
    |--------------------------------------------------------------------------
    | Wildcard Permission Support
    |--------------------------------------------------------------------------
    |
    | Enable wildcard permission matching.
    | Allows: $user->can('patient.*') to match 'patient.view', 'patient.create', etc.
    |
    */

    'enable_wildcard_permission' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Guard
    |--------------------------------------------------------------------------
    |
    | Guard must match Sanctum. This ensures permissions are checked
    | against the Sanctum-authenticated user.
    |
    */

    'default_guard_name' => 'sanctum',

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Permissions and roles are cached for performance.
    | Cache is automatically invalidated when roles or permissions change.
    | Clear cache manually: php artisan permission:cache-reset
    |
    */

    'cache' => [
        'expiration_time'  => \DateInterval::createFromDateString('24 hours'),
        'key'              => 'spatie.permission.cache',
        'store'            => 'default',
    ],

];
