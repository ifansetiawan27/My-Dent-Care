<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->integer('reminder_minutes')->nullable()->after('notes')
                  ->comment('Minutes before appointment to send reminder: 30, 60, 120, 240, 360, 720');
            $table->boolean('reminder_sent')->default(false)->after('reminder_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropColumn(['reminder_minutes', 'reminder_sent']);
        });
    }
};
