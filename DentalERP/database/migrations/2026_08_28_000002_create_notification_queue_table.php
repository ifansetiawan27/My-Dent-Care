<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_queue', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('channel'); // whatsapp, email, sms
            $table->string('recipient'); // phone number or email
            $table->string('template'); // reminder, confirmation, etc
            $table->json('payload'); // dynamic data for template
            $table->string('status')->default('pending'); // pending, sent, failed, cancelled
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->string('reference_id')->nullable(); // appointment_id, etc
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
            $table->index(['channel', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_queue');
    }
};
