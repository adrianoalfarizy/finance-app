<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('debts', function (Blueprint $table) {
            $table->decimal('monthly_payment', 16, 2)->default(0)->after('principal_amount');
        });
    }

    public function down(): void
    {
        Schema::table('debts', function (Blueprint $table) {
            $table->dropColumn('monthly_payment');
        });
    }
};
