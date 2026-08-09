<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table): void {
            $table->uuid('session_id')->after('tokenable_id');
            $table->unique('session_id');
            $table->foreign('session_id', 'personal_access_tokens_session_id_foreign')
                ->references('id')
                ->on('user_sessions')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table): void {
            $table->dropForeign('personal_access_tokens_session_id_foreign');
            $table->dropUnique(['session_id']);
            $table->dropColumn('session_id');
        });
    }
};
