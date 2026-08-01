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
        Schema::create('branches', function (Blueprint $table): void {

            // ---------------------------------------------------------------
            // Primary Key
            // ---------------------------------------------------------------
            $table->uuid('id')->primary();

            // ---------------------------------------------------------------
            // Foreign Key
            // ---------------------------------------------------------------
            $table->uuid('organization_id')
                ->comment('References organizations.id');

            // ---------------------------------------------------------------
            // Identity
            // ---------------------------------------------------------------
            $table->string('branch_code', 30)
                ->comment('Branch code — unique within organization');

            $table->string('branch_name', 200)
                ->comment('Branch or clinic name');

            $table->string('branch_type', 50)
                ->comment('Type of branch e.g. clinic, mobile, hospital');

            // ---------------------------------------------------------------
            // Contact
            // ---------------------------------------------------------------
            $table->string('email', 150)->nullable()
                ->comment('Branch email address');

            $table->string('phone', 30)
                ->comment('Branch phone number');

            // ---------------------------------------------------------------
            // Address
            // ---------------------------------------------------------------
            $table->text('address')
                ->comment('Street address');

            $table->string('city', 100)
                ->comment('City');

            $table->string('province', 100)
                ->comment('Province or state');

            $table->string('country', 100)->default('Indonesia')
                ->comment('Country name');

            $table->string('postal_code', 20)
                ->comment('Postal or ZIP code');

            // ---------------------------------------------------------------
            // Geolocation
            // ---------------------------------------------------------------
            $table->decimal('latitude', 10, 8)->nullable()
                ->comment('Geographic latitude — decimal(10,8) ~1mm precision');

            $table->decimal('longitude', 11, 8)->nullable()
                ->comment('Geographic longitude — decimal(11,8) ~1mm precision');

            // ---------------------------------------------------------------
            // Locale
            // ---------------------------------------------------------------
            $table->string('timezone', 100)->default('Asia/Jakarta')
                ->comment('IANA timezone identifier e.g. Asia/Jakarta');

            // ---------------------------------------------------------------
            // Status
            // ---------------------------------------------------------------
            $table->string('status', 20)->default('active')
                ->comment('active | inactive');

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
            $table->timestamps();
            $table->softDeletes();

            // ---------------------------------------------------------------
            // Indexes
            // ---------------------------------------------------------------
            // Note: organization_id standalone index is intentionally omitted.
            // The composite unique index (organization_id, branch_code) already
            // covers single-column lookups on organization_id via its leftmost prefix.
            $table->index('city',   'branches_city_index');
            $table->index('status', 'branches_status_index');

            // Composite unique: branch_code is unique per organization
            $table->unique(
                ['organization_id', 'branch_code'],
                'branches_organization_id_branch_code_unique'
            );

            // ---------------------------------------------------------------
            // Foreign Key Constraint
            // ---------------------------------------------------------------
            $table->foreign('organization_id', 'branches_organization_id_foreign')
                ->references('id')
                ->on('organizations')
                ->restrictOnDelete();
        });

        // ---------------------------------------------------------------
        // PostgreSQL CHECK constraint for status column
        // Ensures only allowed values are inserted at the database level.
        // ---------------------------------------------------------------
        DB::statement("
            ALTER TABLE branches
            ADD CONSTRAINT branches_status_check
            CHECK (status IN ('active', 'inactive'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            $table->dropForeign('branches_organization_id_foreign');
        });

        Schema::dropIfExists('branches');
    }
};
