<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('organizations', function (Blueprint $t): void {
            $t->string('invoice_prefix', 10)->nullable()->after('logo');
            $t->string('invoice_footer', 500)->nullable()->after('invoice_prefix');
            $t->string('billing_name', 200)->nullable()->after('invoice_footer');
            $t->string('billing_email', 100)->nullable()->after('billing_name');
            $t->string('billing_phone', 20)->nullable()->after('billing_email');
            $t->text('billing_address')->nullable()->after('billing_phone');
        });
    }
    public function down(): void {
        Schema::table('organizations', function (Blueprint $t): void {
            $t->dropColumn(['invoice_prefix','invoice_footer','billing_name','billing_email','billing_phone','billing_address']);
        });
    }
};