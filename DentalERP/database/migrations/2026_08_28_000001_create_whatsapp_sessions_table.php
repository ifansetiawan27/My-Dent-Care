<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('phone_number')->nullable();
            $table->string('display_name')->nullable();
            $table->string('status')->default('disconnected'); // disconnected, qr_ready, connected, error
            $table->text('qr_code')->nullable(); // base64 QR image
            $table->text('session_data')->nullable(); // encrypted session credentials
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_sessions');
    }
};
