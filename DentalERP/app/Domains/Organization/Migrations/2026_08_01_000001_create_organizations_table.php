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
        Schema::create('organizations', function (Blueprint $table): void {

            // ---------------------------------------------------------------
            // Primary Key
            // ---------------------------------------------------------------
            $table->uuid('id')->primary();

            // ---------------------------------------------------------------
            // Identity
            // ---------------------------------------------------------------
            $table->string('company_code', 30)->unique()
                ->comment('Unique company / clinic group code');

            $table->string('company_name', 200)
                ->comment('Trading or brand name');

            $table->string('legal_name', 200)->nullable()
                ->comment('Legal registered company name');

            $table->string('tax_number', 50)->nullable()
                ->comment('NPWP — tax identification number');

            // ---------------------------------------------------------------
            // Contact
            // ---------------------------------------------------------------
            $table->string('email', 150)
                ->comment('Primary company email');

            $table->string('phone', 30)
                ->comment('Primary company phone number');

            $table->string('website', 150)->nullable()
                ->comment('Company website URL');

            // ---------------------------------------------------------------
            // Media
            // ---------------------------------------------------------------
            $table->string('logo', 255)->nullable()
                ->comment('Logo relative storage path');

            // ---------------------------------------------------------------
            // Address
            // ---------------------------------------------------------------
            $table->text('address')
                ->comment('Street address');

            $table->string('city', 100)
                ->comment('City');

            $table->string('province', 100)
                ->comment('Province or state');

            $table->string('country', 100)
                ->comment('Country name');

            $table->string('postal_code', 20)
                ->comment('Postal or ZIP code');

            // ---------------------------------------------------------------
            // Locale
            // ---------------------------------------------------------------
            $table->string('timezone', 100)
                ->comment('IANA timezone identifier e.g. Asia/Jakarta');

            $table->string('currency', 10)
                ->comment('ISO 4217 currency code e.g. IDR');

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
            $table->index('email',   'organizations_email_index');
            $table->index('status',  'organizations_status_index');
            $table->index('country', 'organizations_country_index');
        });

        // ---------------------------------------------------------------
        // PostgreSQL CHECK constraint for status column
        // Ensures only allowed values are inserted at the database level.
        // ---------------------------------------------------------------
        DB::statement("
            ALTER TABLE organizations
            ADD CONSTRAINT organizations_status_check
            CHECK (status IN ('active', 'inactive'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
