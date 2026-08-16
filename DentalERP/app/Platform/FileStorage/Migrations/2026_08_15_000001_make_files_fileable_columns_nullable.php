<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table): void {
            $table->string('fileable_type', 255)->nullable()->change();
            $table->uuid('fileable_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table): void {
            $table->string('fileable_type', 255)->nullable(false)->change();
            $table->uuid('fileable_id')->nullable(false)->change();
        });
    }
};
