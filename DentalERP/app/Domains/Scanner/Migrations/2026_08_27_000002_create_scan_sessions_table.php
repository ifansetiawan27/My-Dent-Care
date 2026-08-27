<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('scan_sessions', function(Blueprint $t){
            $t->uuid('id')->primary();
            $t->uuid('patient_id');
            $t->uuid('doctor_id');
            $t->uuid('device_id');
            $t->string('session_number',30)->unique();
            $t->string('scan_type',20);
            $t->string('status',20)->default('in_progress');
            $t->text('notes')->nullable();
            $t->timestamptz('started_at')->nullable();
            $t->timestamptz('completed_at')->nullable();
            $t->timestamptz('created_at')->useCurrent();
            $t->timestamptz('updated_at')->nullable();
            $t->softDeletesTz('deleted_at');
            $t->foreign('patient_id')->references('id')->on('patients')->onDelete('restrict');
            $t->foreign('doctor_id')->references('id')->on('users')->onDelete('restrict');
            $t->foreign('device_id')->references('id')->on('scanner_devices')->onDelete('restrict');
            $t->index(['patient_id','status']);
            $t->index(['device_id','status']);
            $t->index('session_number');
        });
    }
    public function down(): void { Schema::dropIfExists('scan_sessions'); }
};
