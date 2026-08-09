<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        Schema::create('users', function (Blueprint $table): void {

            // ---------------------------------------------------------------
            // Primary Key
            // ---------------------------------------------------------------
            $table->uuid('id')->primary();

            // ---------------------------------------------------------------
            // Foreign Keys — Multi-Tenant Scope
            // ---------------------------------------------------------------
            $table->uuid('organization_id')
                ->comment('References organizations.id — tenant boundary');

            $table->uuid('branch_id')
                ->comment('References branches.id — operational scope of the user');

            // ---------------------------------------------------------------
            // Identity
            // ---------------------------------------------------------------
            $table->string('employee_code', 30)->unique()
                ->comment('Globally unique employee or staff code — used in HR and clinical linkage');

            $table->string('name', 200)
                ->comment('Full display name of the user');

            $table->string('username', 100)->unique()
                ->comment('Globally unique login username');

            $table->string('email', 150)->unique()
                ->comment('Globally unique email address');

            $table->string('phone', 30)->nullable()
                ->comment('Mobile or office phone number');

            // ---------------------------------------------------------------
            // Authentication
            // ---------------------------------------------------------------
            $table->string('password', 255)
                ->comment('Bcrypt hashed password — never stored in plaintext');

            // ---------------------------------------------------------------
            // Profile
            // ---------------------------------------------------------------
            $table->string('photo', 500)->nullable()
                ->comment('Profile photo relative storage path');

            $table->string('gender', 10)->nullable()
                ->comment('male | female');

            $table->date('birth_date')->nullable()
                ->comment('Date of birth — used for HR reporting');

            // ---------------------------------------------------------------
            // Session & Verification
            // ---------------------------------------------------------------
            $table->timestampTz('last_login_at')->nullable()
                ->comment('Timestamp of the latest successful login');

            $table->timestampTz('email_verified_at')->nullable()
                ->comment('Timestamp when email address was verified');

            // ---------------------------------------------------------------
            // Status
            // ---------------------------------------------------------------
            $table->string('status', 20)->default('active')
                ->comment('active | inactive — inactive users cannot log in');

            // ---------------------------------------------------------------
            // Audit Trail
            // ---------------------------------------------------------------
            $table->uuid('created_by')->nullable()
                ->comment('User UUID who created this record');

            $table->uuid('updated_by')->nullable()
                ->comment('User UUID who last updated this record');

            $table->uuid('deleted_by')->nullable()
                ->comment('User UUID who soft-deleted this record');

            // ---------------------------------------------------------------
            // Timestamps & Soft Delete
            // ---------------------------------------------------------------
            $table->timestampsTz();
            $table->softDeletesTz();

            // ---------------------------------------------------------------
            // Indexes
            // ---------------------------------------------------------------
            $table->index('organization_id', 'users_organization_id_index');
            $table->index('branch_id',       'users_branch_id_index');
            $table->index('status',          'users_status_index');

            // ---------------------------------------------------------------
            // Foreign Key Constraints
            // ---------------------------------------------------------------
            $table->foreign('organization_id', 'users_organization_id_foreign')
                ->references('id')
                ->on('organizations')
                ->restrictOnDelete();

            $table->foreign('branch_id', 'users_branch_id_foreign')
                ->references('id')
                ->on('branches')
                ->restrictOnDelete();
        });

        // ---------------------------------------------------------------
        // PostgreSQL CHECK constraint — status
        // Enforces allowed values at the database level.
        // ---------------------------------------------------------------
        DB::statement("
            ALTER TABLE users
            ADD CONSTRAINT users_status_check
            CHECK (status IN ('active', 'inactive'))
        ");

        // ---------------------------------------------------------------
        // PostgreSQL CHECK constraint — gender
        // Enforces allowed values at the database level.
        // ---------------------------------------------------------------
        DB::statement("
            ALTER TABLE users
            ADD CONSTRAINT users_gender_check
            CHECK (gender IS NULL OR gender IN ('male', 'female'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FK constraints before dropping the table
        // to avoid RESTRICT violation on organizations and branches.
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign('users_organization_id_foreign');
            $table->dropForeign('users_branch_id_foreign');
        });

        Schema::dropIfExists('users');
    }
};
