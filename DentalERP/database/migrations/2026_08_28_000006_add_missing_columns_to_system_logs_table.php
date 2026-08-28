<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_logs', function (Blueprint $table): void {
            $table->jsonb('extra')->nullable();
            $table->text('exception_message')->nullable();
            $table->string('request_id', 36)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('method', 10)->nullable();
            $table->string('url', 2048)->nullable();

            $table->index(['request_id'], 'system_logs_request_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('system_logs', function (Blueprint $table): void {
            $table->dropIndex('system_logs_request_id_idx');
            $table->dropColumn([
                'extra',
                'exception_message',
                'request_id',
                'user_agent',
                'method',
                'url',
            ]);
        });
    }
};
