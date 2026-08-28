<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table): void {
            $table->integer('retry_count')->default(0);
            $table->timestamptz('failed_at')->nullable();
            $table->string('external_id', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropColumn(['retry_count', 'failed_at', 'external_id']);
        });
    }
};
