<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('asset_categories', function (Blueprint $t): void {
            $t->uuid('id')->primary(); $t->string('code', 20)->unique('asset_categories_code_unique'); $t->string('name', 100);
            $t->boolean('is_active')->default(true); $t->uuid('created_by')->nullable(); $t->uuid('updated_by')->nullable(); $t->uuid('deleted_by')->nullable();
            $t->timestamptz('created_at')->useCurrent(); $t->timestamptz('updated_at')->nullable(); $t->softDeletesTz('deleted_at'); $t->index('is_active', 'asset_categories_is_active_idx');
        });
    } public function down(): void { Schema::dropIfExists('asset_categories'); }
};
