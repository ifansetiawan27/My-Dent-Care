<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('scanner_devices', function(Blueprint $t){
            $t->uuid('id')->primary();
            $t->string('device_name',255);
            $t->string('model',255);
            $t->string('serial_number',255)->unique();
            $t->string('manufacturer',255);
            $t->string('firmware_version',50)->nullable();
            $t->string('status',20)->default('active');
            $t->string('location',255)->nullable();
            $t->timestamptz('last_calibration_at')->nullable();
            $t->date('purchase_date')->nullable();
            $t->date('warranty_expiry_date')->nullable();
            $t->timestamptz('created_at')->useCurrent();
            $t->timestamptz('updated_at')->nullable();
            $t->softDeletesTz('deleted_at');
            $t->index('status');
            $t->index('serial_number');
        });
    }
    public function down(): void { Schema::dropIfExists('scanner_devices'); }
};
